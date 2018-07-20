<?php
namespace SLApi\services\v1;
use common\base\BaseService;
use common\models\orm\XmBClass;
use common\models\orm\XmBUserClass;
use common\models\orm\XmBUser;

/**
 *	@author brook wang<wangda@xiaoma.wang>
 *	@date 2018年7月5日 下午3:00:58 
 *	@version 1.0.0 
 *	@copyright  Copyright 2018 xiaoma.wang 
 */
class ClassesService extends BaseService {
    
    public static function addClass($school_id, $number, $name, $type=1, $comments='') {
        
        $class = new XmBClass();
        
        $class->school_id = $school_id;
        $class->number = $number;
        $class->name = $name;
        $class->type = $type;
        $class->comments = $comments;
        $class->status = 1;
        $class->add_time = time();
        $ret = $class->save();
        if (!$ret) {
            $errors = $class->getFirstErrors();
            $message = reset($errors);
            $message = empty($message) ? "保存失败，请稍后重试！" : $message;
            return self::error(0, $message);    
        }
        $return = $class->attributes;
        return $return;
    }
    
    public static function getClassList($page, $pageSize) {
        
        $model = XmBClass::find()->where(['status'=>1]);
        
        $start = $pageSize * ($page - 1);
        $list = $model->offset($start)->limit($pageSize)->asArray()->all();
        $return = [];
        foreach ($list as $key => $val) {
            $return[$val['id']] = $val;
            $return[$val['id']]['student'] = UserService::getUserByClassId($val['id'], constant('STUDENT_TYPE'));
            $return[$val['id']]['teacher'] = UserService::getUserByClassId($val['id'], constant('TEACHER_TYPE'));
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
    
    public static function findOne($id) {
        $class = XmBClass::find()->where(['id'=>$id, 'status'=>1])->asArray()->one();
        $class['student'] = UserService::getUserByClassId($id, constant('STUDENT_TYPE'));
        $class['teacher'] = UserService::getUserByClassId($id, constant('TEACHER_TYPE'));
        return $class;
    }
    
    public static function updateClass($data) {
        $class = XmBClass::findOne(['id'=>$data['id'], 'status'=>1]);
        if (empty($class)) {
            return [
                'is_error'=> true,
                'message' => '无此班级',
            ];
        }
        
        if (isset($data['lesson_id'])) {
            if (empty($class->course_id)) {
                return [
                    'is_error'=> true,
                    'message' => '请先选择课程',
                ];
            }
            $ret = LessonService::checkLessonInCourse($data['lesson_id'], $class->course_id);
            if (!$ret) {
                return [
                    'is_error'=> true,
                    'message' => '无此章节',
                ];
            }
        }
        
        foreach ($data as $key=>$val) {
            $class->$key = $val;
        }
        $ret = $class->save(false);
        if (!$ret) {
            $errors = $class->getFirstErrors();
            $message = reset($errors);
            $message = empty($message) ? "修改失败，请稍后重试！" : $message;
            return [
                'is_error'=> true,
                'message' => $message,
            ];
        }
        $return = $class->attributes;
        return $return;
    }

    public static function addStudent($class_id, $student_ids=[]) {
        $return = [
            'is_error'=> true,
            'message'=> '转入失败',
        ];
        
        $res = self::_checkStudent($student_ids);
        if (!$res) {
            return [
                'is_error'=> true,
                'message'=> '请选择正确的学生',
            ];
        }
        
        if (!empty($student_ids)) {
            foreach ($student_ids as $key=> $val) {
                $inData[] = [
                    'uid' => $val,
                    'class_id' => $class_id,
                ];
            }
            if ($inData) {
                $res = \Yii::$app->db6->createCommand()->batchInsert(XmBUserClass::tableName(),
                    ['uid', 'class_id'],
                    $inData)->execute();
                if ($res) {
                    $return = [
                        'id' => $class_id,
                        'students'=> $student_ids,
                        'student_count'=> count($student_ids),
                    ];
                }
            }
        }
        return $return;
    }
    
    public static function removeStudent($class_id, $student_ids=[]) {
        $return = [
            'is_error'=> true,
            'message'=> '转出失败',
        ];
        $res = self::_checkStudent($student_ids);
        if (!$res) {
            return [
                'is_error'=> true,
                'message'=> '请选择正确的学生',
            ];
        }
        if (!empty($student_ids)) {
            $conditions = [
                'and',
                'class_id='.$class_id,
                [
                    'in', 'uid', $student_ids
                ],
                
            ];
            $res = XmBUserClass::deleteAll($conditions);
            if ($res) {
                $return = [
                    'id' => $class_id,
                    'students'=> $student_ids,
                    'student_count'=> count($student_ids),
                ];
            }
        }
        return $return;
    }
    
    private static function _checkStudent($student_ids) {
        $conditions = [
            'and',
            'account_type=' . constant('STUDENT_TYPE'),
            'status=1',
            [
                'in', 'id', $student_ids
            ]
        ];
        $user_count = XmBUser::find()->where($conditions)->count();
        if ($user_count < count($student_ids)) {
            return false;
        }
        return true;
    }
}
 
 