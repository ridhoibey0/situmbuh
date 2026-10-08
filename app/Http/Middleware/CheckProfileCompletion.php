<?php

namespace App\Http\Middleware;

use App\Support\ActiveChild;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memastikan akun orang tua lengkap dan memiliki anak aktif.
 *
 * Anak aktif diambil dari session (dipilih di halaman "Data Anak"), atau anak pertama
 * bila belum memilih. Hasilnya tersedia di $request->attributes->get('child') dan
 * sebagai variabel $activeChild di semua view.
 */
class CheckProfileCompletion
{
    public const SESSION_KEY = ActiveChild::SESSION_KEY;

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        if (!$user->name || !$user->parent_name || !$user->phone) {
            return redirect()->route('profile.index')
                ->with('message', 'Silakan lengkapi profil Anda terlebih dahulu.');
        }

        $child = ActiveChild::resolve($request, $user);

        if (!$child) {
            return redirect()->route('children.create')
                ->with('message', 'Silakan tambahkan data anak terlebih dahulu.');
        }

        $request->attributes->set('child', $child);
        View::share('activeChild', $child);

        return $next($request);
    }
}
