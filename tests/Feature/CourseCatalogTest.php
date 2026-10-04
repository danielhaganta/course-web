<?php

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Section;
use Inertia\Testing\AssertableInertia as Assert;

test('course index only shows published courses', function () {
    $published = Course::factory()->published()->create();
    Course::factory()->create();

    $this->get(route('courses.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('courses/index')
            ->has('courses', 1)
            ->where('courses.0.slug', $published->slug));
});

test('unpublished course detail returns 404', function () {
    $course = Course::factory()->create();

    $this->get(route('courses.show', $course))->assertNotFound();
});

test('course detail does not expose private lesson data', function () {
    $course = Course::factory()->published()->create();
    $lesson = Lesson::factory()
        ->for(Section::factory()->for($course))
        ->create(['content' => 'Isi materi rahasia']);

    $this->get(route('courses.show', $course))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('courses/show')
            ->where('course.sections.0.lessons.0.id', $lesson->id)
            ->missing('course.sections.0.lessons.0.bunny_video_id')
            ->missing('course.sections.0.lessons.0.content'))
        ->assertDontSee('bunny_video_id')
        ->assertDontSee($lesson->bunny_video_id)
        ->assertDontSee('Isi materi rahasia');
});
