<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function page(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        // if (! $request->user()->is_admin) {
        //     Auth::logout();
        //     $request->session()->invalidate();
        //     $request->session()->regenerateToken();

        //     return back()->withErrors([
        //         'email' => 'Only administrator accounts can access this application.',
        //     ]);
        // }

        return redirect()->intended(route('dashboard'))->with('success', 'Welcome back.');
    }

    public function destroy(): RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }
}
