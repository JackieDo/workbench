<?php namespace Jackiedo\Workbench;

use Illuminate\Filesystem\Filesystem;
use Exception;

class PackageCreator
{

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The package instance
     *
     * @var \Jackiedo\Workbench\Package
     */
    protected $package;

    /**
     * Flag to determine package is plain or not
     *
     * @var boolean
     */
    protected $plain = true;

    /**
     * Flag to determine pointing namespace of package
     * into src/vendor/name directory
     *
     * @var boolean
     */
    protected $pointNsToSimilarDir = false;

    /**
     * Workbench directory path
     *
     * @var string
     */
    protected $workbenchDirPath;

    /**
     * Package directory path
     *
     * @var string
     */
    protected $packageDirPath;

    /**
     * Primary src directory path
     *
     * @var string
     */
    protected $srcDirPath;

    /**
     * Primary namespace directory path
     *
     * @var string
     */
    protected $namespaceDirPath;

    /**
     * Path to resource directories
     *
     * @var array
     */
    protected $resourceDirsPath = [];

    /**
     * The basic building blocks of the package.
     *
     * @param  array
     */
    protected $basicBlocks = [
        'BasicFiles',
        'TestDirectory',
    ];

    /**
     * The building blocks of the package.
     *
     * @param  array
     */
    protected $blocks = [
        'BasicFiles',
        'TestDirectory',
        'ResourceDirectories',
    ];

    /**
     * Create a new package creator instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     *
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * Create a new package.
     *
     * @param  \Jackiedo\Workbench\Package  $package
     * @param  string   $path
     * @param  array    $withResources
     * @param  boolean  $pointNsToSimilarDir
     *
     * @return string
     */
    public function create(Package $package, $path, $withResources = [], $pointNsToSimilarDir = true)
    {
        $this->package = $package;
        $this->workbenchDirPath = $path;
        $this->plain = (count($withResources) == 0) ? true : false;
        $this->pointNsToSimilarDir = $pointNsToSimilarDir;

        $this->packageDirPath = $this->createPackageDir();
        $this->srcDirPath = $this->createSrcDir();
        $this->namespaceDirPath = $this->createNamespaceDir();
        $this->resourceDirsPath = $this->buildResourceDirsPath($withResources);

        // To create the package, we will spin through a list of building blocks
        // that make up package. We'll then call the method to build that block
        // on this class, which keeps the actual building of stuff nice and
        // cleaned.
        foreach ($this->getBlocks() as $block) {
            $this->{"write{$block}"}();
        }

        return realpath($this->packageDirPath);
    }

    /**
     * Build paths to resource directories
     *
     * @param  array $resources
     *
     * @return array
     */
    protected function buildResourceDirsPath($resources)
    {
        $namespaceResources = $this->filterNamespaceResources($resources);
        $noneNamespaceResources = $this->filterNoneNamespaceResources($resources);

        $resourceDirsPath = [];

        foreach ($noneNamespaceResources as $type => $path) {
            $resourceDirsPath[$type] = $this->srcDirPath.'/'.$path;
        }

        foreach ($namespaceResources as $type => $path) {
            $resourceDirsPath[$type] = $this->namespaceDirPath.'/'.$path;
        }

        return $resourceDirsPath;
    }

