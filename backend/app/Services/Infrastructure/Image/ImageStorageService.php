<?php

namespace HiEvents\Services\Infrastructure\Image;

use HiEvents\Services\Infrastructure\Image\DTO\ImageStorageResponseDTO;
use HiEvents\Services\Infrastructure\Image\Exception\CouldNotUploadImageException;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;

class ImageStorageService
{
    public function __construct(
        private readonly FilesystemManager $filesystemManager,
        private readonly Repository        $config,
        private readonly LoggerInterface   $logger,
    )
    {
    }

    /**
     * @throws CouldNotUploadImageException
     */
    public function store(UploadedFile $image, string $imageType): ImageStorageResponseDTO
    {
        $filename = Str::slug(
                title: str_ireplace(
                    search: '.' . $image->getClientOriginalExtension(),
                    replace: '',
                    subject: $image->getClientOriginalName()
                )
            ) . '-' . Str::random(5) . '.' . $image->getClientOriginalExtension();

        $disk = $this->config->get('filesystems.public');

        $filesystem = $this->filesystemManager->disk($disk);

        // Only pass visibility option for cloud disks (S3 etc.) — local disk doesn't support it
        $driver = $this->config->get("filesystems.disks.{$disk}.driver", 'local');
        $options = in_array($driver, ['s3', 'gcs', 'r2'], true)
            ? ['visibility' => 'public']
            : [];

        $path = $filesystem->putFileAs(
            path: strtolower($imageType),
            file: $image,
            name: $filename,
            options: $options,
        );

        if ($path === false) {
            $this->logger->error(__('Could not upload image to :disk. Check :disk is configured correctly', ['disk' => $disk,]), [
                    'filename' => $filename,
                    'original_filename' => $image->getClientOriginalName()
                ]
            );

            throw new CouldNotUploadImageException(__('Could not upload image'));
        }

        return new ImageStorageResponseDTO  (
            filename: $filename,
            disk: $disk,
            path: $path,
            size: $image->getSize(),
            mime_type: $image->getMimeType()
        );
    }
}
