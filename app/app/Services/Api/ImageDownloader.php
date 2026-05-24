<?php declare(strict_types=1);

namespace App\Services\Api;

use App\Services\Api\Exceptions\ImageDownloadException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageDownloader
{
    /**
     * Downloads an image from the specified URL and stores it locally.
     *
     * @param string $url The URL of the image to download.
     * @param string $name The desired name for the saved image file.
     * @param string $directory The directory within the public disk where the file will be stored.
     * @return string The path of the stored file within the public disk.
     * @throws ImageDownloadException If the connection fails, the download is unsuccessful,
     * the MIME type is unsupported, or the file size exceeds the limit.
     */
    public function processUrl(string $url, string $name, string $directory): string
    {
        try {
            $response = Http::timeout(15)->accept('image/*')->get($url);
        } catch (ConnectionException $e) {
            throw new ImageDownloadException('Failed to connect to image host', previous: $e);
        }

        if (!$response->successful()) {
            throw new ImageDownloadException("Image download failed with status {$response->status()}");
        }

        // The server may return "image/jpeg; charset=binary".
        $contentType = explode(';', $response->header('Content-Type'))[0];

        $allowedMimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (!array_key_exists($contentType, $allowedMimeTypes)) {
            throw new ImageDownloadException("Unsupported image type: {$contentType}");
        }

        // Limit file size (5 MB).
        $maxBytes = 5 * 1024 * 1024;

        if (strlen($response->body()) > $maxBytes) {
            throw new ImageDownloadException('Image exceeds maximum allowed size');
        }

        $directory = trim($directory, '/') . '/';
        $extension = $allowedMimeTypes[$contentType];
        $filename = $directory . Str::slug($name) . '.' . $extension;

        Storage::disk('public')->put($filename, $response->body());

        return $filename;
    }
}
