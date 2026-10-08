<?php

namespace App\Http\Controllers\Kader;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\RiskAssessment;
use App\Models\User;
use App\Models\UserMeasurement;
use App\Support\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Halaman kader/nakes: daftar anak yang ditangani, pendaftaran anak, dan pencatatan pengukuran.
 * Nakes hanya membaca (dijaga oleh ChildPolicy dan middleware pada route tulis).
 */
class ChildController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $level = $request->input('level');
        $missed = $request->boolean('missed');

        $latestScore = RiskAssessment::select('score')
            ->whereColumn('child_id', 'children.id')
            ->latest('computed_at')->latest('id')->limit(1);

        // Urutan utama: skor prioritas tertinggi, lalu nama.
        $children = Child::query()
            ->visibleTo(Auth::user())
            ->addSelect(['children.*', 'risk_score' => $latestScore])
            ->with('latestMeasurement', 'latestAssessment', 'parent:id,parent_name,phone')
            ->when($search !== '', fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->when(in_array($level, ['tinggi', 'sedang', 'rendah'], true), fn($q) => $q->whereHas('latestAssessment', fn($a) => $a->where('level', $level)))
            ->when($missed, fn($q) => $q->whereHas('latestAssessment', fn($a) => $a->where(fn($w) => $w
                ->whereJsonContains('factors', ['code' => 'monitoring_overdue'])
                ->orWhereJsonContains('factors', ['code' => 'monitoring_long_overdue'])
            )))
            ->orderByRaw('risk_score IS NULL')
            ->orderByDesc('risk_score')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('pages.kader.children.index', [
            'children' => $children,
            'search' => $search,
            'level' => $level,
            'missed' => $missed,
            'summary' => $this->summary(Auth::user()),
        ]);
    }

    /** Ringkasan untuk kartu di bagian atas daftar: jumlah per level, pemantauan terlewat, tindak lanjut terlambat. */
    private function summary($user): array
    {
        $visible = Child::visibleTo($user)->select('children.id');
        $latestIds = RiskAssessment::selectRaw('MAX(id)')->whereIn('child_id', $visible)->groupBy('child_id');

        $levels = RiskAssessment::whereIn('id', $latestIds)->selectRaw('level, COUNT(*) AS total')->groupBy('level')->pluck('total', 'level');

        $missed = RiskAssessment::whereIn('id', $latestIds)
            ->where(fn($w) => $w->whereJsonContains('factors', ['code' => 'monitoring_overdue'])
                ->orWhereJsonContains('factors', ['code' => 'monitoring_long_overdue']))
            ->count();

        return [
            'tinggi' => (int) ($levels['tinggi'] ?? 0),
            'sedang' => (int) ($levels['sedang'] ?? 0),
            'rendah' => (int) ($levels['rendah'] ?? 0),
            'missed' => $missed,
            'overdue_followups' => \App\Models\FollowUp::active()
                ->whereIn('child_id', $visible)
                ->whereDate('due_date', '<', now()->toDateString())
                ->count(),
        ];
    }

    public function create()
    {
        return view('pages.kader.children.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:male,female'],
            'bod' => ['required', 'date', 'before_or_equal:today'],
            'parent_phone' => ['nullable', 'string', 'max:15'],
        ]);

        $kader = Auth::user();

        // Orang tua dihubungkan lewat nomor HP bila sudah punya akun. Bila belum, nomor disimpan
        // dan anak tertaut otomatis saat akun dengan nomor itu dibuat.
        $phone = Phone::digits($data['parent_phone'] ?? null);
        $parent = $phone !== ''
            ? User::where('roles', UserRole::Parent->value)->get(['id', 'phone'])->first(fn($u) => Phone::digits($u->phone) === $phone)
            : null;

        $child = DB::transaction(function () use ($data, $kader, $parent, $phone) {
            $child = Child::create([
                'parent_id' => $parent?->id,
                'parent_phone' => $parent || $phone === '' ? null : $phone,
                'registered_by' => $kader->id,
                'name' => $data['name'],
                'gender' => $data['gender'],
                'bod' => $data['bod'],
                'village_id' => $kader->village_id,
            ]);

            $child->staff()->attach($kader->id, ['role' => $kader->roles->value]);

            return $child;
        });

        return redirect()->route('kader.children.show', $child)->with('success', 'Anak berhasil didaftarkan.');
    }

    public function show(Child $child)
    {
        $this->authorize('view', $child);

        $child->load('parent', 'measurements', 'kpspResults', 'latestAssessment', 'followUps.assignee', 'followUps.baseline', 'followUps.outcome');

        $ordered = $child->measurements->sortBy('measured_at')->values();
        $chart = [
            'labels' => $ordered->map(fn($m) => ($m->measured_at ?? $m->created_at)->translatedFormat('d M y'))->all(),
            'weight' => $ordered->map(fn($m) => $m->weight !== null ? (float) $m->weight : null)->all(),
            'height' => $ordered->map(fn($m) => $m->height !== null ? (float) $m->height : null)->all(),
        ];

        $overdue = collect($child->latestAssessment?->factors ?? [])->contains(fn($f) => str_starts_with($f['code'], 'monitoring_'));
        $whatsapp = Phone::whatsappUrl(
            $child->parent?->phone ?? $child->parent_phone,
            sprintf(
                'Halo, saya %s dari layanan pemantauan tumbuh kembang. Mengenai %s (%d bulan): %s',
                Auth::user()->name,
                $child->name,
                $child->ageInMonths(),
                $overdue ? 'sudah waktunya diukur kembali. Kapan kira-kira bisa datang?' : 'mohon konfirmasi jadwal pemantauan berikutnya.',
            ),
        );

        return view('pages.kader.children.show', [
            'child' => $child,
            'chart' => $chart,
            'whatsapp' => $whatsapp,
            'timeline' => app(\App\Services\Risk\ChildTimeline::class)->for($child),
            'measurements' => $child->measurements->sortByDesc('measured_at')->values(),
            'assessment' => $child->latestAssessment,
            'followUps' => $child->followUps,
            'canCreateFollowUp' => Auth::user()->can('create', [\App\Models\FollowUp::class, $child]),
            'assignees' => $child->staff()->get(['users.id', 'users.name'])->push(Auth::user())->unique('id')->values(),
            'canRecord' => Auth::user()->can('recordMeasurement', $child),
        ]);
    }

    public function storeMeasurement(Request $request, Child $child)
    {
        $this->authorize('recordMeasurement', $child);

        $data = $request->validate([
            'weight' => ['required', 'numeric', 'between:0.5,30'],
            'height' => ['required', 'numeric', 'between:20,130'],
            'head_circumference' => ['nullable', 'numeric', 'between:20,60'],
            'arm_circumference' => ['nullable', 'numeric', 'between:5,30'],
            'measured_at' => ['required', 'date', 'before_or_equal:today', 'after_or_equal:' . $child->bod->toDateString()],
        ]);

        UserMeasurement::create($data + [
            'child_id' => $child->id,
            'measured_by' => Auth::id(),
        ]);

        return redirect()->route('kader.children.show', $child)->with('success', 'Pengukuran tersimpan.');
    }
}
