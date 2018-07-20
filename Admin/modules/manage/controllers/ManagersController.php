<?php
namespace Admin\modules\manage\controllers;



use common\models\ToolsModel;
use common\models\Pager;
use Admin\modules\AdminController;
use common\models\orm\Rules;
use common\models\orm\Managers;
use yii\db;
// use common\models\utils\Pager;

class ManagersController extends AdminController
{
    public $layout= false;
    public $enableCsrfValidation = false;

    /*
     * 说明：权限、角色管理界面入口
     */
    public function actionIndex() {

        //登录验证

        return $this->render('index.twig', [
            'title' => '管理员管理模块：',
        ]);
    }

    /*
     * 说明：添加管理员
     */
    public function actionManageradd() {

        //实例化Tools工具类
        $tools = new ToolsModel();

        if (count($_POST) == 0) {

            //查询角色列表
            $data = Rules::find()->all();
            $ruleList = $tools->simplifyData($data);

            return $this->render('add.twig', [
                'title' => '添加管理员：',
                'ruleList' => $ruleList,
            ]);
        }
        else {

            //验证保存数据
            if ($tools->validPost(Array('username','password','myname','idcard','tel','rule_id'))) {

                $managers = new Managers();
                $managers->attributes = $_POST;
                $managers->save(false);

                return $this->render('index.twig', [
                    'title' => '管理员添加成功！',
                ]);
            }
            else {

                return $this->render('error.twig', [
                    'title' => '操作失败，POST提交参数审核不通过！',
                ]);
            }
        }
    }

    /*
     * 说明：管理员信息编辑
     */
    public function actionManageredit() {

        //实例化Tools工具类
        $tools = new ToolsModel();

        $id = $_POST['id'];

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
                $data = Managers::findOne($id);
                //查询所有角色列表
                $rules = Rules::find()->all();

                return $this->render('edit.twig', [
                    'title' => '编辑权限信息：',
                    'data' => $data,
                    'rules' => $rules,
                ]);
            }
        }
        else {

            //验证保存数据
            if ($tools->validPost(Array('username','myname','idcard','tel','rule_id'))) {

                $managers = Managers::findOne($id);
                //print_r($_POST);exit;
                $managers->attributes = $_POST;
                $managers->update(false);

                return $this->render('index.twig', [
                    'title' => '管理员添加成功！',
                ]);
            }
            else {

                return $this->render('error.twig', [
                    'title' => '操作失败，POST提交参数审核不通过！',
                ]);
            }
        }
    }

    /*
     * 说明：管理员列表
     */
    public function actionManagerlist() {

        include_once 'com_list_variable.php';

        //使用自定义分页类执行查询
        //---制作本面的查询条件 begin
        $key = $_REQUEST['key'];

        $wheres = 'a.rule_id = b.id';
        if (!empty($key)) $wheres .= ' && (a.username like "%'.$key.'%" || a.myname like "%'.$key.'%"';
        //---制作本面的查询条件 end

        //设置查询字段
        $fields .= ",b.rule_name";

        //本页查询的表
        $tables = 'managers a, rules b';

        //获取分页后的记录集
        $pageobj = new Pager();
        $pager = $pageobj->pager($pageSize,$pageid,$fields,'where '.$wheres,$tables,'order by '.$orders,$contField);
        //print("select ".$fields." from ".$tables." ".'where '.$wheres);
        $list = $pageobj->getCurResult();
        $listCount = count($list);

        //$connection = \Yii::$app->db;
        //$command = $connection->createCommand("select a.*, b.rule_name from managers a, rules b where a.rule_id = b.id");
        //$result = $command->queryAll();

        //打开列表界面
        return $this->render('list.twig', [
            'title'=>'管理员列表：',
            'list' => $list,
            'listCount' => $listCount,
            'pageStr' => '共有：'.$pageobj->numItems.'条记录&nbsp;|&nbsp;'.$pageobj->getParaPageButton($pagekeys,$pagevalues,$index),
        ]);
    }

    /*
     * 说明：删除管理员数据
     */
    public function actionManagerdelete() {

        //获取get_id
        $id = $_GET['id'];

        if (!empty($id)) {

            Managers::deleteAll('id = '.$id);

            return $this->render('index.twig', ['title' => '数据删除成功']);
        }
        else {

            return $this->render('error.twig', ['title' => '数据删除失败，未提供指定数据id']);
        }
    }
}