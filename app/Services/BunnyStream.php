<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BunnyStream
{
    // Format token: https://docs.bunny.net/docs/stream-embed-token-authentication
    public function embedUrl(string $videoId): string
    {
        $libraryId = config('services.bunny_stream.library_id');
        $expires = now()->addSeconds(config('services.bunny_stream.embed_token_ttl'))->timestamp;
        $token = hash('sha256', config('services.bunny_stream.token_key').$videoId.$expires);

        return "https://player.mediadelivery.net/embed/{$libraryId}/{$videoId}?".http_build_query([
            'token' => $token,
            'expires' => $expires,
        ]);
    }

    /**
     * Durasi video dalam detik, null jika gagal atau video belum selesai diproses.
     */
    public function videoDuration(string $videoId): ?int
    {
        $libraryId = config('services.bunny_stream.library_id');

        try {
            $response = Http::withHeaders(['AccessKey' => config('services.bunny_stream.api_key')])
                ->acceptJson()
                ->timeout(5)
                ->get("https://video.bunnycdn.com/library/{$libraryId}/videos/{$videoId}");
        } catch (ConnectionException $exception) {
            Log::warning('Bunny Stream tidak bisa dihubungi', ['video_id' => $videoId, 'error' => $exception->getMessage()]);

            return null;
        }

        if ($response->failed()) {
            Log::warning('Gagal mengambil data video Bunny Stream', ['video_id' => $videoId, 'status' => $response->status()]);

            return null;
        }

        $length = (int) $response->json('length');

        return $length > 0 ? $length : null;
    }
}
