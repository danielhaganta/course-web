<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonAttachment;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = [
            [
                'title' => 'Cinematic Color Grading in DaVinci Resolve',
                'summary' => 'Learn to grade video so it looks like film, from basic color correction to building your own look.',
                'price' => 349_000,
                'sections' => ['Color Correction Basics', 'Building a Cinematic Look', 'Workflow and Export'],
            ],
            [
                'title' => 'Video Editing for Content Creators',
                'summary' => 'Edit clean, fast, watchable videos for YouTube and social media.',
                'price' => 299_000,
                'sections' => ['Project Setup', 'Editing Techniques', 'Audio and Finishing'],
            ],
        ];

        foreach ($courses as $courseIndex => $data) {
            $course = Course::factory()->published()->create([
                'title' => $data['title'],
                'slug' => Str::slug($data['title']),
                'summary' => $data['summary'],
                'price' => $data['price'],
                'sort_order' => $courseIndex + 1,
            ]);

            foreach ($data['sections'] as $sectionIndex => $sectionTitle) {
                $section = Section::factory()->for($course)->create([
                    'title' => $sectionTitle,
                    'sort_order' => $sectionIndex + 1,
                ]);

                $this->seedLessons(
                    $section,
                    isFirstSection: $sectionIndex === 0,
                    isLastSection: $sectionIndex === array_key_last($data['sections']),
                );
            }
        }
    }

    private function seedLessons(Section $section, bool $isFirstSection, bool $isLastSection): void
    {
        $lessons = Lesson::factory()
            ->for($section)
            ->count(fake()->numberBetween(3, 5))
            ->sequence(fn (Sequence $sequence) => [
                'sort_order' => $sequence->index + 1,
                'is_preview' => $isFirstSection && $sequence->index === 0,
            ])
            ->create();

        // Materi terakhir course khusus Premium
        if ($isLastSection) {
            $lessons->last()->update(['is_exclusive' => true]);
        }

        $lessons->random(fake()->numberBetween(1, 2))
            ->each(fn (Lesson $lesson) => LessonAttachment::factory()->for($lesson)->create());
    }
}
