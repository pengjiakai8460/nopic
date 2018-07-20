<?php
namespace Course\modules\api\controllers\index;

use common\models\orm\XmUsers;
use common\models\orm\XmVClasses;
use common\models\orm\XmVClassesUsers;
use common\models\orm\XmVCourseChapters;
use common\models\orm\XmVCourseLessons;
use common\models\orm\XmVLogs;
use common\models\orm\XmVOrders;
use common\models\orm\XmVUsersCourseService;
use common\models\orm\XmVUsersHomework;
use Course\modules\api\controllers\ApiBaseController;
use Course\services\api\ComposeService;
use Course\services\api\CourseServiceService;
use Course\services\api\UsersCourseServiceService;
use Course\services\api\YouZanService;

class TestController extends ApiBaseController
{
    public function actionTest()
    {
        $compose_id = \Yii::$app->request->get('compose_id');
        $uuid = \Yii::$app->request->get('uuid');
        if (!empty($compose_id)) {
            echo ComposeService::alphaID($compose_id, false, 8, 'xmw') . '<br />';
        }
        if (!empty($uuid)) {
            echo self::alphaID($uuid, true, 8, 'xmw');//字符串解密
        }
    }

    //暂时使用后续销毁
    public function actionTest1()
    {
        $class_id = \Yii::$app->request->get('class_id');
        $class = XmVClasses::find()->where(['id' => $class_id])->one();//班级信息
        $courseLesson = XmVCourseLessons::find()->where(['courseId' => $class['courseId']])->asArray()->all();//对应课程的的id
        $lessons = array_column($courseLesson, 'id');//课程的章节id数组

        $courseChapters = XmVCourseChapters::find()->where(['>', 'homeworkId', 0])->andWhere(['in', 'lessonId', $lessons])->andWhere(['is_delete' => 1])->asArray()->all();//所有需要做作业的小节信息

        $clssesUser = XmVClassesUsers::find()->where(['classId' => $class_id])->asArray()->all();
        $classUser = array_column($clssesUser, 'usersId');//需要做该项作业的所有学生id（班级中的学生id）
        $ret = array();
        foreach ($courseChapters as $key => $value) {
            $userHomework = XmVUsersHomework::find()->where(['classId' => $class_id])->andWhere(['homeworkId' => $value['homeworkId']])->andWhere(['chapterId' => $value['id']])->asArray()->all();//已经做完该项作业的学员
            $makeHomeworkUser = array_column($userHomework, 'userId');//已经做完作业的学员的id
            //数组去重
            $makeHomeworkUser = array_unique($makeHomeworkUser);
            $noMakeHomeworkUser = array_diff($classUser, $makeHomeworkUser);
            $users = XmUsers::find()->select('account')->where(['in', 'id', $noMakeHomeworkUser])->asArray()->all();
            $usersAcount = array_column($users, 'account');
            $ret[$value['id']]['account'] = $usersAcount;
            $ret[$value['id']]['title'] = $value['title'];
        }
        echo '<pre>';
        var_dump($ret);
    }

    //临时使用后期删除
    public function actionApp()
    {
        $orders = XmVOrders::find()->where(['status' => 'paid'])->andWhere(['=', 'classesId', 0])->asArray()->all();
        $ret = [];
        $i = 0;
        foreach ($orders as $key => $value) {
            $users = XmUsers::find()->where(['id' => $value['userId']])->asArray()->one();
            $ret[$i]['account'] = $users['account'];
            $ret[$i]['price'] = $value['price'] / 100;
            $ret[$i]['course'] = $value['courseId'];
            $i++;
        }
        $data = $ret;
//        Header("Content-type: application/octet-stream ");
//        Header("Accept-Ranges: bytes ");
//        header("Content-type:application/vnd.ms-excel;charset=UTF-8");
//        Header("Content-Disposition:attachment;filename=".'统计'.".xlsx ");
//        echo "<table border='1' align='center'><tr>";
//        echo '<th>手机号码</th>';
//        echo '<th>价格</th>';
//        echo '<th>课程id</th>';
//
//        foreach ($data as $k => $v) {
//            echo "<tr>";
//            echo "<td>" . $v['account'] . "</td>";
//            echo "<td>" . $v['price'] . "</td>";
//            echo "<td>" . $v['course'] . "</td>";
//            echo "</tr>";
//        }
//        echo "</table>";
//        exit;
        $this->exportExcel($data, ['phone', 'price', 'course_id']);

    }

