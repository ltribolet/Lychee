<?php

declare(strict_types=1);

namespace App\Image;

interface ImageHandlerInterface
{
    public function __construct(int $compressionQuality);

    /**
     * @param int &$resWidth
     * @param int &$resHeight
     */
    public function scale(
        string $source,
        string $destination,
        int $newWidth,
        int $newHeight,
        int &$resWidth,
        int &$resHeight
    ): bool;

    public function crop(string $source, string $destination, int $newWidth, int $newHeight): bool;

    /**
     * Rotates and flips a photo based on its EXIF orientation.
     *
     * @param array<string> $info
     *
     * @return array<string>
     */
    public function autoRotate(string $path, array $info): array;
}
