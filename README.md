# caramel-l5
Ajax templating wrapper for Laravel 5

#L5.3

* Update starter-heaven
* php artisan make:middleware Caramel
* Ajouter le middleware dans les services provider dans Kernel.php dans $routeMiddleware

        'caramel' => \App\Http\Middleware\Caramel::class,

* Updater Caramel Middleware avec [src](https://github.com/jcsuzanne/caramel-l5/blob/master/src/CaramelMiddleware/Caramel.php)
* php artisan make:controller CaramelController
* Updater CaramelController avec [src](https://github.com/jcsuzanne/caramel-l5/blob/master/src/CaramelController/Caramel.php)
* Englober les routes (attention repertoire différent entre L5.2 et L5.3)

        Route::group(['middleware' => ['caramel']], function () {
            Route::get('/', 'CaramelController@home');
        });

* Si WP, rajouter le flush du cache

        // Flush the website
        Route::get('flushcache',function()
        {
            Cache::flush();
            $options = DB::table('wp_options')->where('option_name', 'LIKE', '_transient_%handmade%')->delete();
        });

* Virer la redirection si espace dans url dans .htaccess
* Ajouter Agent dans composer et lancer *composer update*

        "jenssegers/agent": "2.4.0"

* Verifer le .env

        MINIFY=true
        CACHE_ENABLED=true

* Si localisation, utiliser la librairie Mcamara Localisation

        "mcamara/laravel-localization": "1.1.*"

* dans Kernel.php

        'localize' => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRoutes::class,
        'localizationRedirect' => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter::class,
        'localeSessionRedirect' => \Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect::class

* et changer les routes

        // Redirect when no pages
        Route::get('/{lang?}', 'PageController@redirection');

        // Set all the routes
        Route::group(
            [
                'prefix' => LaravelLocalization::setLocale(),
                'middleware' => [ 'localeSessionRedirect', 'localizationRedirect' , 'caramel']
            ],
            function () {
                Route::get(LaravelLocalization::transRoute('routes.home'), 'PageController@home');
            }
        );

