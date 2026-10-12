<?php

use App\Models\Course;
use App\Models\CoursePurchase;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Plan;
use App\Models\Section;
use App\Models\Subscription;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('dashboard shows the active subscription and purchased courses', function () {
    $subscription = Subscription::factory()->for(Plan::factory()->premium())->create();
    Subscription::factory()->expired()->for($subscription->user)->create();
    $purchase = CoursePurchase::factory()->for($subscription->user)->create();

    $this->actingAs($subscription->user)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->where('subscription.plan_name', 'Premium Bulanan')
            ->where('subscription.ends_at', $subscription->ends_at->toIso8601String())
            ->has('purchasedCourses', 1)
            ->where('purchasedCourses.0.slug', $purchase->course->slug));
});

test('dashboard shows no subscription when it has expired', function () {
    $subscription = Subscription::factory()->expired()->create();

    $this->actingAs($subscription->user)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page->where('subscription', null));
});

test('dashboard shows the last studied course with its next unfinished lesson', function () {
    $subscription = Subscription::factory()->create();
    $course = Course::factory()->published()->create();
    $section = Section::factory()->for($course)->create();
    $lessons = Lesson::factory()->for($section)->count(3)->sequence(fn ($sequence) => ['sort_order' => $sequence->index + 1])->create();
    LessonProgress::factory()->for($subscription->user)->for($lessons[0])->create();

    $this->actingAs($subscription->user)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('continueLearning.slug', $course->slug)
            ->where('continueLearning.completed_lessons_count', 1)
            ->where('continueLearning.lessons_count', 3)
            ->where('continueLearning.next_lesson_id', $lessons[1]->id)
            ->where('courses.0.slug', $course->slug)
            ->where('courses.0.completed_lessons_count', 1));
});

test('dashboard lists only courses the member can access', function () {
    $purchase = CoursePurchase::factory()->create();
    Course::factory()->published()->create();

    $this->actingAs($purchase->user)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('courses', 1)
            ->where('courses.0.slug', $purchase->course->slug)
            ->where('continueLearning', null));
});

test('dashboard shows the last ended subscription for renewal', function () {
    $ended = Subscription::factory()->expired()->for(Plan::factory()->premium())->create();

    $this->actingAs($ended->user)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('subscription', null)
            ->where('endedSubscription.plan_name', 'Premium Bulanan')
            ->has('courses', 0));
});
