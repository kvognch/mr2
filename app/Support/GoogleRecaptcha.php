<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class GoogleRecaptcha
{
    public static function assertValid(string $token, string $errorBag): void
    {
        $settings = HomepageSettings::all();
        $secretKey = $settings['google_recaptcha']['secret_key'] ?? null;

        if (! $secretKey) {
            static::throwValidationException($errorBag, 'Не настроена Google reCAPTCHA.');
        }

        $response = Http::asForm()
            ->timeout(10)
            ->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secretKey,
                'response' => $token,
            ]);

        if (! $response->ok() || ! $response->json('success')) {
            static::throwValidationException($errorBag, 'Подтвердите, что вы не робот.');
        }
    }

    public static function throwValidationException(string $errorBag, string $message): never
    {
        $exception = ValidationException::withMessages([
            'captcha' => $message,
        ]);
        $exception->errorBag = $errorBag;

        throw $exception;
    }
}
