<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'credits',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'credits' => 'integer',
            'total_hours_required' => 'integer',
            'weekly_hours_required' => 'decimal:2',
        ];
    }

    protected function credits(): Attribute
    {
        return Attribute::make(
            set: fn (int $credits): array => [
                'credits' => $credits,
                'total_hours_required' => $credits * 48,
                'weekly_hours_required' => $credits * 4,
            ],
        );
    }

    protected function totalHoursRequired(): Attribute
    {
        return Attribute::get(fn (): int => $this->credits * 48);
    }

    protected function weeklyHoursRequired(): Attribute
    {
        return Attribute::get(fn (): float => $this->total_hours_required / 12);
    }

    /**
     * @return HasMany<Task, $this>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * @return HasMany<StudySession, $this>
     */
    public function studySessions(): HasMany
    {
        return $this->hasMany(StudySession::class);
    }
}
