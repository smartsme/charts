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

define('DEFAULT_START_DATE', date('Y') . '-01-01');
define('DEFAULT_END_DATE', date('Y-m-d'));

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\View\Exception\MissingTemplateException;

/**
 * Static content controller
 *
 * This controller will render views from templates/Pages/
 *
 * @link https://book.cakephp.org/4/en/controllers/pages-controller.html
 */
class PagesController extends AppController
{
    public function initialize(): void
    {
        $this->loadComponent('Auth', [
            'authorize' => 'Controller',
        ]);
        $this->loadComponent('GetChartData');
        $this->Auth->allow('login');
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

    public function home()
    {
        $session = $this->request->getSession();
        $this->set('is_admin', (bool)$this->getTableLocator()->get('Users')->find('all')->where(['id' => $session->read('user_id')])->select('is_admin')->first()->is_admin);
        $this->set('title', 'Strona główna');
    }

    /**
     * Chart page
     *
     * @return \Cake\Http\Response|null
     */
    public function chart()
    {
        $hourlyTables = [
            'generation_from_wind_sources',
            'inter_system_exchange_of_power_flows',
            'kse_demands',
            'prices_and_quantity_of_energy_in_the_balancing_market',
        ];

        $newTables = [
            'brent_oil',
            'coal_api2',
            'carbon_emissions',
        ];

        if ($this->request->getQuery('tables')) {
            $startDate = empty($this->request->getQuery('start')) ? '2023-01-01' : $this->request->getQuery('start');
            $endDate = empty($this->request->getQuery('end')) ? date('Y-m-d') : $this->request->getQuery('end');
            $tables = json_decode($this->request->getQuery('tables'), true);
            $tablesCount = count($tables);
            $hourlyTable = false;
            $data = [];

            for ($i = 0; $i < $tablesCount; $i++) {
                if (in_array($tables[$i]['tableName'], $hourlyTables)) {
                    $hourlyTable = true;
                }
            }

            for ($i = 0; $i < $tablesCount; $i++) {
                $tableName = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $tables[$i]['tableName']))));
                if (in_array($tables[$i]['tableName'], $hourlyTables)) {
                    $data = array_merge($data, $this->GetChartData->$tableName($startDate ?? DEFAULT_START_DATE, $endDate ?? DEFAULT_END_DATE, $this->request->getQuery('sum'), $startDate != $endDate));
                }

                if (in_array($tables[$i]['tableName'], $newTables)) {
                    $data = array_merge($data, $this->GetChartData->$tableName($startDate ?? DEFAULT_START_DATE, $endDate ?? DEFAULT_END_DATE, $startDate == $endDate));
                }

                if (!in_array($tables[$i]['tableName'], $hourlyTables) && !in_array($tables[$i]['tableName'], $newTables)) {
                    for ($j = 0; $j < count($tables[$i]['codes']); $j++) {
                        $data = array_merge($data, $this->GetChartData->$tableName($startDate ?? DEFAULT_START_DATE, $endDate ?? DEFAULT_END_DATE, $tables[$i]['codes'][$j], $startDate == $endDate));
                    }
                }
            }

            if ($startDate != $endDate) {
                $labels = $this->dateRange($startDate ?? DEFAULT_START_DATE, $endDate ?? DEFAULT_END_DATE);
            }

            $this->set('labels', $labels ?? []);
            $this->set('data', $data ?? []);
        }

        $this->set('title', 'Wykresy');
    }

    private function dateRange($first, $last, $step = '+1 day', $output_format = 'Y-m-d')
    {
        $dates = [];
        $current = strtotime($first);
        $last = strtotime($last);

        while ($current <= $last) {
            $dates[] = date($output_format, $current);
            $current = strtotime($step, $current);
        }

        return $dates;
    }
}
