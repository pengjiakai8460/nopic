<?php
namespace Admin\modules;

use common\base\MyDynamicModel;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use Admin\modules\srbac\controllers\SrbacController;
use common\models\service\MongoService;
use common\models\service\AdminOptLogService;

/**
 * Site controller
 */
class AdminController extends Controller
{

    const PAGE_PER_NUMBER = 10;

    public $layout = false;

    public $user;

    public function beforeAction($action)
    {
        //print_r($_SESSION);EXIT;
        $ip = $_SERVER["REMOTE_ADDR"];
        $module = $this->module->id;
        $controller = Yii::$app->controller->id;
        $action = Yii::$app->controller->action->id;

        if(in_array(strtolower($action), ['adminlogin']))
        	return true;

//         if ( Yii::$app->user->isGuest && $this->action->id != 'login')
//             return $this->redirect('/account/default/login');
        if ( !isset($_SESSION['uid']) && $action != 'login' && $action !='picyz' && $action !='pictwice') {
            return $this->redirect('/account/default/login');
        }
        //$this->user = Yii::$app->getUser();
        //权限判断
        $access = "admin@".$module . "_" . $controller . "_" . $action; //权限name
        //echo Yii::$app->user->can($access);exit;
        if (! Yii::$app->user->can($access) && $action != 'login' && $action != 'unlocked-user-list') {
            if (Yii::$app->request->isAjax) {
                $ret['status'] = 0;
                $ret['info'] = "对不起，您现在还没获此操作的权限，请联系管理员添加权限。";
                $ret['data'] = '';
                //echo json_encode($ret);
                //exit;
            } else {
                //echo "<script>alert('对不起，您现在还没获此操作的权限，请联系管理员添加权限。');window.location.href='/';</script>";
            }
            //return false;
            //$this->redirect('/');
            //throw new \yii\web\UnauthorizedHttpException('对不起，您现在还没获此操作的权限，请联系管理员添加权限。');
        }
        return true;


        //添加操作日志
        if (isset($this->user) && $this->user->id) {
            $req_tye = '';
            $req_data = [];
            if (Yii::$app->request->isAjax) {
                $req_tye = 'ajax_';
            }
            if (Yii::$app->request->isGet) {
                $req_tye .= 'get';
                $req_data = Yii::$app->request->get();

            } elseif (Yii::$app->request->isPost) {
                $req_tye .= 'post';
                $req_data = Yii::$app->request->post();

            }

            $logs = [];
            $logs['uid'] = $this->user->id;
            $logs['username'] = $this->user->username ?? '';
            $logs['nickname'] = $this->user->nickname ?? '';
            $logs['realname'] = $this->user->realname;
            $logs['module'] = $module;
            $logs['controller'] = $controller;
            $logs['action'] = $action;
            $logs['ip'] = $ip;
            $logs['req_type'] = $req_tye;
            $logs['req_data'] = $req_data;

            //$return = AdminOptLogService::model()->addAdminLog($logs);

        }
        //echo $return;exit;
        return true;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [
                        	'adminlogin',
                            'login',
                            'error',
                            'user',
                            'auth',
                            'index',
                            'test'
                        ],
                        'allow' => true
                    ],
                    [
                        'actions' => [
                            'logout',
                            'index'
                        ],
                        'allow' => true,
                        'roles' => [
                            '@'
                        ]
                    ]
                ]
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => [
                        'post'
                    ]
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
//         $this->user=Yii::$app->user;
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction'
            ]
        ];
    }

    /*
     * 返回转码后的js框
     */
    public function reMessg($str, $typ = 1)
    {
        if ($typ == 1) {
            echo "<script > alert('" . $str . "');history.go(-1);</script>";
            exit();
        } elseif ($typ == 2) {
            echo "<script > alert('" . $str . "');history.go(-2);</script>";
            exit();
        } else {
            echo "<script > alert('" . $str . "');</script>";
        }
    }

    public function ajaxReturn($data = null, $info = '', $success = true) {
        header('Content-type: application/json');
        $all = [
            'status' => $success,
            'info' 	=> $info,
            'data'	=> $data,
            'csrf'	=> Yii::$app->request->getCsrfToken()
        ];
        echo  json_encode($all);
        exit;
    }

    /**
     * @title 取得权限全名
     */
    private function getFullAction($action)
    {
        $namespace = str_replace('\controllers', '', \Yii::$app->controllerNamespace);
        $mod = \Yii::$app->controller->module !== null ? '@'.\Yii::$app->controller->module->id : "";
        $controller = \Yii::$app->controller->id;
        $ac = $action->id;
        return $namespace.$mod.'-'.$controller.'-'.$ac;
    }

    public function validate($data, $rules)
    {
        $m = MyDynamicModel::validateData($data, $rules);
        if ($m->hasErrors()) {
            return $m->getFirstError(array_keys($m->getErrors())[0]);
        }

        return true;
    }

    public function dosave()
    {

    }
}
