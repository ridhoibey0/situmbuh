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
            $apiKey = config('services.gemini.key');

            if (!$apiKey) {
                \Log::warning('GEMINI_API_KEY belum dikonfigurasi.');
                return 'Maaf, layanan asisten belum tersedia.';
            }

            $contents = $history->map(fn($m) => [
                'role' => $m->sender === 'user' ? 'user' : 'model',
                'parts' => [['text' => $m->message]],
            ])->all();
            $contents[] = ['role' => 'user', 'parts' => [['text' => $message]]];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $apiKey,
            ])->post('https://generativelanguage.googleapis.com/v1beta/models/' . config('services.gemini.model') . ':generateContent', [
                'system_instruction' => ['parts' => [['text' => AssistantPrompt::system($childContext)]]],
                'contents' => $contents,
            ]);

            $responseData = $response->json();

            if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
                return $responseData['candidates'][0]['content']['parts'][0]['text'];
            }

            // Penyebab umum: model dihentikan (404), kredit/kuota habis, atau kunci tidak valid.
            \Log::error('Gemini API error (' . $response->status() . '): ' . json_encode($responseData));
            return 'Maaf, asisten sedang tidak tersedia. Silakan coba lagi nanti atau hubungi kader.';
        } catch (\Exception $e) {
            \Log::error('Error calling Gemini API: ' . $e->getMessage());
            return 'Maaf, terjadi kesalahan saat menghubungi layanan AI.';
        }
    }
}
