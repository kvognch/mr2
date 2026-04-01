<?php

namespace App\Filament\Resources;

use App\Enums\ReviewStatus;
use App\Filament\Resources\ServiceReviewResource\Pages\EditServiceReview;
use App\Filament\Resources\ServiceReviewResource\Pages\ListServiceReviews;
use App\Models\ServiceReview;
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

class ServiceReviewResource extends Resource
{
    protected static ?string $model = ServiceReview::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string | \UnitEnum | null $navigationGroup = 'Отзывы';

    protected static ?string $navigationLabel = 'Отзывы о сервисе';

    protected static ?string $modelLabel = 'отзыв о сервисе';

    protected static ?string $pluralModelLabel = 'отзывы о сервисе';

    protected static ?string $breadcrumb = 'Отзывы о сервисе';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('author_name')
                    ->label('Имя автора')
                    ->required()
                    ->maxLength(255),
                TextInput::make('author_role')
                    ->label('Род деятельности')
                    ->required()
                    ->maxLength(255),
                TextInput::make('title')
                    ->label('Заголовок')
                    ->required()
                    ->maxLength(255),
                Textarea::make('body')
                    ->label('Текст отзыва')
                    ->required()
                    ->rows(5)
                    ->maxLength(3000),
                Select::make('rating')
                    ->label('Оценка')
                    ->required()
                    ->options([
                        1 => '1',
                        2 => '2',
                        3 => '3',
                        4 => '4',
                        5 => '5',
                    ]),
                Select::make('is_recommended')
                    ->label('Рекомендует')
                    ->options([
                        1 => 'Да',
                        0 => 'Нет',
                    ])
                    ->default(0)
                    ->formatStateUsing(fn ($state) => $state ?? 0)
                    ->required(),
                Select::make('status')
                    ->label('Статус')
                    ->options(ReviewStatus::options())
                    ->required()
                    ->visible(fn () => auth()->user()?->isSuperadmin() || auth()->user()?->isManager()),
                TextInput::make('owner_display')
                    ->label('Владелец')
                    ->formatStateUsing(fn (?ServiceReview $record): string => $record?->user ? sprintf('%s (%s)', $record->user->name, $record->user->email) : '—')
                    ->disabled()
                    ->dehydrated(false)
                    ->visible(fn () => auth()->user()?->isSuperadmin() || auth()->user()?->isManager()),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columnManager(false)
            ->columns([
                TextColumn::make('title')
                    ->label('Заголовок')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('author_name')
                    ->label('Автор')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('rating')
                    ->label('Оценка')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->formatStateUsing(fn (ReviewStatus | string $state): string => $state instanceof ReviewStatus ? $state->label() : ReviewStatus::from($state)->label())
                    ->color(fn (ReviewStatus | string $state): string => $state instanceof ReviewStatus ? $state->color() : ReviewStatus::from($state)->color())
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options(ReviewStatus::options()),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceReviews::route('/'),
            'edit' => EditServiceReview::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->orderedForModeration();
        $user = auth()->user();

        if ($user?->isSuperadmin() || $user?->isManager()) {
            return $query;
        }

        return $query->where('user_id', $user?->id);
    }

    public static function canViewAny(): bool
    {
        return auth()->check();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()
            ->where('status', ReviewStatus::Pending->value)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
