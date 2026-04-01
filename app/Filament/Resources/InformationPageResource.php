<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InformationPageResource\Pages\CreateInformationPage;
use App\Filament\Resources\InformationPageResource\Pages\EditInformationPage;
use App\Filament\Resources\InformationPageResource\Pages\ListInformationPages;
use App\Models\InformationPage;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class InformationPageResource extends Resource
{
    protected static ?string $model = InformationPage::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static string | \UnitEnum | null $navigationGroup = 'Настройки';

    protected static ?string $navigationLabel = 'Информация';

    protected static ?string $modelLabel = 'информационная страница';

    protected static ?string $pluralModelLabel = 'информация';

    protected static ?string $breadcrumb = 'Информация';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Заголовок')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state): void {
                        $oldSlug = filled($old) ? Str::slug(Str::transliterate((string) $old)) : '';
                        $currentSlug = (string) ($get('slug') ?? '');

                        if (filled($currentSlug) && ($currentSlug !== $oldSlug)) {
                            return;
                        }

                        $set('slug', Str::slug(Str::transliterate((string) $state)));
                    })
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label('Алиас')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Используется в URL страницы. Должен быть уникальным.'),
                Toggle::make('use_rich_editor')
                    ->label('Визуальный редактор')
                    ->helperText('Выключите, чтобы править HTML-код вручную.')
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set, bool $state): void {
                        if ($state) {
                            $bodyHtml = $get('body_html');

                            if (is_string($bodyHtml)) {
                                $set('body', $bodyHtml);
                            }

                            return;
                        }

                        $body = $get('body');

                        if (is_string($body)) {
                            $set('body_html', $body);
                        }
                    })
                    ->dehydrated(false)
                    ->default(true),
                RichEditor::make('body')
                    ->label('Текст')
                    ->required(fn (Get $get): bool => (bool) $get('use_rich_editor'))
                    ->visible(fn (Get $get): bool => (bool) $get('use_rich_editor'))
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'strike',
                        'h2',
                        'h3',
                        'bulletList',
                        'orderedList',
                        'blockquote',
                        'link',
                        'redo',
                        'undo',
                    ])
                    ->columnSpanFull(),
                Textarea::make('body_html')
                    ->label('HTML-код')
                    ->required(fn (Get $get): bool => ! $get('use_rich_editor'))
                    ->rows(20)
                    ->visible(fn (Get $get): bool => ! $get('use_rich_editor'))
                    ->extraAttributes(['style' => 'font-family: monospace;']),
                TextInput::make('meta_title')
                    ->label('SEO Title')
                    ->maxLength(255),
                Textarea::make('meta_description')
                    ->label('SEO Description')
                    ->rows(3)
                    ->maxLength(1000),
                Toggle::make('is_active')
                    ->label('Статус')
                    ->default(true),
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
                ToggleColumn::make('is_active')
                    ->label('Статус')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('title');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInformationPages::route('/'),
            'create' => CreateInformationPage::route('/create'),
            'edit' => EditInformationPage::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isSuperadmin() || auth()->user()?->isManager();
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canDeleteAny(): bool
    {
        return static::canViewAny();
    }

}
