<?php

namespace SLApi\services\v1;

use Yii;
use common\models\orm\XmBUser;
use common\base\BaseService;
use SLApi\models\form\LoginForm;
use common\models\orm\XmBSchool;
use common\models\orm\XmBUserClass;

class UserService extends BaseService
{
    const DEFAULT_PASSWORD = '123456';

    public static $userInfo;

    /**
     * 初始化，每个Service都必须执行此方法
     * @param string $className
     * @return PayService //必须添加这行注释，用于代码提示
     * @author wangda
     */
    public static function model($className = __CLASS__)
    {
        if (isset(self::$_models[$className]))
            return self::$_models[$className];
        else {
            $model = self::$_models[$className] = new $className(null, null, []);
            return $model;
        }
    }

    //登录
    /**
     * @param type:1(学校);2(教师);3(学生);
     */
    public static function login($account, $password, $type, $school_id)
    {
        $message = '账号密码错误';
        $model = new LoginForm();
        $model->account = $account;
        $model->password = $password;
        $model->account_type = $type;
        $model->school_id = $school_id;
        $res = $model->login();
        if ($res) {
            self::$userInfo = $res;
            return [
                'code'=>200,
                'data'=>$res,
            ];
        } else {
            $message = current($model->getFirstErrors());
        }
        return [
            'code'=>0,
            'message'=> $message,
        ];
    }
    
    public static function addUser($school_id, $account, $name, $account_type = 3) {
        /*$user = self::_checkAccountExist($account);
        $text = $account_type == 1 ? '工号' : '学号';
        if (!empty($user)) {
            return self::error(0, "此{$text}已存在");
        }*/
        $user = new XmBUser();
        $user->school_id = $school_id;
        $user->name = $name;
        $user->account_type = $account_type;
        $user->add_time = time();
        $user->account = $account;
        $user->password = md5(self::DEFAULT_PASSWORD);
        $user->status = 1;
        $ret = $user->save(false);
        $return = [
            'id'=> $user->attributes['id'],
            'headimg'=> $user->attributes['headimg'],
            'name'=> $user->attributes['name'],
            'account'=> $user->attributes['account'],
            'school_id'=> $user->attributes['school_id'],
            'update_time'=> $user->attributes['update_time'],
            'add_time'=> $user->attributes['add_time'],
            'status'=> $user->attributes['status'],
        ];
        if (!$ret) {
            $errors = $user->getFirstErrors();
            $message = reset($errors);
            $message = empty($message) ? "保存失败，请稍后重试！" : $message;
            return self::error(0, $message);
            
        }
        
        return $return;
    }
    
    public static function addSchool($creator, $account, $name, $account_type = 1, $logo, $provience, $city, $area, $group_id) {
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            $time = time();
            $school = new XmBSchool();
            $school->creator = $creator;
            $school->logo = $logo;
            $school->name = $name;
            $school->province = $provience;
            $school->city = $city;
            $school->area = $area;
            $school->group_id = $group_id;
            $school->add_time = $time;
            $school->status = 1;
            $school->save(false);
            $user = new XmBUser();
            $user->name = $name;
            $user->school_id = $school->attributes['id'];
            $user->account_type = $account_type;
            $user->add_time = $time;
            $user->account = $account;
            $user->password = md5(self::DEFAULT_PASSWORD);
            $user->status = 1;
            $user->save(false);
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
        $return = [
            'id'=> $school->attributes['id'],
            'logo'=> $school->attributes['logo'],
            'area'=> $school->attributes['area'],
            'city'=> $school->attributes['city'],
            'province'=> $school->attributes['province'],
            'name'=> $school->attributes['name'],
            'group_id'=> $school->attributes['group_id'],
            'updated_time'=> $school->attributes['update_time'],
            'created_time'=> $school->attributes['add_time'],
            'status'=> $school->attributes['status'],
        ];
        return $return;
    }
    
    public static function removeUser($id) {
        $user = XmBUser::findOne($id);
        $user->status = 0;
        $user->update_time = time();
        $ret = $user->save(false);
        if (!$ret) {
            return self::error(0, "删除失败，请稍后重试！");
            
        }
        return $ret;
    }
    
    public static function updateUser($data) {
        $user = XmBUser::findOne($data['id']);
        if (empty($user)) {
            return self::error(0, "无此用户！");
        }
        if (isset($data['account_type']) && $user->account_type != $data['account_type']) {
            return self::error(0, "非法操作！");
        }
        $user->name = $data['name'];
        $user->update_time = time();
        $ret = $user->save(false);
        if (!$ret) {
            return self::error(0, "修改失败，请稍后重试！");
            
        }
        $return = [
            'id'=> $user->id,
            'headimg'=> $user->headimg,
            'name'=> $user->name,
            'account'=> $user->account,
            'school_id'=> $user->school_id,
            'update_time'=> $user->update_time,
            'add_time'=> $user->add_time,
            'status'=> $user->status,
        ];
        return $return;
    }
    
