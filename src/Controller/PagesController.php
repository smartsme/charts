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
        $connection = ConnectionManager::get('default');

        $hourlyTables = [
            'generation_from_wind_sources',
            'inter_system_exchange_of_power_flows',
            'kse_demands',
            'prices_and_quantity_of_energy_in_the_balancing_market',
            'carbon_emissions',
            'coal_api2',
            'brent_oil',
        ];

        // if ($this->request->getQuery('table')) {
        $startDate = '2023-04-10'; //empty($this->request->getQuery('start')) ? '2023-01-01' : $this->request->getQuery('start');
        $endDate = '2023-04-10'; //empty($this->request->getQuery('end')) ? date('Y-m-d') : $this->request->getQuery('end');
        // $query = explode('&', $_SERVER['QUERY_STRING']);
        // $params = [];

        // foreach ($query as $param) {
        //     if (strpos($param, '=') === false) {
        //         $param += '=';
        //     }

        //     [$name, $value] = explode('=', $param, 2);
        //     $params[urldecode($name)][] = urldecode($value);
        // }

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

        $data = array_merge(
            // $this->GetChartData->brentOil($startDate ?? DEFAULT_START_DATE, $endDate ?? DEFAULT_END_DATE, $startDate == $endDate),
            // $this->GetChartData->carbonEmissions($startDate ?? DEFAULT_START_DATE, $endDate ?? DEFAULT_END_DATE, $startDate == $endDate),
            // $this->GetChartData->coalApi2($startDate ?? DEFAULT_START_DATE, $endDate ?? DEFAULT_END_DATE, $startDate == $endDate),
            $this->GetChartData->generationFromWindSources($startDate ?? DEFAULT_START_DATE, $endDate ?? DEFAULT_END_DATE, false, $startDate != $endDate),
            // $this->GetChartData->interSystemExchangeOfPowerFlows($startDate ?? DEFAULT_START_DATE, $endDate ?? DEFAULT_END_DATE, false, $startDate != $endDate),
            // $this->GetChartData->kseDemands($startDate ?? DEFAULT_START_DATE, $endDate ?? DEFAULT_END_DATE, false, $startDate != $endDate),
            // $this->GetChartData->pricesAndQuantityOfEnergyInTheBalancingMarket($startDate ?? DEFAULT_START_DATE, $endDate ?? DEFAULT_END_DATE, false, $startDate != $endDate),
            $this->GetChartData->electricOtfEnergy($startDate ?? DEFAULT_START_DATE, $endDate ?? DEFAULT_END_DATE, 'BASE_Y-26', $startDate == $endDate),
            $this->GetChartData->electricRdnEnergy($startDate ?? DEFAULT_START_DATE, $endDate ?? DEFAULT_END_DATE, 'TGe15', $startDate == $endDate),
            $this->GetChartData->otfGas($startDate ?? DEFAULT_START_DATE, $endDate ?? DEFAULT_END_DATE, 'GAS_BASE_Y-25', $startDate == $endDate),
            $this->GetChartData->propertyRightsOfRpmOffSession($startDate ?? DEFAULT_START_DATE, $endDate ?? DEFAULT_END_DATE, 'TGEbg', $startDate == $endDate),
            $this->GetChartData->propertyRightsOfRpmSession($startDate ?? DEFAULT_START_DATE, $endDate ?? DEFAULT_END_DATE, 'TGEbgTP', $startDate == $endDate),
            $this->GetChartData->rdbGas($startDate ?? DEFAULT_START_DATE, $endDate ?? DEFAULT_END_DATE, 'TGEgasID', $startDate == $endDate),
            $this->GetChartData->rdnGasContract($startDate ?? DEFAULT_START_DATE, $endDate ?? DEFAULT_END_DATE, 'GAS', $startDate == $endDate),
            $this->GetChartData->rdnGasIndex($startDate ?? DEFAULT_START_DATE, $endDate ?? DEFAULT_END_DATE, 'TGEgasDA', $startDate == $endDate),
        );
        if ($startDate != $endDate) {
            $labels = dateRange($startDate ?? DEFAULT_START_DATE, $endDate ?? DEFAULT_END_DATE);
        }

            // function generateSql($table, $columns, $hourly, $code, $startDate, $endDate, $groupedHours = true, $sum = false, $mode = '')
            // {
            //     $fields = '';
            //     if ($table != 'generation_of_power_generation_units') {
            //         $columnsCount = count($columns[$table]);
            //         for ($i = 0; $i < $columnsCount; $i++) {
            //             $column = $columns[$table][$i];
            //             $fields .= 'ROUND(' . ($hourly && $groupedHours ? (filter_var($sum, FILTER_VALIDATE_BOOLEAN) ? 'SUM' : 'AVG') . "(`$column`)" : "`$column`") . ", 2) as $column, ";
            //         }
            //     } else {
            //         $fields = 'ROUND((`h1` + `h2` + `h3` + `h4` + `h5` + `h6` + `h7` + `h8` + `h9` + `h10` + `h11` + `h12` + `h13` + `h14` + `h15` + `h16` + `h17` + `h18` + `h19` + `h20` + `h21` + `h22` + `h23` + `h24`) / 24, 2) as power';
            //     }

            //     $code = $table == 'rdn_gas_contract' ? "LIKE '$code%'" : "= '$code'";
            //     $fields = rtrim($fields, ', ');
            //     $mode = $table == 'generation_of_power_generation_units' ? " AND `mode` = '$mode'" : '';
            //     $sql = 'SELECT ' . ($groupedHours ? '`date`' : 'CONCAT(`date`, \' - \', `hour`) as date') . ", $fields FROM $table WHERE `date` BETWEEN '$startDate' AND '$endDate'" . (!$hourly ? "AND `code` $code $mode" : ($groupedHours ? ' GROUP BY `date`' : ' ORDER BY `hour` ASC'));

            //     return $sql;
            // }

            // function populateData($records, $data, $table, $label = '')
            // {
            //     $keys = [];

            //     if (empty($label)) {
            //         $recordsCount = count(array_keys($records[0]));
            //         for ($i = 1; $i < $recordsCount; $i++) {
            //             array_push($keys, array_keys($records[0])[$i]);
            //             $data[array_keys($records[0])[$i] . ' - ' . $table] = [];
            //         }
            //     } else {
            //         array_push($keys, $label);
            //         $data["$label - $table"] = [];
            //     }

            //     $keysCount = count($keys);
            //     for ($i = 0; $i < $keysCount; $i++) {
            //         $recordsCount = count($records);
            //         for ($j = 0; $j < $recordsCount; $j++) {
            //             array_push($data[$keys[$i] . ' - ' . $table], [
            //                 'x' => $records[$j]['date'],
            //                 'y' => $records[$j][$table != 'generation_of_power_generation_units' ? $keys[$i] : 'power'],
            //             ]);
            //         }
            //     }

            //     return $data;
            // }

            // $tableCodePairs = [];
            // $paramsCount = count($params['table']);
            // for ($i = 0; $i < $paramsCount; $i++) {
            //     if (in_array($params['table'][$i], $hourlyTables)) {
            //         $tableCodePairs[$params['table'][$i]] = '';
            //     } else {
            //         $tableCodePairs[$params['table'][$i]] = $params['code'][0];
            //         array_shift($params['code']);
            //     }
            // }

            // $data = [];
            // $labels = [];

            // if (!array_diff(array_keys($tableCodePairs), $hourlyTables) && $startDate == $endDate && !count(array_intersect(['carbon_emissions', 'coal_api2', 'brent_oil'], array_keys($tableCodePairs)))) {
            //     for ($i = 1; $i <= 24; $i++) {
            //         array_push($labels, "$startDate - $i");
            //     }
            //     foreach ($tableCodePairs as $table => $code) {
            //         $sql = generateSql($table, $tablesColumns, in_array($table, $hourlyTables), $code, $startDate, $endDate, false, $this->request->getQuery('sum'));
            //         $records = $connection->execute($sql)->fetchAll('assoc');
            //         if ($records) {
            //             $data = populateData($records, $data, $table);
            //         }
            //     }
            // } elseif (count(array_intersect($hourlyTables, array_keys($tableCodePairs))) < count($tableCodePairs) && $startDate == $endDate) {
            //     $dates = [];
            //     for ($i = 0; $i < 24; $i++) {
            //         array_push($dates, $startDate . ' - ' . ($i + 1));
            //     }
            //     foreach ($tableCodePairs as $table => $code) {
            //         if (in_array($table, $hourlyTables) && $table != 'carbon_emissions' && $table != 'coal_api2' && $table != 'brent_oil') {
            //             $sql = generateSql($table, $tablesColumns, true, $code, $startDate, $endDate, false, $this->request->getQuery('sum'));
            //             $records = $connection->execute($sql)->fetchAll('assoc');
            //             if ($records) {
            //                 $populated = populateData($records, $data, $table);
            //                 foreach ($populated as $table => $arr) {
            //                     $data[$table] = $arr;
            //                 }
            //             }
            //         } else {
            //             $sql = generateSql($table, $tablesColumns, in_array($table, $hourlyTables), $code, $startDate, $endDate, true, $this->request->getQuery('sum'));
            //             $records = $connection->execute($sql)->fetchAll('assoc');
            //             $final = [];
            //             for ($i = 0; $i < 24; $i++) {
            //                 for ($j = 0; $j < count($records); $j++) {
            //                     $records[$j]['date'] = $dates[$i];
            //                     array_push($final, $records[$j]);
            //                 }
            //             }
            //             if ($records) {
            //                 $populated = populateData($final, $data, $table);
            //                 foreach ($populated as $table => $arr) {
            //                     $data[$table] = $arr;
            //                 }
            //             }
            //         }
            //     }
            // } else {
            //     foreach ($tableCodePairs as $table => $code) {
            //         $labels = dateRange($startDate, $endDate);
            //         if ($table != 'generation_of_power_generation_units') {
            //             $sql = generateSql($table, $tablesColumns, in_array($table, $hourlyTables), $code, $startDate, $endDate, true, $this->request->getQuery('sum'));
            //             $records = $connection->execute($sql)->fetchAll('assoc');
            //             if ($records) {
            //                 $data = populateData($records, $data, $table);
            //             }
            //         } else {
            //             $sql = generateSql($table, $tablesColumns, in_array($table, $hourlyTables), $code, $startDate, $endDate, true, $this->request->getQuery('sum'), 'Generacja');
            //             $records = $connection->execute($sql)->fetchAll('assoc');
            //             if ($records) {
            //                 $data = populateData($records, $data, $table, 'Generacja');
            //             }
            //             $sql = generateSql($table, $tablesColumns, in_array($table, $hourlyTables), $code, $startDate, $endDate, true, $this->request->getQuery('sum'), 'Pompowanie');
            //             $records = $connection->execute($sql)->fetchAll('assoc');
            //             if ($records) {
            //                 $data = array_merge($data, populateData($records, $data, $table, 'Pompowanie'));
            //             }
            //         }
            //     }
            // }
        // }

        $this->set('labels', $labels ?? []);
        $this->set('data', $data ?? []);
        $this->set('title', 'Wykresy');
    }
}
