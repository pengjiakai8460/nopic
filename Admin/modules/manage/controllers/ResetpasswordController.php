<?php
namespace Admin\modules\manage\controllers;

use Admin\modules\AdminController;
use Yii;
use common\models\orm\Admin;
use App\models\service\ApiService;

class ResetpasswordController extends AdminController
{

    public function actionRestpassword(){
        if(Yii::$app->request->isPost){
            $uid = Yii::$app->user->identity->id;
            $password =Yii::$app->request->post('old');
            $newpassword = Yii::$app->request->post('new');
            $user = Admin::findOne(['id'=>Yii::$app->user->identity->id]);
            if(Yii::$app->getSecurity()->validatePassword(md5($password), $user->password_hash)){
                $new = Yii::$app->getSecurity()->generatePasswordHash(md5($newpassword));
                $user->password_hash = $new;
                if($user->save()){
                    return ApiService::reMessg("密码修改成功！",'/'); //后退二个页面
//                     return $this->redirect('/account/default/login');
                }
            }else {
                echo '旧密码错误';
            }
        }
        return $this->render('resetpassword.twig');
    }
}