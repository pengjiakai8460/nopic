<?php

namespace Api\modules\v1\controllers\user;

use Api\modules\v1\controllers\ApiBaseController;
use Api\services\v1\CalcuService;
use Api\services\v1\RedisService;
use Yii;

class CalcuController extends ApiBaseController
{

    /**
     * 计算能力成长每天执行
     */
    public function actionAbility()
    {
        $data = CalcuService::calcuData();
        return $this -> result($data['code'],$data['message'],$data['data']);
    }

    public function actionClear()
    {
        // DELETE from xm_report_exam where user_id = 3181;
        //DELETE from xm_report_exam_question where user_id = 3181;
        //DELETE from xm_report_error_question where user_id = 3181;
        //DELETE from xm_report_task where user_id = 3181;
        //DELETE from xm_report_task_detail where user_id = 3181;
        //DELETE from xm_user_rate where uid = 3181;
        //DELETE from xm_report_user_data where user_id = 3181;
        $userId = Yii::$app->request->get('uid');
        Yii::$app->db->createCommand()->delete('xm_report_exam', "user_id = {$userId}")->execute();
        Yii::$app->db->createCommand()->delete('xm_report_exam_question', "user_id = {$userId}")->execute();
        Yii::$app->db->createCommand()->delete('xm_report_error_question', "user_id = {$userId}")->execute();
        Yii::$app->db->createCommand()->delete('xm_report_task', "user_id = {$userId}")->execute();
        Yii::$app->db->createCommand()->delete('xm_report_task_detail', "user_id = {$userId}")->execute();
        Yii::$app->db->createCommand()->delete('xm_user_rate', "uid = {$userId}")->execute();
        Yii::$app->db->createCommand()->delete('xm_report_user_data', "user_id = {$userId}")->execute();
        RedisService::deleteKey('wrong_record_uid_'.$userId);
        return $this -> success();
    }
}