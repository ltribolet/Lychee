<?php

declare(strict_types=1);

namespace App\ControllerFunctions\Install;

class PermissionsChecker
{
    /**
     * @var array<string, bool>
     */
    protected $results = [];

    /**
     * Set the result array permissions and errors.
     */
    public function __construct()
    {
        $this->results['permissions'] = [];
        $this->results['errors'] = null;
    }

    /**
     * Return true if we are stupid enough to use Windows.
     */
    public function is_win(): bool
    {
        return \mb_stripos(PHP_OS_FAMILY, 'WIN') !== false;
    }

    /**
     * Check for the folders permissions.
     *
     * @param array<string, string> $folders
     *
     * @return array<string, bool>
     */
    public function check(array $folders): array
    {
        foreach ($folders as $folder => $permission) {
            $this->addFile($folder, $permission, $this->getPermission($folder, $permission));
        }

        return $this->results;
    }

    /**
     * Get a folder permission.
     *
     * @return int the position of 1 determines the errors
     */
    private function getPermission(string $folder, string $permissions): int
    {
        $return = 0;
        foreach (\explode('|', $permissions) as $permission) {
            \preg_match('/(!*)(.*)/', $permission, $f);
            $return <<= 1;
            // we overwrite the value if windows and executable check.
            $return |= $f[2] === 'is_executable' && $this->is_win() ? 0 : ! ($f[2](\base_path(
                $folder
            )) xor ($f[1] === '!'));
        }

        return $return;
    }

    /**
     * Add the file to the list of results.
     */
    private function addFile(string $folder, string $permission, int $isSet): void
    {
        $this->results['permissions'][] = [
            'folder' => $folder,
            'permission' => $this->map_perm_set($permission, $isSet),
            'isSet' => $isSet,
        ];

        // set error if $isSet is positive
        if ($isSet > 0) {
            // @codeCoverageIgnoreStart
            $this->results['errors'] = true;
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * @return array<mixed>
     */
    private function map_perm_set(string $permissions, int $areSet): array
    {
        $array_permission = \array_reverse(\explode('|', $permissions));
        $ret = [];
        $i = 0;
        foreach ($array_permission as $perm) {
            $perm = \str_replace(['file_', '!', 'is_'], ['', 'not', ' '], $perm);
            $ret[$i++] = [$perm, $areSet & 1];
            $areSet >>= 1;
        }

        return $ret;
    }
}
