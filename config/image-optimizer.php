<?php

declare(strict_types=1);

use App\ModelFunctions\LogFunctions;
use Spatie\ImageOptimizer\Optimizers\Cwebp;
use Spatie\ImageOptimizer\Optimizers\Gifsicle;
use Spatie\ImageOptimizer\Optimizers\Jpegoptim;
use Spatie\ImageOptimizer\Optimizers\Optipng;
use Spatie\ImageOptimizer\Optimizers\Pngquant;
use Spatie\ImageOptimizer\Optimizers\Svgo;

return [
    /*
     * When calling `optimize` the package will automatically determine which optimizers
     * should run for the given image.
     */
    'optimizers' => [
        Jpegoptim::class => [
            // this strips out all text information such as comments and EXIF data
            '--strip-all',
            // this will make sure the resulting image is a progressive one
            '--all-progressive',
        ],

        Pngquant::class => [
            // required parameter for this package
            '--force',
        ],

        Optipng::class => [
            // this will result in a non-interlaced, progressive scanned image
            '-i0',
            // this set the optimization level to two (multiple IDAT compression trials)
            '-o2',
            // required parameter for this package
            '-quiet',
        ],

        Svgo::class => [
            // disabling because it is know to cause troubles
            '--disable=cleanupIDs',
        ],

        Gifsicle::class => [
            // required parameter for this package
            '-b',
            // this produces the slowest but best results
            '-O3',
        ],

        Cwebp::class => [
            // for the slowest compression method in order to get the best compression.
            '-m 6',
            // for maximizing the amount of analysis pass.
            '-pass 10',
            // multithreading for some speed improvements.
            '-mt',
        ],
    ],

    /*
    * The directory where your binaries are stored.
    * Only use this when you binaries are not accessible in the global environment.
    */
    'binary_path' => '',

    /*
     * The maximum time in seconds each optimizer is allowed to run separately.
     */
    'timeout' => 10,

    /*
     * If set to `true` all output of the optimizer binaries will be appended to the default log.
     * You can also set this to a class that implements `Psr\Log\LoggerInterface`.
     */
    'log_optimizer_activity' => LogFunctions::class,
];
