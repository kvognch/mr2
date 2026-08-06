<?php

namespace App\Filament\Resources;

use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\ContractorCategoryResource\Pages\ListContractorCategories;
use App\Filament\Resources\ContractorCategoryResource\Pages\CreateContractorCategory;
use App\Filament\Resources\ContractorCategoryResource\Pages\EditContractorCategory;
use App\Filament\Resources\ContractorCategoryResource\Pages;
use App\Models\ContractorCategory;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class ContractorCategoryResource extends Resource
{
    protected static ?string $model = ContractorCategory::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-tag';

    protected static string | \UnitEnum | null $navigationGroup = 'Организации';

    protected static ?string $navigationLabel = 'Категории организаций';

    protected static ?string $modelLabel = 'категория организации';

    protected static ?string $pluralModelLabel = 'категории организаций';

    protected static ?string $breadcrumb = 'Категории организаций';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Название')
                ->required()
                ->maxLength(255),
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
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContractorCategories::route('/'),
            'create' => CreateContractorCategory::route('/create'),
            'edit' => EditContractorCategory::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isSuperadmin() || auth()->user()?->isManager();
    }
}
