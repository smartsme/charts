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
namespace App\Controller\Admin;

use App\Controller\AppController as AppController;
use Cake\Auth\DefaultPasswordHasher;
use Cake\Core\Configure;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\Utility\Security;
use Cake\Utility\Text;
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
        $this->loadComponent('Auth', [
            'authorize' => 'Controller',
        ]);
        $this->loadComponent('Flash');
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

    public function panel()
    {
        $users = $this->getTableLocator()->get('Users')->find();
        $this->set('users', $users->all());
    }

    public function create()
    {
        if ($this->request->is('post')) {
            $userTable = $this->getTableLocator()->get('Users');
            $user = $userTable->newEmptyEntity();

            $hasher = new DefaultPasswordHasher();
            $first_name = $this->request->getData('first_name');
            $last_name = $this->request->getData('last_name');
            $login = $this->request->getData('login');
            $email = $this->request->getData('email');
            $password = $this->request->getData('password');
            $password_confirm = $this->request->getData('password_confirm');
            $token = Security::hash(Security::randomBytes(32));

            if ($password != $password_confirm) {
                $this->Flash->error('<p class="text-danger text-center">Hasła się nie zgadzają.</p>', [
                    'key' => 'create',
                    'clear' => true,
                    'escape' => false,
                ]);

                return $this->redirect(['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'create']);
            }

            $user = $userTable->newEntity($this->request->getData());
            $user->id = Text::uuid();
            $user->token = $token;
            $user->first_name = $first_name;
            $user->last_name = $last_name;
            $user->login = $login;
            $user->email = $email;
            $user->password = $hasher->hash($password);

            if ($userTable->save($user)) {
                $this->Flash->success('<p class="text-success text-center">Użytkownik został stworzony.</p>', [
                    'key' => 'create',
                    'clear' => true,
                    'escape' => false,
                ]);

                return $this->redirect(['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'create']);
            } else {
                $this->Flash->error('<p class="text-danger text-center">Nie udało się stworzyć użytkownika! Spróbuj ponownie później.</p>', [
                    'key' => 'create',
                    'clear' => true,
                    'escape' => false,
                ]);

                return $this->redirect(['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'create']);
            }
        }
    }

    public function update()
    {
        if ($this->request->is('post')) {
            $userTable = $this->getTableLocator()->get('Users');
            $user = $userTable->get($this->request->getData('user_id'));

            $hasher = new DefaultPasswordHasher();
            $first_name = $this->request->getData('first_name');
            $last_name = $this->request->getData('last_name');
            $login = $this->request->getData('login');
            $email = $this->request->getData('email');
            $password = $this->request->getData('password');
            $password_confirm = $this->request->getData('password_confirm');

            if ($password != $password_confirm) {
                $this->Flash->error('<p class="text-danger text-center">Hasła się nie zgadzają.</p>', [
                    'key' => 'update',
                    'clear' => true,
                    'escape' => false,
                ]);

                return $this->redirect(['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'update']);
            }

            $user->first_name = $first_name;
            $user->last_name = $last_name;
            $user->login = $login;
            $user->email = $email;
            $user->password = $hasher->hash($password);

            if ($userTable->save($user)) {
                $this->Flash->success('<p class="text-success text-center">Dane użytkownika zostały zmienione.</p>', [
                    'key' => 'update',
                    'clear' => true,
                    'escape' => false,
                ]);

                return $this->redirect(['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'update']);
            } else {
                $this->Flash->error('<p class="text-danger text-center">Nie udało się zedytować danych użytkownika! Spróbuj ponownie później.</p>', [
                    'key' => 'update',
                    'clear' => true,
                    'escape' => false,
                ]);

                return $this->redirect(['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'update']);
            }
        }

        $users = $this->getTableLocator()->get('Users')->find('all')->select(['id', 'login'])->all()->toList();
        $selectList = [];
        foreach ($users as $user) {
            $selectList[$user->id] = $user->login;
        }
        $this->set('users', $selectList);
    }

    public function delete()
    {
        if ($this->request->is('post')) {
            $userTable = $this->getTableLocator()->get('Users');
            $user = $userTable->get($this->request->getData('user_id'));
            if ($userTable->delete($user)) {
                $this->Flash->success('<p class="text-success text-center">Użytkownik został usunięty.</p>', [
                    'key' => 'delete',
                    'clear' => true,
                    'escape' => false,
                ]);

                return $this->redirect(['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'delete']);
            } else {
                $this->Flash->error('<p class="text-danger text-center">Nie udało się usunąć użytkownika! Spróbuj ponownie później.</p>', [
                    'key' => 'delete',
                    'clear' => true,
                    'escape' => false,
                ]);

                return $this->redirect(['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'delete']);
            }
        }

        $users = $this->getTableLocator()->get('Users')->find('all')->select(['id', 'login'])->toList();
        $selectList = [];
        foreach ($users as $user) {
            $selectList[$user->id] = $user->login;
        }
        $this->set('users', $selectList);
    }
}
