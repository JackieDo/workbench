**As of July 1 2020, Laravel Workbench is no longer maintained.**

It will still exist as an archive, but it will not be taken care of, resolve issues and no new releases will be released in the future.

Because the versions of the package have been fragmented, at the same time its features are no longer useful and suitable for modern Laravel versions, I decided to stop developing for it. Instead, I built another more efficient package called [Laravel Packager](https://github.com/JackieDo/laravel-packager) and put energy into it.

— *Jackie Do*

---

# Laravel Workbench

[![Latest Stable Version](https://poser.pugx.org/jackiedo/workbench/v/stable)](https://packagist.org/packages/jackiedo/workbench)
[![Total Downloads](https://poser.pugx.org/jackiedo/workbench/downloads)](https://packagist.org/packages/jackiedo/workbench)
[![Latest Unstable Version](https://poser.pugx.org/jackiedo/workbench/v/unstable)](https://packagist.org/packages/jackiedo/workbench)
[![License](https://poser.pugx.org/jackiedo/workbench/license)](https://packagist.org/packages/jackiedo/workbench)

Laravel Workbench (originally from Laravel 4.x, has now stopped growing) support our in building perfect structured packages for Laravel without spending a lot of time.

This package was created to bring Laravel Workbench back to Laravel 5+ and higher. Let this package support you in every detail through it's powerful features.

# Features
* Build directory structure for package.
* Generate a standard composer.json file for package.
* Generate a standard Service Provider file for package.
* Generate some scaffold resources file, such as:
    * Facade files
    * Interface files
    * Abstract files
    * Exception files
    * Controller files
    * Middleware files
    * Model files
    * Artisan CLI files
    * Configuration file
    * Migration files
    * Language files
    * View files
    * Route file
    * Helper file
    * ...
* Autoload dumping to be able to use your package immediately (by adding Service Provider of your package into `providers` section in `config/app.php` file or using discovery package feature through `extra/laravel` section in `composer.json` file).

# Overview
Look at one of the following topics to learn more about Laravel Workbench

* [Versions and compatibility](#versions-and-compatibility)
* [Installation](#installation)
* [Usage](#usage)
* [Screenshot](#screenshot)
* [Configuration](#configuration)
* [Other documentation](#other-documentation)

## Versions and compatibility

Each branch of Laravel Workbench is similarities with each version of Laravel 5+. Currently, this package supports the following versions of Laravel:

| Branch                                                | Laravel version  |
| ----------------------------------------------------- | ---------------- |
| [5.0](https://github.com/JackieDo/workbench/tree/5.0) | 5.0              |
| [5.1](https://github.com/JackieDo/workbench/tree/5.1) | 5.1              |
| [5.2](https://github.com/JackieDo/workbench/tree/5.2) | 5.2              |
| [5.3](https://github.com/JackieDo/workbench/tree/5.3) | 5.3              |
| [5.4](https://github.com/JackieDo/workbench/tree/5.4) | 5.4              |
| [5.5](https://github.com/JackieDo/workbench/tree/5.5) | 5.5              |
| [5.6](https://github.com/JackieDo/workbench/tree/5.6) | 5.6              |

In each branch we have multiple versions, tagged syntax as `5.0.*`, `5.1.*`, `5.2.*`...

## Installation

**Step 1 - Install this package through [Composer](https://getcomposer.org).**

Run the `composer require` command from the terminal on your project source:

```
$ composer require jackiedo/workbench:{{laravel-version}}.*
```

> _Note: The `{{laravel-version}}.*` string above is main version of Laravel that you want to install Laravel Workbench on it. Example, if you want to install this package on Laravel 5.6, you have to set require is `jackiedo/workbench:5.6.*`_

**Step 2 - Add mechanism to autoload service provider (for Laravel 5.4 or earlier only).**

Open `config/app.php` file, and add a new item to the providers array:

```
...
'providers' => array(
    ...
    Jackiedo\Workbench\WorkbenchServiceProvider::class,
),
```

> _Note: If we are using Laravel version 5.5 or later, with the feature `Discovery Packages`, we can skip above step._

**Step 3 - Publish the configuration file.**

From the terminal on your project source, run the command as follow:

```
$ php artisan vendor:publish --provider="Jackiedo\Workbench\WorkbenchServiceProvider" --force
```

> _Note: You should use `--force` option in publish command to override configuration file with newest one._

**Step 4 - Register the workbench package loaders.**

Open the `bootstrap/app.php` file at the root of Laravel project and put this following code at the very top of script (after the PHP open tag):

```
<?php

/*
|--------------------------------------------------------------------------
| Register The Workbench Loaders
|--------------------------------------------------------------------------
|
| The Laravel workbench provides a convenient place to develop packages
| when working locally. However we will need to load in the Composer
| auto-load files for the packages so that these can be used here.
|
*/

if (is_dir($workbench = __DIR__.'/../workbench'))
{
    Jackiedo\Workbench\Starter::autoload($workbench);
}
```

**Step 5 - Add mechanism to auto discover workbench packages (for Laravel 5.5 or later only).**

In this final step, if we are using Laravel version 5.5 or later, we should to add the feature `Auto Discover Workbench Packages` into `post-autoload-dump` section in `composer.json` file of our Laravel project.

Open `composer.json` file, and add the line `@php artisan workbench:discover` **after** the line `@php artisan package:discover` as follow:

```
"post-autoload-dump": [
    ...
    "@php artisan package:discover",
    "@php artisan workbench:discover"
]
```

> _Note: If we are using Laravel version 5.4 or earlier, we do not perform the above step._

## Usage

Now, you can use workbench commands to create your packages same as on Laravel 4.2.

> _Note: Before you create a package, you should to update `name` and `email` config value in your `config/workbench.php` file._

#### Creating a basic package.

Syntax:
```
$ php artisan workbench:make vendor/name
```

> _Note: The `vendor/name` string above is the form of your package name. Example: `jackiedo/demo-package`_

#### Creating a package with generating some scaffold resources.

Syntax:
```
$ php artisan workbench:make vendor/name --resources
```

#### Dump autoloader for all workbench packages

During the process of building your package, every time you generate a new class, file or change the `composer.json` file of package, you should rebuild the autoloader for the package through the following command:

```
$ php artisan workbench:dump-autoload
```

#### Manual discover workbench packages

During the process of building your package, every time you change the `extra/laravel` section in `composer.json` file of your package, you should rebuild cached through the following command:

```
$ php artisan workbench:discover
```

Or you can use the `composer dump-autoload` command, because we add above command into `post-autoload-dump` section in `composer.json` file of Laravel project during the installation process.

#### Delete an existing workbench package

Syntax:
```
$ php artisan workbench:delete vendor/name
```

## Screenshot

**Create package without generating scaffold resources.**

![create-without-resources](https://user-images.githubusercontent.com/9862115/26842158-0acb2cc2-4b16-11e7-93fb-d46063c57ef3.png)
![result-without-resources](https://user-images.githubusercontent.com/9862115/26842412-f3906cce-4b16-11e7-9190-d1e1c6eeeefc.png)

**Create package with generating scaffold resources.**

![create-with-resources](https://user-images.githubusercontent.com/9862115/26842286-7aed820c-4b16-11e7-89e6-3feaf16ee623.png)
![result-with-resources](https://user-images.githubusercontent.com/9862115/26842435-0a4dc2b8-4b17-11e7-9d39-2e1c46373d29.png)

**Create package with generating scaffold resources and point PSR-4 autoloading namespace to the src directory.**

![create-with-point-namespace-to-src-dir](https://user-images.githubusercontent.com/9862115/26842343-ad979d28-4b16-11e7-99dc-ece4decdafd4.png)
![result-with-point-namespace-to-src-dir](https://user-images.githubusercontent.com/9862115/26842459-1dfe6aba-4b17-11e7-9c27-8dcca0bca23a.png)

## Configuration

All details are provided in your `config/workbench.php` as comments (you have to run Artisan vendor:publish command before). Please read carefully before use.

## Other documentation

For more documentation about package development, you can visit Official Laravel Documentation pages:

- [Laravel 4.2 Package Development](https://laravel.com/docs/4.2/packages)
- [Laravel 5.0 Package Development](https://laravel.com/docs/5.0/packages)
- [Laravel 5.1 Package Development](https://laravel.com/docs/5.1/packages)
- [Laravel 5.2 Package Development](https://laravel.com/docs/5.2/packages)
- [Laravel 5.3 Package Development](https://laravel.com/docs/5.3/packages)
- [Laravel 5.4 Package Development](https://laravel.com/docs/5.4/packages)
- [Laravel 5.5 Package Development](https://laravel.com/docs/5.5/packages)
- [Laravel 5.6 Package Development](https://laravel.com/docs/5.6/packages)