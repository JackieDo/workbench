<?php namespace Jackiedo\Workbench\Console;

use Illuminate\Console\Command;
use Jackiedo\Workbench\Starter;
use Symfony\Component\Console\Input\InputArgument;

class WorkbenchDeleteCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'workbench:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete an existing workbench package';

    /**
     * The package name
     *
     * @var string
     */
    protected $package;

    /**
     * The path to package directory
     *
     * @var string
     */
    protected $packagePath;

    /**
     * The path to vendor directory of package
     *
     * @var string
     */
    protected $vendorDir;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->loadPackageInfo();

        $confirm = $this->confirm('Are you sure that you want to delete workbench package "'.$this->package.'"?');

        if ($confirm) {
            @undir($this->packagePath);

            if (is_empty_dir($this->vendorDir)) {
                @undir($this->vendorDir);
            }

            Starter::removeDiscoveredPackage($this->package);
        }

        $this->info('>>> Workbench package "'.$this->package.'" has been deleted.');
    }

    /**
     * Alias of the fire() method
     *
     * @return void
     */
    public function handle()
    {
        $this->fire();
    }

    /**
     * Get package path on the package argument
     *
     * @throws \InvalidArgumentException
     * @return string
     */
    protected function loadPackageInfo()
    {
        $package = $this->argument('package');
        $slashPos = strpos($package, '/');

        if ($slashPos === false || $slashPos == 0 || $slashPos == (strlen($package) - 1)) {
            throw new \InvalidArgumentException("The package argument must be of the form [vendor/name]");
        }

        if (!is_dir(base_path('workbench/'.$package))) {
            throw new \InvalidArgumentException("The package with name \"".$package."\" is not found");
        }

        $this->package = $package;
        $this->packagePath = base_path('workbench/'.$package);
        $this->vendorDir = dirname($this->packagePath);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('package', InputArgument::REQUIRED, 'The fullname (vendor/name) of the package.'),
        );
    }
}
