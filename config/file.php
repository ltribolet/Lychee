<?php

declare(strict_types=1);

return [
    'invalid_characters' => \array_merge(
        \array_map('chr', \range(0, 31)),
        ['<', '>', ':', '"', '/', '\\', '|', '?', '*']
    ),
];
