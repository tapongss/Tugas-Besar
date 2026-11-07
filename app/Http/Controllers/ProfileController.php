<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('profile.edit', [
            'user' => $user,
            'stats' => [
                'schedules' => $user->defenseSessions()->count(),
                'activities' => $user->activityLogs()->count(),
                'last_login' => $user->last_login_at,
                'has_token' => filled($user->getRawOriginal('api_token')),
            ],
            'recentActivities' => ActivityLog::where('user_id', $user->id)->latest()->limit(5)->get(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($user)],
            'phone' => ['nullable', 'string', 'max:30'],
        ], [
            'email.unique' => 'Email sudah digunakan akun lain.',
        ]);

        $user->update($data);

        ActivityLogger::log('profile.updated', $user, 'Memperbarui profil akun.');

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'current_password.current_password' => 'Kata sandi saat ini tidak sesuai.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
        ]);

        $request->user()->update(['password' => $data['password']]);

        ActivityLogger::log('profile.password', $request->user(), 'Mengubah kata sandi akun.');

        return back()->with('success', 'Kata sandi berhasil diperbarui.');
    }

    public function regenerateToken(Request $request): RedirectResponse
    {
        $token = $request->user()->regenerateApiToken();

        ActivityLogger::log('profile.token', $request->user(), 'Membuat ulang token API.');

        return redirect()->route('profile.edit')
            ->with('success', 'Token API baru berhasil dibuat. Simpan token ini sekarang karena tidak akan ditampilkan lagi.')
            ->with('api_token', $token);
    }
}