    public static function getUserList($account, $name, $page, $pageSize, $account_type) {
        
        $model = XmBUser::find();      
        $fields = [
            'id',
            'headimg',
            'account',
            'name',
            'school_id',
            'update_time',
            'add_time',
            'status',
        ];     
        $model->select($fields);       
        $model->where(['account_type'=> $account_type, 'status'=>1]);       
        $start = $pageSize * ($page - 1);
        if (!empty($account)) {
            $model->andWhere(['like', 'account', $account]);
        }       
        if (!empty($name)) {
            $model->andWhere(['like', 'name', $name]);
        }   
        $count = $model->count();       
        $list = $model->offset($start)->limit($pageSize)->orderBy('id DESC')->all();      
        $page_count = count($list);      
        $prev_page = $page == 1 ? null : $page - 1;      
        $next_page = $count - $page * $pageSize <= 0 ? null : $page + 1;
        
        $meta = [
            'total' => $count,
            'page' => $page,
            'page_count'=> $page_count,
            'limit' => $pageSize,
            'prev_page'=> $prev_page,
            'next_page'=> $next_page,
        ];
        
        $return = ['list'=> $list, 'meta'=> $meta];
        
        return $return;
    }
    
    public static function getSchoolList($account, $name, $page, $pageSize) {
        
        $model = XmBSchool::find();    
        $fields = [
            'id',
            'logo',
            'area',
            'city',
            'province',
            'name',
            'update_time',
            'add_time',
            'status',
        ];
        $model->select($fields);      
        $start = $pageSize * ($page - 1);       
        if (!empty($account)) {
            $model->andWhere(['like', 'account', $account]);
        }       
        if (!empty($name)) {
            $model->andWhere(['like', 'name', $name]);
        }       
        $count = $model->count();        
        $list = $model->offset($start)->limit($pageSize)->all();       
        $page_count = count($list);      
        $prev_page = $page == 1 ? null : $page - 1;        
        $next_page = $count - $page * $pageSize <= 0 ? null : $page + 1;
        
        $meta = [
            'total' => $count,
            'page' => $page,
            'page_count'=> $page_count,
            'limit' => $pageSize,
            'prev_page'=> $prev_page,
            'next_page'=> $next_page,
        ];
        
        $return = ['list'=> $list, 'meta'=> $meta];
        
        return $return;
        
    }
    
    public static function getSchoolOne($id) {
        $model = XmBSchool::find();
        $fields = [
            'id',
            'logo',
            'area',
            'city',
            'province',
            'name',
            'update_time',
            'add_time',
            'status',
        ];
        $school = $model->select($fields)->where(['id'=> $id])->one();     
        return $school;
    }
    
    public static function findOne($id, $account_type) {
        $conditions = [
            'id'=>$id, 
            'account_type'=>$account_type, 
            'status'=>1
            
        ];
        $fields = [
            'id',
            'headimg',
            'account',
            'name',
            'school_id',
            'update_time',
            'add_time',
            'status',
        ];
        return XmBUser::find()->select($fields)->where($conditions)->one();
    }
    
    protected static function _checkAccountExist($account) {
        $user = XmBUser::find()->where(['account'=>$account])->one();
        return $user;
    }
    
    public static function getUserByClassId($class_id, $account_type) {
        $model = XmBUser::find();
        $fields = [
            'id',
        ];
        if ($account_type == constant('TEACHER_TYPE')) {
            $fields = array_merge($fields, [
                            'headimg',
                            'name',
                        ]);
        }

        $class_user = XmBUserClass::find()->where(['class_id'=> $class_id])->asArray()->all();

        $conditions = [
            'and',
            'account_type=' . $account_type,
            'status=1',
            [
                'in', 'id', array_column($class_user, 'uid')
            ]
        ];
        $list = $model->select($fields)->where($conditions)->all();

        $return = [];
        foreach ($list as $key=> $val) {
            if ($account_type == constant('TEACHER_TYPE')) {
                $return[$key] = [
                    'id'=> $val['id'],
                    'headimg'=> $val['headimg'],
                    'name'=> $val['name'],
                ];
            } else {
                $return[] = $val['id'];
            }
        }
        return $return;
    } 
    
    public static function getUserInfo() {
        if (!empty($this->userInfo)) {
            return $this->userInfo;
        }
        return false;
    }



}
