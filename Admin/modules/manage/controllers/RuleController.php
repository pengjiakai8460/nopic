<?php
namespace Admin\modules\manage\controllers;

use Admin\modules\AdminController;
use common\models\Rules;
use common\models\Permissions;
use common\models\ToolsModel;
use yii\db;
use common\models\Pager;

class RuleController extends AdminController
{

    public $enableCsrfValidation = false;

    /*
     * 说明：权限、角色管理界面入口
     */
    public function actionIndex() {

        //登录验证

        return $this->render('index.twig', [
            'title' => '权限和角色管理模块：',
        ]);
    }

    /*
     * 说明：添加权限
     */
    public function actionPermissionsadd() {

        $permissionsName = $_POST['permissionsName'];
        $permissionsNum = $_POST['permissionsNum'];

        //验证保存数据
        if (!empty($permissionsName) && !empty($permissionsNum)) {

            $permissions = new Permissions();
            $permissions->permissions_name = $permissionsName;
            $permissions->permissions_num = $permissionsNum;
            $permissions->created = time();
            $permissions->save(false);

            return $this->render('index.twig', [
                'title' => '权限添加成功！',
            ]);
        }
        else {

            return $this->render('permissionsadd.twig', [
                'title' => '添加权限',
            ]);
        }
    }

    /*
     * 说明：权限编辑
     */
    public function actionPermissionsedit() {

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
                $data = Permissions::findOne($id);

                return $this->render('permissionsedit.twig', [
                    'title' => '编辑权限信息：',
                    'data' => $data,
                ]);
            }
        }
        else {

            //验证提交数据
            $permissionsName = $_POST['permissionsName'];
            $permissionsNum = $_POST['permissionsNum'];

            //post_id有值，则为update操作
            $permissions = Permissions::findOne($id);
            $permissions->permissions_name = $permissionsName;
            $permissions->permissions_num = $permissionsNum;
            $permissions->update(false);

            return $this->render('index.twig', ['title' => '权限信息编辑成功']);
        }
    }

    /*
     * 说明：权限列表
     */
    public function actionPermissionslist() {

        include_once 'com_list_variable.php';

        //使用自定义分页类执行查询
        //---制作本面的查询条件 begin
        $key = $_REQUEST['key'];

        $wheres = '1';
        if (!empty($key)) $wheres .= ' && a.permissions_name like "%'.$key.'%"';
        //---制作本面的查询条件 end

        //设置查询字段
        $fields .= "";

        //本页查询的表
        $tables = 'permissions a';

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
        return $this->render('permissionslist.twig', [
            'title'=>'权限列表：',
            'list' => $list,
            'listCount' => $listCount,
            'pageStr' => '共有：'.$pageobj->numItems.'条记录&nbsp;|&nbsp;'.$pageobj->getParaPageButton($pagekeys,$pagevalues,$index),
        ]);
    }

    /*
     * 说明：删除权限数据
     */
    public function actionPermissionsdelete() {

        //获取get_id
        $id = $_GET['id'];

        if (!empty($id)) {

            Permissions::deleteAll('id = '.$id);

            return $this->render('index.twig', ['title' => '数据删除成功']);
        }
        else {

            return $this->render('error.twig', ['title' => '数据删除失败，未提供指定数据id']);
        }
    }

    /*
     * 说明：创建角色
     */
    public function actionRuleadd() {

        $ruleName = $_POST['ruleName'];
        $rulePermissions = $_POST['rulePermissions'];

        if (!empty($ruleName) && count($rulePermissions) > 0) {

            //执行insert
            $ruleStr = implode('|', $rulePermissions);
            $data = new Rules();
            $data->rule_name = $ruleName;
            $data->rule_permissions = $ruleStr;
            $data->created = time();

            $data->save(false);

            return $this->render('index.twig', [
                'title' => '角色添加成功！',
            ]);
        }
        else {

            //打开添加界面

            //获取权限列表
            $permissions = Permissions::find()->all();

            return $this->render('ruleadd.twig', [
                'title' => '创建角色',
                'pops' => $permissions,
            ]);
        }
    }

    /*
     * 说明：角色列表
     */
    public function actionRulelist() {

        include_once 'com_list_variable.php';

        //使用自定义分页类执行查询
        //---制作本面的查询条件 begin
        $key = $_REQUEST['key'];

        $wheres = '1';
        if (!empty($key)) $wheres .= ' && a.rule_name like "%'.$key.'%"';
        //---制作本面的查询条件 end

        //设置查询字段
        $fields .= "";

        //本页查询的表
        $tables = 'rules a';

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
        return $this->render('rulelist.twig', [
            'title'=>'角色列表：',
            'list' => $list,
            'listCount' => $listCount,
            'pageStr' => '共有：'.$pageobj->numItems.'条记录&nbsp;|&nbsp;'.$pageobj->getParaPageButton($pagekeys,$pagevalues,$index),
        ]);
    }

    /*
     * 说明：角色编辑
     */
    public function actionRuleedit() {

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
                $rules = Rules::findOne($id);
                //拆分权限数据
                $rulePermissionsList = explode('|', $rules['rule_permissions']);

                //查询所有权限列表
                $data = Permissions::find()->all();
                $tools = new ToolsModel();
                $permissionsList = $tools->simplifyData($data);

                return $this->render('ruleedit.twig', [
                    'title' => '编辑权限信息：',
                    'rules' => $rules,
                    'rulePermissionsList' => $rulePermissionsList,
                    'permissionsList' => $permissionsList,
                ]);
            }
        }
        else {

            $ruleName = $_POST['ruleName'];
            $rulePermissions = $_POST['rulePermissions'];
            $ruleStr = implode('|', $rulePermissions);

            if (!empty($ruleName) && count($rulePermissions) > 0) {

                //执行insert
                $data = Rules::findOne($id);
                $data->rule_name = $ruleName;
                $data->rule_permissions = $ruleStr;

                $data->update(false);

                return $this->render('index.twig', [
                    'title' => '角色编辑成功！',
                ]);
            }
            else {

                //输出错误
                return $this->render('error.twig', [
                    'title' => '编辑失败，提交数据不完整',
                ]);
            }
        }
    }

    /*
     * 说明：删除角色数据
     */
    public function actionRuledelete() {

        //获取get_id
        $id = $_GET['id'];

        if (!empty($id)) {

            Rules::deleteAll('id = '.$id);

            return $this->render('index.twig', ['title' => '数据删除成功']);
        }
        else {

            return $this->render('error.twig', ['title' => '数据删除失败，未提供指定数据id']);
        }
    }
}

?>