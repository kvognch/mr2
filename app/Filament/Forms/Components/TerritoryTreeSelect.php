<?php

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;

class TerritoryTreeSelect extends Field
{
    protected string $view = 'filament.forms.components.territory-tree-select';

    protected array|Closure $tree = [];

    protected array|Closure $descendants = [];

    public function tree(array|Closure $tree): static
    {
        $this->tree = $tree;

        return $this;
    }

    public function descendants(array|Closure $descendants): static
    {
        $this->descendants = $descendants;

        return $this;
    }

    public function getTree(): array
    {
        return $this->evaluate($this->tree) ?? [];
    }

    public function getDescendants(): array
    {
        return $this->evaluate($this->descendants) ?? [];
    }
}
