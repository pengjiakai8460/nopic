<?php
namespace Admin\modules\manage\controllers;

use Admin\modules\AdminController;
use common\models\orm\Rules;
use common\models\orm\Managers;
use yii\db;
use Yii;
use yii\data\Pagination;
use common\models\utils\FuncUtil;
use common\models\orm\Admin;
use common\models\orm\AuthAssignment;
use App\models\service\ApiService;
use yii\web\Controller;


class ManagerController extends AdminController
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
    public function actionIndex() {
        return $this->render('index.twig', [
            'title' => '管理员管理模块1：',
        ]);
    }

    /*
     * 说明：添加管理员
     */
    public function actionManageradd() {

        //实例化Tools工具类
//         $tools = new ToolsModel();

//         if (count($_POST) == 0) {

//             //查询角色列表
//             $data = Rules::find()->all();
//             $ruleList = $tools->simplifyData($data);

//             return $this->render('add.twig', [
//                 'title' => '添加管理员：',
//                 'ruleList' => $ruleList,
//             ]);
//         }
//         else {

//             //验证保存数据
//             if ($tools->validPost(Array('username','password','myname','idcard','tel','rule_id'))) {

//                 $managers = new Managers();
//                 $managers->attributes = $_POST;
//                 $managers->save(false);

//                 return $this->render('index.twig', [
//                     'title' => '管理员添加成功！',
//                 ]);
//             }
//             else {

//                 return $this->render('error.twig', [
//                     'title' => '操作失败，POST提交参数审核不通过！',
//                 ]);
//             }
//         }

         $auth = Yii::$app->authManager;
         $roles = $auth->getRoles();
         return $this->render('add.twig', ['roles' => $roles]);

    }
    public function actionAdminadd() {

        return $this->render('add.twig');
    }

    public function actionAddadm(){
        if(Yii::$app->request->isPost){
            $post = FunctionsUtil::parseData(Yii::$app->request->post());
            $adm = Admin::findOne(['username' => $post['username']]);
            if ($adm) {
                echo "<script>alert('该管理员用户名已存在,请重新添加!');window.location.href='/manage/manager/manageradd'</script>";
                //$this->redirect('/manage/manager/manageradd');
                return true;

            }

            $m = new Admin();
            $m->username = $post['username'];
            $m->phone = $post['phone'];
            $m->nickname = $post['nickname'];
            $m->realname = $post['realname'];
            $m->idcard = $post['idcard'];
            if (isset($post['type'])){
                if ($post['type']=="寄售"){
                    $t=0;
                }elseif ($post['type']=="担保"){
                    $t=1;
                }elseif ($post['type']=="账号"){
                    $t=2;
                }elseif ($post['type']=="充值"){
                    $t=3;
                }else {
                    $t=null;
                }
                $m->type = $t;
            }
            $m->status = 1;
            $m->password_hash = Yii::$app->getSecurity()->generatePasswordHash(md5($post['password']));
            $m->password = md5($post['password']);
            $m->created_at = time();
            $m->updated_at = time();
            if($m->save()){
                //$rid = Yii::$app->db->getLastInsertID();
                $rid = Admin::find()->select('id')->where(['username' => $post['username']])->asArray()->one();
                $authAssignment = new AuthAssignment();

                if ($rid['id'] && isset($post['role']) && $post['role']) {
                    $authAssignment->item_name = $post['role'];
                    $authAssignment->user_id = $rid['id'];
                    $authAssignment->created_at = time();
                    $authAssignment->save();
                }
                echo "<script>alert('添加管理员成功!');</script>";
                $this->redirect('/manage/manager/managerlist');
                //return FunctionsUtil::ajaxReturn(1, '添加用户成功'); //后退二个页面
                //exit;
            }
            else{
                echo "<script>alert('添加管理员失败,请重新添加!');</script>";
                $this->redirect('/manage/manager/manageradd');
            }
        }
    }

    /*
     * 说明：管理员信息编辑
     */
    public function actionManageredit() {
        //实例化Tools工具类
//         $tools = new ToolsModel();

        $id = isset($_POST['id'])?$_POST['id']:'';

        //如果post_id为空，则读取get_id，打开编辑界面
        if (empty($id)) {

            $id = $_GET['id'];
            //判断post_id是否为空，如果空，返回错误，否则打开编辑界面
            if (empty($id)) {

                //如果post_id仍为空，则判定没有传参，返回错误信息
                return $this->render('error.twig', ['title' => '错误，数据参数没有传入']);
            }
            else {

                //get_id有值，则根据id查询数据并打开编辑界面
                $data = Admin::find()->where(['id'=>$id])->asArray()->one();


                if ($data['type']==0){
                    $data['type']="寄售";
                }elseif ($data['type']==1){
                    $data['type']='担保';
                }elseif ($data['type']==2){
                    $data['type']="账号";
                }elseif ($data['type']==3){
                    $data['type']="充值";
                }else {
                    $data['type']=null;
                }
                $auth = Yii::$app->authManager;
                $roles = $auth->getRoles();
                $au = AuthAssignment::find()->where(['user_id'=>$id])->asArray()->one();
                $item = '';
                //print_r($au);exit;
                if (!empty($au)) {
                    $item = $au['item_name'];
                }
//                 $data = Managers::findOne($id);
                //查询所有角色列表
//                 $rules = Rules::find()->all();

                return $this->render('editbak.twig', [
                    'title' => '编辑权限信息：',
                    'data' => $data,
                    'roles' => $roles,
                    'item' => $item
//                     'rules' => $rules,
                ]);
            }
        }
        else {
            $post = FuncUtil::parseData(Yii::$app->request->post());
            
//             var_dump($post);die();
            $id=intval($post['id']);
            $admin=Admin::find()->where(['id'=>$id])->one();
         
            if ($post['type']=="寄售"){
                $admin->type=0;
            }elseif ($post['type']=="担保"){
                $admin->type=1;
            }elseif ($post['type']=="账号"){
                $admin->type=2;
            }elseif ($post['type']=="充值"){
                $admin->type=3;
            }else {
                $admin->type=null;
            }
            if (array_key_exists('isChangePassword',$post)){
                $admin->password_hash = Yii::$app->getSecurity()->generatePasswordHash(md5($post['password']));
                $admin->password= md5($post['password']);
            } 
            $admin->updated_at = time();
            $admin->nickname=$post['nickname'];
            $admin->realname = $post['realname'];
            $admin->idcard = $post['idcard'];
            $admin->phone = $post['phone'];
            $res = $admin->save();;

            //修改角色数据
            $role = isset($post['role']) ? $post['role'] : '';
            $au = AuthAssignment::find()->where(['user_id'=>$id])->asArray()->one();
            if ($role && !$au) {
                $role = Yii::$app->authManager->getRole($role);
                Yii::$app->authManager->assign($role, $id);

            } elseif ($role && $au) {
                $auth = AuthAssignment::findOne(['user_id'=>$id]);
                $auth->item_name = $role;
                $auth->save();

            } elseif (!$role && $au) {
                AuthAssignment::findOne(['user_id'=>$id])->delete();
            }

            //验证保存数据
            if ($res) {
                //return ApiService::reMessg("修改成功！",'','/manage/manager/managerlist');
                echo "<script>alert('修改成功!');</script>";
                $this->redirect('/manage/manager/managerlist');
            }
            else {

                return $this->render('error.twig', [
                    'title' => '修改失败',
                ]);
            }
        }
    }

    /*
     * 说明：管理员列表
     */
    public function actionManagerlist() {
        $search = Yii::$app->request->post('search');
        $sql='SELECT * FROM admin';
        if(!empty($search)){
            $sql .= ' where username like "%'.$search.'%"';
        }
        $data = Yii::$app->db->createCommand($sql)
        ->queryAll();
        $count=count($data);
        $pages = new Pagination(['totalCount' =>$count, 'pageSize' => 10]);
        $list = Yii::$app->db->createCommand($sql." limit ".$pages->limit." offset ".$pages->offset."")->queryAll();
        $mess['pages']=$pages;
        $mess['list']=$list;
        $mess['count']=$count;
        $mess['pagenum']=ceil($count/10);
        return $this->render('list.twig',$mess);
    }
