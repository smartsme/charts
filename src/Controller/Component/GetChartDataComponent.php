<?php

namespace App\Controller\Component;

define('TABLES_COLUMNS', [
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
    'brent_oil' => [
        'value',
    ],
]);

use Cake\Controller\Component;

class GetChartDataComponent extends Component
{
    public $components = ['StringFunctions'];

    /**
     * Function that generates data for the generation_of_power_generation_units table
     *
     * @param string $startDate Starting date
     * @param string $endDate Ending date
     * @param string $code Code
     * @param bool $repeat Repeat data or not
     * @return array
     */
    public function generationOfPowerGenerationUnits($startDate, $endDate, $code, $repeat = false)
    {
        //Converting function name to snake_case
        $table = $this->StringFunctions->camelCaseToSnakeCase(__FUNCTION__);
        //Creating empty array
        $formattedData = $this->createEmptyArray($table, $code);
        //Rounding all 24 columns
        $roundedHours = ['power' => 'ROUND((`h1` + `h2` + `h3` + `h4` + `h5` + `h6` + `h7` + `h8` + `h9` + `h10` + `h11` + `h12` + `h13` + `h14` + `h15` + `h16` + `h17` + `h18` + `h19` + `h20` + `h21` + `h22` + `h23` + `h24`) / 24, 2)'];
        //Two queries for two operating modes, pumping and generating
        $generation = $this->formatDate(\Cake\ORM\TableRegistry::getTableLocator()->get(ucfirst(__FUNCTION__))->find('all')->select(array_merge(['date'], $roundedHours))->where(["`date` BETWEEN '$startDate' AND '$endDate'", 'code' => $code, 'mode' => 'Generacja'])->all()->toList());
        $pumping = $this->formatDate(\Cake\ORM\TableRegistry::getTableLocator()->get(ucfirst(__FUNCTION__))->find('all')->select(array_merge(['date'], $roundedHours))->where(["`date` BETWEEN '$startDate' AND '$endDate'", 'code' => $code, 'mode' => 'Pompowanie'])->all()->toList());

        //Populating correct tables
        foreach ($generation as $record) {
            array_push($formattedData["Generacja - $table - $code"], [
                'x' => $record['date'],
                'y' => $record['power'],
            ]);
        }

        foreach ($pumping as $record) {
            array_push($formattedData["Pompowanie - $table - $code"], [
                'x' => $record['date'],
                'y' => $record['power'],
            ]);
        }

        //Removing empty arrays for queries that yielded no results
        $formattedData = array_filter(array_map('array_filter', $formattedData));

        if (!array_filter($formattedData)) {
            return [];
        }

        if ($repeat) {
            return $this->repeatData($table, $formattedData, $code);
        }

        return $formattedData;
    }

    /**
     * Function that is responsible for date formatting
     *
     * It formats CakePHP FrozenDate object type to plain string for simpler use
     *
     * @param object $data Query data to format
     * @return array
     */
    private function formatDate($data)
    {
        foreach ($data as $d) {
            if (gettype($d['date']) === 'object') {
                $d['date'] = $d['date']->format('Y-m-d');
            }
        }

        return $data;
    }

    /**
     * Creates empty array that are further used to being populated with data and returned
     *
     * @param string $table Table
     * @param string $code Code
     * @return array
     */
    private function createEmptyArray($table, $code = null)
    {
        $array = [];
        foreach (TABLES_COLUMNS[$table] as $column) {
            $array["$column - $table" . ($code ? " - $code" : '')] = [];
        }

        return $array;
    }

    /**
     * Repeats passed data 24 times
     *
     * @param string $table Table
     * @param object $data Data
     * @param string $code Code
     * @return array
     */
    private function repeatData($table, $data, $code = null)
    {
        $tempArr = $this->createEmptyArray($table, $code);
        foreach ($data as $column => $record) {
            if (isset($record[0])) {
                $date = $record[0]['x'];
                for ($i = 0; $i < 24;) {
                    $record[0]['x'] = $date . ' - ' . ++$i;
                    array_push($tempArr[$column], $record[0]);
                }
            }
        }

        return $tempArr;
    }

