<?php

namespace App\Http\Controllers;

use App\Enums\LlmProvider;
use App\Http\Requests\UpdateLlmSettingRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class LlmSettingController extends Controller
{
    public function edit(): Response
    {
        $setting = request()->user()->llmSetting;

        return Inertia::render('Settings/AiSettings', [
            'llmSetting' => [
                'provider' => $setting?->provider?->value ?? LlmProvider::OpenAI->value,
                'model' => $setting?->model ?? '',
                'base_url' => $setting?->base_url ?? '',
                'has_api_key' => ! empty($setting?->api_key),
            ],
            'providerDefaults' => collect(LlmProvider::cases())->mapWithKeys(fn (LlmProvider $provider) => [
                $provider->value => [
                    'base_url' => $provider->defaultBaseUrl(),
                    'model' => $provider->defaultModel(),
                ],
            ]),
        ]);
    }

    public function update(UpdateLlmSettingRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = $request->user();

        $attributes = [
            'provider' => $data['provider'],
            'model' => $data['model'],
            'base_url' => $data['base_url'] ?? null,
        ];

        if (! empty($data['api_key'])) {
            $attributes['api_key'] = $data['api_key'];
        }

        $setting = $user->llmSetting()->first();

        if ($setting) {
            $setting->update($attributes);
        } else {
            $user->llmSetting()->create([...$attributes, 'api_key' => $data['api_key'] ?? null]);
        }

        return back();
    }
}
