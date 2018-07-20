<?php
/**
 * Created by PhpStorm.

 * Date: 2017/10/11
 * Time: 16:43
 */

namespace Api\modules\site\controllers;


use common\base\BaseController;

class ErrorController extends BaseController
{
    /**
     * @return array
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'common\base\exception\LogicErrorAction',
            ],
        ];
    }
}