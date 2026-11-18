<?php

namespace App\Filament\Pages;

use App\Models\BlogArticle;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;

class BlogHome extends SeoSettings
{
    protected static ?string $navigationLabel = 'Главная блога';

    protected static string|\UnitEnum|null $navigationGroup = 'Блог';

    protected static ?string $title = 'Главная блога';

    protected static ?string $slug = 'blog-home';

    public function mount(): void
    {
        $data = json_decode((string) Setting::where('key', 'blog.home')->value('value'), true) ?? ['interval' => 7, 'slides' => []];
        $available = BlogArticle::published()->whereIn('id', array_column($data['slides'] ?? [], 'article_id'))->pluck('id')->all();
        $data['slides'] = array_values(array_filter($data['slides'] ?? [], fn (array $slide): bool => in_array((int) ($slide['article_id'] ?? 0), $available, true)));
        $this->form->fill($data);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->components([TextInput::make('interval')->label('Интервал переключения, секунд')->integer()->minValue(3)->maxValue(120)->required(), Repeater::make('slides')->label('Статьи карусели')->schema([Select::make('article_id')->label('Опубликованная статья')->options(fn () => BlogArticle::published()->pluck('title', 'id'))->required()->distinct()->searchable()])->reorderable()->defaultItems(0)]);
    }

    protected function getHeaderActions(): array
    {
        return [Action::make('save')->label('Сохранить')->action('save')];
    }

    public function save(): void
    {
        Setting::updateOrCreate(['key' => 'blog.home'], ['value' => json_encode($this->form->getState(), JSON_UNESCAPED_UNICODE)]);
        Notification::make()->title('Настройки блога сохранены')->success()->send();
    }
}
