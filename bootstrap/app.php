<?php

use Respect\Validation\Validator as v;
use App\Mail\Mailer;

session_start();

require __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true,
        'db' => [
            'driver' => 'mysql',
            'host' => getenv('host'),
            'database' => getenv('database'),
            'username' => getenv('username'),
            'password' => getenv('password'),
            'port'     => getenv('port'),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ],
        'mail' => [
            'smtp_auth' => true,
            'smtp_secure'   => 'tls',
            'host' => getenv('mail.host'),
            'username' => getenv('mail.username'),
            'password' => getenv('mail.password'),
            'port' => getenv('mail.port'),
            'name' => getenv('mail.name'),
            'html'  => true
        ]
    ],
]);

$container = $app->getContainer();

$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();
    
$container['db'] = function ($container) use ($capsule) {
    return $capsule;
};

$container['auth'] = function ($container) {
    return new \App\Auth\Auth;
};

$container['flash'] = function ($container) {
    return new \Slim\Flash\Messages;
};

$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig(__DIR__ . '/../resources/views', [
        'cache' => false,
    ]);

    $view->addExtension(new \Slim\Views\TwigExtension(
        $container->router,
        $container->request->getUri()
    ));

    $view->getEnvironment()->addGlobal('auth', [
        'check' => $container->auth->check(),
        'user' => $container->auth->user(),
    ]);
    
    $view->getEnvironment()->addGlobal('flash', $container->flash);

    return $view;
};

$container['validator'] = function ($container) {
    return new App\Validation\Validator;
};

$container['HomeController'] = function ($container) {
    return new \App\Controllers\HomeController($container);
};

$container['AuthController'] = function ($container) {
    return new \App\Controllers\Auth\AuthController($container);
};

$container['PasswordController'] = function ($container) {
    return new \App\Controllers\Auth\PasswordController($container);
};

$container['csrf'] = function ($container) {
    return new \Slim\Csrf\Guard;
};

$container['mail'] = function ($container) {
    $mailer = new \PHPMailer;
    $mailer->isSMTP();
    $mailer->Host = $container['settings']['mail']['host'];
    $mailer->SMTPAuth = $container['settings']['mail']['smtp_auth'];
    $mailer->SMTPSecure = $container['settings']['mail']['smtp_secure'];
    $mailer->Port = $container['settings']['mail']['port'];
    $mailer->Username = $container['settings']['mail']['username'];
    $mailer->Password = $container['settings']['mail']['password'];
    $mailer->isHTML(true);
    $mailer->setFrom($container['settings']['mail']['username'], $container['settings']['mail']['name']);

    return new Mailer($container['view'], $mailer);
};

$app->add(new \App\Middleware\ValidationErrorsMiddleware($container));
$app->add(new \App\Middleware\OldInputMiddleware($container));
$app->add(new \App\Middleware\CsrfViewMiddleware($container));

$app->add($container->csrf);

v::with('App\\Validation\\Rules\\');

require __DIR__ . '/../app/routes.php';
