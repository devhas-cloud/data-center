<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Mail\ResetPasswordMail;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function login(Request $request)
{
    // Rate limiting: max 5 attempts per minute per IP
    $key = 'login-attempts:' . $request->ip();
    
    if (RateLimiter::tooManyAttempts($key, 5)) {
        $seconds = RateLimiter::availableIn($key);
        throw ValidationException::withMessages([
            'username' => ['Too many login attempts. Please try again in ' . ceil($seconds / 60) . ' minutes.'],
        ]);
    }

    // Validate input
    $credentials = $request->validate([
        'username' => 'required|string|max:255',
        'password' => 'required|string'
    ]);

    // Sanitize username
    $credentials['username'] = strip_tags(trim($credentials['username']));

    if (Auth::attempt($credentials, $request->filled('remember'))) {
        $request->session()->regenerate();

        $user = Auth::user();

        // ===========================
        // HAPUS SESSION LAMA USER
        // ===========================
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', session()->getId())
            ->delete();

        // Set user timezone if provided
        $timezone = $request->timezone ?? 'UTC';
        session(['timezone' => $timezone]);
        $user->timezone = $timezone;
        $user->save();

        // Clear rate limiter
        RateLimiter::clear($key);

        // Log successful login
        Log::info('User logged in successfully', [
            'username' => $credentials['username'],
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timezone' => $timezone
        ]);

        // Redirect based on role
        if ($user->role === 'admin') {
            return redirect()->intended('/admin/home');
        } else {
            return redirect()->intended('/user/home');
        }
    }

    // Increment rate limiter
    RateLimiter::hit($key, 60);

    // Log failed login attempt
    Log::warning('Failed login attempt', [
        'username' => $credentials['username'],
        'ip' => $request->ip(),
        'user_agent' => $request->userAgent()
    ]);

    // Generic error message
    return back()->withErrors([
        'username' => 'The provided credentials do not match our records.',
    ])->onlyInput('username');
}


    public function logout(Request $request)
    {
        $username = Auth::user() ? Auth::user()->username : 'unknown';
        
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Log logout
        Log::info('User logged out', [
            'username' => $username,
            'ip' => $request->ip()
        ]);

        return redirect('/');
    }

    public function checkLogin(Request $request)
    {
        if (Auth::check()) {
            if (Auth::user()->role === 'admin') {
                return redirect('/admin/home');
            } else {
                return redirect('/user/home');
            }
        }
        return view('login');
    }


    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        // Rate limiting: max 3 attempts per 5 minutes per IP
        $key = 'password-reset:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors(['username' => 'Too many password reset attempts. Please try again in ' . ceil($seconds / 60) . ' minutes.']);
        }

        $request->validate([
            'username' => 'required|string|max:255'
        ]);
        
        // Sanitize input
        $username = strip_tags(trim($request->username));

        // Get user with email
        $user = User::where('username', $username)->first();

        // Generic response to prevent user enumeration
        $successMessage = 'If your username exists in our system and has an email registered, you will receive a password reset link shortly.';

        if (!$user) {
            RateLimiter::hit($key, 300); // 5 minutes
            
            // Log suspicious activity
            Log::warning('Password reset attempted for non-existent user', [
                'username' => $username,
                'ip' => $request->ip()
            ]);
            
            return back()->with('status', $successMessage);
        }

        if (!$user->email) {
            RateLimiter::hit($key, 300);
            
            Log::warning('Password reset attempted for user without email', [
                'username' => $username,
                'ip' => $request->ip()
            ]);
            
            return back()->with('status', $successMessage);
        }

        // Generate secure random token
        $token = Str::random(64);

        // Delete existing tokens for this user to prevent token accumulation
        DB::table('password_reset_tokens')->where('username', $username)->delete();

        // Insert new token with hashed value
        DB::table('password_reset_tokens')->insert([
            'username' => $username,
            'token' => Hash::make($token),
            'created_at' => Carbon::now()
        ]);

        // Send email
        try {
            Mail::to($user->email)->send(new ResetPasswordMail($token, $user->username));
            
            RateLimiter::hit($key, 300);
            
            Log::info('Password reset email sent', [
                'username' => $username,
                'email' => substr($user->email, 0, 3) . '***' // Partially hide email in logs
            ]);
            
            return back()->with('status', $successMessage);
        } catch (\Exception $e) {
            RateLimiter::hit($key, 300);
            
            // Log error without exposing sensitive details
            Log::error('Failed to send password reset email', [
                'username' => $username,
                'error' => $e->getMessage()
            ]);
            
            // Generic error message - DO NOT expose token in production
            return back()->with('status', 'Unable to send reset email at this time. Please contact administrator if the problem persists.');
        }
    }

    public function showResetPasswordForm($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        // Rate limiting: max 5 attempts per 10 minutes per IP
        $key = 'password-reset-submit:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'token' => ['Too many password reset attempts. Please try again in ' . ceil($seconds / 60) . ' minutes.'],
            ]);
        }

        $request->validate([
            'token' => 'required|string|size:64',
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
        ], [
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.regex' => 'Password harus mengandung minimal 1 huruf besar, 1 huruf kecil, dan 1 angka.'
        ]);
        
        // Sanitize inputs
        $username = strip_tags(trim($request->username));
        $token = $request->token;

        // Check if token exists and is valid (within 60 minutes)
        $passwordReset = DB::table('password_reset_tokens')
            ->where('username', $username)
            ->first();

        if (!$passwordReset) {
            RateLimiter::hit($key, 600); // 10 minutes
            
            Log::warning('Invalid password reset token attempt', [
                'username' => $username,
                'ip' => $request->ip()
            ]);
            
            // Generic error message to prevent enumeration
            return back()->withErrors(['token' => 'Token reset password tidak valid atau telah kadaluarsa.']);
        }

        // Check if token matches
        if (!Hash::check($token, $passwordReset->token)) {
            RateLimiter::hit($key, 600);
            
            Log::warning('Password reset token mismatch', [
                'username' => $username,
                'ip' => $request->ip()
            ]);
            
            return back()->withErrors(['token' => 'Token reset password tidak valid atau telah kadaluarsa.']);
        }

        // Check if token is expired (60 minutes)
        if (Carbon::parse($passwordReset->created_at)->addMinutes(60)->isPast()) {
            RateLimiter::hit($key, 600);
            
            // Delete expired token
            DB::table('password_reset_tokens')->where('username', $username)->delete();
            
            Log::warning('Expired password reset token used', [
                'username' => $username,
                'ip' => $request->ip()
            ]);
            
            return back()->withErrors(['token' => 'Token reset password tidak valid atau telah kadaluarsa.']);
        }

        // Verify user exists
        $user = User::where('username', $username)->first();
        
        if (!$user) {
            RateLimiter::hit($key, 600);
            
            Log::error('Password reset for non-existent user', [
                'username' => $username,
                'ip' => $request->ip()
            ]);
            
            return back()->withErrors(['username' => 'User tidak ditemukan.']);
        }

        // Update user password
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the token after successful password reset
        DB::table('password_reset_tokens')->where('username', $username)->delete();
        
        // Clear rate limiter
        RateLimiter::clear($key);
        
        Log::info('Password successfully reset', [
            'username' => $username,
            'ip' => $request->ip()
        ]);

        return redirect()->route('login')->with('status', 'Password berhasil direset! Silakan login dengan password baru.');
    }
}
