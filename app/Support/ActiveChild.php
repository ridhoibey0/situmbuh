<?php

namespace App\Support;

use App\Models\Child;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Anak yang sedang dilihat orang tua: yang dipilih di session, atau anak pertama.
 */
class ActiveChild
{
    public const SESSION_KEY = 'active_child_id';

    public static function resolve(Request $request, ?User $user): ?Child
    {
        if (!$user) {
            return null;
        }

        $children = $user->children()->orderBy('id')->get();

        if ($children->isEmpty()) {
            return null;
        }

        $child = $children->firstWhere('id', $request->session()->get(self::SESSION_KEY)) ?? $children->first();
        $request->session()->put(self::SESSION_KEY, $child->id);

        return $child;
    }
}
