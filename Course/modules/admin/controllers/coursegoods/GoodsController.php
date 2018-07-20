<?php

namespace Course\modules\admin\controllers\coursegoods;

use common\base\Common;
use common\models\orm\XmVCourseGoods;
use Course\modules\admin\controllers\AdminBaseController;
use Course\services\api\CourseGoodsService;

class GoodsController extends AdminBaseController
{
    public function actionIndex()
    {
        //TODO 规范排序格式 sort=asc   $sort = ['id' => SORT_ASC, 'name' => SORT_DESC]
        $status = $this->request->get('status');
        $sort = $this->request->get('sort');
        $limit = $this->request->get('limit')?intval($this->request->get('limit')):10;
        if (in_array(strtoupper($sort), ['ASC', 'DESC'])){
            $sortMap = ['id' => "SORT_{$sort}"];
        }
        $condition = [];

        if ($id = $this->request->get('id')) array_push($condition, ['=', XmVCourseGoods::tableName().'.id', $id]);
        if ($created_from = $this->request->get('created_from')) array_push($condition, ['>', XmVCourseGoods::tableName().'.created_at', $created_from]);
        if ($created_to = $this->request->get('created_to')) array_push($condition, ['<', XmVCourseGoods::tableName().'.created_at', $created_to]);
        if ($key_words = $this->request->get('key_words')) array_push($condition, ['like', XmVCourseGoods::tableName().'.name', $key_words]);
        if ($status!=null && in_array($status, [0,1,2])) array_push($condition, ['=', XmVCourseGoods::tableName().'.status', $status]);

        $res = CourseGoodsService::listCourseGoods($limit, ['*'], $condition, $sortMap??null);

        return $this->apiResult(Common::SUCCESS_CODE, 'success', $res);
    }

    public function actionShow()
    {
        if (!$id = $this->request->get('id')) return $this->apiResult(Common::ERR_INVALID_REQUEST_METHOD, 'id 不得为空');
        try{
            if(!$res = CourseGoodsService::getCourseGoods($id)){

                return $this->apiResult(Common::ERR_UNKNOWN_ERROR, $res);
            }

            return $this->apiResult(Common::SUCCESS_CODE, 'success',$res);
        }catch (\Exception $e){
            return $this->apiResult($e->getCode(), $e->getMessage());
        }
    }

    public function actionStore()
    {
        try{
             if(!$res = CourseGoodsService::saveCourseGoods()){

                 return $this->apiResult(Common::ERR_UNKNOWN_ERROR, $res);
             }

            return $this->apiResult(Common::SUCCESS_CODE, $res);
        }catch (\Exception $e){
            return $this->apiResult($e->getCode(), $e->getMessage());
        }
    }

    public function actionUpdate()
    {
        if (!$this->request->post('id')) return $this->apiResult(Common::ERR_VALIDATE_ERROR, 'id 不得为空');
        try{
            $courseGoods = XmVCourseGoods::find()->where(['id' => $this->request->post('id')])->one();

            if(!$res= CourseGoodsService::updateCourseGoods($courseGoods)){
                return $this->apiResult(Common::ERR_UNKNOWN_ERROR[Common::RESULT_CODE], Common::ERR_UNKNOWN_ERROR[Common::RESULT_MESS]);
            }

            return $this->apiResult(Common::SUCCESS_CODE, $res);
        }catch (\Exception $e){
            return $this->apiResult($e->getCode(), $e->getMessage());
        }
    }

    public function actionPatch()
    {
        if (!in_array($this->request->post('status'), ['0', '1'])) return $this->apiResult(Common::ERR_VALIDATE_ERROR, 'status 不得为空');
        try{
            $courseGoods = XmVCourseGoods::find()->where(['id' => $this->request->post('id')])->one();

            if(!$res= CourseGoodsService::patchCourseGoods($courseGoods)){

                return $this->apiResult(Common::ERR_UNKNOWN_ERROR[Common::RESULT_CODE], Common::ERR_UNKNOWN_ERROR[Common::RESULT_MESS]);
            }

            return $this->apiResult(Common::SUCCESS_CODE, $res);
        }catch (\Exception $e){
            return $this->apiResult($e->getCode(), $e->getMessage());
        }
    }

    public function actionListCourseGoods()
    {
        $example = new XmVCourseGoods();
        $example->setAttribute('id', false);
        $example->setAttribute('name', false);
        $example->setAttribute('available_from', false);
        $example->setAttribute('available_to', ['>', time()]);
        $example->setAttribute('status', ['=', 1]);

        return $this->apiResult(Common::SUCCESS_CODE, 'success', CourseGoodsService::listCourseGoodsByExample($example));
    }
}