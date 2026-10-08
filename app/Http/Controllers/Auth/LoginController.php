<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    // protected $redirectTo = '/home';
    protected function redirectTo()
    {
        $role = Auth::user()->roles;
        return match (true) {
            $role?->isAdmin() => '/admin/dashboard',
            $role === \App\Enums\UserRole::Parent => '/users',
            $role?->isStaff() => '/kader',
            default => '/',
        };
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    protected function credentials(\Illuminate\Http\Request $request)
    {
        return [
            'phone' => $request->input('phone'),
            'password' => $request->input('password'),
        ];
    }

    public function username()
    {
        return 'phone';
    }
}
