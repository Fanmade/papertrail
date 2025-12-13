<?php

namespace Fanmade\Papertrail;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Fanmade\Papertrail\Contracts\PdfFormFieldExtractor;
use Fanmade\Papertrail\Contracts\PdfImageGenerator;
use Fanmade\Papertrail\Contracts\PdfPageImageRenderer;
use Fanmade\Papertrail\Contracts\PdfPageMetadataExtractor;
use Fanmade\Papertrail\Http\Middleware\Authorize;
use Fanmade\Papertrail\Services\ImagickPdfImageRenderer;
use Fanmade\Papertrail\Services\ImagickPdfPageMetadataExtractor;
use Fanmade\Papertrail\Services\PopplerPdfImageGenerator;
use Fanmade\Papertrail\Services\PythonFormFieldsExtractor;

class PapertrailServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishes(
            [
                __DIR__ . '/../config/papertrail.php' => config_path('papertrail.php'),
            ],
            'papertrail-config'
        );

        $this->publishesMigrations(
            [
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ],
            'papertrail-migrations'
        );

        $this->app->booted(
            function () {
                $this->routes();
            }
        );

        $this->loadJsonTranslationsFrom(__DIR__ . '/../lang');

        Nova::serving(
            function (ServingNova $event) {
                $this->bootTranslations();
            }
        );
    }

    /**
     * Register the tool's routes.
     */
    protected function routes(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Nova::router(['nova', 'nova.auth', Authorize::class], 'papertrail')
            ->group(__DIR__ . '/../routes/inertia.php');

        Route::middleware(['nova', 'nova.auth', Authorize::class])
            ->prefix('nova-vendor/papertrail')
            ->group(__DIR__ . '/../routes/api.php');
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/papertrail.php', 'papertrail');

        $this->app->bind(
            PdfImageGenerator::class,
            function () {
                return match (config('papertrail.thumb_driver')) {
                    'poppler' => new PopplerPdfImageGenerator,
                    default => new ImagickPdfImageRenderer,
                };
            }
        );

        $this->app->bind(
            PdfPageImageRenderer::class,
            function () {
                // For now only Imagick implementation is provided
                return new ImagickPdfImageRenderer;
            }
        );

        $this->app->bind(
            PdfPageMetadataExtractor::class,
            function () {
                // For now only Imagick implementation is provided
                return new ImagickPdfPageMetadataExtractor;
            }
        );

        $this->app->bind(
            PdfFormFieldExtractor::class,
            function () {
                return match (config('papertrail.fields_driver')) {
                    'python' => new PythonFormFieldsExtractor,
                    default => throw new \Exception('Invalid fields driver'),
                };
            }
        );
    }

    protected function bootTranslations(): void
    {
        $locale = $this->app->getLocale();

        Nova::translations(__DIR__ . "/../lang/$locale.json");
    }
}
