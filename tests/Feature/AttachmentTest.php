<?php

use App\Enums\LessonType;
use App\Filament\Resources\Courses\Resources\Sections\Pages\EditSection;
use App\Filament\Resources\Courses\Resources\Sections\RelationManagers\LessonsRelationManager;
use App\Models\Course;
use App\Models\CoursePurchase;
use App\Models\Lesson;
use App\Models\LessonAttachment;
use App\Models\Plan;
use App\Models\Section;
use App\Models\Subscription;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Filament\Forms\Components\Repeater;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake(LessonAttachment::diskName());

    $this->course = Course::factory()->published()->create();
    $this->lesson = Lesson::factory()->for(Section::factory()->for($this->course))->create(['is_preview' => true]);
    $this->attachment = LessonAttachment::factory()->for($this->lesson)->create([
        'name' => 'LUT Pack',
        'path' => "lessons/{$this->lesson->id}/abc123.zip",
    ]);
    LessonAttachment::disk()->put($this->attachment->path, 'isi file latihan');
});

function downloadUrl(LessonAttachment $attachment): string
{
    return route('attachments.download', $attachment);
}

test('premium member can download an attachment with its display name', function () {
    $user = Subscription::factory()->for(Plan::factory()->premium())->create()->user;

    $this->actingAs($user)
        ->get(downloadUrl($this->attachment))
        ->assertOk()
        ->assertDownload('LUT Pack.zip');
});

test('standard member and course buyer cannot download', function (Closure $makeUser) {
    $this->actingAs($makeUser($this->course))
        ->get(downloadUrl($this->attachment))
        ->assertForbidden();
})->with([
    'standard' => fn () => Subscription::factory()->create()->user,
    'course buyer' => fn (Course $course) => CoursePurchase::factory()->for($course)->create()->user,
]);

test('guest is redirected to login', function () {
    $this->get(downloadUrl($this->attachment))->assertRedirect(route('login'));
});

test('premium download redirects to a 10 minute temporary url on r2', function () {
    config([
        'filesystems.attachments.disk' => 'r2',
        'filesystems.disks.r2.key' => 'key',
        'filesystems.disks.r2.secret' => 'secret',
        'filesystems.disks.r2.bucket' => 'course-files',
        'filesystems.disks.r2.endpoint' => 'https://account.r2.cloudflarestorage.com',
    ]);
    $user = Subscription::factory()->for(Plan::factory()->premium())->create()->user;

    $location = $this->actingAs($user)
        ->get(downloadUrl($this->attachment))
        ->assertRedirect()
        ->headers->get('Location');

    expect($location)
        ->toStartWith("https://account.r2.cloudflarestorage.com/course-files/{$this->attachment->path}")
        // SDK menghitung dari jam sistem, bisa 599 kalau detik berganti saat request
        ->toMatch('/X-Amz-Expires=(599|600)&/')
        ->toContain('response-content-disposition='.rawurlencode('attachment; filename="LUT Pack.zip"'));
});

test('lesson page sends attachment data without the storage path', function () {
    $this->get(route('lessons.show', ['course' => $this->course, 'lesson' => $this->lesson]))
        ->assertInertia(fn (Assert $page) => $page
            ->component('lessons/show')
            ->where('attachments.0', [
                'id' => $this->attachment->id,
                'name' => 'LUT Pack',
                'size_bytes' => $this->attachment->size_bytes,
            ])
            ->where('canDownload', false))
        ->assertDontSee($this->attachment->path, escape: false);
});

test('deleting an attachment deletes its file', function () {
    $this->attachment->delete();

    LessonAttachment::disk()->assertMissing($this->attachment->path);
});

test('deleting a lesson deletes its attachment files', function () {
    $this->lesson->delete();

    LessonAttachment::disk()->assertMissing($this->attachment->path);
    expect(LessonAttachment::count())->toBe(0);
});

test('admin upload on a new lesson stores the file in its folder with name and size', function () {
    Storage::fake('local');
    Repeater::fake();
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $section = $this->lesson->section;

    Livewire::test(LessonsRelationManager::class, ['ownerRecord' => $section, 'pageClass' => EditSection::class])
        ->callAction(TestAction::make(CreateAction::class)->table(), [
            'title' => 'Materi baru',
            'type' => LessonType::Text,
            'content' => '<p>Isi</p>',
            'attachments' => [
                ['path' => [UploadedFile::fake()->create('Footage Latihan.mp4', 2048)], 'name' => null],
            ],
        ])
        ->assertHasNoFormErrors();

    $newLesson = $section->lessons()->where('title', 'Materi baru')->sole();
    $uploaded = $newLesson->attachments()->sole();

    expect($uploaded->name)->toBe('Footage Latihan.mp4')
        ->and($uploaded->size_bytes)->toBe(2048 * 1024)
        ->and($uploaded->path)->toStartWith("lessons/{$newLesson->id}/")->toEndWith('.mp4');
    LessonAttachment::disk()->assertExists($uploaded->path);
});
