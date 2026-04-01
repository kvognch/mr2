<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GeoUnit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeoController extends Controller
{
    public function tree(Request $request): JsonResponse
    {
        $parentId = $request->query('parent_id');

        $items = GeoUnit::query()
            ->when($parentId !== null, fn ($q) => $q->where('parent_id', (int) $parentId), fn ($q) => $q->whereNull('parent_id'))
            ->orderBy('name')
            ->get(['id', 'parent_id', 'name', 'is_active']);

        return response()->json(['data' => $items]);
    }

    public function unit(int $id): JsonResponse
    {
        $unit = GeoUnit::query()
            ->with('children:id,parent_id,name,is_active')
            ->findOrFail($id);

        return response()->json(['data' => $unit]);
    }

    public function mapFeatures(Request $request): JsonResponse
    {
        $level = $request->query('level');

        $units = GeoUnit::query()
            ->active()
            ->whereNotNull('geometry_yandex')
            ->when($level, fn ($q) => $q->where('level', $level))
            ->get();

        $items = [];

        foreach ($units as $unit) {
            $items[] = [
                'id' => $unit->id,
                'name' => $unit->name,
                'level' => $unit->level,
                'admin_level' => $unit->admin_level,
                'geometry' => $unit->geometry_yandex,
                'bbox' => [
                    'min_lat' => $unit->bbox_min_lat,
                    'min_lon' => $unit->bbox_min_lon,
                    'max_lat' => $unit->bbox_max_lat,
                    'max_lon' => $unit->bbox_max_lon,
                ],
            ];
        }

        return response()->json(['data' => $items]);
    }
}
