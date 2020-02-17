<?php namespace Jackiedo\Workbench\Console;

use Illuminate\Console\Command;
use Jackiedo\Workbench\Starter;
use Symfony\Component\Finder\Finder;

class WorkbenchDumpAutoloadCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'workbench:dump-autoload';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dump autoloader for all workbench packages';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $finder = new Finder;
        $path = $this->laravel['path.base'].'/workbench';
        $directories = $finder->in($path)->directories()->depth('== 1')->followLinks();

        foreach ($directories as $directory) {
            $this->info('>>> Dumping autoloader for package '.str_replace('\\', '/', $directory->getRelativePathname()));
            Starter::dumpAutoload($directory->getRealPath());
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
