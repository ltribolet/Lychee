<?php

declare(strict_types=1);

namespace App\ControllerFunctions\Diagnostics;

use App\ModelFunctions\ConfigFunctions;
use App\Models\Configs;

class ConfigSanityCheck implements DiagnosticCheckInterface
{
    /**
     * @var ConfigFunctions
     */
    private $configFunctions;

    public function __construct(ConfigFunctions $configFunctions)
    {
        $this->configFunctions = $configFunctions;
    }

    /**
     * @param array<string> $errors
     */
    public function check(array &$errors): void
    {
        // Load settings
        $settings = Configs::get();

        $keys_checked = [
            'username', 'password', 'sorting_Photos', 'sorting_Albums',
            'imagick', 'skip_duplicates', 'check_for_updates', 'version',
        ];

        foreach ($keys_checked as $key) {
            if (! isset($settings[$key])) {
                $errors[] = 'Error: ' . $key . ' not set in database';
            }
        }

        /*
         * Sanity check over all the variables
         */
        $this->configFunctions->sanity($errors);

        // Check dropboxKey
        if (! isset($settings['dropbox_key'])) {
            $errors[]
                = 'Warning: Dropbox import not working. No property for dropbox_key.';
        } elseif ($settings['dropbox_key'] === '') {
            $errors[]
                = 'Warning: Dropbox import not working. dropbox_key is empty.';
        }
    }
}
