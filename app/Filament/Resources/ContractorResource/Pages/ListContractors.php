<?php

namespace App\Filament\Resources\ContractorResource\Pages;

use App\Filament\Resources\ContractorResource;
use App\Services\ContractorImportService;
use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ListContractors extends ListRecords
{
    protected static string $resource = ContractorResource::class;

    protected static ?string $title = 'Подрядчики';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('exportXlsx')
                ->label('Экспорт XLSX')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(route('contractors.export'))
                ->openUrlInNewTab()
                ->visible(fn (): bool => auth()->user()?->isSuperadmin() || auth()->user()?->isManager()),
            Actions\Action::make('importXlsx')
                ->label('Импорт XLSX')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    FileUpload::make('file')
                        ->label('Файл')
                        ->disk('local')
                        ->directory('imports/contractors')
                        ->helperText('Поддерживаются файлы .xlsx и .xls')
                        ->required(),
                ])
                ->action(function (array $data, ContractorImportService $importService): void {
                    $path = $data['file'] ?? null;

                    if (is_array($path)) {
                        $path = $path[0] ?? null;
                    }

                    if (! is_string($path) || $path === '') {
                        Notification::make()
                            ->title('Файл импорта не выбран')
                            ->danger()
                            ->send();

                        return;
                    }

                    $extension = Str::lower(pathinfo($path, PATHINFO_EXTENSION));

                    if (! in_array($extension, ['xlsx', 'xls'], true)) {
                        Storage::disk('local')->delete($path);

                        Notification::make()
                            ->title('Неверный формат файла')
                            ->body('Загрузите файл в формате .xlsx или .xls')
                            ->danger()
                            ->send();

                        return;
                    }

                    $result = $importService->importFromLocalPath($path);

                    Storage::disk('local')->delete($path);

                    Notification::make()
                        ->title('Импорт подрядчиков завершён')
                        ->body("Создано: {$result['created']}, обновлено: {$result['updated']}, пропущено: {$result['skipped']}, ошибок: {$result['errors']}")
                        ->success()
                        ->send();
                })
                ->visible(fn (): bool => auth()->user()?->isSuperadmin() || auth()->user()?->isManager()),
            CreateAction::make(),
        ];
    }
}
