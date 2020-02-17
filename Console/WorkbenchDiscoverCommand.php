<?php namespace Jackiedo\Workbench\Console;

use Illuminate\Console\Command;
use Jackiedo\Workbench\Starter;
use Symfony\Component\Finder\Finder;

class WorkbenchDiscoverCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'workbench:discover';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Discover all workbench packages';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $finder = new Finder;
        $workbench_path = $this->laravel['path.base'].'/workbench';

        // We will use the finder to locate all package directories in the workbench
        // directory, then we will discover each package.
        $directories = $finder->in($workbench_path)->directories()->depth('== 1')->followLinks();

        if (iterator_count($directories) > 0) {
            foreach ($directories as $directory) {
                $realPath = $directory->getRealPath();
                $vendor = basename($directory->getPath());
                $package = $directory->getBasename();

                if (Starter::discoverPackage($realPath)) {
                    $this->line('Discovered Workbench Package: <fg=green>'.$vendor.'/'.$package.'</>');
                }
            }

            $this->info('Workbench packages manifest generated successfully.');
        } else {
            $this->info('No workbench packages were found.');
        }
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
}
