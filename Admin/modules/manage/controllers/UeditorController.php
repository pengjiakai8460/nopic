<?php
namespace Admin\modules\manage\controllers;

use Admin\modules\AdminController;
use common\models\orm\Rules;
use common\models\orm\Managers;
use yii\db;
use yii;
use yii\data\Pagination;
use common\models\utils\Pager;
use common\models\utils\UeditorUtil;

class UeditorController extends AdminController
{
    public $layout=false;   
    public $enableCsrfValidation = false;

    public function actionTest() {
        return $this->render('test.twig');
    }
    
    /*
     * 百度编辑器上传文件接口
     */
    public function actionIndex() {
        //登录验证
        
        return UeditorUtil::actions();
    }

}