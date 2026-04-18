<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(Request $request): View|RedirectResponse
    {
        if ($request->session()->get('admin.authenticated')) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $adminEmail = env('ADMIN_PANEL_EMAIL', 'admin@splitmate.local');
        $adminPassword = env('ADMIN_PANEL_PASSWORD', 'splitmate-admin');

        if ($credentials['email'] !== $adminEmail || $credentials['password'] !== $adminPassword) {
            return back()
                ->withErrors(['email' => 'Invalid admin credentials.'])
                ->withInput($request->except('password'));
        }

        $request->session()->put('admin.authenticated', true);
        $request->session()->put('admin.email', $credentials['email']);

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['admin.authenticated', 'admin.email']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
