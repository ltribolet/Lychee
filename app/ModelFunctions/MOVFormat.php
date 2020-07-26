<?php

declare(strict_types=1);

namespace App\ModelFunctions;

use FFMpeg;

// Class for FFMpeg to convert files to mov format
class MOVFormat extends FFMpeg\Format\Video\DefaultVideo
{
    public function __construct(string $audioCodec = 'copy', string $videoCodec = 'copy')
    {
        $this
            ->setAudioCodec($audioCodec)
            ->setVideoCodec($videoCodec);
    }

    public function supportBFrames(): bool
    {
        return false;
    }

    /**
     * @return array<string>
     */
    public function getExtraParams(): array
    {
        return ['-f', 'mov'];
    }

    /**
     * @return array<string>
     */
    public function getAvailableAudioCodecs(): array
    {
        return ['copy'];
    }

    /**
     * @return array<string>
     */
    public function getAvailableVideoCodecs(): array
    {
        return ['copy'];
    }
}
