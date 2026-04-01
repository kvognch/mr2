<?php

namespace App\Services\Geo;

use Generator;
use RuntimeException;

class GeoJsonFeatureStreamReader
{
    /**
     * Iterate features from a GeoJSON FeatureCollection without loading full file into memory.
     *
     * @return Generator<array>
     */
    public function iterate(string $path): Generator
    {
        $handle = fopen($path, 'rb');
        if (! $handle) {
            throw new RuntimeException("Unable to open file: {$path}");
        }

        try {
            $this->seekToFeaturesArray($handle);

            while (($char = fgetc($handle)) !== false) {
                if (ctype_space($char) || $char === ',') {
                    continue;
                }

                if ($char === ']') {
                    break;
                }

                if ($char !== '{') {
                    continue;
                }

                $json = $this->readJsonObject($handle, $char);
                $feature = json_decode($json, true);

                if (is_array($feature)) {
                    yield $feature;
                }
            }
        } finally {
            fclose($handle);
        }
    }

    private function seekToFeaturesArray($handle): void
    {
        $buffer = '';

        while (($char = fgetc($handle)) !== false) {
            $buffer .= $char;

            if (strlen($buffer) > 64) {
                $buffer = substr($buffer, -64);
            }

            if (str_contains($buffer, '"features"')) {
                break;
            }
        }

        while (($char = fgetc($handle)) !== false) {
            if ($char === '[') {
                return;
            }
        }

        throw new RuntimeException('Invalid GeoJSON: features array not found.');
    }

    private function readJsonObject($handle, string $firstChar): string
    {
        $json = $firstChar;
        $depth = 1;
        $inString = false;
        $escaped = false;

        while (($char = fgetc($handle)) !== false) {
            $json .= $char;

            if ($inString) {
                if ($escaped) {
                    $escaped = false;
                    continue;
                }

                if ($char === '\\') {
                    $escaped = true;
                    continue;
                }

                if ($char === '"') {
                    $inString = false;
                }

                continue;
            }

            if ($char === '"') {
                $inString = true;
                continue;
            }

            if ($char === '{') {
                $depth++;
                continue;
            }

            if ($char === '}') {
                $depth--;

                if ($depth === 0) {
                    return $json;
                }
            }
        }

        throw new RuntimeException('Unexpected EOF while reading GeoJSON feature object.');
    }
}
