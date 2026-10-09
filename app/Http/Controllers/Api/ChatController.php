<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Services\Chat\AssistantPrompt;
use App\Services\Chat\ChildContext;
use App\Support\ActiveChild;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;

class ChatController extends Controller
{
    /** Jumlah pesan sebelumnya yang dikirim ke model agar percakapan nyambung. */
    private const HISTORY_LIMIT = 8;

    public function __construct(private ChildContext $context)
    {
    }

    public function page(Request $request)
    {
        $child = ActiveChild::resolve($request, Auth::user());

        if ($child) {
            View::share('activeChild', $child);
        }

        return view('pages.users.chat-ai.index', ['child' => $child]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'session_id' => 'nullable|exists:chat_sessions,id',
        ]);

        $userId = Auth::id();

        $session = $request->session_id
            ? ChatSession::where('user_id', $userId)->findOrFail($request->session_id)
            : ChatSession::create(['user_id' => $userId]);

        // Riwayat diambil sebelum pesan baru disimpan.
        $history = $session->messages()->latest('id')->take(self::HISTORY_LIMIT)->get()->reverse()->values();

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender' => 'user',
            'message' => $request->message,
        ]);

        $child = ActiveChild::resolve($request, Auth::user());
        $childContext = $child ? $this->context->describe($child) : null;

        $aiResponse = $this->getAIResponse($request->message, $childContext, $history);

        $aiMessage = ChatMessage::create([
            'chat_session_id' => $session->id,
            'sender' => 'ai',
            'message' => $aiResponse,
        ]);

        return response()->json([
            'session_id' => $session->id,
            'messages' => [$aiMessage],
        ]);
    }

    public function getSession(Request $request)
    {
        $session = ChatSession::where('user_id', Auth::id())->latest()->first();

        return response()->json([
            'session' => $session ? $session->load('messages') : null,
        ]);
    }

    private function getAIResponse(string $message, ?string $childContext, $history): string
    {
        try {
            $apiKey = config('services.sumopod.key');

            if (!$apiKey) {
                \Log::warning('SUMOPOD_API_KEY belum dikonfigurasi.');
                return 'Maaf, layanan asisten belum tersedia.';
            }

            $messages = [['role' => 'system', 'content' => AssistantPrompt::system($childContext)]];
            foreach ($history as $m) {
                $messages[] = ['role' => $m->sender === 'user' ? 'user' : 'assistant', 'content' => $m->message];
            }
            $messages[] = ['role' => 'user', 'content' => $message];

            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout(60)
                ->post(rtrim(config('services.sumopod.url'), '/') . '/chat/completions', [
                    'model' => config('services.sumopod.model'),
                    'messages' => $messages,
                    'max_tokens' => 800,
                    'temperature' => 0.4,
                ]);

            $text = $response->json('choices.0.message.content');

            if (is_string($text) && trim($text) !== '') {
                return trim($text);
            }

            \Log::error('Sumopod API error (' . $response->status() . '): ' . $response->body());
            return 'Maaf, asisten sedang tidak tersedia. Silakan coba lagi nanti atau hubungi kader.';
        } catch (\Exception $e) {
            \Log::error('Error calling Sumopod API: ' . $e->getMessage());
            return 'Maaf, terjadi kesalahan saat menghubungi layanan AI.';
        }
    }
}
