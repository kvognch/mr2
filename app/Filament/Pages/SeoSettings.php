<?php

namespace App\Filament\Pages;

use App\Support\SeoSettings as SeoSettingsStore;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SeoSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?string $navigationLabel = 'SEO';

    protected static string|\UnitEnum|null $navigationGroup = 'Настройки';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'SEO';

    protected static ?string $slug = 'settings/seo';

    protected string $view = 'filament.pages.seo-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(SeoSettingsStore::all());
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperadmin() || auth()->user()?->isManager();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Главная страница')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('home.title')->label('Title')->required(),
                                Textarea::make('home.description')->label('Description')->rows(3),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
                Section::make('Страница поиска')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('search.title')->label('Title')->required(),
                                Textarea::make('search.description')->label('Description')->rows(3),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
                Section::make('Description организаций')
                    ->schema([
                        TextInput::make('organizations.description_prefix')
                            ->label('Начальный текст')
                            ->required(),
                        TextInput::make('organizations.description_suffix')
                            ->label('Конечный текст')
                            ->required(),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->footerActions([$this->saveSectionAction()]),
                Section::make('Robots.txt')
                    ->schema([
                        Textarea::make('robots')
                            ->label('Директивы')
                            ->rows(12)
                            ->required(),
                    ])
                    ->columns(1)
                    ->collapsible()
                    ->collapsed()
                    ->footerActions([$this->saveSectionAction('save_robots')]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        SeoSettingsStore::save($this->form->getState());

        Notification::make()
            ->title('SEO-настройки сохранены')
            ->success()
            ->send();
    }

    protected function saveSectionAction(string $name = 'save_seo'): Action
    {
        return Action::make($name)
            ->label('Сохранить')
            ->submit('save')
            ->color('primary');
    }
}