    //临时使用后续删除
    public function exportExcel($data = array(), $title = array(), $filename = 'report')
    {
        header("Content-type:application/octet-stream");
        header("Accept-Ranges:bytes");
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:attachment;filename=" . $filename . ".xls");
        header("Pragma: no-cache");
        header("Expires: 0");
//导出xls开始
        if (!empty($title)) {
            foreach ($title as $k => $v) {
                $title[$k] = iconv("UTF-8", "GB2312", $v);
            }
            $title = implode("\t", $title);
            echo "$title\n";
        }
        if (!empty($data)) {
            foreach ($data as $key => $val) {
                foreach ($val as $ck => $cv) {
                    $data[$key][$ck] = iconv("UTF-8", "GB2312", $cv);
                }
                $data[$key] = implode("\t", $data[$key]);
            }
            echo implode("\n", $data);
        }
    }

    //导入有赞订单信息到小码世界订单表中
    public function actionAddOrders()
    {
//        $idsArr = array(
//            17865396656,
//            18667921866,
//            13705497757,
//            18958064702,
//            13567180233,
//            13911582508,
//            13011016855,
//            13173456713,
//            15888992890,
//            15306715852,
//            13510179824,
//            15967774316,
//            18921113158,
//            13868008410,
//            18980680008,
//            15004199705,
//            18978919881,
//            13591103966,
//            13823589919,
//            13868375540,
//            13587112279,
//            13225967798,
//            13583973219,
//            13665492633,
//            18931146916,
//            13645391920,
//            13563900398,
//            13562916166,
//            15163984989,
//            15963320003,
//            13563950279,
//            13562900998,
//            13625498166,
//            13758288885,
//            13685390039,
//            15882253565,
//            18853938738,
//            15653976669,
//            15069960515,
//            13562983304,
//            15653976522,
//            13858180894,
//            13739057666,
//            13857829719,
//            18917007053,
//            13757175119,
//            13088626767,
//            13456472268,
//            15885273436,
//            13857180219,
//            18258221999,
//            15157161038,
//            13858083645,
//            18606536635,
//            13588210202,
//            13426291180,
//            15101028553,
//            13858448798,
//            13857002460,
//            18258118057,
//            15559553389,
//            13757809612,
//            13438222741,
//            13695797935,
//            13625781398,
//            13735973360,
//            13957090979,
//            13757807672,
//            13967061617,
//            15925791611,
//            13757800942,
//            13587173009,
//            13957076997,
//            15105783977,
//            13905781673,
//            13606699701,
//            15967268775,
//            15057868259,
//            13957092500,
//            13606691148,
//            18957048969,
//            13587196050,
//            13957098408,
//            13396881080,
//            13957096481,
//            13606691690,
//            15906880582,
//            15057881288,
//            13957093320,
//            13587196177,
//            13587138932,
//            13918412652,
//            13621919229,
//            18803116696,
//            13861878168,
//            13626127088,
//            13403165118,
//            18905880057,
//            13891558411,
//            13834105569,
//            13563741064,
//            13950070903,
//            13588852421,
//            18857199059,
//            13524423516,
//            13507970651,
//            13698060552,
//            13770937855,
//            17600389729,
//            18039576669,
//            18193127267,
//            13567118305,
//            13636441684,
//            13100511061,
//            13736778864,
//            15868467579,
//            13999623389,
//            13588845665,
//            13609991098,
//            13788942646,
//            15857950611,
//            13967398682,
//            13706503553,
//            13703351194,
//            13701157968,
//            13565061578,
//            18981532046,
//            18704609558,
//            13566355224,
//            13566636767,
//            13852828018,
//            18106777008,
//            15880770611,
//            15907956668,
//            15990162392,
//            18902809237,
//            13960783571,
//            15726877558,
//            18102818986,
//            13225967798,
//            13906004358,
//            13651273571,
//            18005392050
//        );
        $idsArr = array(
            13757129836,
            13867411373,
            18745538770,
            18696614591,
            13532282280,
            15988863788,
            18757107748,
            18258254686,
            13757119308,
            13538185695,
            13735874491,
            13666697160,
            13758178016,
            18329005696,
            13839382220,
            13804284897,
            13955264642,
            13467823358,
            13439805800,
            15960830623,
            13173938168,
            13618264255,
            13735604899,
            18673543065,
            15115567726,
            13960280602,
            15886559844,
            18927544831,
            18926229859,
            13705708898,
            18975589679,
            15886531271,
            13786532951,
            13975550551,
            13675708804,
            15106000085,
            13905700281,
            15059801257,
            13850721705,
            18608487609,
            13968005655,
            13714186879,
            15910586003,
            18202257883,
            18720970805,
            13710543947,
            13019405280,
            13311378915,
            15915832699,
            17382186938,
            13883370270,
            18566669185,
            13588118007,
            15390281665,
            18381702257,
            18911977414,
            18502178008,
            13906932105,
            13986560498,
            13868061109,
            13811518551,
            13858366574,
            13957878834,
            13857897507,
            18847638566,
            13531731057,
            13625399676,
            15902977178,
            13866668979,
            15821847845,
            17782336866,
            13921326260,
            13117916677,
            18917926672,
            18868774466,
            13821227681,
            18931184981,
            13806498228,
            15552930116,
            18072902312,
            13588135355,
            13053903558,
            13958130969,
            13186995733,
            13685767519,
            13858169110,
            18199289926,
            13551337435,
            13772132120,
            18149785820,
            13505390183
        );
        $cs = \Yii::$app->request->get('canshu');
        if ($cs != 'xiaolonglong12138') {
            echo 111;
            return 11;
        }
        $users = XmUsers::find()->select('id')->where(['in', 'account', $idsArr])->asArray(true)->all();
        $data = array();
        $i = 0;
        $ti = time();
        foreach ($users as $key => $value) {
            //检查是否已经购买
            $orderN = XmVOrders::find()->where(['courseId' => 1, 'userId' => $value['id'], 'status' => 'paid'])->asArray()->one();
            if (!empty($orderN)) {
                continue;
            }

            $data[$i]['orderSn'] = 'C_YZ_' . date('YmdHis') . $value['id'] . rand(10000, 99999);
            $data[$i]['courseId'] = 1;
            $data[$i]['title'] = '小码世界-Scratch-L1';
            $data[$i]['price'] = 19900;
            $data[$i]['discount'] = 0.00;
            $data[$i]['userId'] = $value['id'];
            $data[$i]['status'] = 'paid';
            $data[$i]['createdTime'] = $ti;
            $data[$i]['paidTime'] = $ti;
            $data[$i]['payment'] = 'wechat';
            $data[$i]['note'] = '订单来源于有赞';
            $data[$i]['is_delete'] = 1;
            $data[$i]['chn'] = 0;
            $data[$i]['classesId'] = 0;
            $i++;
        }
        //小码世界-Scratch-L1
        $a = 0;
        foreach ($data as $key => $value) {
            $orders = new XmVOrders();
            foreach ($value as $k => $v) {
                $orders->$k = $v;
            }
            $orders->save();
            $a++;
        }
//        $ret = \Yii::$app->db4->createCommand()->batchInsert(XmVOrders::tableName(), [
//            'orderSn', 'courseId', 'title', 'price', 'discount', 'userId', 'status', 'createdTime', 'paidTime', 'payment', 'note', 'is_delete', 'chn', 'classesId'
//        ], $data)->execute();;
        echo $a;
        exit;
    }

    //测试配置
    public function actionEnv()
    {
        $log = new XmVLogs();
        $log->message = 'ddd';
        $log->createdTime = time();
        $log->save();
        return 111;
    }

    //同步订单到待服务表中
    public function actionTongbuOrder()
    {
        $orders = XmVOrders::find()->where(['status' => 'paid'])->asArray()->all();
        foreach ($orders as $key => $value) {
            UsersCourseServiceService::addUsersCourseService($value['courseId'], 1, $value['id'], $value['source'], '');
        }
    }

    public function actionTest22()
    {
        CourseServiceService::getSignUrl();
        echo 111;
        exit;
    }

    public function actionTestbranch()
    {
        echo "3333";
        exit;
    }
}