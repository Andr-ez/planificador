<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubjectRequest;
use App\Models\Subject;
use App\Services\StudyPlannerService;
use Illuminate\Http\JsonResponse;

class SubjectController extends Controller
{
    public function index(): JsonResponse
    {
        $subjects = Subject::query()
            ->withSum('studySessions', 'duration_minutes')
            ->get()
            ->each(function (Subject $subject): void {
                $subject->makeHidden('study_sessions_sum_duration_minutes');
            });

        return response()->json($subjects);
    }

    public function store(StoreSubjectRequest $request, StudyPlannerService $studyPlannerService): JsonResponse
    {
        $validated = $request->validated();
        $hours = $studyPlannerService->calculateHours($validated['credits']);

        $subject = new Subject([
            'name' => $validated['name'],
            'credits' => $validated['credits'],
        ]);

        $subject->forceFill([
            'total_hours_required' => $hours['total_hours'],
            'weekly_hours_required' => $hours['weekly_hours'],
        ]);
        $subject->save();

        return response()->json($subject, 201);
    }
}
