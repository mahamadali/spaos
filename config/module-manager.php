<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Module Config
    |--------------------------------------------------------------------------
    |
    */

    'namespace' => 'Modules',

    'stubs' => [
        'path' => base_path('stubs/laravel-starter-stubs'),
    ],

    'module' => [
        'files' => [
            'composer' => ['composer.stub', 'composer.json'],
            'json' => ['module.stub', 'module.json'],
            'config' => ['Config/config.stub', 'Config/config.php'],
            'database' => ['database/migrations/stubMigration.stub', 'database/migrations/stubMigration.php', 'rename'],
            'factories' => ['database/factories/stubFactory.stub', 'database/factories/stubFactory.php', 'rename'],
            'seeders' => ['database/seeders/stubSeeders.stub', 'database/seeders/stubSeeders.php', 'rename'],
            'command' => ['Console/Commands/StubCommand.stub', 'Console/Commands/StubCommand.php', 'rename'],
            'lang' => ['lang/en/text.stub', 'lang/en/text.php'],
            'models' => ['Models/stubModel.stub', 'Models/stubModel.php'],
            'providersRoute' => ['Providers/RouteServiceProvider.stub', 'Providers/RouteServiceProvider.php'],
            'providers' => ['Providers/stubServiceProvider.stub', 'Providers/stubServiceProvider.php'],
            'route_web' => ['routes/web.stub', 'routes/web.php'],
            'route_api' => ['routes/api.stub', 'routes/api.php'],
            'controller_backend' => ['Http/Controllers/Backend/stubBackendController.stub', 'Http/Controllers/Backend/stubBackendController.php'],
            'assets_js_app' => ['Resources/assets/js/app.js', 'Resources/assets/js/app.js'],
            'assets_sass_app' => ['Resources/assets/sass/app.scss', 'Resources/assets/sass/app.scss'],
            'assets_js_component' => ['Resources/assets/js/components/FormOffcanvas.vue', 'Resources/assets/js/components/FormOffcanvas.vue'],
            'assets_js_constant' => ['Resources/assets/js/constant.js', 'Resources/assets/js/constant.js'],
            'views_backend_index_datatable' => ['Resources/views/backend/stubViews/index_datatable.blade.stub', 'Resources/views/backend/stubViews/index_datatable.blade.php'],
            'test_feature' => ['Tests/Feature/stubTest.stub', 'Tests/Feature/stubTest.php'],
            'test_unit' => ['Tests/Unit/stubTest.stub', 'Tests/Unit/stubTest.php'],
            'package.json' => ['package.json', 'package.json'],
            'webpack.mix.js' => ['webpack.mix.js', 'webpack.mix.js'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Composer
    |--------------------------------------------------------------------------
    |
    | Config for the composer.json file
    |
    */

    'composer' => [
        'vendor' => 'iqonicdesign',
        'author' => [
            'name' => 'Iqonic Design',
            'email' => 'hello@iqonic.design',
        ],
    ],
];
