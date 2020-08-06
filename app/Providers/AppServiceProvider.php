<?php

declare(strict_types=1);

namespace App\Providers;

use App\Image\ImageHandler;
use App\Image\ImageHandlerInterface;
use App\ModelFunctions\AlbumFunctions;
use App\ModelFunctions\ConfigFunctions;
use App\ModelFunctions\PhotoFunctions;
use App\ModelFunctions\SessionFunctions;
use App\ModelFunctions\SymLinkFunctions;
use App\Models\Configs;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * @var array<string, string>
     */
    public array $singletons = [
        SymLinkFunctions::class => SymLinkFunctions::class,
        PhotoFunctions::class => PhotoFunctions::class,
        AlbumFunctions::class => AlbumFunctions::class,
        ConfigFunctions::class => ConfigFunctions::class,
        SessionFunctions::class => SessionFunctions::class,
    ];

    public function boot(): void
    {
        if (Config::get('app.db_log_sql', false)) {
            DB::listen(function ($query): void {
                Log::info($query->sql, [$query->bindings, $query->time]);
            });
        }

        if (Config::get('app.force_https')) {
            URL::forceScheme('https');
        }
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ImageHandlerInterface::class, static function ($app): ImageHandler {
            $compressionQuality = (int) Configs::get_value('compression_quality', 90);

            return new ImageHandler($compressionQuality);
        });
    }
}
