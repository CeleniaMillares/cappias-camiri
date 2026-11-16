<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class InsensitiveSearch
{
    public static function whereContains(Builder $query, string $column, string $term): void
    {
        $column = $query->getModel()->qualifyColumn($column);
        $pattern = '%'.$term.'%';

        if (DB::connection()->getDriverName() === 'pgsql') {
            $expression = "translate(lower({$column}), 'áéíóúüñ', 'aeiouun')";
            $query->whereRaw(
                "{$expression} ILIKE translate(lower(?), 'áéíóúüñ', 'aeiouun')",
                [$pattern]
            );

            return;
        }

        $expression = "LOWER({$column})";
        $replacements = [
            'Á' => 'a', 'á' => 'a',
            'É' => 'e', 'é' => 'e',
            'Í' => 'i', 'í' => 'i',
            'Ó' => 'o', 'ó' => 'o',
            'Ú' => 'u', 'ú' => 'u',
            'Ü' => 'u', 'ü' => 'u',
            'Ñ' => 'n', 'ñ' => 'n',
        ];

        foreach ($replacements as $source => $replacement) {
            $expression = "REPLACE({$expression}, '{$source}', '{$replacement}')";
        }

        $normalizedPattern = strtr(mb_strtolower($pattern, 'UTF-8'), [
            'á' => 'a', 'é' => 'e', 'í' => 'i',
            'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
        ]);
        $query->whereRaw("{$expression} LIKE ?", [$normalizedPattern]);
    }
}
