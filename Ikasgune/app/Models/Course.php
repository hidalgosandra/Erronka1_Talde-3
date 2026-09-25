<?php

namespace App\Models;

use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'description', 'category', 'level', 'duration_minutes', 'is_featured', 'translations'])]
class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['translations' => 'array', 'is_featured' => 'boolean'];
    }

    public function localized(string $field): string
    {
        $translated = $this->translations[app()->getLocale()][$field] ?? null;

        return filled($translated) ? $translated : (string) $this->getAttribute($field);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }
}
