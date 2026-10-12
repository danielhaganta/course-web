<?php

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Section;
use App\Models\Subscription;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config([
        'services.bunny_stream.library_id' => '123',
        'services.bunny_stream.token_key' => 'test-token-key',
        'services.bunny_stream.embed_token_ttl' => 7200,
    ]);

    $this->course = Course::factory()->published()->create();
    $section = Section::factory()->for($this->course)->create();
    $this->previewLesson = Lesson::factory()->for($section)->create(['is_preview' => true, 'sort_order' => 1]);
    $this->memberLesson = Lesson::factory()->for($section)->create(['is_preview' => false, 'sort_order' => 2]);
});

function lessonUrl(Course $course, Lesson $lesson): string
{
    return route('lessons.show', ['course' => $course, 'lesson' => $lesson]);
}

test('guest can open a preview lesson and gets an embed url', function () {
    $this->get(lessonUrl($this->course, $this->previewLesson))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('lessons/show')
            ->where('lesson.id', $this->previewLesson->id)
            ->where('embedUrl', fn (string $url) => str_starts_with(
                $url,
                "https://player.mediadelivery.net/embed/123/{$this->previewLesson->bunny_video_id}?token=",
            ))
            ->where('watermark', null));
});

test('guest gets the locked page for a member lesson without an embed url', function () {
    $this->get(lessonUrl($this->course, $this->memberLesson))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('lessons/locked')
            ->missing('embedUrl')
            ->missing('lesson.content'))
        ->assertDontSee($this->memberLesson->bunny_video_id)
        ->assertDontSee('player.mediadelivery.net');
});

test('admin can open a member lesson', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get(lessonUrl($this->course, $this->memberLesson))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('lessons/show')
            ->where('lesson.id', $this->memberLesson->id)
            ->where('embedUrl', fn (?string $url) => filled($url))
            ->where('watermark', $admin->email));
});

test('lesson from another course returns 404', function () {
    $otherCourse = Course::factory()->published()->create();

    $this->get(lessonUrl($otherCourse, $this->previewLesson))->assertNotFound();
});

test('lesson of an unpublished course returns 404', function () {
    $this->course->update(['is_published' => false]);

    $this->get(lessonUrl($this->course, $this->previewLesson))->assertNotFound();
});

test('pages never expose bunny_video_id', function () {
    // Video aktif hanya boleh muncul di dalam URL embed bertoken
    $this->get(lessonUrl($this->course, $this->previewLesson))
        ->assertInertia(fn (Assert $page) => $page
            ->missing('lesson.bunny_video_id')
            ->missing('course.sections.0.lessons.0.bunny_video_id'))
        ->assertDontSee('bunny_video_id')
        ->assertDontSee($this->memberLesson->bunny_video_id);

    $this->get(route('courses.show', $this->course))
        ->assertDontSee('bunny_video_id')
        ->assertDontSee($this->previewLesson->bunny_video_id)
        ->assertDontSee($this->memberLesson->bunny_video_id);
});

test('embed url contains a valid token and expiry', function () {
    $this->freezeTime();
    $videoId = $this->previewLesson->bunny_video_id;
    $expires = now()->addSeconds(7200)->timestamp;
    $token = hash('sha256', 'test-token-key'.$videoId.$expires);

    $this->get(lessonUrl($this->course, $this->previewLesson))
        ->assertInertia(fn (Assert $page) => $page->where(
            'embedUrl',
            "https://player.mediadelivery.net/embed/123/{$videoId}?token={$token}&expires={$expires}",
        ));
});

test('course detail marks member lessons as locked for guests', function () {
    $this->get(route('courses.show', $this->course))
        ->assertInertia(fn (Assert $page) => $page
            ->where('course.sections.0.lessons.0.can_watch', true)
            ->where('course.sections.0.lessons.1.can_watch', false));
});

test('standard subscriber gets the premium locked page for an exclusive lesson', function () {
    $this->memberLesson->update(['is_exclusive' => true]);
    $user = Subscription::factory()->create()->user;

    $this->actingAs($user)
        ->get(lessonUrl($this->course, $this->memberLesson))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('lessons/locked')
            ->where('lesson.is_exclusive', true)
            ->missing('embedUrl'));
});
