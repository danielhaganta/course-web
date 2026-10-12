<?php

use App\Models\Course;
use App\Models\CoursePurchase;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Section;
use App\Models\Subscription;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config(['services.bunny_stream.library_id' => '123', 'services.bunny_stream.token_key' => 'test-token-key']);

    $this->course = Course::factory()->published()->create();
    $firstSection = Section::factory()->for($this->course)->create(['sort_order' => 1]);
    $secondSection = Section::factory()->for($this->course)->create(['sort_order' => 2]);
    $this->lessons = collect([
        Lesson::factory()->for($firstSection)->create(['sort_order' => 1]),
        Lesson::factory()->for($firstSection)->create(['sort_order' => 2]),
        Lesson::factory()->for($secondSection)->create(['sort_order' => 1]),
    ]);
    $this->member = Subscription::factory()->create()->user;
});

function progressUrl(Course $course, Lesson $lesson): string
{
    return route('lessons.progress', ['course' => $course, 'lesson' => $lesson]);
}

test('member can mark and unmark a lesson they can watch', function () {
    $lesson = $this->lessons->first();

    $this->actingAs($this->member)
        ->post(progressUrl($this->course, $lesson), ['completed' => true])
        ->assertRedirect();

    expect(LessonProgress::sole()->completed_at)->not->toBeNull();

    $this->actingAs($this->member)
        ->post(progressUrl($this->course, $lesson), ['completed' => false])
        ->assertRedirect();

    expect(LessonProgress::sole()->completed_at)->toBeNull();
});

test('member cannot mark a lesson they cannot watch', function () {
    $userWithoutAccess = User::factory()->create();
    $exclusive = $this->lessons->last();
    $exclusive->update(['is_exclusive' => true]);

    $this->actingAs($userWithoutAccess)
        ->post(progressUrl($this->course, $this->lessons->first()), ['completed' => true])
        ->assertForbidden();

    // Pembeli satuan tidak bisa menonton materi exclusive
    $buyer = CoursePurchase::factory()->for($this->course)->create()->user;
    $this->actingAs($buyer)
        ->post(progressUrl($this->course, $exclusive), ['completed' => true])
        ->assertForbidden();

    expect(LessonProgress::count())->toBe(0);
});

test('guest is asked to log in before marking a lesson', function () {
    $this->post(progressUrl($this->course, $this->lessons->first()), ['completed' => true])
        ->assertRedirect(route('login'));
});

test('continue learning points to the first unfinished lesson', function () {
    LessonProgress::factory()->for($this->member)->for($this->lessons[0])->create();
    LessonProgress::factory()->for($this->member)->for($this->lessons[1])->create(['completed_at' => null]);

    $this->actingAs($this->member)
        ->get(route('courses.show', $this->course))
        ->assertInertia(fn (Assert $page) => $page
            ->where('course.completed_lesson_ids', [$this->lessons[0]->id])
            ->where('course.resume_lesson_id', $this->lessons[1]->id));
});

test('continue learning goes back to the first lesson when everything is finished', function () {
    $this->lessons->each(fn (Lesson $lesson) => LessonProgress::factory()->for($this->member)->for($lesson)->create());

    $this->actingAs($this->member)
        ->get(route('courses.show', $this->course))
        ->assertInertia(fn (Assert $page) => $page->where('course.resume_lesson_id', $this->lessons[0]->id));
});

test('catalog shows progress only for courses the user can access', function () {
    LessonProgress::factory()->for($this->member)->for($this->lessons[0])->create();

    $this->actingAs($this->member)
        ->get(route('courses.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('courses.0.completed_lessons_count', 1)
            ->where('courses.0.lessons_count', 3));

    $this->actingAs(User::factory()->create())
        ->get(route('courses.index'))
        ->assertInertia(fn (Assert $page) => $page->where('courses.0.completed_lessons_count', null));
});

test('lesson and course pages with progress never expose bunny_video_id', function () {
    LessonProgress::factory()->for($this->member)->for($this->lessons[0])->create();

    $this->actingAs($this->member)
        ->get(route('lessons.show', ['course' => $this->course, 'lesson' => $this->lessons[0]]))
        ->assertInertia(fn (Assert $page) => $page
            ->component('lessons/show')
            ->where('completedLessonIds', [$this->lessons[0]->id]))
        ->assertDontSee('bunny_video_id')
        ->assertDontSee($this->lessons[1]->bunny_video_id);

    $this->actingAs($this->member)
        ->get(route('courses.show', $this->course))
        ->assertDontSee('bunny_video_id')
        ->assertDontSee($this->lessons[0]->bunny_video_id);
});
