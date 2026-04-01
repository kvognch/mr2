<?php

namespace App\Http\Controllers;

use App\Enums\ServiceRequestStatus;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Support\GoogleRecaptcha;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PublicFormController extends Controller
{
    public function storeRequest(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('requestModal', [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:64'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'source_url' => ['nullable', 'string', 'max:2048'],
            'g-recaptcha-response' => ['required', 'string'],
        ]);

        $formattedPhone = User::formatPhone($data['phone']);

        if (! $formattedPhone || ! str_starts_with($formattedPhone, '+7')) {
            $this->throwBaggedValidationException('requestModal', [
                'phone' => 'Введите телефон в формате +7 (999) 999-99-99.',
            ]);
        }

        GoogleRecaptcha::assertValid($data['g-recaptcha-response'], 'requestModal');

        ServiceRequest::query()->create([
            'name' => $data['name'],
            'phone' => $formattedPhone,
            'comment' => $data['comment'] ?: null,
            'status' => ServiceRequestStatus::Pending,
            'source_url' => $data['source_url'] ?: $request->headers->get('referer'),
        ]);

        return back()
            ->with('open_request_modal', true)
            ->with('request_modal_success', 'Заявка отправлена.');
    }

    private function throwBaggedValidationException(string $bag, array $messages): never
    {
        $exception = ValidationException::withMessages($messages);
        $exception->errorBag = $bag;

        throw $exception;
    }
}
