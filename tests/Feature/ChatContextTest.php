<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\KpspResult;
use App\Models\User;
use App\Models\WhoGrowthStandard;
use App\Services\Chat\ChildContext;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatContextTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-07 08:00:00');
        config(['services.gemini.key' => 'test-key']);

        foreach (['TB/U' => [70.6, 2.6], 'BB/U' => [8.6, 1.0]] as $parameter => [$median, $sd]) {
            WhoGrowthStandard::create([
                'gender' => 'male', 'parameter' => $parameter, 'age_in_months' => 8,
                'sd_3_negatif' => $median - 3 * $sd, 'sd_2_negatif' => $median - 2 * $sd, 'sd_1_negatif' => $median - $sd,
                'sd_median' => $median,
                'sd_1_positif' => $median + $sd, 'sd_2_positif' => $median + 2 * $sd, 'sd_3_positif' => $median + 3 * $sd,
            ]);
        }
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function riskyChild(User $parent): Child
    {
        $child = Child::factory()->create([
            'parent_id' => $parent->id, 'name' => 'Budi Santoso', 'gender' => 'male', 'bod' => '2026-01-27',
            'nik' => '3201010101010001',
        ]);
        $child->measurements()->create(['weight' => 5.0, 'height' => 62.0, 'head_circumference' => 40, 'measured_at' => '2026-10-04']);
        $child->followUps()->create(['action_type' => 'home_visit', 'due_date' => '2026-10-14', 'status' => 'open']);
        KpspResult::create([
            'child_id' => $child->id, 'age_category_id' => 1, 'yes_count' => 5,
            'interpretation' => 'Meragukan', 'intervensi' => 'Lakukan pemeriksaan lanjutan',
        ]);

        return $child;
    }

    public function test_context_contains_measurements_zscores_priority_followups_and_kpsp_but_no_identity(): void
    {
        $parent = User::factory()->create(['parent_name' => 'Ibu Rahasia', 'phone' => '081299998888']);
        $child = $this->riskyChild($parent);

        $text = app(ChildContext::class)->describe($child->fresh());

        $this->assertStringContainsString('Nama panggilan: Budi', $text);
        $this->assertStringNotContainsString('Santoso', $text);
        $this->assertStringNotContainsString('3201010101010001', $text);
        $this->assertStringNotContainsString('Ibu Rahasia', $text);
        $this->assertStringNotContainsString('081299998888', $text);
        $this->assertStringContainsString('Usia: 8 bulan', $text);
        $this->assertStringContainsString('Berat badan: 5 kg (Z-score BB/U -3.60, status: Gizi Buruk)', $text);
        $this->assertStringContainsString('Sangat Pendek', $text);
        $this->assertStringContainsString('Prioritas pemantauan', $text);
        $this->assertStringContainsString('Kunjungan rumah', $text);
        $this->assertStringContainsString('Meragukan', $text);
    }

    public function test_chat_sends_child_context_and_history_to_the_model(): void
    {
        $parent = User::factory()->create();
        $this->riskyChild($parent);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'Halo, Budi tumbuh dengan baik.']]]]],
        ])]);

        $session = ChatSession::create(['user_id' => $parent->id]);
        ChatMessage::create(['chat_session_id' => $session->id, 'sender' => 'user', 'message' => 'Halo']);
        ChatMessage::create(['chat_session_id' => $session->id, 'sender' => 'ai', 'message' => 'Hai Bunda']);

        $this->actingAs($parent)
            ->postJson(route('chat.send'), ['message' => 'Bagaimana kondisi anak saya?', 'session_id' => $session->id])
            ->assertOk()
            ->assertJsonPath('messages.0.message', 'Halo, Budi tumbuh dengan baik.');

        Http::assertSent(function ($request) {
            $system = $request['system_instruction']['parts'][0]['text'];
            $contents = $request['contents'];

            return str_contains($system, 'Nama panggilan: Budi')
                && str_contains($system, 'Berat badan')
                && str_contains($system, 'Jangan mendiagnosis')
                && count($contents) === 3
                && $contents[0]['role'] === 'user' && $contents[1]['role'] === 'model'
                && $contents[2]['parts'][0]['text'] === 'Bagaimana kondisi anak saya?';
        });
    }

    public function test_chat_without_child_gets_general_prompt(): void
    {
        $parent = User::factory()->create();
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'Z-score adalah ...']]]]],
        ])]);

        $this->actingAs($parent)->postJson(route('chat.send'), ['message' => 'Apa itu Z-score?'])->assertOk();

        Http::assertSent(fn($request) => str_contains($request['system_instruction']['parts'][0]['text'], 'Tidak ada data anak'));
    }

    public function test_chat_uses_the_active_child_not_a_siblings_data(): void
    {
        $parent = User::factory()->create();
        $first = Child::factory()->create(['parent_id' => $parent->id, 'name' => 'Pertama', 'gender' => 'male', 'bod' => '2026-01-27']);
        $second = Child::factory()->create(['parent_id' => $parent->id, 'name' => 'Kedua', 'gender' => 'male', 'bod' => '2026-01-27']);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => 'ok']]]]]])]);

        $this->actingAs($parent)->post(route('children.select', $second));
        $this->postJson(route('chat.send'), ['message' => 'Halo'])->assertOk();

        Http::assertSent(fn($r) => str_contains($r['system_instruction']['parts'][0]['text'], 'Nama panggilan: Kedua')
            && !str_contains($r['system_instruction']['parts'][0]['text'], 'Pertama'));
    }

    public function test_chat_page_shows_child_specific_suggestions(): void
    {
        $parent = User::factory()->create();
        Child::factory()->create(['parent_id' => $parent->id, 'name' => 'Mawar Indah', 'gender' => 'female', 'bod' => '2026-01-27']);

        $this->actingAs($parent)->get(route('chat.page'))
            ->assertOk()
            ->assertSee('Jelaskan kondisi pertumbuhan Mawar saat ini')
            ->assertSee('Tahu data Mawar');
    }

    public function test_chat_uses_configured_model_and_reports_api_failures_clearly(): void
    {
        config(['services.gemini.model' => 'model-uji']);
        $parent = User::factory()->create();
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['error' => ['code' => 404, 'message' => 'model gone']], 404)]);

        $this->actingAs($parent)->postJson(route('chat.send'), ['message' => 'Halo'])
            ->assertOk()
            ->assertJsonPath('messages.0.message', 'Maaf, asisten sedang tidak tersedia. Silakan coba lagi nanti atau hubungi kader.');

        Http::assertSent(fn($request) => str_contains($request->url(), '/models/model-uji:generateContent'));
    }
}
