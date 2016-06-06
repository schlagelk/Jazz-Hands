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

    public function getForgotPassword($request, $response)
    {
        return $this->view->render($response, 'auth/password/forgot.twig');
    }

    public function postRecoverPassword($request, $response)
    {
        $email = $request->getParam('email');
        
        $validation = $this->validator->validate($request, [
            'email' => v::noWhitespace()->notEmpty()->email(),
        ]);

        if ($validation->failed()) {
            return $response->withRedirect($this->router->pathFor('auth.password.forgot'));
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->flash->addMessage('error', 'We could not find that user');
        } else {
            $identifier = md5($email);
            $user->update([
                'recover_hash' => password_hash($identifier, PASSWORD_DEFAULT)
            ]);
            $this->flash->addMessage('info', 'Please check your email for instructions on resetting your password.');

            //send email
            $this->mail->send('email/auth/password-recover.php', ['user' => $user, 'identifier' => $identifier, 'url' => $this->container['settings']['app']['url']], function($message) use ($user) {
                $message->to($user->email);
                $message->subject('Password Reset');
            });
        }
        return $response->withRedirect($this->router->pathFor('home'));
    }

    public function getResetPassword($request, $response)
    {
        $email = $request->getParam('email');
        $identifier = $request->getParam('identifier');
        
        $user = User::where('email', $email)->first();

        if (!$user || !$user->recover_hash) {
            return $response->withRedirect($this->router->pathFor('home'));
        }

        if (!password_verify($identifier, $user->recover_hash)) {
            return $response->withRedirect($this->router->pathFor('home'));
        }
        return $this->view->render($response, 'auth/password/reset.twig', ['user' => $user, 'identifier' => $identifier]);
    }

    public function postResetPassword($request, $response)
    {
        $email = $request->getParam('email');
        $identifier = $request->getParam('identifier');
        $password = $request->getParam('password');
        
        $user = User::where('email', $email)->first();

        if (!$user || !$user->recover_hash) {
            return $response->withRedirect($this->router->pathFor('home'));
        }

        if (!password_verify($identifier, $user->recover_hash)) {
            return $response->withRedirect($this->router->pathFor('home'));
        }

        $validation = $this->validator->validate($request, [
            'password' => v::noWhitespace()->notEmpty(),
        ]);

        if (!$validation->failed()) {
            $user->update([
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'recover_hash'  => null
            ]);

            $this->flash->addMessage('info', 'Your password has been reset and you can now log in.');
            return $response->withRedirect($this->router->pathFor('home'));
        }

        return $this->view->render($response, 'auth/password/reset.twig', ['email' => $user.email, 'identifier' => $identifier]);
    }
}
