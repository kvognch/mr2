<?php

namespace App\Filament\Resources;

use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\ResourceTypeResource\Pages\ListResourceTypes;
use App\Filament\Resources\ResourceTypeResource\Pages\CreateResourceType;
use App\Filament\Resources\ResourceTypeResource\Pages\EditResourceType;
use App\Filament\Resources\ResourceTypeResource\Pages;
use App\Models\ResourceType;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class ResourceTypeResource extends Resource
{
    protected static ?string $model = ResourceType::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static string | \UnitEnum | null $navigationGroup = 'Подрядчики';

    protected static ?string $navigationLabel = 'Виды ресурсов';

    protected static ?string $modelLabel = 'вид ресурса';

    protected static ?string $pluralModelLabel = 'виды ресурсов';

    protected static ?string $breadcrumb = 'Виды ресурсов';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Название')
                ->required()
                ->maxLength(255),
            TextInput::make('abbreviation')
                ->label('Сокращение')
                ->required()
                ->maxLength(20),
            FileUpload::make('icon')
                ->label('Иконка')
                ->disk('public')
                ->directory('resource-types/icons')
                ->visibility('public')
                ->preserveFilenames()
                ->acceptedFileTypes([
                    'image/jpeg',
                    'image/png',
                    'image/gif',
                    'image/webp',
                    'image/svg+xml',
                    'image/bmp',
                    'image/x-icon',
                    'image/tiff',
                ]),
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
            'index' => ListResourceTypes::route('/'),
            'create' => CreateResourceType::route('/create'),
            'edit' => EditResourceType::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isSuperadmin() || auth()->user()?->isManager();
    }
}
