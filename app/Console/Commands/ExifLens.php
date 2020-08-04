<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Metadata\Extractor;
use App\ModelFunctions\PhotoFunctions;
use App\Models\Photo;
use Illuminate\Console\Command;
use Storage;

class ExifLens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lychee:exif_lens {from=0 : from which do we start} {nb=5 : generate exif data if missing} {tm=600 : timeout time requirement}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get EXIF data from pictures if missing';

    /**
     * @var PhotoFunctions
     */
    private $photoFunctions;

    /**
     * @var Extractor
     */
    private $metadataExtractor;

    /**
     * Create a new command instance.
     */
    public function __construct(PhotoFunctions $photoFunctions, Extractor $metadataExtractor)
    {
        parent::__construct();

        $this->photoFunctions = $photoFunctions;
        $this->metadataExtractor = $metadataExtractor;
    }

    public function handle(): bool
    {
        $argument = $this->argument('nb');
        $from = $this->argument('from');
        $timeout = $this->argument('tm');
        \set_time_limit($timeout);

        // we use lens because this is the one which is most likely to be empty.
        $photos = Photo::where('lens', '=', '')->whereNotIn(
            'lens',
            $this->photoFunctions->getValidVideoTypes()
        )->offset($from)->limit($argument)->get();
        if (\count($photos) === 0) {
            $this->line('No pictures requires EXIF updates.');

            return false;
        }

        $i = $from;
        foreach ($photos as $photo) {
            $url = Storage::path('big/' . $photo->url);
            if (\file_exists($url)) {
                $info = $this->metadataExtractor->extract($url, $photo->type);
                if ($photo->size === '') {
                    $photo->size = $info['size'];
                }
                if ($photo->iso === '') {
                    $photo->iso = $info['iso'];
                }
                if ($photo->aperture === '') {
                    $photo->aperture = $info['aperture'];
                }
                if ($photo->make === '') {
                    $photo->make = $info['make'];
                }
                if ($photo->getAttribute('model') === '') {
                    $photo->setAttribute('model', $info['model']);
                }
                if ($photo->lens === '') {
                    $photo->lens = $info['lens'];
                }
                if ($photo->shutter === '') {
                    $photo->shutter = $info['shutter'];
                }
                if ($photo->focal === '') {
                    $photo->focal = $info['focal'];
                }
                if ($photo->save()) {
                    $this->line($i . ': EXIF updated for ' . $photo->title);
                } else {
                    $this->line($i . ': Could not get EXIF data/nothing to update for ' . $photo->title . '.');
                }
            } else {
                $this->line($i . ': File does not exist for ' . $photo->title . '.');
            }
            ++$i;
        }

        return true;
    }
}