//         include_once 'com_list_variable.php';

        //使用自定义分页类执行查询
        //---制作本面的查询条件 begin
//         if ( isset($_REQUEST['key']) && !empty($_REQUEST['key']) ) {
//             $key = $_REQUEST['key'];
//         }

//         $wheres = 'a.rule_id = b.id';
//         if (!empty($key)) $wheres .= ' && (a.username like "%'.$key.'%" || a.myname like "%'.$key.'%"';
//         //---制作本面的查询条件 end

//         //设置查询字段
//         $fields .= ",b.rule_name";

//         //本页查询的表
//         $tables = 'managers a, rules b';

//         //获取分页后的记录集
//         $pageobj = new Pager();
//         $pager = $pageobj->pager($pageSize,$pageid,$fields,'where '.$wheres,$tables,'order by '.$orders,$contField);
//         //print("select ".$fields." from ".$tables." ".'where '.$wheres);
//         $list = $pageobj->getCurResult();
//         $listCount = count($list);

//         //$connection = \Yii::$app->db;
//         //$command = $connection->createCommand("select a.*, b.rule_name from managers a, rules b where a.rule_id = b.id");
//         //$result = $command->queryAll();

//         //打开列表界面
//         return $this->render('list.twig', [
//             'title'=>'管理员列表：',
//             'list' => $list,
//             'listCount' => $listCount,
//             'pageStr' => '共有：'.$pageobj->numItems.'条记录&nbsp;|&nbsp;'.$pageobj->getParaPageButton($pagekeys,$pagevalues),
//         ]);
//     }

    /*
     * 说明：删除管理员数据
     */
    public function actionManagerdelete() {

        //获取get_id
        $id = $_GET['id'];

        if (!empty($id)) {

            //Managers::deleteAll('id = '.$id);
            Admin::deleteAll('id = '.$id);

            //return $this->render('index.twig', ['title' => '数据删除成功']);
        }
        else {

            //return $this->render('error.twig', ['title' => '数据删除失败，未提供指定数据id']);
        }
        $this->redirect('/manage/manager/managerlist');

    }
    public function actionAdmin(){
        $id=Yii::$app->request->get();
        $query=new \yii\db\Query();
        $data=$query->select('*')->from('admin')->where(['id'=>$id])->one();
        //var_dump($data);die();
        return $this->render('admin.twig',['data'=>$data]);
    }
}