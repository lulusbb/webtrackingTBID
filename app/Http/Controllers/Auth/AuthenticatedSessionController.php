<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */

    public function store(LoginRequest $request)
    {
        $request->authenticate();
        $request->session()->regenerate();

        $role = strtolower($request->user()->role ?? '');

        // hitung tujuan per role (paksa per role, abaikan intended)
        $dest = match ($role) {
            'admin'     => route('admin.dashboard'),
            'marketing' => route('marketing.dashboard'),
            'studio'    => route('studio.dashboard'),
            'project'   => route('project.dashboard'),
            default     => url('/'),
        };

        // buang intended supaya tidak kembali ke halaman yang disimpan sebelumnya
        $request->session()->forget('url.intended');

        return redirect()->to($dest);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login'); // arahkan ke login
    }
}
