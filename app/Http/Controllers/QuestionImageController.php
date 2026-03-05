<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuestionImageController extends Controller
{
    public function show(string $path): StreamedResponse
    {
        $disk = Storage::disk(config('quiz.image_disk'));

        abort_unless($disk->exists($path), 404);

        $mimeType = $disk->mimeType($path) ?: 'image/jpeg';

        return response()->stream(function () use ($disk, $path): void {
            echo $disk->get($path);
        }, 200, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
}
