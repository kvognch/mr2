<?php

namespace App\Filament\Pages;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Actions\Action;
use App\Support\HomepageSettings as HomepageSettingsStore;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use BackedEnum;
use UnitEnum;

class HomepageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Общие настройки';

    protected static string | \UnitEnum | null $navigationGroup = 'Настройки';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Общие настройки';

    protected static ?string $slug = 'settings/general';

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
                Section::make('Шапка')
                    ->schema([
                        TextInput::make('header.brand')->label('Бренд')->required(),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('header.login_button_text_guest')->label('Текст кнопки до авторизации')->required(),
                                TextInput::make('header.login_button_text_auth')->label('Текст кнопки после авторизации')->required(),
                                TextInput::make('header.login_button_url')->label('Ссылка на личный кабинет')->required(),
                            ]),
                        Repeater::make('header.menu')
                            ->label('Пункты меню')
                            ->addActionLabel('Добавить')
                            ->schema([
                                TextInput::make('label')->label('Название')->required(),
                                TextInput::make('url')->label('Ссылка')->required(),
                            ])
                            ->columns(2)
                            ->defaultItems(3)
                            ->collapsible(),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->footerActions([$this->saveSectionAction('save_header')]),

                Section::make('Подвал')
                    ->schema([
                        TextInput::make('footer.brand')->label('Бренд')->required(),
                        TextInput::make('footer.group_1_title')->label('Заголовок колонки 1')->required(),
                        Repeater::make('footer.group_1_links')
                            ->label('Ссылки колонки 1')
                            ->addActionLabel('Добавить')
                            ->schema([
                                TextInput::make('label')->label('Название')->required(),
                                TextInput::make('url')->label('Ссылка')->required(),
                            ])
                            ->columns(2)
                            ->collapsible(),
                        TextInput::make('footer.group_2_title')->label('Заголовок колонки 2')->required(),
                        Repeater::make('footer.group_2_links')
                            ->label('Ссылки колонки 2')
                            ->addActionLabel('Добавить')
                            ->schema([
                                TextInput::make('label')->label('Название')->required(),
                                TextInput::make('url')->label('Ссылка')->required(),
                            ])
                            ->columns(2)
                            ->collapsible(),
                        TextInput::make('footer.email')->label('Email')->required()->email(),
                        TextInput::make('footer.phone_display')->label('Телефон')->required(),
                        Repeater::make('footer.socials')
                            ->label('Соцсети')
                            ->addActionLabel('Добавить')
                            ->schema([
                                TextInput::make('key')->label('Ключ (telegram/vk/youtube)')->required(),
                                TextInput::make('url')->label('Ссылка')->required(),
                            ])
                            ->columns(2)
                            ->collapsible(),
                        TextInput::make('footer.copyright')->label('Копирайт')->required(),
                        TextInput::make('footer.legal')->label('Юридическая строка')->required(),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->footerActions([$this->saveSectionAction('save_footer')]),

                Section::make('Общие')
                    ->schema([
                        Section::make('Главная страница')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('meta.home.title')->label('Title')->required(),
                                        Textarea::make('meta.home.description')->label('Description')->rows(3),
                                    ]),
                            ]),
                        Section::make('Страница поиска')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('meta.search.title')->label('Title')->required(),
                                        Textarea::make('meta.search.description')->label('Description')->rows(3),
                                    ]),
                            ]),
                        Section::make('Карточка подрядчика')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('meta.contractor.title')->label('Title')->helperText('Используйте %name% для краткого названия подрядчика.')->required(),
                                        Textarea::make('meta.contractor.description')->label('Description')->rows(3)->helperText('Используйте %name% для краткого названия подрядчика.'),
                                    ]),
                            ]),
                    ])
                    ->heading('SEO')
                    ->collapsible()
                    ->collapsed()
                    ->footerActions([$this->saveSectionAction('save_meta')]),

                Section::make('Google reCAPTCHA')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('google_recaptcha.site_key')->label('Site key')->required(),
                                TextInput::make('google_recaptcha.secret_key')->label('Secret key')->required(),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->footerActions([$this->saveSectionAction('save_google_recaptcha')]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        HomepageSettingsStore::save($state);

        Notification::make()
            ->title('Общие настройки сохранены')
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
}
