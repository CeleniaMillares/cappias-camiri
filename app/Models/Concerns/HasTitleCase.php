<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

trait HasTitleCase
{
    protected static function bootHasTitleCase(): void
    {
        static::saving(function (Model $model): void {
            foreach ($model->titleCaseAttributes() as $attribute) {
                $value = $model->getAttribute($attribute);
                if (! is_string($value) || trim($value) === '') {
                    continue;
                }

                $model->setAttribute(
                    $attribute,
                    mb_convert_case(mb_strtolower(trim($value), 'UTF-8'), MB_CASE_TITLE, 'UTF-8')
                );
            }
        });
    }

    protected function titleCaseAttributes(): array
    {
        return [];
    }
}
