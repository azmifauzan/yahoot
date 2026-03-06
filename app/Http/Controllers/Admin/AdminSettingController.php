<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class AdminSettingController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Settings', [
            'settings' => [
                'app_name' => config('app.name'),
                'app_locale' => config('app.locale'),
                'registration_enabled' => config('fortify.features') && in_array('registration', config('fortify.features', [])),
            ],
        ]);
    }
}
