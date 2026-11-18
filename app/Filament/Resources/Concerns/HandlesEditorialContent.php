<?php

namespace App\Filament\Resources\Concerns;

use App\Models\BlogArticle;

trait HandlesEditorialContent
{
    protected array $relatedRows = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['use_rich_editor'] = (bool) ($data['use_rich_editor'] ?? true);
        $data['body_html'] = $data['body'] ?? '';
        $data['related_rows'] = $this->record->relatedArticles()->published()->get()->map(fn ($p) => ['article_id' => $p->id])->all();

        return $data;
    }

    protected function prepareEditorialData(array $data): array
    {
        if (! $this->form->getRawState()['use_rich_editor']) {
            $data['body'] = $data['body_html'] ?? '';
        }
        $this->relatedRows = $data['related_rows'] ?? [];
        unset($data['related_rows'],$data['body_html']);

        return $data;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->prepareEditorialData($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->prepareEditorialData($data);
    }

    protected function syncEditorialRelations(): void
    {
        if ($this->record->kind === 'news') {
            $ids = [];
            foreach ($this->relatedRows as $i => $row) {
                if (! empty($row['article_id']) && BlogArticle::published()->whereKey($row['article_id'])->exists()) {
                    $ids[$row['article_id']] = ['sort_order' => count($ids)];
                }
            }
            $this->record->relatedArticles()->sync(array_slice($ids, 0, 3, true));
        }
    }

    protected function afterCreate(): void
    {
        $this->syncEditorialRelations();
    }

    protected function afterSave(): void
    {
        $this->syncEditorialRelations();
    }
}
