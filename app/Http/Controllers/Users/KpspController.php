<?php

namespace App\Http\Controllers\Users;
use App\Http\Controllers\Controller;
use App\Models\Province;
use App\Models\User;
use App\Models\KpspQuestion;
use App\Models\KpspResult;
use App\Models\KpspAnswer;
use App\Models\Testimonial;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class KpspController extends Controller
{
    public function index()
    {
        return view('pages.users.questioner.index');
    }
    public function question(Request $request, $id)
    {
        $user = Auth::user();
        $child = $request->attributes->get('child');
        $this->authorize('recordMeasurement', $child);
        $ageInMonths = $child->ageInMonths();
        $questions = KpspQuestion::whereHas('ageCategory', function ($query) use ($ageInMonths) {
            $query->where('max_age', '>=', $ageInMonths)->where('min_age', '<=', $ageInMonths);
        })
            ->where('category_id', $id)
            ->with('ageCategory', 'category')
            ->get();
        if ($questions->isEmpty()) {
            return view('pages.users.question.kpsp.not-available', compact('user', 'ageInMonths'));
        }

        return view('pages.users.question.kpsp.index', compact('user', 'questions'));
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $child = $request->attributes->get('child');
            $this->authorize('recordMeasurement', $child);

            $answers = $request->input('answers');
            $yesCount = count(array_filter($answers, fn($answer) => $answer == 'true'));
            $noCount = count(array_filter($answers, fn($answer) => $answer == 'false'));

            $categoryId = $request->input('category_id');
            $rules = $this->getInterpretationRules((int) $categoryId);
            $interpretation = null;
            $intervensi = null;
            usort($rules, function ($a, $b) {
                return $b['threshold'] <=> $a['threshold'];
            });
            foreach ($rules as $rule) {
                if ($categoryId == 2) {
                    if ($noCount == 0) {
                        $interpretation = 'Sesuai umur';
                        $intervensi = 'Selamat! Anak anda berkembang dengan sangat baik sesuai usianya. Teruskan stimulasi yang sudah dilakukan agar perkembangannya semakin optimal.';
                        break;
                    } elseif ($noCount >= $rule['threshold']) {
                        $interpretation = $rule['interpretation'];
                        $intervensi = $rule['intervensi'];
                        break;
                    }
                } elseif ($categoryId == 6) {
                    if ($noCount >= $rule['threshold']) {
                        $interpretation = $rule['interpretation'];
                        $intervensi = $rule['intervensi'];
                        break;
                    }
                } else {
                    if ($yesCount >= $rule['threshold']) {
                        $interpretation = $rule['interpretation'];
                        $intervensi = $rule['intervensi'];
                        break;
                    }
                }
            }

            $result = KpspResult::create([
                'user_id' => Auth::id(),
                'child_id' => $child->id,
                'age_category_id' => $request->input('category_id'),
                'yes_count' => $yesCount,
                'interpretation' => $interpretation,
                'intervensi' => $intervensi,
            ]);

            foreach ($answers as $questionId => $answer) {
                KpspAnswer::create([
                    'kpsp_result_id' => $result->id,
                    'question_id' => $questionId,
                    'answer' => filter_var($answer, FILTER_VALIDATE_BOOLEAN),
                ]);
            }
            DB::commit();

            return redirect()->route('kpsp.result', $result->id);
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }

    public function result($id)
    {
        $result = KpspResult::whereIn('child_id', \App\Models\Child::visibleTo(Auth::user())->select('children.id'))
            ->where('id', $id)
            ->first();

        $sudahTestimoni = Testimonial::where('user_id', Auth::user()->id)->exists();

        $tampilkanModalTestimoni = false;

        if (KpspResult::where('user_id', Auth::user()->id)->count() && !$sudahTestimoni) {
            $tampilkanModalTestimoni = true;
        }
        if (!$result) {
            return abort(404);
        }
        return view('pages.users.question.kpsp.result', compact('result', 'tampilkanModalTestimoni'));
    }

    private function getInterpretationRules($categoryId)
    {
        $rules = [
            1 => [
                [
                    'threshold' => 9,
                    'interpretation' => 'Sesuai umur',
                    'intervensi' => 'Selamat! Anak anda berkembang dengan sangat baik sesuai usianya. Teruskan stimulasi yang sudah dilakukan agar perkembangannya semakin optimal.',
                ],
                [
                    'threshold' => 7,
                    'interpretation' => 'Meragukan',
                    'intervensi' => 'Hasil pemeriksaan menunjukkan keraguan terhadap perkembangan anak. Dianjurkan untuk melakukan pemeriksaan lanjutan atau konsultasi dengan tenaga kesehatan terkait.',
                ],
                [
                    'threshold' => 0,
                    'interpretation' => 'Ada kemungkinan penyimpangan',
                    'intervensi' => 'Kami melihat bahwa perkembangan anak Anda membutuhkan perhatian lebih. Segera konsultasikan ke dokter spesialis anak atau fasilitas kesehatan untuk evaluasi lebih lanjut.',
                ],
            ],
            2 => [
                [
                    'threshold' => 1,
                    'interpretation' => 'Ada kemungkinan penyimpangan',
                    'intervensi' => 'Rujuk ke RS rujukan tumbuh kembang level 1.',
                ],
            ],
            3 => [['threshold' => 0, 'interpretation' => 'Curiga kelainan pupil putih pada anak', 'intervensi' => 'Rujuk ke RS rujukan tumbuh kembang level 1'], ['threshold' => 2, 'interpretation' => 'Sesuai umur', 'intervensi' => 'Berikan pujian kepada orang tua atau pengasuh dan anak. Lanjutkan stimulasi sesuai umur.']],
            4 => [
                [
                    'threshold' => 0,
                    'interpretation' => 'Daya lihat anak kurang (visus <6/12 atau <6/60)',
                    'intervensi' => 'Rujuk ke RS rujukan tumbuh kembang level 1',
                ],
                [
                    'threshold' => 1,
                    'interpretation' => 'Daya lihat anak baik (visus >6/12 atau >6/60)',
                    'intervensi' => 'Berikan pujian kepada orang tua atau pengasuh dan anak. Lanjutkan stimulasi sesuai umur.',
                ],
            ],
            5 => [
                [
                    'threshold' => 0,
                    'interpretation' => 'Normal',
                    'intervensi' => 'Baik',
                ],
                [
                    'threshold' => 1,
                    'interpretation' => 'Kemungkinan anak mengalami masalah perilaku emosional (meragukan)',
                    'intervensi' => 'Kemungkinan anak mengalami masalah perilaku emosional (meragukan)',
                ],
                [
                    'threshold' => 2,
                    'interpretation' => 'Kemungkinan anak mengalami masalah perilaku emosional',
                    'intervensi' => 'Kemungkinan anak mengalami masalah perilaku emosional',
                ],
            ],
            6 => [
                [
                    'threshold' => 0,
                    'interpretation' => 'Risiko rendah gangguan spektrum autisme',
                    'intervensi' => 'Selamat! Anak anda',
                ],
                [
                    'threshold' => 3,
                    'interpretation' => 'Risiko sedang-tinggi gangguan spektrum autisme',
                    'intervensi' => 'Rujuk ke RS Tumbuh kembang level 1',
                ],
            ],
        ];
        return $rules[$categoryId] ?? [];
    }
}
