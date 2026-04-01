<?php

namespace App\Services\Geo;

class GeometryService
{
    public function flattenCoordinates(array $geometry): array
    {
        $type = $geometry['type'] ?? null;
        $coordinates = $geometry['coordinates'] ?? [];

        if ($type === 'Polygon') {
            return $coordinates[0] ?? [];
        }

        if ($type === 'MultiPolygon') {
            $points = [];
            foreach ($coordinates as $polygon) {
                foreach (($polygon[0] ?? []) as $point) {
                    $points[] = $point;
                }
            }

            return $points;
        }

        return [];
    }

    public function centroid(array $geometry): ?array
    {
        $points = $this->flattenCoordinates($geometry);

        if ($points === []) {
            return null;
        }

        $sumLon = 0.0;
        $sumLat = 0.0;

        foreach ($points as $point) {
            if (! isset($point[0], $point[1])) {
                continue;
            }

            $sumLon += (float) $point[0];
            $sumLat += (float) $point[1];
        }

        $count = count($points);
        if ($count === 0) {
            return null;
        }

        return [
            'lon' => $sumLon / $count,
            'lat' => $sumLat / $count,
        ];
    }

    public function bbox(array $geometry): ?array
    {
        $points = $this->flattenCoordinates($geometry);

        if ($points === []) {
            return null;
        }

        $lons = [];
        $lats = [];

        foreach ($points as $point) {
            if (! isset($point[0], $point[1])) {
                continue;
            }

            $lons[] = (float) $point[0];
            $lats[] = (float) $point[1];
        }

        if ($lons === [] || $lats === []) {
            return null;
        }

        return [
            'min_lon' => min($lons),
            'max_lon' => max($lons),
            'min_lat' => min($lats),
            'max_lat' => max($lats),
        ];
    }

    public function pointInGeometry(float $lon, float $lat, array $geometry): bool
    {
        $type = $geometry['type'] ?? null;
        $coordinates = $geometry['coordinates'] ?? [];

        if ($type === 'Polygon') {
            return $this->pointInRing($lon, $lat, $coordinates[0] ?? []);
        }

        if ($type === 'MultiPolygon') {
            foreach ($coordinates as $polygon) {
                if ($this->pointInRing($lon, $lat, $polygon[0] ?? [])) {
                    return true;
                }
            }
        }

        return false;
    }

    private function pointInRing(float $lon, float $lat, array $ring): bool
    {
        $inside = false;
        $count = count($ring);

        if ($count < 3) {
            return false;
        }

        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            $pi = $ring[$i];
            $pj = $ring[$j];

            if (! isset($pi[0], $pi[1], $pj[0], $pj[1])) {
                continue;
            }

            $xi = (float) $pi[0];
            $yi = (float) $pi[1];
            $xj = (float) $pj[0];
            $yj = (float) $pj[1];

            $intersects = (($yi > $lat) !== ($yj > $lat))
                && ($lon < (($xj - $xi) * ($lat - $yi) / (($yj - $yi) ?: 1.0) + $xi));

            if ($intersects) {
                $inside = ! $inside;
            }
        }

        return $inside;
    }
}
