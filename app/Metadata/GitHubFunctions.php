<?php

declare(strict_types=1);

namespace App\Metadata;

use App;
use App\Assets\Helpers;
use App\Configs;
use App\Exceptions\NotInCacheException;
use App\Exceptions\NotMasterException;
use App\Logs;
use App\ModelFunctions\JsonRequestFunctions;
use Config;
use Exception;

class GitHubFunctions
{
    /**
     * @var string
     */
    public string $head;

    /**
     * @var string
     */
    public string $branch;

    /**
     * @var GitRequest
     */
    private GitRequest $gitRequest;

    /**
     * Base constructor.
     */
    public function __construct(GitRequest $gitRequest)
    {
        $this->gitRequest = $gitRequest;
        try {
            $this->branch = $this->get_current_branch();
            $this->head = $this->get_current_commit();
            // @codeCoverageIgnoreStart
            // when testing on master branch this is not covered.
        } catch (\Throwable $e) {
            $this->branch = false;
            $this->head = false;
            try {
                Logs::notice(__METHOD__, __LINE__, $e->getMessage());
            } catch (\Throwable $e) {
                // Composer stuff.
            }
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Given a commit id, return the 7 first characters (7 hex digits) and trim it to remove \n.
     *
     * @param $commit_id
     */
    private function trim(string $commit_id): string
    {
        return \trim(\mb_substr($commit_id, 0, 7));
    }

    /**
     * look at .git/HEAD and return the current branch.
     * Return false if the file is not readable.
     * Return master if it is CI.
     *
     * @return false|string
     */
    public function get_current_branch()
    {
        if (App::runningUnitTests()) {
            return 'master';
        }

        // @codeCoverageIgnoreStart
        $head_file = \base_path('.git/HEAD');
        $branch_ = \file_get_contents($head_file);
        //separate out by the "/" in the string
        $branch_ = \explode('/', $branch_, 3);

        return \trim($branch_[2]);
        // @codeCoverageIgnoreEnd
    }

    /**
     * Return the current commit id (7 hex digits).
     *
     * @return false|string
     */
    public function get_current_commit()
    {
        $file = \base_path('.git/refs/heads/' . $this->branch);
        $head_ = \file_get_contents($file);

        return $this->trim($head_);
    }

    /**
     * return the list of the last 30 commits on the master branch.
     *
     * @return bool|array<mixed>
     *
     * @throws NotInCacheException
     */
    private function get_commits(bool $cached = true)
    {
        return $this->gitRequest->get_json($cached);
    }

    /**
     * Count the number of commits between current version and master/HEAD.
     * Throws NotMaster if the branch is not ... master
     * Throws NotInCache if the commits are not cached
     * Returns between 0 and 30 if we can find the value
     * Returns false if more than 30 commits behind.
     *
     * @return bool|int
     *
     * @throws NotInCacheException
     * @throws NotMasterException
     */
    public function count_behind(bool $cached = true)
    {
        if ($this->branch !== 'master') {
            // @codeCoverageIgnoreStart
            throw new NotMasterException();
            // @codeCoverageIgnoreEnd
        }

        $commits = $this->get_commits($cached);

        $i = 0;
        while ($i < \count($commits)) {
            if ($this->trim($commits[$i]->sha) === $this->head) {
                break;
            }
            // @codeCoverageIgnoreStart
            // when testing on master branch this is not covered: we are up to date.
            $i++;
            // @codeCoverageIgnoreEnd
        }

        return $i === \count($commits) ? false : $i;
    }

    /**
     * return the commit id (7 hex digits) of the had if found.
     */
    // @codeCoverageIgnoreStart
    public function get_github_head(): string
    {
        try {
            $commits = $this->get_commits();

            return ' (' . $this->trim($commits[0]->sha) . ')';
        } catch (\Throwable $e) {
            return '';
        }
    }

    // @codeCoverageIgnoreEnd

    /**
     * Return a string indicating whether we are up to date (used in Diagnostics).
     *
     * This function should not throw exceptions !
     */
    public function get_behind_text(): string
    {
        try {
            // NotInCache or NotMaster
            $count = $this->count_behind();
        } catch (\Throwable $e) {
            return ' - ' . $e->getMessage();
        }

        $last_update = $this->gitRequest->get_age_text();

        if ($count === 0) {
            return \sprintf(' - Up to date (%s).', $last_update);
        }
        // @codeCoverageIgnoreStart
        if ($count !== false) {
            return \sprintf(' - %s commits behind master %s (%s)', $count, $this->get_github_head(), $last_update);
        }

        return ' - Probably more than 30 commits behind master';
        // @codeCoverageIgnoreEnd
    }

    /**
     * Check if the repo is up to date, throw an exception if fails.
     *
     * @throws NotMasterException
     * @throws NotInCacheException
     */
    public function is_up_to_date(bool $cached = true): bool
    {
        $count = $this->count_behind($cached);
        if ($count === 0) {
            return true;
        }

        // @codeCoverageIgnoreStart
        return false;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Simple check if git is usable or not.
     */
    public function has_permissions(): bool
    {
        if (!$this->branch) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        return Helpers::hasFullPermissions(\base_path('.git')) && Helpers::hasPermissions(
                \base_path('.git/refs/heads/' . $this->branch)
            );
    }

    /**
     * Check for updates (old).
     *
     * @param array<string> $return
     */
    public function checkUpdates(array &$return): void
    {
        // add a setting to do this check only once per day ?
        if (Configs::get_value('check_for_updates', '0') === '1') {
            $json = new JsonRequestFunctions(Config::get('urls.update.json'));
            $json = $json->get_json();
            if ($json !== false) {
                /* @noinspection PhpUndefinedFieldInspection */
                $return['update_json'] = $json->lychee->version;
                $return['update_available']
                    = (\intval(Configs::get_value('version', '40000'))
                        < $return['update_json']);
            }
        }
    }
}
