<?php

namespace App\Http\Controllers;

use App\Models\LessonAttachment;
use App\Services\AccessService;
use Illuminate\Filesystem\LocalFilesystemAdapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    private const int TEMPORARY_URL_MINUTES = 10;

    public function download(Request $request, LessonAttachment $attachment, AccessService $access): RedirectResponse|StreamedResponse
    {
        $lesson = $attachment->lesson;

        abort_unless($lesson->section->course->is_published, 404);
        abort_unless($access->canDownload($request->user(), $lesson), 403);

        $disk = LessonAttachment::disk();
        $fileName = $attachment->downloadName();

        // Disk lokal juga punya URL sementara, tapi tidak bisa mengatur nama file download
        if ($disk instanceof LocalFilesystemAdapter || ! $disk->providesTemporaryUrls()) {
            abort_unless($disk->exists($attachment->path), 404);

            return $disk->download($attachment->path, $fileName);
        }

        return redirect()->away($disk->temporaryUrl(
            $attachment->path,
            now()->addMinutes(self::TEMPORARY_URL_MINUTES),
            ['ResponseContentDisposition' => HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                $fileName,
                str_replace('%', '', Str::ascii($fileName)),
            )],
        ));
    }
}
