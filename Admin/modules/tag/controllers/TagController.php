<?php
namespace Admin\modules\tag\controllers;

use Admin\modules\AdminController;
use Admin\service\TagService;
use yii\helpers\Url;
use yii;

class TagController extends AdminController
{
    const PAGE_PER_NUMBER = 10;      // 15

    // 访问控制，暂时为空
    public function behaviors ()
    {
        return [

        ];
    }

    /**
     * 标签树展示
     */
    public function actionIndexlist()
    {
        $renderData = [];
        $renderData['tags'] = TagService::getAllLabels();
        return $this->render('index.twig', $renderData);
    }


    /**
     * 添加标签页面
     */
    public function actionAdd()
    {
        $pid = Yii::$app->request->get('id');
        $renderData = TagService::showAddView($pid);
        $renderData['postUrl'] = Url::to(['tag/dosave']);
        return $this->render('add.twig', $renderData);
    }


    public function actionEdit()
    {

        $pid = Yii::$app->request->get('id');
        if($pid && is_numeric($pid)){
            $renderData = TagService::showEditView($pid);
            $renderData['postUrl'] = Url::to(['tag/dosave']);
            return $this->render('edit.twig', $renderData);
        }

    }

    public function actionDosave()
    {
        $request = Yii::$app->request;
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        if($request->isPost){
            $addLabelData = $request->post();
            $data = TagService::dosave($addLabelData);
            if($data){
                $response->data = ['code'=>200,'message'=>'操作成功','data'=>$data];
            }
        }else{
            $response->data = ['code'=>400,'message'=>'操作失败'];
        }

    }

    public function actionDelete()
    {
        $id = Yii::$app->request->get('id');
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;

        if(is_numeric($id) && $id > 0){
            $response->data = TagService::doDelete($id);
        }else{
            $response->data = ['code' => 400, 'message' => '参数错误'];
        }
    }
}