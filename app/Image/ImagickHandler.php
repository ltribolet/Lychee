<?php

declare(strict_types=1);

namespace App\Image;

use App\Models\Configs;
use App\Models\Logs;
use Imagick;
use ImagickException;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;

class ImagickHandler implements ImageHandlerInterface
{
    /**
     * @var int
     */
    private $compressionQuality;

    public function __construct(int $compressionQuality)
    {
        $this->compressionQuality = $compressionQuality;
    }

    public function scale(
        string $source,
        string $destination,
        int $newWidth,
        int $newHeight,
        int &$resWidth,
        int &$resHeight
    ): bool {
        try {
            // Read image
            $image = new \Imagick();
            $image->readImage($source);
            $image->setImageCompressionQuality($this->compressionQuality);

            $profiles = $image->getImageProfiles('icc', true);

            $image->scaleImage($newWidth, $newHeight, ($newWidth !== 0 && $newHeight !== 0));

            // the image may need to be rotated prior saving
            $this->autoRotateInternal($image);

            // Remove metadata to save some bytes
            $image->stripImage();

            if (! empty($profiles)) {
                $image->profileImage('icc', $profiles['icc']);
            }

            $image->writeImage($destination);
            Logs::notice(__METHOD__, (string) __LINE__, 'Saving thumb to ' . $destination);
            $resWidth = $image->getImageWidth();
            $resHeight = $image->getImageHeight();
            $image->clear();
            $image->destroy();

            // Optimize image
            if (Configs::get_value('lossless_optimization', '0') === '1') {
                ImageOptimizer::optimize($destination);
            }
        } catch (ImagickException $exception) {
            Logs::error(__METHOD__, (string) __LINE__, $exception->getMessage());

            return false;
        }

        return true;
    }

    public function crop(string $source, string $destination, int $newWidth, int $newHeight): bool
    {
        try {
            $image = new \Imagick();
            $image->readImage($source);
            $image->setImageCompressionQuality($this->compressionQuality);

            $profiles = $image->getImageProfiles('icc', true);

            $image->cropThumbnailImage($newWidth, $newHeight);

            // the image may need to be rotated prior saving
            $this->autoRotateInternal($image);

            // Remove metadata to save some bytes
            $image->stripImage();

            if (! empty($profiles)) {
                $image->profileImage('icc', $profiles['icc']);
            }

            $image->writeImage($destination);
            Logs::notice(__METHOD__, (string) __LINE__, 'Saving thumb to ' . $destination);
            $image->clear();
            $image->destroy();

            // Optimize image
            if (Configs::get_value('lossless_optimization', '0') === '1') {
                ImageOptimizer::optimize($destination);
            }
        } catch (ImagickException $exception) {
            Logs::error(__METHOD__, (string) __LINE__, $exception->getMessage());

            return false;
        }

        return true;
    }

    public function autoRotate(string $path, array $info): array
    {
        try {
            $image = new \Imagick();
            $image->readImage($path);

            $rotate = $image->getImageOrientation() !== \Imagick::ORIENTATION_TOPLEFT;

            $dimensions = $this->autoRotateInternal($image);

            if ($rotate) {
                $image->writeImage($path);
            }

            $image->clear();
            $image->destroy();

            return $dimensions;
        } catch (ImagickException $exception) {
            Logs::error(__METHOD__, (string) __LINE__, $exception->getMessage());

            return [false, false];
        }
    }

    /**
     * Rotates a given image based on the given orientation.
     *
     * @param \Imagick $image the image reference to rotate
     *
     * @return array<bool> a dictionary of width and height of the rotated image
     */
    private function autoRotateInternal(Imagick $image): array
    {
        try {
            $orientation = $image->getImageOrientation();

            switch ($orientation) {
                case \Imagick::ORIENTATION_TOPLEFT:
                    // nothing to do
                    break;
                case \Imagick::ORIENTATION_TOPRIGHT:
                    $image->flopImage();
                    break;
                case \Imagick::ORIENTATION_BOTTOMRIGHT:
                    $image->rotateImage(new \ImagickPixel(), 180);
                    break;
                case \Imagick::ORIENTATION_BOTTOMLEFT:
                    $image->flopImage();
                    $image->rotateImage(new \ImagickPixel(), 180);
                    break;
                case \Imagick::ORIENTATION_LEFTTOP:
                    $image->flopImage();
                    $image->rotateImage(new \ImagickPixel(), -90);
                    break;
                case \Imagick::ORIENTATION_RIGHTTOP:
                    $image->rotateImage(new \ImagickPixel(), 90);
                    break;
                case \Imagick::ORIENTATION_RIGHTBOTTOM:
                    $image->flopImage();
                    $image->rotateImage(new \ImagickPixel(), 90);
                    break;
                case \Imagick::ORIENTATION_LEFTBOTTOM:
                    $image->rotateImage(new \ImagickPixel(), -90);
                    break;
            }

            $image->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);

            return [
                'width' => $image->getImageWidth(),
                'height' => $image->getImageHeight(),
            ];
        } catch (ImagickException $exception) {
            Logs::error(__METHOD__, (string) __LINE__, $exception->getMessage());

            return [false, false];
        }
    }
}
