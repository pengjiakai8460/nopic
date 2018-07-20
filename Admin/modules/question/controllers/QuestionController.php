<?php
namespace Admin\modules\question\controllers;
use Api\services\v1\SpecialService;
use common\models\service\CommonService;
use Yii;
use yii\data\Pagination;
use common\models\utils\FuncUtil;
use Admin\modules\AdminController;
use Admin\service\QuestionService;
use Admin\service\TagService;


class QuestionController extends AdminController
{
    const PAGE_PER_NUMBER = 10;      // 15

    // 访问控制，暂时为空
    public function behaviors ()
    {
        return [

        ];
    }
    /**
     * 用户管理默认显示页面
     */
    public function actionIndex()
    {
        $order = '';
        $get = FuncUtil::parseData(Yii::$app->request->get());
        $get['name'] = isset($get['name']) ? trim($get['name']) : '';
        $type=Yii::$app->request->get('selecttype');
        $pageno = ! empty($get['page']) ? $get['page'] - 1 : 0 ;
        $or = null;

        if (!empty($get['search'])){
            $or = $get['search'];
        }

        if (!empty($get['type'])){
            $order = $get['type'];
        }
        $lists = QuestionService::selectQuestion($pageno, false, self::PAGE_PER_NUMBER ,$or,$order,$type, $get);
        $count = QuestionService::getCount($get);
        $topTagsData = TagService::getTopTag();
        $pi = new Pagination(['totalCount' => $count,'pageSize' => self::PAGE_PER_NUMBER]);
        $renderData = [
            'islocked' => false,        // 告诉模板是正常用户
            'lists' => $lists,
            'count' => $count,
            'pagenum' => $pi->pageCount,
            'pagination' => $pi,
            'yue'=>Yii::$app->request->get('type'),
            'img'=>Yii::$app->request->get('img'),
            'selecttype'=>$type,
            'tags'=>$topTagsData,
        ];
        return $this->render('index.twig', $renderData);
    }

    //添加试题
    public function actionAddquestion() {
        $adm = $_SESSION;
        $renderData = [
            'islocked' => false,        // 告诉模板是正常用户
            'adm' => $adm
        ];
        return $this->render('addquestion.twig', $renderData);

    }

    //删除试题
    public function actionDelquestion() {
        $post = Yii::$app->request->post();
        if (!isset($post['qid'])) {
            $r["code"] = 1;
            $r["msg"] = "题目ID不能为空!";
        }
        $ret = QuestionService::delQuestion($post);
        echo json_encode($ret);
    }

    //提交试题
    public function actionAddquest() {
        $post = FuncUtil::parseData(Yii::$app->request->post());
        $ret = QuestionService::addQuest($post);
        if ($ret) {
            $data['code'] = 0;
            $data['msg'] = "保存成功!";
            $data['data'] = [];
            echo json_encode($data);exit;

        } else {
            $data['code'] = 1;
            $data['msg'] = "保存失败,请重新添加!";
            $data['data'] = [];
            echo json_encode($data);exit;
        }

    }

    public function actionUeditor() {
        $CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents("ueditor/php/config.json")), true);
        $action = $_GET['action'];
        switch ($action) {
            case 'config':
                $result = json_encode($CONFIG);
                break;
            /* 上传图片 */
            case 'uploadimage':
                /* 上传涂鸦 */
            case 'uploadscrawl':
                /* 上传视频 */
            case 'uploadvideo':
                /* 上传文件 */
            case 'uploadfile':
                $result = include("/ueditor/php/action_upload.php");
                break;
            /* 列出图片 */
            case 'listimage':
                $result = include("/ueditor/php/action_list.php");
                break;
            /* 列出文件 */
            case 'listfile':
                $result = include("/ueditor/php/action_list.php");
                break;
            /* 抓取远程文件 */
            case 'catchimage':
                $result = include("/ueditor/php/action_crawler.php");
                break;
            default:
                $result = json_encode(array(
                    'state'=> '请求地址出错'
                ));
                break;
        }
        /* 输出结果 */
        if (isset($_GET["callback"])) {
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                echo json_encode(array(
                    'state'=> 'callback参数不合法'
                ));
            }
        } else {
            echo $result;
        }
    }
   
    public function actionLoadmodal()
    {
        $renderData = [];
        $get = FuncUtil::parseData(Yii::$app->request->get());
        $type = ! empty($get['type']) ? $get['type'] : '' ;
        $renderData['type'] = $type;
        switch ($type){
            case 'comp':
                break;
            case 'tag':
                $renderData['tags'] = TagService::getAllLabels();
                break;
            case 'from':
                break;
            default:
                break;
        }
        return $this->render('loadmodal.twig', $renderData);
    }

    //编辑信息
    public function actionEdit() {
        $get = FuncUtil::parseData(Yii::$app->request->get());
        $ret = QuestionService::getQuestionById($get);
        return $this->render('editquestion.twig', $ret);

    }

    //编辑完成之后保存相关信息
    public function actionSave() {
        $post = FuncUtil::parseData(Yii::$app->request->post());
        print_r($post);exit;
    }

    //测试上传
    public function actionUpload() {
        $ret = QuestionService::upload();
        print_r($ret);exit;
    }

}