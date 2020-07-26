<?php

declare(strict_types=1);

namespace App\Image;

use App\Configs;

class ImageHandler implements ImageHandlerInterface
{
    /**
     * @var int
     */
    private int $compressionQuality;

    /**
     * @var array<ImageHandlerInterface>
     */
    private array $engines;

    public function __construct(int $compressionQuality)
    {
        $this->compressionQuality = $compressionQuality;
        $this->engines = [];
        if (Configs::hasImagick()) {
            $this->engines[] = new ImagickHandler($this->compressionQuality);
        }
        $this->engines[] = new GdHandler($this->compressionQuality);
    }

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
    ): bool {
        $i = 0;
        while ($i < \count($this->engines) && !$this->engines[$i]->scale(
            $source,
            $destination,
            $newWidth,
            $newHeight,
            $resWidth,
            $resHeight
        )) {
            $i++;
        }

        return $i !== \count($this->engines);
    }

    public function crop(string $source, string $destination, int $newWidth, int $newHeight): bool
    {
        $i = 0;
        while ($i < \count($this->engines) && !$this->engines[$i]->crop($source, $destination, $newWidth, $newHeight)) {
            $i++;
        }

        return $i !== \count($this->engines);
    }

    /**
     * Rotates and flips a photo based on its EXIF orientation.
     *
     * @param array<string> $info
     *
     * @return array<string>
     */
    public function autoRotate(string $path, array $info): array
    {
        return $this->engines[0]->autoRotate($path, $info);
    }
}
