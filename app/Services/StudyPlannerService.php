<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Subject;

class StudyPlannerService
{
    /**
     * @return array{total_hours: int, weekly_hours: float}
     */
    public function calculateHours(int $credits): array
    {
        $totalHours = $credits * 48;

        return [
            'total_hours' => $totalHours,
            'weekly_hours' => round($totalHours / 12, 2),
        ];
    }

    public function calculateProgress(Subject $subject): float
    {
        $totalHoursRequired = $subject->total_hours_required;

        if ($totalHoursRequired <= 0) {
            return 0.0;
        }

        $totalStudyMinutes = array_key_exists('study_sessions_sum_duration_minutes', $subject->getAttributes())
            ? (int) $subject->getAttribute('study_sessions_sum_duration_minutes')
            : (int) $subject->studySessions()->sum('duration_minutes');
        $totalStudyHours = $totalStudyMinutes / 60;

        return ($totalStudyHours / $totalHoursRequired) * 100;
    }
}
