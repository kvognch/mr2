<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportGeoPolygonsCommand extends Command
{
    protected $signature = 'geo:import-polygons {file?}';

    protected $description = 'Deprecated: polygons are stored in geo_units, use geo:import-units-from-geojson';

    public function handle(): int
    {
        $this->warn('Command deprecated. Use: geo:import-units-from-geojson');

        return self::SUCCESS;
    }
}
