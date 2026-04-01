<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\GoogleRecaptcha;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthModalController extends Controller
{
    private const PHONE_REGEX = '/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/';

    public function redirectToLogin(): RedirectResponse
    {
        return redirect()
            ->route('home')
            ->with('open_auth_modal', 'login');
    }

    public function redirectToRegister(): RedirectResponse
    {
        return redirect()
            ->route('home')
            ->with('open_auth_modal', 'register');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('authLogin', [
            'identifier' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $user = $this->findUserByIdentifier($data['identifier']);

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            $this->throwBaggedValidationException('authLogin', [
                'identifier' => 'Неверный логин или пароль.',
            ]);
        }

        if ($user->isClient() && ! $user->isActive()) {
            $this->throwBaggedValidationException('authLogin', [
                'identifier' => 'Аккаунт ещё не подтверждён. Дождитесь подтверждения администратора.',
            ]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return back();
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('authRegister', [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:64', 'regex:' . self::PHONE_REGEX],
            'password' => ['required', 'string', 'confirmed', 'min:8', 'max:255'],
            'g-recaptcha-response' => ['required', 'string'],
        ]);

        $formattedPhone = User::formatPhone($data['phone']);

        if (User::query()->where('phone', $formattedPhone)->exists()) {
            $this->throwBaggedValidationException('authRegister', [
                'phone' => 'Пользователь с таким телефоном уже зарегистрирован.',
            ]);
        }

        $email = mb_strtolower(trim($data['email']));

        if (User::query()->where('email', $email)->exists()) {
            $this->throwBaggedValidationException('authRegister', [
                'email' => 'Пользователь с таким email уже зарегистрирован.',
            ]);
        }

        GoogleRecaptcha::assertValid($data['g-recaptcha-response'], 'authRegister');

        User::query()->create([
            'name' => $data['name'],
            'phone' => $formattedPhone,
            'email' => $email,
            'role' => UserRole::Client,
            'is_active' => true,
            'password' => $data['password'],
        ]);

        return back()
            ->with('open_auth_modal', 'login')
            ->with('auth_register_success', 'Регистрация завершена. Теперь вы можете войти в личный кабинет.');
    }

    private function findUserByIdentifier(string $identifier): ?User
    {
        $identifier = trim($identifier);
        $formattedPhone = User::formatPhone($identifier);

        return User::query()
            ->when(
                filter_var($identifier, FILTER_VALIDATE_EMAIL),
                fn ($query) => $query->where('email', mb_strtolower($identifier)),
                fn ($query) => $query->where('phone', $formattedPhone ?: $identifier)
            )
            ->first();
    }

    private function throwBaggedValidationException(string $bag, array $messages): never
    {
        $exception = ValidationException::withMessages($messages);
        $exception->errorBag = $bag;

        throw $exception;
    }
}
