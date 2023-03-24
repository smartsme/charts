<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Auth\DefaultPasswordHasher;
use Cake\Core\Configure;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\Mailer\Mailer;
use Cake\Utility\Security;
use Cake\View\Exception\MissingTemplateException;

/**
 * Static content controller
 *
 * This controller will render views from templates/Pages/
 *
 * @link https://book.cakephp.org/4/en/controllers/pages-controller.html
 */
class UsersController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Auth', [
            'authenticate' => [
                'Form' => [
                    'fields' => ['username' => 'login', 'password' => 'password'],
                ],
            ],
        ]);
        $this->Auth->allow(['login', 'forgotPassword', 'resetPassword']);
    }

    public function isAuthorized($user = null)
    {
        // Any registered user can access public functions
        if (!$this->request->getParam('prefix')) {
            return true;
        }

        // Only admins can access admin functions
        if ($this->request->getParam('prefix') === 'Admin') {
            return (bool)($user['is_admin']);
        }

        // Default deny
        return false;
    }

    /**
     * Displays a view
     *
     * @param string ...$path Path segments.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Http\Exception\ForbiddenException When a directory traversal attempt.
     * @throws \Cake\View\Exception\MissingTemplateException When the view file could not
     *   be found and in debug mode.
     * @throws \Cake\Http\Exception\NotFoundException When the view file could not
     *   be found and not in debug mode.
     * @throws \Cake\View\Exception\MissingTemplateException In debug mode.
     */
    public function display(string ...$path): ?Response
    {
        if (!$path) {
            return $this->redirect('/');
        }
        if (in_array('..', $path, true) || in_array('.', $path, true)) {
            throw new ForbiddenException();
        }
        $page = $subpage = null;

        if (!empty($path[0])) {
            $page = $path[0];
        }
        if (!empty($path[1])) {
            $subpage = $path[1];
        }
        $this->set(compact('page', 'subpage'));

        try {
            return $this->render(implode('/', $path));
        } catch (MissingTemplateException $exception) {
            if (Configure::read('debug')) {
                throw $exception;
            }
            throw new NotFoundException();
        }
    }

    public function login()
    {
        $session = $this->request->getSession();
        if ($this->request->is('post')) {
            $user = $this->Auth->identify();
            if ($user) {
                $this->Auth->setUser($user);
                if ($this->Auth->authenticationProvider()->needsPasswordRehash()) {
                    $user = $this->Users->get($this->Auth->user('id'));
                    $user->password = $this->request->getData('password');
                    $this->Users->save($user);
                }
                $session->write('user_id', $this->Auth->user('id'));

                return $this->redirect($this->Auth->redirectUrl());
            } else {
                $this->Flash->error('<p class="text-danger text-center">Login lub hasło jest niepoprawne.</p>', [
                    'key' => 'authError',
                    'clear' => true,
                    'escape' => false,
                ]);
            }
        }
    }

    public function logout()
    {
        return $this->redirect($this->Auth->logout());
    }

    public function forgotPassword()
    {
        if ($this->request->is('post')) {
            $email = $this->request->getData('email');
            $token = Security::hash(Security::randomBytes(25));

            $userTable = $this->getTableLocator()->get('Users');
            if ($email == null) {
                $this->Flash->error('<p class="text-danger text-center">Proszę wpisać swój adres email.</p>', [
                    'key' => 'forgotPassword',
                    'clear' => true,
                    'escape' => false,
                ]);

                return $this->redirect(['prefix' => null, 'controller' => 'Users', 'action' => 'forgotPassword']);
            }

            $user = $userTable->find('all')->where(['email' => $email])->first();

            if ($user) {
                $user->token = $token;
                if ($userTable->save($user)) {
                    $mailer = new Mailer('default');
                    $mailer->setTransport('smtp');
                    $mailer->setFrom(['no-reply@smartsme.pl' => 'Smartsme'])
                    ->setTo($email)
                    ->setEmailFormat('html')
                    ->setSubject('Pomoc w odzyskiwaniu hasła smartsme')
                    ->deliver("Hello<br/>Kliknj tutaj żeby zresetować swoje hasło<br/><br/><a href='https://www.smartsme.pl/reset-password/$token'>Resetuj hasło</a>");
                }
                $this->Flash->success('<p class="text-success text-center">Wiadomość została wysłana na podany adres email!</p>', [
                    'key' => 'forgotPassword',
                    'clear' => true,
                    'escape' => false,
                ]);

                return $this->redirect(['prefix' => null, 'controller' => 'Users', 'action' => 'forgotPassword']);
            } else {
                $this->Flash->error('<p class="text-danger text-center">Nie znaleźliśmy użytkownika o podanym adresie email!</p>', [
                    'key' => 'forgotPassword',
                    'clear' => true,
                    'escape' => false,
                ]);

                return $this->redirect(['prefix' => null, 'controller' => 'Users', 'action' => 'forgotPassword']);
            }
        }
    }

    public function resetPassword($token)
    {
        if ($this->request->is('post')) {
            $hasher = new DefaultPasswordHasher();
            $password = $this->request->getData('password');
            $password_confirm = $this->request->getData('password_confirm');

            if ($password == $password_confirm) {
                $userTable = $this->getTableLocator()->get('Users');
                $user = $userTable->find('all')->where(['token' => $token])->first();
                if ($user) {
                    $user->password = $hasher->hash($password);
                    if ($userTable->save($user)) {
                        $this->Flash->success('<p class="text-success text-center">Hasło zostało zmienione pomyślnie!</p>', [
                            'key' => 'passwordReset',
                            'clear' => true,
                            'escape' => false,
                        ]);

                        return $this->redirect(['prefix' => null, 'controller' => 'Users', 'action' => 'login']);
                    }
                }
            } else {
                $this->Flash->error('<p class="text-danger text-center">Hasła muszą być takie same!</p>', [
                    'key' => 'resetPassword',
                    'clear' => true,
                    'escape' => false,
                ]);

                return $this->redirect(['prefix' => null, 'controller' => 'Users', 'action' => 'resetPassword']);
            }
        }
    }
}