    /**
     * Filter unused namespace resources from all resources.
     *
     * @param  array $resources
     *
     * @return array
     */
    protected function filterNoneNamespaceResources($resources = [])
    {
        if (count($resources) == 0) {
            return $resources;
        }

        return array_filter($this->package->getNoneNamespaceResources(), function ($key) use ($resources) {
            return in_array($key, $resources);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Filter used namespace resources from all resources.
     *
     * @param  array $resources
     *
     * @return array
     */
    protected function filterNamespaceResources($resources = [])
    {
        if (count($resources) == 0) {
            return $resources;
        }

        return array_filter($this->package->getNamespaceResources(), function ($key) use ($resources) {
            return in_array($key, $resources);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Get the blocks for a given package.
     *
     * @return array
     */
    protected function getBlocks()
    {
        return $this->plain ? $this->basicBlocks : $this->blocks;
    }

    /**
     * Write the basic files to the package root.
     *
     * @return void
     */
    protected function writeBasicFiles()
    {
        foreach (array('PhpUnit', 'Travis', 'Composer', 'PrimaryClass', 'ServiceProvider', 'Ignore') as $file) {
            $this->{"write{$file}File"}();
        }
    }

    /**
     * Write the PHPUnit stub file.
     *
     * @return void
     */
    protected function writePhpUnitFile()
    {
        $stub = __DIR__.'/stubs/phpunit.stub';

        $this->files->copy($stub, $this->packageDirPath.'/phpunit.xml');
    }

    /**
     * Write the Travis stub file.
     *
     * @return void
     */
    protected function writeTravisFile()
    {
        $stub = __DIR__.'/stubs/travis.stub';

        $this->files->copy($stub, $this->packageDirPath.'/.travis.yml');
    }

    /**
     * Write the composer.json stub file.
     *
     * @return void
     */
    protected function writeComposerFile()
    {
        $stub = $this->files->get(__DIR__.'/stubs/composer.stub');

        $stub = $this->formatPackageStub($stub);

        $this->files->put($this->packageDirPath.'/composer.json', $stub);
    }

    /**
     * Write main source file for package
     *
     * @return void
     */
    protected function writePrimaryClassFile()
    {
        $content = $this->formatPackageStub($this->files->get(__DIR__.'/stubs/main.stub'));

        $this->files->put($this->namespaceDirPath.'/'.$this->package->name.'.php', $content);
    }

    /**
     * Write the stub ServiceProvider for the package.
     *
     * @return void
     */
    protected function writeServiceProviderFile()
    {
        // Once we have the service provider stub file, we will need to format it
        // and make the necessary replacements to the class, namespaces, etc.
        // Then we'll be able to write it out into the package's workbench
        // directory for them.
        $stub = $this->files->get(__DIR__.'/stubs/provider.stub');

        $stub = $this->formatPackageStub($stub);

        $this->files->put($this->namespaceDirPath.'/'.$this->package->name.'ServiceProvider.php', $stub);
    }

    /**
     * Write the stub .gitignore file for the package.
     *
     * @return void
     */
    protected function writeIgnoreFile()
    {
        $this->files->copy(__DIR__.'/stubs/gitignore.stub', $this->packageDirPath.'/.gitignore');
    }

    /**
     * Create the test directory for the package.
     *
     * @return void
     */
    protected function writeTestDirectory()
    {
        $this->files->makeDirectory($this->packageDirPath.'/tests');

        $this->files->put($this->packageDirPath.'/tests/.gitkeep', '');
    }

    /**
     * Create the resources directories for a package
     * and write same featured file to this directory
     *
     * @return void
     */
    protected function writeResourceDirectories()
    {
        foreach ($this->resourceDirsPath as $resourceType => $resourcePath) {
            if (! $this->files->isDirectory($resourcePath)) {
                $this->files->makeDirectory($resourcePath, 0777, true);
            }

            // Once we create the resource directory, we will write one file that
            // have same feature from stub file to the directory. If stub file is
            // not exists, we will use an empty file so that this directory will be
            // kept in source control allowing the dev to go ahead and push
            // these components to GitHub right on creation.
            if ($this->files->exists(__DIR__.'/stubs/'.$resourceType.'.stub')) {
                $stub = $this->files->get(__DIR__.'/stubs/'.$resourceType.'.stub');

                $stub = $this->formatPackageStub($stub);

                $this->files->put($this->buildResourceFilePath($resourceType), $stub);
            } else {
                $this->files->put($resourcePath.'/.gitkeep', '');
            }
        }
    }

    /**
     * Create a directory for the package.
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    protected function createPackageDir()
    {
        $path = $this->buildPackageDirPath();

        // If the directory doesn't exist, we will go ahead and create the package
        // directory in the workbench location. We will use this entire package
        // name when creating the directory to avoid any potential conflicts.
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true);

            return $path;
        }

        throw new \InvalidArgumentException("Package exists.");
    }

    /**
     * Create the main source directory for the package.
     *
     * @return string
     */
    protected function createSrcDir()
    {
        $path = $this->buildSrcDirPath();

        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true);
        }

        return $path;
    }

    /**
     * Create the main namespace directory for the package.
     *
     * @return string
     */
    protected function createNamespaceDir()
    {
        $path = $this->buildNamespaceDirPath();

        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true);
        }

        return $path;
    }

    /**
     * Get path to package directory
     *
     * @return string
     */
    protected function buildPackageDirPath()
    {
        return $this->workbenchDirPath.'/'.$this->package->getFullName();
    }

    /**
     * Get path to primary src directory
     *
     * @return string
     */
    protected function buildSrcDirPath()
    {
        return $this->buildPackageDirPath().'/src';
    }

    /**
     * Get path to primary namespace directory
     *
     * @return string
     */
    protected function buildNamespaceDirPath()
    {
        if ($this->pointNsToSimilarDir) {
            return $this->buildSrcDirPath().'/'.$this->package->vendor.'/'.$this->package->name;
        }

        return $this->buildSrcDirPath();
    }

    /**
     * Create path to resource file
     *
     * @param  string $resourceType
     *
     * @return void
     */
    protected function buildResourceFilePath($resourceType)
    {
        $path = $this->resourceDirsPath[$resourceType];

        switch ($resourceType) {
            case 'lang':
                $localePath = $path.'/en';
                if (! $this->files->isDirectory($localePath)) {
                    $this->files->makeDirectory($localePath, 0777, true);
                }

                return $localePath.'/'.$this->buildResourceFileName('lang');
                break;

            default:
                return $path.'/'.$this->buildResourceFileName($resourceType);
                break;
        }
    }

    /**
     * Format resource filename
     *
     * @param  string $resourceType
     *
     * @return string
     */
    protected function buildResourceFileName($resourceType)
    {
        switch ($resourceType) {
            case 'config':
                return 'config.php';
                break;

            case 'migration':
                return date('Y_m_d_His').'_create_'.$this->package->snakeName.'_table.php';
                break;

            case 'lang':
                return 'lang.php';
                break;

            case 'view':
                return 'view.blade.php';
                break;

            case 'facade':
                return $this->package->name.'.php';
                break;

            case 'interface':
                return $this->package->name.'Interface.php';
                break;

            case 'abstract':
                return $this->package->name.'Abstract.php';
                break;

            case 'controller':
                return $this->package->name.'Controller.php';
                break;

            case 'model':
                return $this->package->name.'Model.php';
                break;

            case 'middleware':
                return $this->package->name.'Middleware.php';
                break;

            case 'route':
                return 'routes.php';
                break;

            case 'console':
                return $this->package->name.'Command.php';
                break;

            case 'exception':
                return $this->package->name.'Exception.php';
                break;

            case 'helper':
                return 'helpers.php';
                break;

            default:
                return '.gitkeep';
                break;
        }
    }

    /**
     * Format a generic package stub file.
     *
     * @param  string  $stub
     *
     * @return string
     */
    protected function formatPackageStub($stub)
    {
        // Placeholder handling
        $stub = preg_replace_callback('/\h*\{\{\@placeholder\h+(.*)\h+\@end\}\}\h*\r*\n+/msU', function ($match) {
            $parts = explode('|', trim($match[1]));
            $placeHolderFile = __DIR__.'/placeholders/' .$parts[0]. '.placeholder';
            $replaceWith = file_exists($placeHolderFile) ? $this->files->get($placeHolderFile) : null;

            if (!isset($parts[1])) {
                return $replaceWith;
            } else {
                if (array_key_exists($parts[1], $this->resourceDirsPath)) {
                    return $replaceWith;
                } else {
                    return null;
                }
            }
        }, $stub);

        // Callback handling
        $stub = preg_replace_callback('/\{\{\@callback\s*(.*)\s*\@end\}\}/msU', function ($match) {
            return eval(trim($match[1]));
        }, $stub);

        // Package properties handling
        foreach (get_object_vars($this->package) as $key => $value) {
            $stub = str_replace('{{'.snake_case($key).'}}', $value, $stub);
        }

        // Recursive if stub content have still {{...}} block
        if (preg_match('/\{\{(.*)\}\}/msU', $stub)) {
            return $this->formatPackageStub($stub);
        }

        return $stub;
    }

    /**
     * Find the relative file system path between two file system paths
     *
     * @param  string  $fromPath  Path to start from
     * @param  string  $toPath    Path we want to end up in
     *
     * @return string             Path leading from $fromPath to $toPath
     */
    protected function getRelativePath($fromPath, $toPath)
    {
        $from = explode('/', str_replace(DIRECTORY_SEPARATOR, '/', $fromPath));
        $to = explode('/', str_replace(DIRECTORY_SEPARATOR, '/', $toPath));
        $relativePath = '';

        $i = 0;

        // Find how far the path is the same
        while (isset($from[$i]) && isset($to[$i])) {
            if ($from[$i] != $to[$i]) {
                break;
            }
            $i++;
        }

        $j = count($from) - 1;

        // Add '../' until the path is the same
        while ($i <= $j) {
            if (!empty($from[$j])) {
                $relativePath .= '../';
            }
            $j--;
        }

        // Go to folder from where it starts differing
        while (isset($to[$i])) {
            if (!empty($to[$i])) {
                $relativePath .= $to[$i].'/';
            }
            $i++;
        }

        // Strip last separator
        return substr($relativePath, 0, -1);
    }
}
