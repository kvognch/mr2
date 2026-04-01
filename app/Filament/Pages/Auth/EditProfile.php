<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class EditProfile extends BaseEditProfile
{
    protected static bool $isDiscovered = true;

    protected static ?string $title = 'Настройки аккаунта';

    protected static ?string $navigationLabel = 'Настройки аккаунта';

    protected static string | \UnitEnum | null $navigationGroup = 'Настройки';

    protected static ?int $navigationSort = 999;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user';

    public static function getNavigationUrl(): string
    {
        return Route::has('filament.admin.profile')
            ? route('filament.admin.profile')
            : filament()->getProfileUrl();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Имя')
                    ->required()
                    ->maxLength(255)
                    ->autofocus(),
                TextInput::make('phone')
                    ->label('Телефон')
                    ->mask('+7 (999) 999-99-99')
                    ->placeholder('+7 (___) ___-__-__')
                    ->extraInputAttributes(['inputmode' => 'tel'])
                    ->rule('regex:/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/')
                    ->maxLength(64),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->label('Новый пароль')
                    ->password()
                    ->revealable(filament()->arePasswordsRevealable())
                    ->rule(Password::default())
                    ->autocomplete('new-password')
                    ->dehydrated(fn ($state): bool => filled($state))
                    ->dehydrateStateUsing(fn ($state): string => Hash::make($state))
                    ->same('passwordConfirmation'),
                TextInput::make('passwordConfirmation')
                    ->label('Подтвердите пароль')
                    ->password()
                    ->autocomplete('new-password')
                    ->revealable(filament()->arePasswordsRevealable())
                    ->dehydrated(false),
            ]);
    }
}
