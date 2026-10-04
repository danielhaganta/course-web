<?php

use App\Enums\LessonType;
use App\Filament\Resources\Courses\Pages\CreateCourse;
use App\Filament\Resources\Courses\Resources\Sections\Pages\EditSection;
use App\Filament\Resources\Courses\Resources\Sections\RelationManagers\LessonsRelationManager;
use App\Models\Course;
use App\Models\Section;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

test('non admin users cannot access the admin panel', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/admin')->assertForbidden();
});

test('admin can open course pages', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $course = Course::factory()->create();

    $this->get('/admin/courses')->assertOk();
    $this->get("/admin/courses/{$course->slug}/edit")->assertOk();
});

// Test terpisah: Filament menyimpan request pertama (originalRequest) selama satu test
test('admin can open section page', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $section = Section::factory()->create();

    $this->get("/admin/courses/{$section->course->slug}/sections/{$section->id}/edit")->assertOk();
});

test('slug is generated from title when creating a course', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));

    Livewire::test(CreateCourse::class)
        ->fillForm(['title' => 'Belajar Color Grading'])
        ->assertSchemaStateSet(['slug' => 'belajar-color-grading'])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Course::where('slug', 'belajar-color-grading')->exists())->toBeTrue();
});

test('video lesson requires a bunny video id', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $section = Section::factory()->create();

    $lessons = fn () => Livewire::test(LessonsRelationManager::class, [
        'ownerRecord' => $section,
        'pageClass' => EditSection::class,
    ]);

    $lessons()
        ->callAction(TestAction::make(CreateAction::class)->table(), [
            'title' => 'Intro',
            'type' => LessonType::Video,
            'bunny_video_id' => null,
        ])
        ->assertHasFormErrors(['bunny_video_id' => 'required']);

    $lessons()
        ->callAction(TestAction::make(CreateAction::class)->table(), [
            'title' => 'Catatan',
            'type' => LessonType::Text,
            'content' => '<p>Isi materi</p>',
        ])
        ->assertHasNoFormErrors();

    expect($section->lessons()->sole()->type)->toBe(LessonType::Text);
});
