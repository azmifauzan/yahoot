<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    private const DEFAULTS = [
        'app_name' => 'Yahoot',
        'default_language' => 'id',
        'allow_registration' => '1',
        'require_email_verification' => '0',
        'allow_guest_play' => '1',
        'maintenance_mode' => '0',
    ];

    public function index(): Response
    {
        $settings = AppSetting::all(array_keys(self::DEFAULTS));

        foreach (self::DEFAULTS as $key => $default) {
            if (! isset($settings[$key])) {
                $settings[$key] = $default;
            }
        }

        return Inertia::render('Admin/Settings', ['settings' => $settings]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => 'sometimes|string|max:100',
            'default_language' => 'sometimes|in:id,en',
            'allow_registration' => 'sometimes|boolean',
            'require_email_verification' => 'sometimes|boolean',
            'allow_guest_play' => 'sometimes|boolean',
            'maintenance_mode' => 'sometimes|boolean',
        ]);

        foreach ($validated as $key => $value) {
            AppSetting::set($key, (string) (int) (bool) $value);
        }

        if (isset($validated['app_name'])) {
            AppSetting::set('app_name', $validated['app_name']);
        }

        if (isset($validated['default_language'])) {
            AppSetting::set('default_language', $validated['default_language']);
        }

        return back();
    }
}
