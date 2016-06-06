<?php

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;

$app->get('/', 'HomeController:index')->setName('home');

$app->group('', function () {
    $this->get('/auth/signup', 'AuthController:getSignUp')->setName('auth.signup');
    $this->post('/auth/signup', 'AuthController:postSignUp');

    $this->get('/auth/signin', 'AuthController:getSignIn')->setName('auth.signin');
    $this->post('/auth/signin', 'AuthController:postSignIn');
	$this->get('/activate', 'AuthController:activate')->setName('activate');
	$this->get('/auth/password/forgot', 'AuthController:getForgotPassword')->setName('auth.password.forgot');
	$this->post('/auth/password/recover', 'AuthController:postRecoverPassword')->setName('auth.password.recover');
	$this->get('/auth/password/reset', 'AuthController:getResetPassword')->setName('auth.password.reset');
	$this->post('/auth/password/reset', 'AuthController:postResetPassword')->setName('auth.password.reset');
})->add(new GuestMiddleware($container));

$app->group('', function () {
    $this->get('/auth/signout', 'AuthController:getSignOut')->setName('auth.signout');
    $this->get('/auth/password/change', 'PasswordController:getChangePassword')->setName('auth.password.change');
    $this->post('/auth/password/change', 'PasswordController:postChangePassword');
})->add(new AuthMiddleware($container));
