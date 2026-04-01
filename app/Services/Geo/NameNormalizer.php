<?php

namespace App\Services\Geo;

class NameNormalizer
{
    public function normalize(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $value = mb_strtolower(trim($value));
        $value = preg_replace('/\b(г|гор|город|пос|поселок|посёлок|с|село|дер|деревня|р\-н|район|обл|область|респ|республика|край|ao|ао)\.?\b/u', ' ', $value);
        $value = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value);
        $value = preg_replace('/\s+/u', ' ', $value);

        return trim($value ?? '');
    }
}
