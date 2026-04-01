<?php

namespace App\Services\Geo;

class YandexGeometryConverter
{
    /**
     * Convert OSM/GeoJSON coordinates [lon, lat] into Yandex JS default order [lat, lon].
     */
    public function fromOsmGeometry(array $geometry): ?array
    {
        $type = $geometry['type'] ?? null;
        $coordinates = $geometry['coordinates'] ?? null;

        if (! in_array($type, ['Polygon', 'MultiPolygon'], true) || ! is_array($coordinates)) {
            return null;
        }

        return [
            'type' => $type,
            'coordinates' => $this->swapCoordinateOrder($coordinates),
        ];
    }

    private function swapCoordinateOrder(array $value): array
    {
        // Leaf coordinate pair [lon, lat] -> [lat, lon].
        if (isset($value[0], $value[1]) && is_numeric($value[0]) && is_numeric($value[1]) && count($value) === 2) {
            return [(float) $value[1], (float) $value[0]];
        }

        $result = [];
        foreach ($value as $item) {
            if (is_array($item)) {
                $result[] = $this->swapCoordinateOrder($item);
            }
        }

        return $result;
    }
}