    /**
     * Generating data for new tables, e.g. BrentOil, CoalApi2 or CarbonEmissions(They're called new tables because they have mixed properties of both of the other types and were introduced lately)
     *
     * @param string $table Table
     * @param string $startDate Starting date
     * @param string $endDate Ending date
     * @param bool $repeat Repeat data
     * @return array
     */
    public function generateNewTableData($table, $startDate, $endDate, $repeat = false)
    {
        $tableSnakeCase = $this->StringFunctions->camelCaseToSnakeCase($table);
        debug($tableSnakeCase);
        $formattedData = $this->createEmptyArray($tableSnakeCase);
        $data = $this->formatDate(\Cake\ORM\TableRegistry::getTableLocator()->get(ucfirst($table))->find('all')->select(array_merge(['date'], TABLES_COLUMNS[$tableSnakeCase]))->where("`date` BETWEEN '$startDate' AND '$endDate'")->all()->toList());

        foreach (TABLES_COLUMNS[$tableSnakeCase] as $column) {
            foreach ($data as $record) {
                array_push($formattedData[$column . " - $tableSnakeCase"], [
                    'x' => $record['date'],
                    'y' => $record[$column],
                ]);
            }
        }

        if (!array_filter($formattedData)) {
            return [];
        }

        if ($repeat) {
            return $this->repeatData($tableSnakeCase, $formattedData);
        }

        return $formattedData;
    }

    /**
     * Generating data for daily tables, e.g. ElectricOtfEnergy, OtfGas, PropertyRightsOfRpm etc. (They're called daily tables because they hold data for the whole day insted of being broken down to seperate hours)
     *
     * @param string $table Table
     * @param string $startDate Starting date
     * @param string $endDate Ending date
     * @param string $code Code
     * @param bool $repeat Repeat data
     * @return array
     */
    public function generateDailyTableData($table, $startDate, $endDate, $code, $repeat = false)
    {
        $tableSnakeCase = $this->StringFunctions->camelCaseToSnakeCase($table);
        $formattedData = $this->createEmptyArray($tableSnakeCase, $code);
        $data = $this->formatDate(\Cake\ORM\TableRegistry::getTableLocator()->get(ucfirst($table))->find('all')->select(array_merge(['date'], TABLES_COLUMNS[$tableSnakeCase]))->where(["`date` BETWEEN '$startDate' AND '$endDate'", ($table == 'RdnGasContract' ? "`code` LIKE '$code%'" : "`code` = '$code'")])->all()->toList());

        foreach (TABLES_COLUMNS[$tableSnakeCase] as $column) {
            foreach ($data as $record) {
                array_push($formattedData[$column . " - $tableSnakeCase - $code"], [
                    'x' => $record['date'],
                    'y' => $record[$column],
                ]);
            }
        }

        if (!array_filter($formattedData)) {
            return [];
        }

        if ($repeat) {
            return $this->repeatData($tableSnakeCase, $formattedData, $code);
        }

        return $formattedData;
    }

    /**
     * Generating data for hourly tables, e.g. GenerationFromWindSources, KseDemands, InterSystemExchangeOfPowerFlows etc. (They're called hourly tables because the hold data that is broken down into seperate hours)
     *
     * @param string $table Table
     * @param string $startDate Starting date
     * @param string $endDate Ending date
     * @param bool $sum Use MySQL SUM function or AVG
     * @param bool $group Group data into hours and averaging(or summing) into one value
     * @return array
     */
    public function generateHourlyTableData($table, $startDate, $endDate, $sum = false, $group = true)
    {
        $tableSnakeCase = $this->StringFunctions->camelCaseToSnakeCase($table);
        $formattedData = $this->createEmptyArray($tableSnakeCase);
        $query = \Cake\ORM\TableRegistry::getTableLocator()->get(ucfirst($table))->find('all');
        $formatField = fn($field) => [$field => !$group ? "ROUND(`$field`, 2)" : 'ROUND(' . (filter_var($sum, FILTER_VALIDATE_BOOLEAN) ? 'SUM' : 'AVG') . "(`$field`), 2)"];
        $query = $query->select([
            'date',
            ...array_merge(...array_map($formatField, TABLES_COLUMNS[$tableSnakeCase])),
        ] + (!$group ? ['hour' => 'hour'] : []))->where("`date` BETWEEN '$startDate' AND '$endDate'")->order(['hour ASC', 'date ASC']);

        if ($group && $startDate != $endDate) {
            $query = $query->group('date');
        }

        $data = $this->formatDate($query->all()->toList());

        foreach (TABLES_COLUMNS[$tableSnakeCase] as $column) {
            foreach ($data as $record) {
                array_push($formattedData[$column . " - $tableSnakeCase"], [
                    'x' => $record['date'] . (isset($record['hour']) ? ' - ' . $record['hour'] : ''),
                    'y' => $record[$column],
                ]);
            }
        }

        if (!array_filter($formattedData)) {
            return [];
        }

        return $formattedData;
    }
}
