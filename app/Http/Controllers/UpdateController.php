<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\ControllerFunctions\Update\Apply as ApplyUpdate;
use App\ControllerFunctions\Update\Check as CheckUpdate;
use App\Response;

/**
 * Class UpdateController.
 */
class UpdateController extends Controller
{
    private ApplyUpdate $applyUpdate;

    private CheckUpdate $checkUpdate;

    public function __construct(ApplyUpdate $applyUpdate, CheckUpdate $checkUpdate)
    {
        $this->applyUpdate = $applyUpdate;
        $this->checkUpdate = $checkUpdate;
    }

    /**
     * Return if up to date or the number of commits behind
     * This invalidates the cache for the url.
     */
    public function check(): string
    {
        try {
            return Response::json($this->checkUpdate->getText());
            // @codeCoverageIgnoreStart
        } catch (\Throwable $e) {
            // Not master
            return Response::error($e->getMessage());
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * This requires a php to have a shell access.
     * This method execute the update (git pull).
     *
     * @return array<string>|string
     */
    public function apply()
    {
        try {
            $this->checkUpdate->canUpdate();
            // @codeCoverageIgnoreStart
        } catch (\Throwable $e) {
            return Response::error($e->getMessage());
        }
        // @codeCoverageIgnoreEnd

        // @codeCoverageIgnoreStart
        return $this->applyUpdate->run();
    }
}
