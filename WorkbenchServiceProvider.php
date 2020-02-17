<?php namespace Jackiedo\Workbench;

use Illuminate\Support\ServiceProvider;
use Jackiedo\Workbench\Console\WorkbenchDeleteCommand;
use Jackiedo\Workbench\Console\WorkbenchDumpAutoloadCommand;
use Jackiedo\Workbench\Console\WorkbenchMakeCommand;

class WorkbenchServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $packageConfigPath = __DIR__.'/Config/config.php';
        $appConfigPath     = config_path('workbench.php');

        $this->mergeConfigFrom($packageConfigPath, 'workbench');

        $this->publishes([
            $packageConfigPath => $appConfigPath,
        ], 'config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('package.creator', function ($app) {
            return new PackageCreator($app['files']);
        });

        $this->app->singleton('command.workbench.make', function ($app) {
            return new WorkbenchMakeCommand($app['package.creator']);
        });

        $this->app->singleton('command.workbench.delete', function ($app) {
            return new WorkbenchDeleteCommand;
        });

        $this->app->singleton('command.workbench.dump-autoload', function ($app) {
            return new WorkbenchDumpAutoloadCommand;
        });

        $this->commands('command.workbench.make');
        $this->commands('command.workbench.delete');
        $this->commands('command.workbench.dump-autoload');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array(
            'package.creator',
            'command.workbench.make',
            'command.workbench.delete',
            'command.workbench.dump-autoload',
        );
    }
}
