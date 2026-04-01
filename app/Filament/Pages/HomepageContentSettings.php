<?php

namespace App\Filament\Pages;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Actions\Action;
use App\Support\HomepageSettings as HomepageSettingsStore;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use BackedEnum;
use UnitEnum;

class HomepageContentSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-home-modern';

    protected static ?string $navigationLabel = 'Главная страница';

    protected static string | \UnitEnum | null $navigationGroup = 'Настройки';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Главная страница';

    protected static ?string $slug = 'settings/homepage';

    protected string $view = 'filament.pages.homepage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(HomepageSettingsStore::all());
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperadmin() || auth()->user()?->isManager();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Первый экран')
                    ->schema([
                        Group::make()
                            ->schema([
                                Grid::make(1)
                                    ->schema([
                                        TextInput::make('hero.title')->label('Заголовок')->required(),
                                        Textarea::make('hero.description')->label('Описание')->required()->rows(3),
                                    ]),
                            ]),
                        Group::make()
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('hero.primary_button_text')->label('Текст кнопки поиска')->required(),
                                        TextInput::make('hero.primary_button_url')->label('Ссылка на поиск')->required(),
                                    ]),
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('hero.secondary_button_text_guest')->label('Текст кнопки регистрации')->required(),
                                        TextInput::make('hero.secondary_button_text_auth')->label('Текст кнопки после авторизации')->required(),
                                        TextInput::make('hero.secondary_button_url')->label('Ссылка на личный кабинет')->required(),
                                    ]),
                            ]),
                        Group::make()
                            ->schema([
                                Grid::make(1)
                                    ->schema([
                                        TextInput::make('hero.video_button_text')->label('Текст кнопки видео')->required(),
                                        Textarea::make('hero.video_embed_code')
                                            ->label('Код видео для popup')
                                            ->rows(3)
                                            ->helperText('Вставьте embed-код, например iframe из YouTube или VK Видео.')
                                            ->required(),
                                    ]),
                            ]),
                        Group::make()
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        Fieldset::make('Статистика 1')
                                            ->columns(1)
                                            ->schema([
                                                TextInput::make('hero.stats.0.value')->label('Значение')->required(),
                                                Textarea::make('hero.stats.0.description')->label('Описание')->required()->rows(3),
                                            ]),
                                        Fieldset::make('Статистика 2')
                                            ->columns(1)
                                            ->schema([
                                                TextInput::make('hero.stats.1.value')->label('Значение')->required(),
                                                Textarea::make('hero.stats.1.description')->label('Описание')->required()->rows(3),
                                            ]),
                                        Fieldset::make('Статистика 3')
                                            ->columns(1)
                                            ->schema([
                                                TextInput::make('hero.stats.2.value')->label('Значение')->required(),
                                                Textarea::make('hero.stats.2.description')->label('Описание')->required()->rows(3),
                                            ]),
                                    ]),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->footerActions([$this->saveSectionAction('save_hero')]),

                Section::make('Поиск организаций')
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                TextInput::make('search.title')->label('Заголовок')->required(),
                                Textarea::make('search.description')->label('Описание')->required()->rows(3),
                            ]),
                        Grid::make(3)
                            ->schema([
                                Fieldset::make('Категория 1')
                                    ->columns(1)
                                    ->schema([
                                        TextInput::make('search.categories.0.title')->label('Название')->required(),
                                        $this->iconUpload('search.categories.0.icon', 'Иконка'),
                                    ]),
                                Fieldset::make('Категория 2')
                                    ->columns(1)
                                    ->schema([
                                        TextInput::make('search.categories.1.title')->label('Название')->required(),
                                        $this->iconUpload('search.categories.1.icon', 'Иконка'),
                                    ]),
                                Fieldset::make('Категория 3')
                                    ->columns(1)
                                    ->schema([
                                        TextInput::make('search.categories.2.title')->label('Название')->required(),
                                        $this->iconUpload('search.categories.2.icon', 'Иконка'),
                                    ]),
                                Fieldset::make('Категория 4')
                                    ->columns(1)
                                    ->schema([
                                        TextInput::make('search.categories.3.title')->label('Название')->required(),
                                        $this->iconUpload('search.categories.3.icon', 'Иконка'),
                                    ]),
                                Fieldset::make('Категория 5')
                                    ->columns(1)
                                    ->schema([
                                        TextInput::make('search.categories.4.title')->label('Название')->required(),
                                        $this->iconUpload('search.categories.4.icon', 'Иконка'),
                                    ]),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->footerActions([$this->saveSectionAction('save_search')]),

                Section::make('Будьте профессионалом')
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                TextInput::make('be_pro.title')->label('Заголовок')->required(),
                                Textarea::make('be_pro.description')->label('Описание')->required()->rows(3),
                            ]),
                        Grid::make(3)
                            ->schema([
                                Fieldset::make('Карточка 1')
                                    ->columns(1)
                                    ->schema([
                                        TextInput::make('be_pro.cards.0.title')->label('Заголовок')->required(),
                                        Textarea::make('be_pro.cards.0.description')->label('Описание')->required()->rows(3),
                                        $this->iconUpload('be_pro.cards.0.icon', 'Иконка'),
                                    ]),
                                Fieldset::make('Карточка 2')
                                    ->columns(1)
                                    ->schema([
                                        TextInput::make('be_pro.cards.1.title')->label('Заголовок')->required(),
                                        Textarea::make('be_pro.cards.1.description')->label('Описание')->required()->rows(3),
                                        $this->iconUpload('be_pro.cards.1.icon', 'Иконка'),
                                    ]),
                                Fieldset::make('Карточка 3')
                                    ->columns(1)
                                    ->schema([
                                        TextInput::make('be_pro.cards.2.title')->label('Заголовок')->required(),
                                        Textarea::make('be_pro.cards.2.description')->label('Описание')->required()->rows(3),
                                        $this->iconUpload('be_pro.cards.2.icon', 'Иконка'),
                                    ]),
                            ]),
                        $this->imageUpload('be_pro.image', 'Изображение'),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->footerActions([$this->saveSectionAction('save_be_pro')]),

                Section::make('Услуги')
                    ->schema([
                        Toggle::make('plans.enabled')
                            ->label('Показывать блок на сайте')
                            ->inline(false)
                            ->default(true),
                        Grid::make(1)
                            ->schema([
                                TextInput::make('plans.title')->label('Заголовок')->required(),
                                Textarea::make('plans.description')->label('Описание')->required()->rows(3),
                            ]),
                        Grid::make(3)
                            ->schema([
                                Fieldset::make('Карточка 1')
                                    ->columns(1)
                                    ->schema([
                                        TextInput::make('plans.items.0.title')->label('Название')->required(),
                                        Textarea::make('plans.items.0.description')->label('Описание')->required()->rows(3),
                                        TextInput::make('plans.items.0.price')->label('Цена')->required(),
                                        TextInput::make('plans.items.0.button_text')->label('Кнопка')->required(),
                                    ]),
                                Fieldset::make('Карточка 2')
                                    ->columns(1)
                                    ->schema([
                                        TextInput::make('plans.items.1.title')->label('Название')->required(),
                                        Textarea::make('plans.items.1.description')->label('Описание')->required()->rows(3),
                                        TextInput::make('plans.items.1.price')->label('Цена')->required(),
                                        TextInput::make('plans.items.1.button_text')->label('Кнопка')->required(),
                                    ]),
                                Fieldset::make('Карточка 3')
                                    ->columns(1)
                                    ->schema([
                                        TextInput::make('plans.items.2.title')->label('Название')->required(),
                                        Textarea::make('plans.items.2.description')->label('Описание')->required()->rows(3),
                                        TextInput::make('plans.items.2.price')->label('Цена')->required(),
                                        TextInput::make('plans.items.2.button_text')->label('Кнопка')->required(),
                                    ]),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->footerActions([$this->saveSectionAction('save_plans')]),

                Section::make('Присоединиться к платформе')
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                TextInput::make('join.title')->label('Заголовок')->required(),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('join.cta_button_text_guest')->label('Текст кнопки регистрации')->required(),
                                TextInput::make('join.cta_button_text_auth')->label('Текст кнопки после авторизации')->required(),
                                TextInput::make('join.cta_button_url')->label('Ссылка на личный кабинет')->required(),
                            ]),
                        Grid::make(3)
                            ->schema([
                                Fieldset::make('Шаг 1')
                                    ->columns(1)
                                    ->schema([
                                        TextInput::make('join.steps.0.title')->label('Заголовок')->required(),
                                        Textarea::make('join.steps.0.description')->label('Описание')->required()->rows(3),
                                    ]),
                                Fieldset::make('Шаг 2')
                                    ->columns(1)
                                    ->schema([
                                        TextInput::make('join.steps.1.title')->label('Заголовок')->required(),
                                        Textarea::make('join.steps.1.description')->label('Описание')->required()->rows(3),
                                    ]),
                                Fieldset::make('Шаг 3')
                                    ->columns(1)
                                    ->schema([
                                        TextInput::make('join.steps.2.title')->label('Заголовок')->required(),
                                        Textarea::make('join.steps.2.description')->label('Описание')->required()->rows(3),
                                    ]),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->footerActions([$this->saveSectionAction('save_join')]),

                Section::make('Помощь в подборе')
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                TextInput::make('need_help.title')->label('Заголовок')->required(),
                                Textarea::make('need_help.description')->label('Описание')->required()->rows(3),
                            ]),
                        Grid::make(1)
                            ->schema([
                                TextInput::make('need_help.button_text')->label('Кнопка')->required(),
                            ]),
                        $this->imageUpload('need_help.image', 'Изображение'),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->footerActions([$this->saveSectionAction('save_need_help')]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->normalizeState($this->form->getState());

        HomepageSettingsStore::save($state);

        Notification::make()
            ->title('Контент главной страницы сохранен')
            ->success()
            ->send();
    }

    protected function saveSectionAction(string $name): Action
    {
        return Action::make($name)
            ->label('Сохранить')
            ->submit('save')
            ->color('primary');
    }

    protected function imageUpload(string $name, string $label, string $directory = 'homepage-images'): FileUpload
    {
        return FileUpload::make($name)
            ->label($label)
            ->image()
            ->extraAttributes(['style' => 'max-width: 256px;'])
            ->disk('public')
            ->directory($directory)
            ->visibility('public')
            ->preserveFilenames()
            ->acceptedFileTypes($this->acceptedImageFileTypes())
            ->required();
    }

    protected function iconUpload(string $name, string $label): FileUpload
    {
        return $this->imageUpload($name, $label, 'homepage-icons')
            ->imagePreviewHeight('128')
            ->extraAttributes(['style' => 'max-width: 128px;']);
    }

    protected function acceptedImageFileTypes(): array
    {
        return [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml',
            'image/bmp',
            'image/x-icon',
            'image/tiff',
        ];
    }

    protected function normalizeState(array $state): array
    {
        $state['hero']['stats'] = $this->normalizePairs(
            $state['hero']['stats'] ?? [],
            3,
            ['value', 'description'],
        );

        $state['search']['categories'] = $this->normalizePairs(
            $state['search']['categories'] ?? [],
            5,
            ['title', 'icon'],
        );

        foreach ($state['search']['categories'] as $index => $category) {
            $state['search']['categories'][$index]['alt'] = $category['title'];
        }

        $state['be_pro']['cards'] = $this->normalizePairs(
            $state['be_pro']['cards'] ?? [],
            3,
            ['title', 'description', 'icon'],
        );
        $state['be_pro']['image_alt'] = $state['be_pro']['title'] ?? '';

        $state['join']['steps'] = $this->normalizePairs(
            $state['join']['steps'] ?? [],
            3,
            ['title', 'description'],
        );

        foreach ($state['join']['steps'] as $index => $step) {
            $state['join']['steps'][$index]['number'] = (string) ($index + 1);
        }

        $state['plans']['items'] = $this->normalizePairs(
            $state['plans']['items'] ?? [],
            3,
            ['title', 'description', 'price', 'button_text'],
        );

        $state['need_help']['image_alt'] = $state['need_help']['button_text'] ?? '';

        return $state;
    }

    protected function normalizePairs(array $items, int $count, array $fields): array
    {
        $normalized = [];

        for ($index = 0; $index < $count; $index++) {
            $item = is_array($items[$index] ?? null) ? $items[$index] : [];

            foreach ($fields as $field) {
                $normalized[$index][$field] = $item[$field] ?? '';
            }
        }

        return $normalized;
    }
}
