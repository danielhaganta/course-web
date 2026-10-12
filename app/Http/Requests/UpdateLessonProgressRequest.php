<?php

namespace App\Http\Requests;

use App\Models\Lesson;
use App\Services\AccessService;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLessonProgressRequest extends FormRequest
{
    /**
     * Hanya materi yang boleh ditonton yang bisa ditandai selesai.
     */
    public function authorize(AccessService $access): bool
    {
        $lesson = $this->route('lesson');

        return $lesson instanceof Lesson && $access->canWatch($this->user(), $lesson);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'completed' => ['required', 'boolean'],
        ];
    }
}
