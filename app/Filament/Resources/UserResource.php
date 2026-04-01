<?php

namespace App\Filament\Resources;

use App\Enums\UserRole;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\User;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';

    protected static string | \UnitEnum | null $navigationGroup = 'Пользователи';

    protected static ?string $navigationLabel = 'Пользователи';

    protected static ?string $modelLabel = 'пользователь';

    protected static ?string $pluralModelLabel = 'пользователи';

    protected static ?string $breadcrumb = 'Пользователи';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        $user = auth()->user();
        $isSuperadmin = $user?->isSuperadmin() ?? false;

        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Имя')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('phone')
                    ->label('Телефон')
                    ->mask('+7 (999) 999-99-99')
                    ->placeholder('+7 (___) ___-__-__')
                    ->extraInputAttributes(['inputmode' => 'tel'])
                    ->rule('regex:/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/')
                    ->maxLength(64),
                Toggle::make('is_active')
                    ->label('Активный аккаунт')
                    ->default(true),
                Select::make('role')
                    ->label('Роль')
                    ->options($isSuperadmin ? UserRole::options() : [UserRole::Client->value => UserRole::Client->label()])
                    ->default(UserRole::Client->value)
                    ->required()
                    ->disabled(! $isSuperadmin)
                    ->dehydrated($isSuperadmin),
                TextInput::make('password')
                    ->label('Пароль')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->maxLength(255),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columnManager(false)
            ->columns([
                TextColumn::make('name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('is_active')
                    ->label('Статус')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Активен' : 'Ожидает подтверждения')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'warning'),
                TextColumn::make('role')
                    ->label('Роль')
                    ->formatStateUsing(fn (UserRole | string $state): string => $state instanceof UserRole ? $state->label() : UserRole::from($state)->label())
                    ->badge(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Статус')
                    ->trueLabel('Активные')
                    ->falseLabel('Ожидают подтверждения')
                    ->native(false),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user?->isManager()) {
            $query->where('role', UserRole::Client->value);
        }

        return $query;
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user?->isSuperadmin() || $user?->isManager();
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        $user = auth()->user();

        if ($user?->isSuperadmin()) {
            return true;
        }

        return $user?->isManager() && $record instanceof User && $record->role === UserRole::Client;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return static::canEdit($record);
    }

    public static function canDeleteAny(): bool
    {
        return static::canViewAny();
    }

    public static function getNavigationBadge(): ?string
    {
        if (! static::canViewAny()) {
            return null;
        }

        $count = User::query()
            ->where('role', UserRole::Client->value)
            ->where('is_active', false)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string | array | null
    {
        return 'warning';
    }
}
