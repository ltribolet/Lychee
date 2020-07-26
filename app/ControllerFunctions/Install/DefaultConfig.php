<?php

declare(strict_types=1);

namespace App\ControllerFunctions\Install;

class DefaultConfig
{
    private $config = [
        /*
        |--------------------------------------------------------------------------
        | Server Requirements
        |--------------------------------------------------------------------------
        |
        | This is our Lychee server requirements, we check if the extension is enabled
        | by looping through the array and run "extension_loaded" on it.
        |
        */
        'core' => ['minPhpVersion' => '7.3.0'],

        'requirements' => [
            'php' => ['openssl', 'pdo', 'mbstring', 'tokenizer', 'JSON', 'exif', 'gd'],
            'apache' => ['mod_rewrite'],
        ],
        /*
        |--------------------------------------------------------------------------
        | Folders Permissions
        |--------------------------------------------------------------------------
        |
        | This is the default Lychee folders permissions.
        | you may want to enable more permissions to allow online updates
        |
        */
        'permissions' => [
            '.' => 'file_exists|is_readable|is_writable|is_executable',
            'storage/framework/' => 'file_exists|is_readable|is_writable|is_executable',
            'storage/framework/views/' => 'file_exists|is_readable|is_writable|is_executable',
            'storage/framework/cache/' => 'file_exists|is_readable|is_writable|is_executable',
            'storage/framework/sessions/' => 'file_exists|is_readable|is_writable|is_executable',
            'storage/logs/' => 'file_exists|is_readable|is_writable|is_executable',
            'bootstrap/cache/' => 'file_exists|is_readable|is_writable|is_executable',
            'public/dist/' => 'file_exists|is_readable|is_writable|is_executable',
            'public/img/' => 'file_exists|is_readable|is_writable|is_executable',
            'public/sym/' => 'file_exists|is_readable|is_writable|is_executable',
            'public/uploads/' => 'file_exists|is_readable|is_writable|is_executable',
            'public/uploads/big/' => 'file_exists|is_readable|is_writable|is_executable',
            'public/uploads/import/' => 'file_exists|is_readable|is_writable|is_executable',
            'public/uploads/medium/' => 'file_exists|is_readable|is_writable|is_executable',
            'public/uploads/raw/' => 'file_exists|is_readable|is_writable|is_executable',
            'public/uploads/small/' => 'file_exists|is_readable|is_writable|is_executable',
            'public/uploads/thumb/' => 'file_exists|is_readable|is_writable|is_executable',
        ],
    ];

    /**
     * @return array<string>
     */
    public function get_core(): array
    {
        return $this->config['core'];
    }

    /**
     * @return array<string>
     */
    public function get_requirements(): array
    {
        return $this->config['requirements'];
    }

    /**
     * @return array<string>
     */
    public function get_permissions(): array
    {
        return $this->config['permissions'];
    }
}
