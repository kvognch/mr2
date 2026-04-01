<?php

namespace App\Filament\Resources;

use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\RatingResource\Pages\ListRatings;
use App\Filament\Resources\RatingResource\Pages\CreateRating;
use App\Filament\Resources\RatingResource\Pages\EditRating;
use App\Filament\Resources\RatingResource\Pages;
use App\Models\Rating;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class RatingResource extends Resource
{
    protected static ?string $model = Rating::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-star';

    protected static string | \UnitEnum | null $navigationGroup = 'Подрядчики';

    protected static ?string $navigationLabel = 'Рейтинги';

    protected static ?string $modelLabel = 'рейтинг';

    protected static ?string $pluralModelLabel = 'рейтинги';

    protected static ?string $breadcrumb = 'Рейтинги';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 40;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Название')
                ->required()
                ->maxLength(255),
            TextInput::make('sort_order')
                ->label('Порядок сортировки')
                ->required()
                ->numeric()
                ->minValue(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columnManager(false)
            ->columns([
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRatings::route('/'),
            'create' => CreateRating::route('/create'),
            'edit' => EditRating::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isSuperadmin() || auth()->user()?->isManager();
    }
}
