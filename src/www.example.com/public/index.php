<?php

include_path('../../');

include 'src/private/includes/Nirvarnia/Autoloader/Psr4';
(new Nirvarnia\Autoloader\Psr4())
    ->map('Website')
    ->to('src/private/autoload/Website')

    ->map('Nirvarnia\Cache')
    ->to('vendor/Nirvarnia/Cache')

    ->map('Nirvarnia\Contract')
    ->to('vendor/Nirvarnia/Contract')

    ->others('src/private/autoload');


$app = new Nirvarnia\Application\Main();

$app->bootstraps(
        new Nirvarnia\Bootstraps\ErrorHandler(),
        new Nirvarnia\Bootstraps\Timezone(),
        new Nirvarnia\Bootstraps\Globals()
    )->run();

$app->dependencies()
    ->bind('Nirvarnia\Contract\Cache\Store')
    ->to(function () {
        return new Nirvarnia\Cache\Store\Memcache([
            'host' => 'localhost',
            'port' => 11211
        ]);
    })
    ->singleton();

$app->dependencies()
    ->in('Website\Controller\Pages')
    ->in('Website\Controller\Blog')
    ->bind('Nirvarnia\Contract\Cache\Store')
    ->to(function () {
        return new Nirvarnia\Cache\Store\File([
            'path' => 'src/private/caches/file'
        ]);
    })
    ->singleton();

$app->dependencies()
    ->bind('Nirvarnia\Contract\Helper\Password')
    ->to('Nirvarnia\Helper\Password');

$app->dependencies()
    ->bind('Nirvarnia\Contract\Jwt\Encode')
    ->to('Nirvarnia\Jwt\Encode');

$app->dependencies()
    ->bind('Nirvarnia\Contract\Translate')
    ->to('Nirvarnia\Translate\Main');

//$app->dependencies()->logger = function ()
//{
//    static $logger = null;
//    if (is_null($logger)) {
//        $logger = new Nirvarnia\Logger\Logger();
//    }
//    return $logger;
//};

//$app->dependencies()->model = function ($name)
//{
//    static $connections = null;
//    static $query_factory = null;
//    static $models = [];
//    if (is_null($connections)) {
//        $config = $this->config->get('database.connections');
//        $connections = new Nirvarnia\Database\Connections($config);
//    }
//    if (is_null($query_factory)) {
//        $query_factory = new Nirvarnia\Database\QueryFactory($connections);
//    }
//    // #TODO: Swap this for a ModelFactory.
//    if (!array_key_exists($name, $models)) {
//        $model = 'Nirvarnia\\Application\\API\Model\\' . $name . 'Model';
//        $models[$name] = new $model($query_factory);
//    }
//    return $models[$name];
//};

//$app->dependencies()->translations = function ()
//{
//    static $translations = null;
//    if (is_null($translations)) {
//        $translations = new Nirvarnia\Translations\Translations('en', ['en']);
//    }
//    return $translations;
//};

//$app->middleware()->register(function ($request, $response, $next) use ($app)
//{
//    $noop = new Nirvarnia\Application\Middleware\Noop();
//    $response = $noop($request, $response, $next);
//    return $response;
//});

//$app->router()->get('/', function ($request, $response) use ($app)
//{
//    $controller = new Nirvarnia\Application\Controller\Home($app);
//    return $controller->index($request, $response);
//});

//$app->router()->catchAll(function ($request, $response) use ($app)
//{
//    $controller = new Nirvarnia\Application\Controller\NotFound($app);
//    return $controller->index($request, $response);
//});

$app->route('*')
    ->via('Nirvarnia\Middleware\XApiKey->test')
    ->via('Nirvarnia\Middleware\ContentType->test');

$app->route('GET /login')
    ->via('Website\Middleware\SomeMiddleware')
    ->to('Website\Controller\Auth->login');

$app->route(404)
    ->to('Website\Controller\NotFound->index');

$app->start();
