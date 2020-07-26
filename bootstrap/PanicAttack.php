<?php

declare(strict_types=1);

class PanicAttack
{
    private $title = '';
    private $code = 0;
    private $message = '';

    /**
     * Check if all the elements of the array are in a string.
     *
     * @param string $haystack string to check against
     * @param array<mixed> $needles array of needles to check
     */
    private function contains(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (mb_strpos($haystack, $needle) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Display errors as nice page.
     *
     * This function is an EXIT.
     */
    private function displaySimpleError(): void
    {
        $error_display = file_get_contents(__DIR__ . '/../simple_error_template.html');
        $replacing = [
            '$title' => $this->title,
            '$code' => $this->code,
            '$message' => $this->message,
        ];
        http_response_code($this->code);
        echo strtr($error_display, $replacing);
        exit;
    }

    /**
     * Called from ../index.php.
     */
    public function root(): void
    {
        $this->title = 'ROOT';
        $this->code = 403;
        $this->message = '<span class="important">This is the root directory and it MUST NOT BE PUBLICALLY ACCESSIBLE.</span><br>
		To access Lychee, go <a href="public/">here</a>.';
        $this->displaySimpleError();
    }

    /**
     * Called from bootstrap/initialize.php if apache rewrite is not enabled.
     */
    public function apacheRewrite(): void
    {
        $this->title = 'mod_rewrite is not enabled';
        $this->code = 503;
        $this->message = 'You are using apache but <code>mod_rewrite</code> is not enabled.<br>
		Please do: <code>a2enmod rewrite</code>';
        $this->displaySimpleError();
    }

    /*
     |--------------------------------------------------------------------------
     | Catch error where composer is loading properly.
     |--------------------------------------------------------------------------
     | When composer has not been run yet the ../vendor/autoload.php file does
     | not exists. We check if the error message contains 'require' and
     | 'vendor/autoload', in such case we display a nice error.
     */
    public function composerVendorNotFound(): void
    {
        $this->title = 'vendor/autoload.php not found';
        $this->code = 503;
        $this->message = '<code>../vendor/autoload.php</code> not found.<br>
		Please do: <code>composer install --no-dev --prefer-dist</code>';
        $this->displaySimpleError();
    }

    /*
     |--------------------------------------------------------------------------
     | Catch error on missing access rights.
     |--------------------------------------------------------------------------
     */
    public function checkAccessRightsViews(): void
    {
        $this->title = 'Invalid access rights';
        $this->code = 503;
        $this->message = '<code>../storage</code> and sub-directories are not writable.<br>
		Please set the proper access rights.';
        $this->displaySimpleError();
    }

    /*
     |--------------------------------------------------------------------------
     | Catch error on missing access rights.
     |--------------------------------------------------------------------------
     */
    public function checkAccessRightsEnv(): void
    {
        $this->title = 'Invalid access rights';
        $this->code = 503;
        $this->message = '<code>.env</code> is not writable.<br>
		Please set the proper access rights.';
        $this->displaySimpleError();
    }

    /**
     *  dispatcher.
     */
    public function handle(string $error_message): void
    {
        $handling = [
            'composerVendorNotFound' => ['require', 'Failed opening required', 'vendor/autoload.php'],
            'checkAccessRightsViews' => ['file_put_contents', 'storage/framework/views', 'Permission denied'],
            'checkAccessRightsEnv' => ['file_put_contents', '.env'],
        ];

        foreach ($handling as $fun => $needles) {
            if ($this->contains($error_message, $needles)) {
                $this->{$fun}();
            }
        }
    }
}
