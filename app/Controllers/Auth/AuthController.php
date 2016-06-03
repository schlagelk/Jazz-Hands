<?php

namespace App\Controllers\Auth;

use App\Models\User;
use App\Controllers\Controller;
use Respect\Validation\Validator as v;

class AuthController extends Controller
{
    public function getSignOut($request, $response)
    {
        $this->auth->logout();

        return $response->withRedirect($this->router->pathFor('home'));
    }

    public function getSignIn($request, $response)
    {
        return $this->view->render($response, 'auth/signin.twig');
    }

    public function postSignIn($request, $response)
    {
        $auth = $this->auth->attempt(
            $request->getParam('email'),
            $request->getParam('password')
        );

        if (!$auth) {
            $this->flash->addMessage('error', 'Could not sign you in with those details.');
            return $response->withRedirect($this->router->pathFor('auth.signin'));
        }

        return $response->withRedirect($this->router->pathFor('home'));
    }

    public function getSignUp($request, $response)
    {
        return $this->view->render($response, 'auth/signup.twig');
    }

    public function postSignUp($request, $response)
    {
        $validation = $this->validator->validate($request, [
            'email' => v::noWhitespace()->notEmpty()->email()->emailAvailable(),
            'name' => v::notEmpty()->alpha(),
            'password' => v::noWhitespace()->notEmpty(),
        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('auth.signup'));
        }

        $user = User::create([
            'email' => $request->getParam('email'),
            'name' => $request->getParam('name'),
            'password' => password_hash($request->getParam('password'), PASSWORD_DEFAULT),
            'active'    => false,
            'active_hash'   => md5($request->getParam('email')),
        ]);

        // send email
        $this->mail->send('email/auth/registered.php', ['user' => $user, 'url' => $this->container['settings']['app']['url']], function($message) use ($user) {
            $message->to($user->email);
            $message->subject('Thanks for registering');
        });

        $this->flash->addMessage('info', 'Thanks for signing up!  You will need to check your email and click on the link to confirm your account');

        $this->auth->attempt($user->email, $request->getParam('password'));

        return $response->withRedirect($this->router->pathFor('home'));
    }

    public function activate($request, $response)
    {
        $email = $request->getParam('email');
        $hash = $request->getParam('identifier');

        $user = User::where('email', $email)->where('active', false)->where('active_hash', $hash)->first();

        if (!$user) {
            $this->flash->addMessage('info', 'You have been signed up!');
        } else {
            $user->activateAccount();
            $this->flash->addMessage('info', 'Your account has been activated!');
        }
        return $response->withRedirect($this->router->pathFor('home'));

    }
}
