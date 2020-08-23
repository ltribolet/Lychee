<?php

declare(strict_types=1);

namespace App\ControllerFunctions\Install;

use Illuminate\Support\Facades\Artisan;

class ApplyMigration
{
    /**
     * @param array<string> $output
     */
    public function migrate(array &$output): bool
    {
        Artisan::call('migrate', ['--force' => true]);
        $this->str_to_array(Artisan::output(), $output);

        /*
         * We also double check there is no "QueryException" in the output (just to be sure).
         */
        foreach ($output as $line) {
            if (\mb_strpos($line, 'QueryException') !== false) {
                // @codeCoverageIgnoreStart
                return true;
                // @codeCoverageIgnoreEnd
            }
        }

        return false;
    }

    /**
     * @param array<string> $output
     */
    public function keyGenerate(array &$output): bool
    {
        try {
            Artisan::call('key:generate', ['--force' => true]);
            $this->str_to_array(Artisan::output(), $output);
            // @codeCoverageIgnoreStart
        } catch (\Throwable $e) {
            $output[] = $e->getMessage();
            $output[] = 'We could not generate the encryption key.';

            return true;
        }
        // @codeCoverageIgnoreEnd

        // key is generated, we can safely remove that file (in theory)
        @\unlink(\base_path('.NO_SECURE_KEY'));

        return false;
    }

    /**
     * Arrayify a string and append it to $output.
     *
     * @param $string
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
}
