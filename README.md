# Laranix

[![Build Status](https://travis-ci.org/samanix/laranix.svg)](https://travis-ci.org/samanix/laranix)

## About
[Laranix](https://samanix.com/laranix) by [Samanix](https://samanix.com)

Laranix provides extended functionality and more feature rich existing functions to Laravel.

It was done as a package, albeit a large one, so that it is more easily updated with new Laravel versions.


### Contributions
Please use the [issue tracker](https://bitbucket.org/samanix-php/laranix/issues) to report any issues, or submit via [pull requests](https://bitbucket.org/samanix-php/laranix/pull-requests/)

Contributions are encouraged via [pull requests](https://bitbucket.org/samanix-php/laranix/pull-requests/)


### Security
For security related issues, please contact <samanixcom@gmail.com>.


### License
Laranix is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)


### Requirements
* PHP 7.1+
* [laravel/framework](https://github.com/laravel/framework) 5.5.*
* [guzzlehttp/guzzle](https://github.com/guzzle/guzzle) ~6.0
* [andreasindal/laravel-markdown](https://github.com/andreasindal/laravel-markdown) ~2.0


## Features
### AntiSpam
Provides invisible recaptcha entry on forms (sign up with Google), as well as a sequence ID field, which will prevent the form being submitted twice.

### Auth
Custom auth package built on top of Laravels own, providing:

* More feature rich User model
* User groups with group flags (permissions)
* Password resets
* Email verification
* User cages - protect an area so a user cannot access it

### Foundation
Provides base packages and configs for your app.

### Installer
Installs, publishes and copies files for Laranix to your app.

### Session
Adds IP locking to sessions, not required if you don't want to use it.

### Support
Adds extra functionality, including:

* Simple URL creator, that will always (try) and use the full URL - if it doesn't, somethings wrong with your setup
* String formatting with named parameters (similar to C#)
* Settings class, that allows you to use a class to determine parameters and their types

### Themer
Provides themes and loads the files given, combines like for like files in to one automatically and updates if any file is added or changed

### Tracker
Provides breadcrumb like tracking for user actions

## Installation
### Composer
`composer require samanix\laranix`

Or add to composer.json and then run `composer update`:

`"samanix\laranix": "~2.0"`

### Service Providers
Services are automatically registered since Laravel 5.5 using package discovery in the `composer.json`.

However, in the `config/app.php` you may still wish to comment out or remove:

    Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,

There are a number of other packages provided with the dev install of Laranix, see the documentation for them for help:

    barryvdh/laravel-debugbar
    barryvdh/laravel-ide-helper
    laravel/dusk

### Optional: Register Facades
Registered automatically since Laravel 5.5.

### Artisan Commands
Registered automatically since Laravel 5.5.

However, before running any commands for Laranix:

* Make sure to remove the default Laravel user and password migrations from 'database/migrations/'
* You can also remove from the relevant app folders:
  * Any default Laravel controllers
  * Any default Laravel views
  * Any default Laravel models
  * Any files/folders in resources/assets provided by default by Laravel
* It is recommended to run `php artisan preset none` as well, although this is not required

Run `php artisan laranix:install -AO`.

See `php artisan laranix:install --help` for options.

The following commands are also run automatically if the required option is set:

    php artisan vendor:publish --tag=laranix-configs
    php artisan vendor:publish --tag=laranix-migrations
    php artisan vendor:publish --tag=laravel-mail
    php artisan vendor:publish --provider="Indal\Markdown\MarkdownServiceProvider"

If you skip these commands, you can run the artisan ones at a later time.

**The command also attempts to run `composer dump-autoload`, if you get an error about this, please ensure you run
manually afterwards.**

### Controller
Laranix runs its own base controller, you can extend this class in your own controllers, or roll your own entirely, if
you do either of these, remember to update the controllers installed by Laranix.

The Laranix base controller resides in `Laranix\Foundation\Controllers\Controller`.

The base controller contains methods and traits that can be called that will scaffold certain scenarios.

### Routes
There are some routes provided for authentication as well as an entry page, you are free to change these.

**When running `laranix:install`, the `web.php` routes file is overwritten entirely with Laranix routes. Take a backup
first if required.**

*It is only overwritten if the `-O|overwrite` parameter is set.*

### Events
Add the following to your `App\Providers\EventServiceProvider.php` inside the `$listen` array:

    \Laranix\Auth\Email\Events\Updated::class => [
        \Laranix\Auth\Email\Listeners\Updated::class,
    ],
    \Illuminate\Auth\Events\Logout::class => [
        \Laranix\Auth\Listeners\Logout::class,
    ],
    \Illuminate\Auth\Events\Registered::class => [
        \Laranix\Auth\Listeners\Registered::class,
    ],
    \Laranix\Auth\Password\Events\Updated::class => [
        \Laranix\Auth\Password\Listeners\Updated::class,
    ],


Add the following to your `App\Providers\EventServiceProvider.php` inside the `$subscribe` array
(create if it does not exist):

    \Laranix\Auth\Email\Verification\Events\Subscriber::class,
    \Laranix\Auth\Events\Login\Subscriber::class,
    \Laranix\Auth\Group\Events\Subscriber::class,
    \Laranix\Auth\Password\Reset\Events\Subscriber::class,
    \Laranix\Auth\User\Cage\Events\Subscriber::class,
    \Laranix\Auth\User\Events\Subscriber::class,
    \Laranix\Auth\User\Groups\Events\Subscriber::class,

***You can always register your own listeners as well as or instead of the default provided.***

### Middleware
Open `app/Http/Kernel.php` and add the following to `$routeMiddleware`:

    'antispam'  => \Laranix\AntiSpam\Middleware\Verify::class,

Then, add `\Laranix\Tracker\Middleware\Flush::class` to the `$middleware` array.

### Configure
Open the `config\auth.php` and edit the `providers` array, add:

    'laranixuser' => [
         'driver'   => 'eloquent',
         'model'    => Laranix\Auth\User\User::class,
    ],

Then change the `guards.web.provider` value to `laranixuser`.

You may also wish to change the `guards.api.provider` to `laranixuser` too.

Next, open the `config\mail.php` and change the `markdown.theme` to `laranix`.

#### .env Files
Add/edit the following settings to your `.env`:

    APP_VERSION=your-app-version
    RECAPTCHA_KEY=site-key
    RECAPTCHA_SECRET=secret-key
    SESSION_DRIVER=laranix

#### Other Config files
Other configurations you can edit are:

***laranixauth.php***

Configures Auth components.

***themer.php***

Configures Themer component.

***appsettings.php***

Provides some extra custom settings for your app.

***globalviewvars.php***

Add variables that will be shared globally to **all** views.

***themerdefaultfiles.php***

Add files that Themer will load on all requests where Themer is initialised.

***socialmedia.php***

Provides links to various social media outlets, you are free to add your own.

***defaultusergroups.php***

Add and configure default user groups for when the database is seeded.

***antispam.php***

Configures AntiSpam components.

***tracker.php***

Configures Tracker component.

##### Other Packages
***markdown.php***

Remember to set `escape_markup` to `true` in this file if you are allowing user input.


### Database
Run migrations using the `php artisan migrate` command.

Seed your database with your default user groups using `php artisan db:seed --class=DefaultGroups` if Laranix has been
installed with the `--D|seeds` option.

## Artisan Commands
In addition to the `laranix:install` command, Laranix also provides some other commands.

> Remember to run `php artisan <command> --help` for options and information.

`laranix:themer:clear`
Clears compiled style and script files from themes.

`laranix:tokens:clear`
Clears expired tokens from the database for given models.

Default models for Laranix tokens are:

* `\\Laranix\\Auth\\Email\\Verification\\Verification`
* `\\Laranix\\Auth\\Password\\Reset\\Reset`

## Additional Notes
### Views
Laranix provides several views to get you started, it also provides some mails in markdown format, so if you edit `config\laranixauth.php` to not use markdown on mails, you will have to edit the views in `resources/views/mail/auth/`.

### Webpack
Remember to update `webpack.mix.js` if you roll your own theme settings.

### Tests

See [here](tests/README.md).
