<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show()
    {
        return inertia('Auth/Login');
    }

    public function login(Request $request)
    {
        $cred = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($cred)) {
            return back()->withErrors(['email' => 'Invalid credentials'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = $request->user();
        $scopes = ['bookings:update'];

        $token = $user->createToken('SPA PAT', $scopes)->accessToken;

        session([
            'pat' => $token,
            'pat_scopes' => $scopes
        ]);

        return redirect()->intended('/bookings');
    }

    public function logout(Request $request)
    {
        if ($request->user() && $request->user()->token()) {
            $request->user()->token()->revoke();
        }

        session()->forget(['pat', 'pat_scopes']);
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
