<?php

use App\Models\Course;
use App\Models\CoursePurchase;
use App\Models\Lesson;
use App\Models\Plan;
use App\Models\Section;
use App\Models\Subscription;
use App\Models\User;
use App\Services\AccessService;

beforeEach(function () {
    $this->access = app(AccessService::class);
    $this->course = Course::factory()->published()->create();
    $section = Section::factory()->for($this->course)->create();
    $this->regularLesson = Lesson::factory()->for($section)->create();
    $this->exclusiveLesson = Lesson::factory()->for($section)->create(['is_exclusive' => true]);
    $this->otherCourse = Course::factory()->published()->create();
    $this->otherCourseLesson = Lesson::factory()->for(Section::factory()->for($this->otherCourse))->create();
});

test('active standard subscriber can watch regular lessons of every course but not exclusive lessons or downloads', function () {
    $user = Subscription::factory()->create()->user;

    expect($this->access->canWatch($user, $this->regularLesson))->toBeTrue()
        ->and($this->access->canWatch($user, $this->otherCourseLesson))->toBeTrue()
        ->and($this->access->canAccessCourse($user, $this->otherCourse))->toBeTrue()
        ->and($this->access->canWatch($user, $this->exclusiveLesson))->toBeFalse()
        ->and($this->access->canDownload($user, $this->regularLesson))->toBeFalse();
});

test('active premium subscriber can watch exclusive lessons and download', function () {
    $user = Subscription::factory()->for(Plan::factory()->premium())->create()->user;

    expect($this->access->canWatch($user, $this->exclusiveLesson))->toBeTrue()
        ->and($this->access->canWatch($user, $this->regularLesson))->toBeTrue()
        ->and($this->access->canDownload($user, $this->regularLesson))->toBeTrue();
});

test('course buyer can only watch regular lessons of the purchased course', function () {
    $user = CoursePurchase::factory()->for($this->course)->create()->user;

    expect($this->access->canWatch($user, $this->regularLesson))->toBeTrue()
        ->and($this->access->canAccessCourse($user, $this->course))->toBeTrue()
        ->and($this->access->canWatch($user, $this->otherCourseLesson))->toBeFalse()
        ->and($this->access->canAccessCourse($user, $this->otherCourse))->toBeFalse()
        ->and($this->access->canWatch($user, $this->exclusiveLesson))->toBeFalse()
        ->and($this->access->canDownload($user, $this->regularLesson))->toBeFalse();
});

test('subscription outside its period gives no access', function (string $state) {
    $user = Subscription::factory()->{$state}()->for(Plan::factory()->premium())->create()->user;

    expect($this->access->canWatch($user, $this->regularLesson))->toBeFalse()
        ->and($this->access->canWatch($user, $this->exclusiveLesson))->toBeFalse()
        ->and($this->access->canDownload($user, $this->regularLesson))->toBeFalse()
        ->and($this->access->canAccessCourse($user, $this->course))->toBeFalse();
})->with(['expired', 'upcoming']);

test('deactivating a plan keeps access for existing subscriptions', function () {
    $subscription = Subscription::factory()->for(Plan::factory()->premium())->create();
    $subscription->plan->update(['is_active' => false]);

    $user = User::find($subscription->user_id);

    expect($this->access->canWatch($user, $this->exclusiveLesson))->toBeTrue()
        ->and($this->access->canDownload($user, $this->regularLesson))->toBeTrue();
});

test('user without subscription or purchase can only watch preview lessons', function () {
    $user = User::factory()->create();
    $this->regularLesson->update(['is_preview' => true]);

    expect($this->access->canWatch($user, $this->regularLesson))->toBeTrue()
        ->and($this->access->canWatch($user, $this->otherCourseLesson))->toBeFalse()
        ->and($this->access->canAccessCourse($user, $this->course))->toBeFalse()
        ->and($this->access->canDownload($user, $this->regularLesson))->toBeFalse();
});

test('admin can watch exclusive lessons and download without a subscription', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    expect($this->access->canWatch($admin, $this->exclusiveLesson))->toBeTrue()
        ->and($this->access->canDownload($admin, $this->regularLesson))->toBeTrue()
        ->and($this->access->canAccessCourse($admin, $this->course))->toBeTrue();
});
