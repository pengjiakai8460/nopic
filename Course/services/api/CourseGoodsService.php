<?php

namespace Course\services\api;

use common\base\Common;
use common\models\orm\XmVCourseGoods;
use Course\services\BaseService;
use yii\data\Pagination;
use yii\db\ActiveQuery;

class CourseGoodsService extends BaseService
{

    /**
     * @param int $limit
     * @param array $sort
     * @param array $fields
     * @param array $conditions
     * @return array
     */
    public static function listCourseGoods($limit = 10, $fields = [], $conditions = [], $sort = [])
    {
        $query = XmVCourseGoods::find();
        $withCourse = ['course' => function ($query) {
            $query->select(['id', 'title']);
        }];
        $withCreator = ['creator' => function ($query) {
            $query->select(['id', 'nickname']);
        }];
        $withOperator = ['operator' => function ($query) {
            $query->select(['id', 'nickname']);
        }];

        $query = self::commonQuery($query, [$withCourse, $withCreator, $withOperator], $fields, $conditions, $sort);

        $count = $query->count();

        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => $limit]);

        $list = $query->asArray()
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $data = [];
        $data['totalPage'] = ceil($count / $limit);
        $data['page'] = $pagination->page + 1;
        $data['total'] = intval($count);
        $data['pageSize'] = $limit;
        $data['data'] = $list;

        return $data;
    }

    public static function getCourseGoods($id)
    {
        $courseGoods = XmVCourseGoods::find()->where(['=', 'id', $id])->one();
        $courseGoods->price /= 100;
        return $courseGoods;
    }

    public static function saveCourseGoods()
    {
        $model = new XmVCourseGoods();
        $model->setScenario('save');
        $post = \Yii::$app->request->post();
        $post['status'] = '0';
        $post['price'] *= 100;//价格  /分
        $post['creator_id'] = $post['operator_id'] = AdminService::$adminInfo['uid'];
        $post['created_at'] = time();

        $model->load(['XmVCourseGoods' => $post]);

        if ($model->validate()) {

            return $model->save();
        } else {
            if ($model->getErrors()) {
                throw new \Exception(($model->getFirstError(key($model->getErrors()))), Common::ERR_VALIDATE_ERROR);
            }
        }
    }

    public static function updateCourseGoods(XmVCourseGoods $courseGoods)
    {
        $model = new XmVCourseGoods();
        $model->setScenario('update');
        $courseGoods->setScenario('update');

        $post = \Yii::$app->request->post();
        $post['price'] *= 100;//价格  /分
        $post['operator_id'] = AdminService::$adminInfo['uid'];
        $post['updated_at'] = time();

        $model->load(['XmVCourseGoods' => $post]);

        if ($model->validate()) {

            $courseGoods->setAttributes($model->getAttributes(null, ['id']));
            return $courseGoods->save();
        } else {
            if ($model->getErrors()) {
                throw new \Exception(($model->getFirstError(key($model->getErrors()))), Common::ERR_VALIDATE_ERROR);
            }
        }
        return false;
    }

    public static function patchCourseGoods(XmVCourseGoods $courseGoods)
    {
        $model = new XmVCourseGoods();
        $model->setScenario('patch');
        $post = \Yii::$app->request->post();
        $post['operator_id'] = AdminService::$adminInfo['uid'];
        $post['updated_at'] = time();
        $model->load(['XmVCourseGoods' => $post]);

        if ($model->validate()) {
            return $courseGoods->updateAttributes($model->getAttributes(['status', 'updated_at']));
        } else {
            if ($model->getErrors()) {
                throw new \Exception(($model->getFirstError(key($model->getErrors()))), Common::ERR_VALIDATE_ERROR);
            }
        }
    }

    public static function listCourseGoodsByExample(XmVCourseGoods $example)
    {
        $fields = [];
        $conditions = [];
        foreach ($example as $k => $v) {
            if ($v || is_bool($v)) {
                array_push($fields, $k);
                if ($v && !is_array($v)) {
                    array_push($conditions, ['=', $k, $v]);
                }
                if (is_array($v) && count($v) == 2) {
                    array_push($conditions, [$v[0], $k, $v[1]]);
                }
            }

        }

        $query = self::commonQuery(XmVCourseGoods::find(), null, $fields, $conditions);

        return $query->all();
    }
}