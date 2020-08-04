<?php

declare(strict_types=1);

namespace App\ControllerFunctions\Update;

use App\Exceptions\ExecNotAvailableException;
use App\Exceptions\GitNotAvailableException;
use App\Exceptions\GitNotExecutableException;
use App\Exceptions\NoOnlineUpdateException;
use App\Exceptions\NotInCacheException;
use App\Exceptions\NotMasterException;
use App\Metadata\GitHubFunctions;
use App\Metadata\GitRequest;
use App\Metadata\LycheeVersion;
use App\Models\Configs;
use Illuminate\Support\Facades\Log;

class Check
{
    /**
     * @var GitHubFunctions
     */
    private $gitHubFunctions;

    /**
     * @var GitRequest
     */
    private $gitRequest;

    /**
     * @var LycheeVersion
     */
    private $lycheeVersion;

    public function __construct(
        GitHubFunctions $gitHubFunctions,
        GitRequest $gitRequest,
        LycheeVersion $lycheeVersion
    ) {
        $this->gitHubFunctions = $gitHubFunctions;
        $this->gitRequest = $gitRequest;
        $this->lycheeVersion = $lycheeVersion;
    }

    /**
     * @throws NoOnlineUpdateException
     * @throws GitNotAvailableException
     * @throws ExecNotAvailableException
     * @throws GitNotExecutableException
     */
    public function canUpdate(): bool
    {
        // we bypass this because we don't care about the other conditions as they don't apply to the release
        if ($this->lycheeVersion->isRelease) {
            // @codeCoverageIgnoreStart
            return true;
            // @codeCoverageIgnoreEnd
        }

        if (Configs::get_value('allow_online_git_pull', '0') === '0') {
            throw new NoOnlineUpdateException();
        }

        // When going with the CI, .git is always executable and exec is also available
        // @codeCoverageIgnoreStart
        if (!\function_exists('exec')) {
            throw new ExecNotAvailableException();
        }
        if (\exec('command -v git') === '') {
            throw new GitNotAvailableException();
        }

        if (!$this->gitHubFunctions->has_permissions()) {
            throw new GitNotExecutableException();
        }
        // @codeCoverageIgnoreEnd

        return true;
    }

    /**
     * Cath the Exception and return the boolean equivalent.
     */
    private function canUpdateBool(): bool
    {
        try {
            return $this->canUpdate();
        } catch (\Throwable $e) {
            Log::error($e->getMessage());

            return false;
        }
    }

    /**
     * Clear cache and check if up to date.
     */
    private function forget_and_check(): bool
    {
        $this->gitRequest->clear_cache();

        return $this->gitHubFunctions->is_up_to_date(false);
    }

    /**
     * Check for updates, return text or an exception if not possible.
     */
    public function getText(): string
    {
        $up_to_date = $this->forget_and_check();

        if (!$up_to_date) {
            return $this->gitHubFunctions->get_behind_text();
        }

        return 'Already up to date';
    }

    /**
     * Check for updates, returns the code
     * 0 - Not Master
     * 1 - Not in cache
     * 1 - Up to date
     * 2 - Not up to date.
     * 3 - Require migration.
     */
    public function getCode(): int
    {
        if ($this->lycheeVersion->isRelease) {
            $versions = $this->lycheeVersion->get();

            return 3 * (int) ($versions['DB']['version'] < $versions['Lychee']['version']);
        }

        $update = $this->canUpdateBool();

        if ($update) {
            try {
                if (!$this->gitHubFunctions->is_up_to_date()) {
                    return 2;
                }

                return 1;
            } catch (NotInCacheException $e) {
                return 1;
            } catch (NotMasterException $e) {
                return 0;
            }
        }

        return 0;
    }
}
