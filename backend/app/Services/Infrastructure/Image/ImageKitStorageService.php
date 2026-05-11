<?php

namespace HiEvents\Services\Infrastructure\Image;

use HiEvents\Services\Infrastructure\Image\DTO\ImageStorageResponseDTO;
use HiEvents\Services\Infrastructure\Image\Exception\CouldNotUploadImageException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;

class ImageKitStorageService
{
    private const UPLOAD_URL = 'https://upload.imagekit.io/api/v1/files/upload';

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws CouldNotUploadImageException
     */
    public function store(UploadedFile $image, string $imageType): ImageStorageResponseDTO
    {
        $privateKey = config('services.imagekit.private_key');
        $urlEndpoint = config('services.imagekit.url_endpoint');

        if (!$privateKey || !$urlEndpoint) {
            throw new CouldNotUploadImageException('ImageKit is not configured. Set IMAGEKIT_PRIVATE_KEY and IMAGEKIT_URL_ENDPOINT.');
        }

        $filename = Str::slug(
                str_ireplace(
                    '.' . $image->getClientOriginalExtension(),
                    '',
                    $image->getClientOriginalName()
                )
            ) . '-' . Str::random(8) . '.' . $image->getClientOriginalExtension();

        $folder = '/eevents/' . strtolower($imageType);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => self::UPLOAD_URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_USERPWD => $privateKey . ':',
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_POSTFIELDS => [
                'file' => new \CURLFile(
                    $image->getRealPath(),
                    $image->getMimeType(),
                    $filename
                ),
                'fileName' => $filename,
                'folder' => $folder,
                'useUniqueFileName' => 'false',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            $this->logger->error('ImageKit upload cURL error', ['error' => $curlError]);
            throw new CouldNotUploadImageException('Failed to connect to ImageKit: ' . $curlError);
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200 || empty($data['url'])) {
            $this->logger->error('ImageKit upload failed', [
                'http_code' => $httpCode,
                'response' => $data,
                'filename' => $filename,
            ]);
            throw new CouldNotUploadImageException(
                'ImageKit upload failed: ' . ($data['message'] ?? 'Unknown error')
            );
        }

        $this->logger->info('ImageKit upload successful', [
            'url' => $data['url'],
            'fileId' => $data['fileId'] ?? null,
        ]);

        // Store the full ImageKit URL as the path so getCdnUrl returns it directly
        return new ImageStorageResponseDTO(
            filename: $filename,
            disk: 'imagekit',
            path: $data['url'],
            size: $image->getSize(),
            mime_type: $image->getMimeType()
        );
    }
}
