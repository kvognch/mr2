<?php

namespace App\Filament\Resources\GeoUnitResource\Pages;

use App\Filament\Resources\GeoUnitResource;
use App\Models\GeoUnit;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListGeoUnits extends ListRecords
{
    protected static string $resource = GeoUnitResource::class;

    protected static ?string $title = 'Геообъекты';

    public ?int $parentId = null;

    protected $queryString = [
        'parentId' => ['as' => 'parent_id'],
    ];

    public function mount(): void
    {
        parent::mount();

        if ($this->parentId === null) {
            $requestParentId = request()->query('parent_id');
            $this->parentId = $requestParentId !== null && $requestParentId !== ''
                ? (int) $requestParentId
                : null;
        }
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        if ($this->parentId !== null) {
            $query->where('parent_id', $this->parentId);
        } else {
            $query->where('admin_level', 4);
        }

        return $query;
    }

    protected function getHeaderActions(): array
    {
        $actions = [];
        $parentId = $this->parentId;

        if ($parentId !== null && $parentId !== '') {
            $parent = GeoUnit::query()->find((int) $parentId);

            if ($parent && $parent->parent_id) {
                $actions[] = Action::make('back')
                    ->label('На уровень выше')
                    ->url(GeoUnitResource::getUrl('index', ['parent_id' => $parent->parent_id]));
            } else {
                $actions[] = Action::make('backTop')
                    ->label('На уровень выше')
                    ->url(GeoUnitResource::getUrl('index'));
            }
        }

        return $actions;
    }
}
