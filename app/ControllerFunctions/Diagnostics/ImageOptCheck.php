<?php

declare(strict_types=1);

namespace App\ControllerFunctions\Diagnostics;

use App\Models\Configs;
use Spatie\ImageOptimizer\Optimizers\Cwebp;
use Spatie\ImageOptimizer\Optimizers\Gifsicle;
use Spatie\ImageOptimizer\Optimizers\Jpegoptim;
use Spatie\ImageOptimizer\Optimizers\Optipng;
use Spatie\ImageOptimizer\Optimizers\Pngquant;
use Spatie\ImageOptimizer\Optimizers\Svgo;

class ImageOptCheck implements DiagnosticCheckInterface
{
    /**
     * @param array<string> $errors
     */
    public function check(array &$errors): void
    {
        $tools = [];
        $tools[] = new Cwebp();
        $tools[] = new Gifsicle();
        $tools[] = new Jpegoptim();
        $tools[] = new Optipng();
        $tools[] = new Pngquant();
        $tools[] = new Svgo();

        $settings = Configs::get();
        if (! isset($settings['lossless_optimization']) || $settings['lossless_optimization'] !== '1') {
            return;
        }

        $binaryPath = \config('image-optimizer.binary_path');

        if ($binaryPath !== '' && \mb_substr($binaryPath, -1) !== DIRECTORY_SEPARATOR) {
            $binaryPath .= DIRECTORY_SEPARATOR;
        }

        foreach ($tools as $tool) {
            $path = \exec('command -v ' . $binaryPath . $tool->binaryName());
            if ($path === '') {
                $errors[] = 'Warning: Image optimizer enabled but ' . $binaryPath . $tool->binaryName() . ' not found!';
            }
        }
    }
}
