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
                'title' => 'Color Grading Sinematik dengan DaVinci Resolve',
                'summary' => 'Belajar mewarnai video agar terlihat seperti film, dari koreksi warna dasar sampai membuat look sendiri.',
                'sections' => ['Dasar Koreksi Warna', 'Membuat Look Sinematik', 'Workflow dan Export'],
            ],
            [
                'title' => 'Video Editing untuk Kreator Konten',
                'summary' => 'Edit video YouTube dan media sosial yang rapi, cepat, dan enak ditonton.',
                'sections' => ['Persiapan Project', 'Teknik Editing', 'Audio dan Finishing'],
            ],
        ];

        foreach ($courses as $courseIndex => $data) {
            $course = Course::factory()->published()->create([
                'title' => $data['title'],
                'slug' => Str::slug($data['title']),
                'summary' => $data['summary'],
                'sort_order' => $courseIndex + 1,
            ]);

            foreach ($data['sections'] as $sectionIndex => $sectionTitle) {
                $section = Section::factory()->for($course)->create([
                    'title' => $sectionTitle,
                    'sort_order' => $sectionIndex + 1,
                ]);

                $this->seedLessons($section, isFirstSection: $sectionIndex === 0);
            }
        }
    }

    private function seedLessons(Section $section, bool $isFirstSection): void
    {
        $lessons = Lesson::factory()
            ->for($section)
            ->count(fake()->numberBetween(3, 5))
            ->sequence(fn (Sequence $sequence) => [
                'sort_order' => $sequence->index + 1,
                'is_preview' => $isFirstSection && $sequence->index === 0,
            ])
            ->create();

        $lessons->random(fake()->numberBetween(1, 2))
            ->each(fn (Lesson $lesson) => LessonAttachment::factory()->for($lesson)->create());
    }
}
