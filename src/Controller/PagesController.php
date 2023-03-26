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
        $connection = ConnectionManager::get('default');
        $tablesColumns = [
            'generation_from_wind_sources' => [
                'solar',
                'wind',
            ],
            'inter_system_exchange_of_power_flows' => [
                'ceps_export',
                'ceps_import',
                'seps_export',
                'seps_import',
                '50hertz_export',
                '50hertz_import',
                'svk_export',
                'svk_import',
                'nek_export',
                'nek_import',
                'litgrid_export',
                'litgrid_import',
            ],
            'kse_demands' => [
                'predicted',
                'actual',
            ],
            'prices_and_quantity_of_energy_in_the_balancing_market' => [
                'cro',
                'cros',
                'croz',
                'contract_status',
                'imbalance',
            ],
            'electric_otf_energy' => [
                'first_transaction_rate',
                'dkr',
                'session_min',
                'session_max',
                'total_volumen',
                'number_of_contracts',
                'total_value_of_turnover',
                'transactions_number',
                'lop',
            ],
            'electric_rdn_energy' => [
                'course',
                'course_change',
                'volume',
                'volume_change',
            ],
            'otf_gas' => [
                'first_transaction_rate',
                'dkr',
                'session_min',
                'session_max',
                'total_volumen',
                'number_of_contracts',
                'total_value_of_turnover',
                'transactions_number',
                'lop',
            ],
            'property_rights_of_rpm_off_session' => [
                'course',
                'course_change',
                'volume',
                'volume_change',
            ],
            'property_rights_of_rpm_session' => [
                'course',
                'course_change',
                'volume',
                'volume_change',
            ],
            'rdb_gas' => [
                'course',
                'course_change',
                'volume',
                'volume_change',
            ],
            'rdn_gas_contract' => [
                'rate_min',
                'rate_max',
                'volume',
            ],
            'rdn_gas_index' => [
                'course',
                'course_change',
                'volume',
                'volume_change',
            ],
            'generation_of_power_generation_units' => [
                'Generacja',
                'Pompowanie',
            ],
            'carbon_emissions' => [
                'value',
            ],
            'coal_api2' => [
                'price',
                'open',
                'high',
                'low',
                'volume',
                'price_change',
            ],
        ];

        $hourlyTables = [
            'generation_from_wind_sources',
            'inter_system_exchange_of_power_flows',
            'kse_demands',
            'prices_and_quantity_of_energy_in_the_balancing_market',
            'carbon_emissions',
            'coal_api2',
        ];

        if ($this->request->getQuery('table')) {
            $startDate = empty($this->request->getQuery('start')) ? '2023-01-01' : $this->request->getQuery('start');
            $endDate = empty($this->request->getQuery('end')) ? date('Y-m-d') : $this->request->getQuery('end');
            $query = explode('&', $_SERVER['QUERY_STRING']);
            $params = [];

            foreach ($query as $param) {
                if (strpos($param, '=') === false) {
                    $param += '=';
                }

                [$name, $value] = explode('=', $param, 2);
                $params[urldecode($name)][] = urldecode($value);
            }

            function dateRange($first, $last, $step = '+1 day', $output_format = 'Y-m-d')
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

            function generateSql($table, $columns, $hourly, $code, $startDate, $endDate, $groupedHours = true, $sum = false, $mode = '')
            {
                $fields = '';
                if ($table != 'generation_of_power_generation_units') {
                    $columnsCount = count($columns[$table]);
                    for ($i = 0; $i < $columnsCount; $i++) {
                        $column = $columns[$table][$i];
                        $fields .= 'ROUND(' . ($hourly && $groupedHours ? (filter_var($sum, FILTER_VALIDATE_BOOLEAN) ? 'SUM' : 'AVG') . "(`$column`)" : "`$column`") . ", 2) as $column, ";
                    }
                } else {
                    $fields = 'ROUND((`h1` + `h2` + `h3` + `h4` + `h5` + `h6` + `h7` + `h8` + `h9` + `h10` + `h11` + `h12` + `h13` + `h14` + `h15` + `h16` + `h17` + `h18` + `h19` + `h20` + `h21` + `h22` + `h23` + `h24`) / 24, 2) as power';
                }

                $code = $table == 'rdn_gas_contract' ? "LIKE '$code%'" : "= '$code'";
                $fields = rtrim($fields, ', ');
                $mode = $table == 'generation_of_power_generation_units' ? " AND `mode` = '$mode'" : '';
                $sql = 'SELECT ' . ($groupedHours ? '`date`' : 'CONCAT(`date`, \' - \', `hour`) as date') . ", $fields FROM $table WHERE `date` BETWEEN '$startDate' AND '$endDate'" . (!$hourly ? "AND `code` $code $mode" : ($groupedHours ? ' GROUP BY `date`' : ' ORDER BY `hour` ASC'));

                return $sql;
            }

            function populateData($records, $data, $table, $label = '')
            {
                $keys = [];

                if (empty($label)) {
                    $recordsCount = count(array_keys($records[0]));
                    for ($i = 1; $i < $recordsCount; $i++) {
                        array_push($keys, array_keys($records[0])[$i]);
                        $data[array_keys($records[0])[$i] . ' - ' . $table] = [];
                    }
                } else {
                    array_push($keys, $label);
                    $data["$label - $table"] = [];
                }

                $keysCount = count($keys);
                for ($i = 0; $i < $keysCount; $i++) {
                    $recordsCount = count($records);
                    for ($j = 0; $j < $recordsCount; $j++) {
                        array_push($data[$keys[$i] . ' - ' . $table], [
                            'x' => $records[$j]['date'],
                            'y' => $records[$j][$table != 'generation_of_power_generation_units' ? $keys[$i] : 'power'],
                        ]);
                    }
                }

                return $data;
            }

            $tableCodePairs = [];
            $paramsCount = count($params['table']);
            for ($i = 0; $i < $paramsCount; $i++) {
                if (in_array($params['table'][$i], $hourlyTables)) {
                    $tableCodePairs[$params['table'][$i]] = '';
                } else {
                    $tableCodePairs[$params['table'][$i]] = $params['code'][0];
                    array_shift($params['code']);
                }
            }

            $data = [];
            $labels = [];

            if (!array_diff(array_keys($tableCodePairs), $hourlyTables) && $startDate == $endDate && !count(array_intersect(['carbon_emissions', 'coal_api2'], array_keys($tableCodePairs)))) {
                for ($i = 1; $i <= 24; $i++) {
                    array_push($labels, "$startDate - $i");
                }
                foreach ($tableCodePairs as $table => $code) {
                    $sql = generateSql($table, $tablesColumns, in_array($table, $hourlyTables), $code, $startDate, $endDate, false, $this->request->getQuery('sum'));
                    $records = $connection->execute($sql)->fetchAll('assoc');
                    if ($records) {
                        $data = populateData($records, $data, $table);
                    }
                }
            } else {
                foreach ($tableCodePairs as $table => $code) {
                    $labels = dateRange($startDate, $endDate);
                    if ($table != 'generation_of_power_generation_units') {
                        $sql = generateSql($table, $tablesColumns, in_array($table, $hourlyTables), $code, $startDate, $endDate, true, $this->request->getQuery('sum'));
                        $records = $connection->execute($sql)->fetchAll('assoc');
                        if ($records) {
                            $data = populateData($records, $data, $table);
                        }
                    } else {
                        $sql = generateSql($table, $tablesColumns, in_array($table, $hourlyTables), $code, $startDate, $endDate, true, $this->request->getQuery('sum'), 'Generacja');
                        $records = $connection->execute($sql)->fetchAll('assoc');
                        if ($records) {
                            $data = populateData($records, $data, $table, 'Generacja');
                        }
                        $sql = generateSql($table, $tablesColumns, in_array($table, $hourlyTables), $code, $startDate, $endDate, true, $this->request->getQuery('sum'), 'Pompowanie');
                        $records = $connection->execute($sql)->fetchAll('assoc');
                        if ($records) {
                            $data = array_merge($data, populateData($records, $data, $table, 'Pompowanie'));
                        }
                    }
                }
            }
        }

        $this->set('labels', $labels ?? []);
        $this->set('data', $data ?? []);
        $this->set('title', 'Wykresy');
    }
}
