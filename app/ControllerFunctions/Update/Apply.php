<?php

declare(strict_types=1);

namespace App\ControllerFunctions\Update;

use App\Metadata\LycheeVersion;
use App\Models\Configs;
use App\Models\Logs;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class Apply
{
    /**
     * @var LycheeVersion
     */
    private $lycheeVersion;

    public function __construct(LycheeVersion $lycheeVersion)
    {
        $this->lycheeVersion = $lycheeVersion;
    }

    /**
     * If we are in a production environment we actually require a double check..
     *
     * @param array<string> $output
     */
    private function check_prod_env_allow_migration(array &$output): bool
    {
        if (Config::get('app.env') === 'production') {
            if (Configs::get_value('force_migration_in_production') === '1') {
                Logs::warning(__METHOD__, (string) __LINE__, 'Force update is production.');

                return true;
            }

            $output[] = 'Update not applied: `APP_ENV` in `.env` is `production` and `force_migration_in_production` is set to `0`.';
            Logs::warning(
                __METHOD__,
                (string) __LINE__,
                'Update not applied: `APP_ENV` in `.env` is `production` and `force_migration_in_production` is set to `0`.'
            );

            return false;
        }

        return true;
    }

    /**
     * call composer over exec.
     *
     * @param array<string> $output
     */
    private function call_composer(array &$output): void
    {
        if (Configs::get_value('apply_composer_update', '0') === '1') {
            // @codeCoverageIgnoreStart
            Logs::warning(__METHOD__, (string) __LINE__, 'Composer is called on update.');

            // Composer\Factory::getHomeDir() method
            // needs COMPOSER_HOME environment variable set
            \putenv('COMPOSER_HOME=' . \base_path('/composer-cache'));
            \chdir(\base_path());
            \exec('composer install --no-dev --no-progress --no-suggest 2>&1', $output);
            \chdir(\base_path('public'));
        // @codeCoverageIgnoreEnd
        } else {
            $output[] = 'Composer update are always dangerous when automated.';
            $output[] = 'So we did not execute it.';
            $output[] = 'If you want to have composer update applied, please set the setting to 1 at your own risk.';
        }
    }

    /**
     * Arrayify a string and append it to $output.
     *
     * @param array<string> $output
     *
     * @return array<string>
     */
    private function str_to_array(string $string, array &$output): array
    {
        $a = \explode("\n", $string);
        foreach ($a as $aa) {
            if ($aa !== '') {
                $output[] = $aa;
            }
        }

        return $output;
    }

    /**
     * call git over exec.
     *
     * @param array<string> $output
     */
    private function git_pull(array &$output): void
    {
        $command = 'git pull --rebase ' . Config::get('urls.git.pull') . ' master 2>&1';
        \exec($command, $output);
    }

    /**
     * call for migrate via the Artisan Facade.
     *
     * @param array<string> $output
     */
    private function artisan(array &$output): void
    {
        Artisan::call('migrate', ['--force' => true]);
        $this->str_to_array(Artisan::output(), $output);
    }

    /**
     * Apply the migration:
     * 1. git pull
     * 2. artisan migrate.
     *
     * @return array<string>
     */
    public function run(): array
    {
        $output = [];
        if ($this->check_prod_env_allow_migration($output)) {
            $this->lycheeVersion->isRelease or $this->git_pull($output);
            $this->artisan($output);
            $this->lycheeVersion->isRelease or $this->call_composer($output);
        }

        return \preg_replace('/\033[[]\d*;*\d*;*\d*m/', '', $output);
    }
}
