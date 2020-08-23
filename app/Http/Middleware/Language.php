<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Configs;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

class Language
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        try {
            $locale = Configs::where('key', '=', 'lang')->first()->value;
        } catch (\PDOException $e) {
            $locale = null;
        }

        if (! \array_key_exists($locale, $this->app->config->get('app.locales'))) {
            $locale = $this->app->config->get('app.fallback_locale');
        }

        $this->app->setLocale($locale);

        return $next($request);
    }
}
