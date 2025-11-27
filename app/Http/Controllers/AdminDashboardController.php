<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DownloadLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AdminDashboardController extends PublicDashboardController
{
    /**
     * Override landing page for admin
     */
    public function landing()
    {
        // Reuse logic from parent but render admin view
        $viewData = $this->getLandingData();
        return view('admin.landing', $viewData);
    }

    /**
     * Override data page for admin
     */
    public function data(Request $request)
    {
        $viewData = $this->getDataViewData($request);
        return view('admin.data', $viewData);
    }

    /**
     * Override charts page for admin
     */
    public function charts(Request $request)
    {
        $viewData = $this->getChartsViewData($request);
        return view('admin.charts', $viewData);
    }

    /**
     * Override compare page for admin
     */
    public function compare(Request $request)
    {
        $viewData = $this->getCompareViewData($request);
        return view('admin.compare', $viewData);
    }

    /**
     * Admin Import Page
     */
    public function import()
    {
        return view('admin.import', [
            'title' => 'Import Data',
        ]);
    }

    /**
     * Admin Download Logs Page
     */
    public function downloadLogs()
    {
        // Mark all unseen logs as seen
        DownloadLog::where('is_seen', false)->update(['is_seen' => true]);

        $logs = DownloadLog::latest()->paginate(20);
        return view('admin.download-logs', [
            'title' => 'Log Download User',
            'logs' => $logs,
        ]);
    }

    /**
     * Get the count of unseen download logs.
     */
    public function getUnseenCount()
    {
        $count = DownloadLog::where('is_seen', false)->count();
        return response()->json(['count' => $count]);
    }

    /**
     * Admin Account Page
     */
    public function account()
    {
        return view('admin.account', [
            'title' => 'Pengaturan Akun',
            'user' => Auth::user(),
        ]);
    }

    /**
     * Update Admin Account
     */
    public function updateAccount(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'avatar' => ['nullable', 'image', 'max:2048'], // Max 2MB
            'banner' => ['nullable', 'image', 'max:5120'], // Max 5MB
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }

        // Handle Avatar Upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar && file_exists(public_path($user->avatar))) {
                unlink(public_path($user->avatar));
            }
            
            $avatarName = 'avatar_' . $user->id . '_' . time() . '.' . $request->avatar->extension();
            $request->avatar->move(public_path('avatars'), $avatarName);
            $user->avatar = 'avatars/' . $avatarName;
        }

        // Handle Banner Upload
        if ($request->hasFile('banner')) {
            // Delete old banner if exists
            if ($user->banner && file_exists(public_path($user->banner))) {
                unlink(public_path($user->banner));
            }

            $bannerName = 'banner_' . $user->id . '_' . time() . '.' . $request->banner->extension();
            $request->banner->move(public_path('banners'), $bannerName);
            $user->banner = 'banners/' . $bannerName;
        }

        $user->save();

        return back()->with('status', 'profile-updated');
    }

    /**
     * Admin Data Fullscreen
     */
    public function fullscreen(Request $request)
    {
        $viewData = $this->getFullscreenViewData($request);
        return view('admin.data-fullscreen', $viewData);
    }

    /**
     * Admin Charts Fullscreen
     */
    public function chartsFullscreen(Request $request)
    {
        $viewData = $this->getChartsFullscreenViewData($request);
        return view('admin.charts-fullscreen', $viewData);
    }

    /**
     * Admin Compare Fullscreen
     */
    public function compareFullscreen(Request $request)
    {
        $viewData = $this->getCompareFullscreenViewData($request);
        return view('admin.compare-fullscreen', $viewData);
    }
}
