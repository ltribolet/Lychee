<?php

declare(strict_types=1);

namespace App\ModelFunctions\PhotoActions;

use App\Assets\Helpers;

class Thumb
{
    public string $thumb = '';

    public string $type = '';

    public string $thumb2x = '';

    public int $thumbID = 0;

    public function __construct(string $type, int $thumbID)
    {
        $this->type = $type;
        $this->thumbID = $thumbID;
    }

    public function set_thumb2x(): void
    {
        $this->thumb2x = Helpers::ex2x($this->thumb);
    }

    /**
     * @param array<string> $thumb
     * @param array<string> $type
     * @param array<string> $thumb2x
     */
    public function insertToArrays(array &$thumb, array &$type, array &$thumb2x): void
    {
        $thumb[] = $this->thumb;
        $type[] = $this->type;
        $thumb2x[] = $this->thumb2x;
    }
}
