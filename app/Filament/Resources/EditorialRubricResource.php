<?php

namespace App\Filament\Resources;

use App\Models\BlogRubric;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

abstract class EditorialRubricResource extends InformationPageResource
{
    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([TextInput::make('name')->label('Название')->required()->maxLength(255), TextInput::make('slug')->label('Slug')->required()->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->maxLength(255)->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->where('kind', static::getModel() === BlogRubric::class ? 'blog' : 'news')), TextInput::make('sort_order')->label('Порядок')->numeric()->default(0)->required(), Toggle::make('is_visible')->label('Показывать в фильтре')->default(true)]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->label('Название'), TextColumn::make('sort_order')->label('Порядок'), ToggleColumn::make('is_visible')->label('В фильтре')])->defaultSort('sort_order')->recordActions([EditAction::make()])->toolbarActions([DeleteBulkAction::make()]);
    }
}
