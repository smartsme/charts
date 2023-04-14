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
class ChartsController extends AppController
{
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

    /**
     * Get codes page
     *
     * @return json
     */
    public function getCodes()
    {
        $table = $this->request->getQuery('table');
        $codes = $this->getTableLocator()->get(lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $table)))))->find('all')->select('code')->group('code')->order('code')->all()->toList();
        $formattedCodes = [];
        for ($i = 0; $i < count($codes); $i++) {
            if ($table == 'rdn_gas_contract') {
                preg_match('/[A-Z]{3}_.*(?=_)/', $codes[$i]['code'], $code);
                array_push($formattedCodes, $code[0]);
            } else {
                array_push($formattedCodes, $codes[$i]['code']);
            }
        }
        $response = $this->response;
        $response = $response->withType('application/json')->withStringBody(json_encode(array_values(array_unique($formattedCodes))));

        return $response;
    }

    public function getRates()
    {
        $this->autoRender = false;
        $dates = [];
        $startDate = $this->request->getQuery('start_date');
        $endDate = $this->request->getQuery('end_date');
        $current = strtotime($startDate);
        $rates = ['PLN' => []];

        while ($current <= strtotime($endDate)) {
            $dates[] = date('Y-m-d', $current);
            $current = strtotime('+1 day', $current);
        }

        $currencies = $this->getTableLocator()->get('Currencies')->find('all')->select('code')->group('code')->all()->toList();
        $values = $this->getTableLocator()->get('Currencies')->find('all')->select(['date', 'code', 'value'])->where("date BETWEEN '$startDate' AND '$endDate'")->all()->toList();
        $avg = $this->getTableLocator()->get('Currencies')->find('all');
        $avg = $avg->select(['code', 'average' => $avg->func()->avg('value')])->group('code')->all()->toList();
        $averages = ['PLN' => []];
        foreach ($avg as $cur) {
            $averages[$cur->code] = $cur->average;
        }
        $arr = ['PLN' => []];
        foreach ($currencies as $currency) {
            $arr[$currency->code] = [];
            $rates[$currency->code] = [];
        }

        foreach ($values as $value) {
            $arr[$value->code][$value->date] = floatval($value->value);
        }

        foreach ($arr as $currency => $values) {
            for ($i = 0; $i < count($dates); $i++) {
                if (!isset($values[$dates[$i]])) {
                    $values[$dates[$i]] = $averages[$currency];
                }

                if ($currency == 'PLN') {
                    $values[$dates[$i]] = 1;
                }
            }
            $rates[$currency] = $values;
        }

        $response = $this->response;
        $response = $response->withType('application/json')->withStringBody(json_encode($rates));

        return $response;
    }
}
