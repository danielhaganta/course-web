<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateLessonProgressRequest;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\RedirectResponse;

class LessonProgressController extends Controller
{
    /**
     * Tandai atau batalkan tanda selesai sebuah materi.
     */
    public function update(UpdateLessonProgressRequest $request, Course $course, Lesson $lesson): RedirectResponse
    {
        abort_unless($course->is_published, 404);

        LessonProgress::updateOrCreate(
            ['user_id' => $request->user()->id, 'lesson_id' => $lesson->id],
            ['completed_at' => $request->boolean('completed') ? now() : null],
        );

        return back();
    }
}
