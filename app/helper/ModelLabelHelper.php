<?php

namespace App\helper;

use Illuminate\Support\Str;

class ModelLabelHelper
{
    public static function getModelLabel(string $modelClass): string
    {
        $modelName = class_basename($modelClass); // e.g., "TaskFollowUps"

        // Convert to headline case (e.g., "Task Follow Ups")
        $headline = Str::headline($modelName);

        // Convert to lowercase and capitalize the first character of the first word
        $formatted = Str::lower($headline); // e.g., "task follow ups"
        $formatted = Str::ucfirst($formatted); // e.g., "Task follow ups"

        return __($formatted);
    }

    public static function getPluralModelLabel(string $modelClass): string
    {
        $modelName = class_basename($modelClass); // e.g., "TaskFollowUps"

        // Convert to headline case (e.g., "Task Follow Ups")
        $headline = Str::headline($modelName);

        // Convert to lowercase and capitalize the first character of the first word
        $formatted = Str::lower($headline); // e.g., "task follow ups"
        $formatted = Str::ucfirst($formatted); // e.g., "Task follow ups"

        // Pluralize the formatted string
        $plural = Str::plural($formatted); // e.g., "Task follow ups" -> "Task follow ups" (plural)

        return __($plural); // Translate the plural label
    }
}
