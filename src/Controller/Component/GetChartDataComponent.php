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
    public function brentOil($startDate, $endDate, $repeat = false)
    {
        $table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', __FUNCTION__));
        $formattedData = $this->createEmptyArray($table);
        $data = $this->formatDate(\Cake\ORM\TableRegistry::getTableLocator()->get(ucfirst(__FUNCTION__))->find('all')->select(array_merge(['date'], TABLES_COLUMNS[$table]))->where("`date` BETWEEN '$startDate' AND '$endDate'")->all()->toList());

        foreach (TABLES_COLUMNS[$table] as $column) {
            foreach ($data as $record) {
                array_push($formattedData[$column . " - $table"], [
                    'x' => $record['date'],
                    'y' => $record[$column],
                ]);
            }
        }

        if (!array_filter($formattedData)) {
            return [];
        }

        if ($repeat) {
            return $this->repeatData($table, $formattedData);
        }

        return $formattedData;
    }

    public function carbonEmissions($startDate, $endDate, $repeat = false)
    {
        $table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', __FUNCTION__));
        $formattedData = $this->createEmptyArray($table);
        $data = $this->formatDate(\Cake\ORM\TableRegistry::getTableLocator()->get(ucfirst(__FUNCTION__))->find('all')->select(array_merge(['date'], TABLES_COLUMNS[$table]))->where("`date` BETWEEN '$startDate' AND '$endDate'")->all()->toList());

        foreach (TABLES_COLUMNS[$table] as $column) {
            foreach ($data as $record) {
                array_push($formattedData[$column . " - $table"], [
                    'x' => $record['date'],
                    'y' => $record[$column],
                ]);
            }
        }

        if (!array_filter($formattedData)) {
            return [];
        }

        if ($repeat) {
            return $this->repeatData($table, $formattedData);
        }

        return $formattedData;
    }

    public function coalApi2($startDate, $endDate, $repeat = false)
    {
        $table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', __FUNCTION__));
        $formattedData = $this->createEmptyArray($table);
        $data = $this->formatDate(\Cake\ORM\TableRegistry::getTableLocator()->get(ucfirst(__FUNCTION__))->find('all')->select(array_merge(['date'], TABLES_COLUMNS[$table]))->where("`date` BETWEEN '$startDate' AND '$endDate'")->all()->toList());

        foreach (TABLES_COLUMNS[$table] as $column) {
            foreach ($data as $record) {
                array_push($formattedData[$column . " - $table"], [
                    'x' => $record['date'],
                    'y' => $record[$column],
                ]);
            }
        }

        if (!array_filter($formattedData)) {
            return [];
        }

        if ($repeat) {
            return $this->repeatData($table, $formattedData);
        }

        return $formattedData;
    }

    public function electricOtfEnergy($startDate, $endDate, $code, $repeat = false)
    {
        $table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', __FUNCTION__));
        $formattedData = $this->createEmptyArray($table, $code);
        $data = $this->formatDate(\Cake\ORM\TableRegistry::getTableLocator()->get(ucfirst(__FUNCTION__))->find('all')->select(array_merge(['date'], TABLES_COLUMNS[$table]))->where(["`date` BETWEEN '$startDate' AND '$endDate'", 'code' => $code])->all()->toList());
        foreach (TABLES_COLUMNS[$table] as $column) {
            foreach ($data as $record) {
                array_push($formattedData[$column . " - $table - $code"], [
                    'x' => $record['date'],
                    'y' => $record[$column],
                ]);
            }
        }

        if (!array_filter($formattedData)) {
            return [];
        }

        if ($repeat) {
            return $this->repeatData($table, $formattedData, $code);
        }

        return $formattedData;
    }

    public function electricRdnEnergy($startDate, $endDate, $code, $repeat = false)
    {
        $table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', __FUNCTION__));
        $formattedData = $this->createEmptyArray($table, $code);
        $data = $this->formatDate(\Cake\ORM\TableRegistry::getTableLocator()->get(ucfirst(__FUNCTION__))->find('all')->select(array_merge(['date'], TABLES_COLUMNS[$table]))->where(["`date` BETWEEN '$startDate' AND '$endDate'", 'code' => $code])->all()->toList());

        foreach (TABLES_COLUMNS[$table] as $column) {
            foreach ($data as $record) {
                array_push($formattedData[$column . " - $table - $code"], [
                    'x' => $record['date'],
                    'y' => $record[$column],
                ]);
            }
        }

        if (!array_filter($formattedData)) {
            return [];
        }

        if ($repeat) {
            return $this->repeatData($table, $formattedData, $code);
        }

        return $formattedData;
    }

    public function generationFromWindSources($startDate, $endDate, $sum = false, $group = true)
    {
        $table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', __FUNCTION__));
        $formattedData = $this->createEmptyArray($table);
        $query = \Cake\ORM\TableRegistry::getTableLocator()->get(ucfirst(__FUNCTION__))->find('all');
        $formatField = fn($field) => [$field => !$group ? "ROUND(`$field`, 2)" : 'ROUND(' . (filter_var($sum, FILTER_VALIDATE_BOOLEAN) ? 'SUM' : 'AVG') . "(`$field`), 2)"];
        $query = $query->select([
            'date',
            ...array_merge(...array_map($formatField, TABLES_COLUMNS[$table])),
        ] + (!$group ? ['hour' => 'hour'] : []))->where("`date` BETWEEN '$startDate' AND '$endDate'")->order(['hour ASC', 'date ASC']);

        if ($group && $startDate != $endDate) {
            $query = $query->group('date');
        }

        $data = $this->formatDate($query->all()->toList());

        foreach (TABLES_COLUMNS[$table] as $column) {
            foreach ($data as $record) {
                array_push($formattedData[$column . " - $table"], [
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

    public function generationOfPowerGenerationUnits($startDate, $endDate, $code, $repeat = false)
    {
        $table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', __FUNCTION__));
        $formattedData = $this->createEmptyArray($table, $code);
        $roundedHours = ['power' => 'ROUND((`h1` + `h2` + `h3` + `h4` + `h5` + `h6` + `h7` + `h8` + `h9` + `h10` + `h11` + `h12` + `h13` + `h14` + `h15` + `h16` + `h17` + `h18` + `h19` + `h20` + `h21` + `h22` + `h23` + `h24`) / 24, 2)'];
        $generation = $this->formatDate(\Cake\ORM\TableRegistry::getTableLocator()->get(ucfirst(__FUNCTION__))->find('all')->select(array_merge(['date'], $roundedHours))->where(["`date` BETWEEN '$startDate' AND '$endDate'", 'code' => $code, 'mode' => 'Generacja'])->all()->toList());
        $pumping = $this->formatDate(\Cake\ORM\TableRegistry::getTableLocator()->get(ucfirst(__FUNCTION__))->find('all')->select(array_merge(['date'], $roundedHours))->where(["`date` BETWEEN '$startDate' AND '$endDate'", 'code' => $code, 'mode' => 'Pompowanie'])->all()->toList());

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

        $formattedData = array_filter(array_map('array_filter', $formattedData));

        if (!array_filter($formattedData)) {
            return [];
        }

        if ($repeat) {
            return $this->repeatData($table, $formattedData, $code);
        }

        return $formattedData;
    }

    public function interSystemExchangeOfPowerFlows($startDate, $endDate, $sum = false, $group = true)
    {
        $table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', __FUNCTION__));
        $formattedData = $this->createEmptyArray($table);
        $query = \Cake\ORM\TableRegistry::getTableLocator()->get(ucfirst(__FUNCTION__))->find('all');
        $formatField = fn($field) => [$field => !$group ? "ROUND(`$field`, 2)" : 'ROUND(' . (filter_var($sum, FILTER_VALIDATE_BOOLEAN) ? 'SUM' : 'AVG') . "(`$field`), 2)"];
        $query = $query->select([
            'date',
            ...array_merge(...array_map($formatField, TABLES_COLUMNS[$table])),
        ] + (!$group ? ['hour' => 'hour'] : []))->where("`date` BETWEEN '$startDate' AND '$endDate'")->order(['hour ASC', 'date ASC']);

        if ($group && $startDate != $endDate) {
            $query = $query->group('date');
        }

        $data = $this->formatDate($query->all()->toList());

        foreach (TABLES_COLUMNS[$table] as $column) {
            foreach ($data as $record) {
                array_push($formattedData[$column . " - $table"], [
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

    public function kseDemands($startDate, $endDate, $sum = false, $group = true)
    {
        $table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', __FUNCTION__));
        $formattedData = $this->createEmptyArray($table);
        $query = \Cake\ORM\TableRegistry::getTableLocator()->get(ucfirst(__FUNCTION__))->find('all');
        $formatField = fn($field) => [$field => !$group ? "ROUND(`$field`, 2)" : 'ROUND(' . (filter_var($sum, FILTER_VALIDATE_BOOLEAN) ? 'SUM' : 'AVG') . "(`$field`), 2)"];
        $query = $query->select([
            'date',
            ...array_merge(...array_map($formatField, TABLES_COLUMNS[$table])),
        ] + (!$group ? ['hour' => 'hour'] : []))->where("`date` BETWEEN '$startDate' AND '$endDate'")->order(['hour ASC', 'date ASC']);

        if ($group && $startDate != $endDate) {
            $query = $query->group('date');
        }

        $data = $this->formatDate($query->all()->toList());

        foreach (TABLES_COLUMNS[$table] as $column) {
            foreach ($data as $record) {
                array_push($formattedData[$column . " - $table"], [
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

    public function otfGas($startDate, $endDate, $code, $repeat = false)
    {
        $table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', __FUNCTION__));
        $formattedData = $this->createEmptyArray($table, $code);
        $data = $this->formatDate(\Cake\ORM\TableRegistry::getTableLocator()->get(ucfirst(__FUNCTION__))->find('all')->select(array_merge(['date'], TABLES_COLUMNS[$table]))->where(["`date` BETWEEN '$startDate' AND '$endDate'", 'code' => $code])->all()->toList());

        foreach (TABLES_COLUMNS[$table] as $column) {
            foreach ($data as $record) {
                array_push($formattedData[$column . " - $table - $code"], [
                    'x' => $record['date'],
                    'y' => $record[$column],
                ]);
            }
        }

        if (!array_filter($formattedData)) {
            return [];
        }

        if ($repeat) {
            return $this->repeatData($table, $formattedData, $code);
        }

        return $formattedData;
    }

    public function pricesAndQuantityOfEnergyInTheBalancingMarket($startDate, $endDate, $sum = false, $group = true)
    {
        $table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', __FUNCTION__));
        $formattedData = $this->createEmptyArray($table);
        $query = \Cake\ORM\TableRegistry::getTableLocator()->get(ucfirst(__FUNCTION__))->find('all');
        $formatField = fn($field) => [$field => !$group ? "ROUND(`$field`, 2)" : 'ROUND(' . (filter_var($sum, FILTER_VALIDATE_BOOLEAN) ? 'SUM' : 'AVG') . "(`$field`), 2)"];
        $query = $query->select([
            'date',
            ...array_merge(...array_map($formatField, TABLES_COLUMNS[$table])),
        ] + (!$group ? ['hour' => 'hour'] : []))->where("`date` BETWEEN '$startDate' AND '$endDate'")->order(['hour ASC', 'date ASC']);

        if ($group && $startDate != $endDate) {
            $query = $query->group('date');
        }

        $data = $this->formatDate($query->all()->toList());

        foreach (TABLES_COLUMNS[$table] as $column) {
            foreach ($data as $record) {
                array_push($formattedData[$column . " - $table"], [
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

    public function rdbGas($startDate, $endDate, $code, $repeat = false)
    {
        $table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', __FUNCTION__));
        $formattedData = $this->createEmptyArray($table, $code);
        $data = $this->formatDate(\Cake\ORM\TableRegistry::getTableLocator()->get(ucfirst(__FUNCTION__))->find('all')->select(array_merge(['date'], TABLES_COLUMNS[$table]))->where(["`date` BETWEEN '$startDate' AND '$endDate'", 'code' => $code])->all()->toList());

        foreach (TABLES_COLUMNS[$table] as $column) {
            foreach ($data as $record) {
                array_push($formattedData[$column . " - $table - $code"], [
                    'x' => $record['date'],
                    'y' => $record[$column],
                ]);
            }
        }

        if (!array_filter($formattedData)) {
            return [];
        }

        if ($repeat) {
            return $this->repeatData($table, $formattedData, $code);
        }

        return $formattedData;
    }

    public function rdnGasContract($startDate, $endDate, $code, $repeat = false)
    {
        $table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', __FUNCTION__));
        $formattedData = $this->createEmptyArray($table, $code);
        $data = $this->formatDate(\Cake\ORM\TableRegistry::getTableLocator()->get(ucfirst(__FUNCTION__))->find('all')->select(array_merge(['date'], TABLES_COLUMNS[$table]))->where(["`date` BETWEEN '$startDate' AND '$endDate'", "`code` LIKE '$code%'"])->all()->toList());

        foreach (TABLES_COLUMNS[$table] as $column) {
            foreach ($data as $record) {
                array_push($formattedData[$column . " - $table - $code"], [
                    'x' => $record['date'],
                    'y' => $record[$column],
                ]);
            }
        }

        if (!array_filter($formattedData)) {
            return [];
        }

        if ($repeat) {
            return $this->repeatData($table, $formattedData, $code);
        }

        return $formattedData;
    }

    public function rdnGasIndex($startDate, $endDate, $code, $repeat = false)
    {
        $table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', __FUNCTION__));
        $formattedData = $this->createEmptyArray($table, $code);
        $data = $this->formatDate(\Cake\ORM\TableRegistry::getTableLocator()->get(ucfirst(__FUNCTION__))->find('all')->select(array_merge(['date'], TABLES_COLUMNS[$table]))->where(["`date` BETWEEN '$startDate' AND '$endDate'", 'code' => $code])->all()->toList());

        foreach (TABLES_COLUMNS[$table] as $column) {
            foreach ($data as $record) {
                array_push($formattedData[$column . " - $table - $code"], [
                    'x' => $record['date'],
                    'y' => $record[$column],
                ]);
            }
        }

        if (!array_filter($formattedData)) {
            return [];
        }

        if ($repeat) {
            return $this->repeatData($table, $formattedData, $code);
        }

        return $formattedData;
    }

    public function propertyRightsOfRpmOffSession($startDate, $endDate, $code, $repeat = false)
    {
        $table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', __FUNCTION__));
        $formattedData = $this->createEmptyArray($table, $code);
        $data = $this->formatDate(\Cake\ORM\TableRegistry::getTableLocator()->get(ucfirst(__FUNCTION__))->find('all')->select(array_merge(['date'], TABLES_COLUMNS[$table]))->where(["`date` BETWEEN '$startDate' AND '$endDate'", 'code' => $code])->all()->toList());

        foreach (TABLES_COLUMNS[$table] as $column) {
            foreach ($data as $record) {
                array_push($formattedData[$column . " - $table - $code"], [
                    'x' => $record['date'],
                    'y' => $record[$column],
                ]);
            }
        }

        if (!array_filter($formattedData)) {
            return [];
        }

        if ($repeat) {
            return $this->repeatData($table, $formattedData, $code);
        }

        return $formattedData;
    }

    public function propertyRightsOfRpmSession($startDate, $endDate, $code, $repeat = false)
    {
        $table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', __FUNCTION__));
        $formattedData = $this->createEmptyArray($table, $code);
        $data = $this->formatDate(\Cake\ORM\TableRegistry::getTableLocator()->get(ucfirst(__FUNCTION__))->find('all')->select(array_merge(['date'], TABLES_COLUMNS[$table]))->where(["`date` BETWEEN '$startDate' AND '$endDate'", 'code' => $code])->all()->toList());

        foreach (TABLES_COLUMNS[$table] as $column) {
            foreach ($data as $record) {
                array_push($formattedData[$column . " - $table - $code"], [
                    'x' => $record['date'],
                    'y' => $record[$column],
                ]);
            }
        }

        if (!array_filter($formattedData)) {
            return [];
        }

        if ($repeat) {
            return $this->repeatData($table, $formattedData, $code);
        }

        return $formattedData;
    }

    private function formatDate($data)
    {
        foreach ($data as $d) {
            if (gettype($d['date']) === 'object') {
                $d['date'] = $d['date']->format('Y-m-d');
            }
        }

        return $data;
    }

    private function createEmptyArray($table, $code = null)
    {
        $array = [];
        foreach (TABLES_COLUMNS[$table] as $column) {
            $array["$column - $table" . ($code ? " - $code" : '')] = [];
        }

        return $array;
    }

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
}
