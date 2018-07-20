<?php

namespace Admin\modules\account\controllers;

use Admin\models\form\LoginForm;
use common\models\utils\FuncUtil;
use Yii;
use common\models\orm\User;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use Admin\modules\AdminController;

/**
 * Site controller
 */
class DefaultController extends AdminController
{

//     public $layout = '@layouts/main.php';
    public $layout = false;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $this->enableCsrfValidation = false;
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['signup', 'login', 'requestPasswordReset', 'sendSmsSignupVerifyCode'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
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


    public function goHome()
    {
        return $this->redirect('/account/default/login');
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        $model = new LoginForm();
        if (Yii::$app->request->isPost) {
            $post['LoginForm'] = Yii::$app->request->post();

            if ($model->load($post) && $model->login()) {
                return $this->redirect('/question/question/index');
            } else {
                return $this->render('login', [
                    'model' => $model,
                ]);
//                 var_dump($model->getErrors());exit();
//                 echo "<script>alert('密码错误!');window.location.href='/account/default/login';</script>";
//                 return false;
            }
        }
        return $this->render('login', [
            'model' => $model,
        ]);

    }


    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        session_destroy();
        Yii::$app->user->logout();
        return $this->redirect('/account/default/login');
    }
}
