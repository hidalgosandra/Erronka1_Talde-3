<?php

namespace App\Models;

use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'description', 'category', 'level', 'duration_minutes', 'is_featured', 'translations', 'teacher_id'])]
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
        $translated = data_get($this->getAttribute('translations'), app()->getLocale().'.'.$field);

        return filled($translated) ? $translated : (string) $this->getAttribute($field);
    }

    public function scopeVisibleTo(Builder $query, ?User $user): void
    {
        if ($user === null || ! $user->isStudent()) {
            return;
        }

        $query->whereHas('teacher', fn ($teacher) => $teacher->where('is_teacher', true)
            ->whereHas('teachingClasses', fn ($classes) => $classes->whereKey($user->school_class_id ?? 0)));
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(CourseMaterial::class);
    }
}
