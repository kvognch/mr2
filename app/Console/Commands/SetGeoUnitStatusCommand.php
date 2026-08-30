<?php

namespace App\Console\Commands;

use App\Models\GeoUnit;
use Illuminate\Console\Command;

class SetGeoUnitStatusCommand extends Command
{
    protected $signature = 'geo:set-status
        {unit_id : geo_units.id}
        {status : active|inactive}
        {--cascade : Apply status recursively to descendants}';

    protected $description = 'Set map activity status for geo tree item';

    public function handle(): int
    {
        $unit = GeoUnit::query()->find((int) $this->argument('unit_id'));
        if (! $unit) {
            $this->error('Объект не найден.');

            return self::FAILURE;
        }

        $status = (string) $this->argument('status');
        if (! in_array($status, ['active', 'inactive'], true)) {
            $this->error('Допустимо: active|inactive');

            return self::FAILURE;
        }

        $isActive = $status === 'active';
        if ((bool) $this->option('cascade')) {
            $unit->setActiveWithDescendants($isActive);
        } else {
            $unit->is_active = $isActive;
            $unit->save();
        }

        $this->info('Статус обновлен.');

        return self::SUCCESS;
    }
}
