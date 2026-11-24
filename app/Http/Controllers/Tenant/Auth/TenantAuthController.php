<?php

namespace App\Http\Controllers\Tenant\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TenantAuthController extends Controller
{
    /**
     * Show tenant login form.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::guard('tenant_web')->check()) {
            return redirect()->route('tenant.products.index');
        }

        return view('tenant.auth.login');
    }

    /**
     * Handle tenant login submission.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ], [
            'login.required' => 'মোবাইল নম্বর অথবা ইমেইল দিন।',
            'password.required' => 'পাসওয়ার্ড দিন।',
        ]);

        $login = trim($request->input('login'));
        $loginField = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $credentials = [
            $loginField => $login,
            'password' => $request->input('password'),
        ];

        $remember = $request->boolean('remember');

        if (Auth::guard('tenant_web')->attempt($credentials, $remember)) {
            $user = Auth::guard('tenant_web')->user();

            if (!$user->is_active) {
                Auth::guard('tenant_web')->logout();
                return back()->with('error', 'আপনার দোকানদারের একাউন্টটি নিষ্ক্রিয় অবস্থায় আছে। অ্যাডমিনের সাথে যোগাযোগ করুন।');
            }

            $request->session()->regenerate();

            return redirect()->intended(route('tenant.products.index'))
                ->with('success', "স্বাগতম, {$user->name}! আপনার দোকানে সফলভাবে প্রবেশ করেছেন।");
        }

        return back()
            ->withInput($request->only('login', 'remember'))
            ->withErrors(['login' => 'প্রদত্ত মোবাইল/ইমেইল অথবা পাসওয়ার্ড সঠিক নয়।']);
    }

    /**
     * Handle tenant logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('tenant_web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('tenant.login')->with('success', 'সফলভাবে লগআউট হয়েছেন।');
    }
}

