<?php
namespace Admin\modules\manage\controllers;

use Admin\modules\AdminController;
use common\models\orm\Rules;
use common\models\orm\Managers;
use yii\db;
use Yii;
use yii\data\Pagination;
use common\models\utils\FunctionsUtil;
use common\models\orm\Admin;
use common\models\service\AdminOptLogService;
use common\models\service\UsersManageService;


class AdminlogController extends AdminController
{
public $layout=false;
    public $enableCsrfValidation = false;

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }
    /*
     * 说明：权限、角色管理界面入口
     */
    public function actionLoglist() {
        //var_dump('123');die();
        $order = '';
        $get = FunctionsUtil::parseData(Yii::$app->request->get());
        $type=Yii::$app->request->get('selecttype');

        $pageno = ! empty($get['page']) ? $get['page'] - 1 : 0 ;
        $or = null;

        if (!empty($get['search'])){
            $or = $get['search'];
        }

        if (!empty($get['type'])){
            $order = $get['type'];
        }

        $lists = AdminOptLogService::model()->getAdminLog($pageno, false, self::PAGE_PER_NUMBER ,$get,$order,$type);
        //print_r(json_encode($lists));exit;
        $count = AdminOptLogService::model()->getCount(false,$get,$type);

        $pi = new Pagination(['totalCount' => $count,'pageSize' => self::PAGE_PER_NUMBER]);

        $renderData = [
            'islocked' => false,        // 告诉模板是正常用户
            'lists' => $lists,
            'count' => $count,
            'pagenum' => $pi->pageCount,
            'pagination' => $pi,
            'yue'=>Yii::$app->request->get('type'),
            'img'=>Yii::$app->request->get('img'),
            'selecttype'=>$type,
        ];
        //print_r($renderData);exit();
        return $this->render('loglist.twig', $renderData);
    }
    
}