<?php

namespace App\Filament\Resources;

use App\Enums\ServiceRequestStatus;
use App\Filament\Resources\ServiceRequestResource\Pages\EditServiceRequest;
use App\Filament\Resources\ServiceRequestResource\Pages\ListServiceRequests;
use App\Models\ServiceRequest;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ServiceRequestResource extends Resource
{
    protected static ?string $model = ServiceRequest::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-inbox-stack';

    protected static string | \UnitEnum | null $navigationGroup = 'Заявки';

    protected static ?string $navigationLabel = 'Заявки';

    protected static ?string $modelLabel = 'заявка';

    protected static ?string $pluralModelLabel = 'заявки';

    protected static ?string $breadcrumb = 'Заявки';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Имя')
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('phone')
                    ->label('Телефон')
                    ->disabled()
                    ->dehydrated(false),
                Textarea::make('comment')
                    ->label('Комментарий')
                    ->rows(5)
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('source_url')
                    ->label('Источник')
                    ->disabled()
                    ->dehydrated(false),
                Select::make('status')
                    ->label('Статус')
                    ->options(ServiceRequestStatus::options())
                    ->required(),
                Textarea::make('admin_note')
                    ->label('Примечание')
                    ->rows(5)
                    ->maxLength(3000),
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
                TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable(),
                TextColumn::make('comment')
                    ->label('Комментарий')
                    ->limit(60),
                TextColumn::make('status')
                    ->label('Статус')
                    ->formatStateUsing(fn (ServiceRequestStatus | string $state): string => $state instanceof ServiceRequestStatus ? $state->label() : ServiceRequestStatus::from($state)->label())
                    ->color(fn (ServiceRequestStatus | string $state): string => $state instanceof ServiceRequestStatus ? $state->color() : ServiceRequestStatus::from($state)->color())
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(ServiceRequestStatus::options()),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceRequests::route('/'),
            'edit' => EditServiceRequest::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->orderedForModeration();
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user?->isSuperadmin() || $user?->isManager();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canDelete(Model $record): bool
    {
        return static::canViewAny();
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

        $count = static::getEloquentQuery()
            ->where('status', ServiceRequestStatus::Pending->value)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string | array | null
    {
        return 'warning';
    }
}
