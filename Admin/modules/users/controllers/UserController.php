<?php
namespace Admin\modules\users\controllers;

use common\models\orm\XmUser;


use common\models\orm\MemberPcOrder;
use common\models\orm\ReleaseOrder;
use common\models\service\OrderService;
use common\models\service\UserCacheService;
use Yii;
use Admin\modules\AdminController;
use App\models\service\ApiService;
use yii\data\Pagination;
use common\models\utils\Tools;
use common\models\orm\Auth;
use common\models\orm\UserBase;
use common\models\orm\UserAliaccount;
use common\models\orm\UserPay;
use amnah\yii2\user\models\UserAuth;
use common\models\orm\UserWithdrawCash;
use common\models\orm\UserWealth;
use common\models\orm\UserExchange;
use common\models\orm\LogGift;
use common\models\service\CommonService;
use common\models\orm\UserExtend;
use common\models\orm\UserPhoto;
use common\models\orm\UserVideo;
use common\models\service\UserService;
use Admin\service\UsersManageService;
use common\models\utils\FuncUtil;
use common\models\service\ManagementMessageService;
use common\models\orm\Member;
use common\models\service\MemberService;
use common\models\orm\MemberProfile;
use common\models\orm\MemberWealth;
use common\models\service\MongoService;



class UserController extends AdminController
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
        $this->redirect('/users/user/unlocked-user-list');
    }

    /**
     * 用户管理默认显示页面
     */
    public function actionUnlockedUserList()
    {
        //var_dump(Yii::$app->user);exit;
        $order = '';
        $get = FuncUtil::parseData(Yii::$app->request->get());
        $type=Yii::$app->request->get('selecttype');
        $pageno = ! empty($get['page']) ? $get['page'] - 1 : 0 ;
        $or = null;

        if (!empty($get['search'])){
            $or = $get['search'];
        }

        if (!empty($get['type'])){
            $order = $get['type'];
        }

        $users = UsersManageService::model()->selectUsers($pageno, false, self::PAGE_PER_NUMBER ,$or,$order,$type);
        foreach ($users as $k => $v) {
            $count = MemberPcOrder::find()->where(['uid' => $v['id']])->one();
            $users[$k]['deal_count'] = $count->receiver_count ?? '0';
            $users[$k]['send_count'] = ReleaseOrder::find()->where(['uid' => $v['id']])->count();
        }
        $count = UsersManageService::model()->getCount(false,$or,$type);

        $pi = new Pagination(['totalCount' => $count,'pageSize' => self::PAGE_PER_NUMBER]);
        $adm = $_SESSION;
        $renderData = [
            'islocked' => false,        // 告诉模板是正常用户
            'users' => $users,
            'count' => $count,
            'pagenum' => $pi->pageCount,
            'pagination' => $pi,
            'yue'=>Yii::$app->request->get('type'),
            'img'=>Yii::$app->request->get('img'),
            'selecttype'=>$type,
            'adm' => $adm
        ];
        return $this->render('index.twig', $renderData);
    }

    /**
     * 用户个人信息详情
     */
    public function actionProfile ($id)
    {
        $data = array();
        $data['user'] = Auth::findOne(['uid'=>$id]);
        $data['userBase'] = UserBase::findOne(['uid'=>$id]);
        $data['userAli'] = UserAliaccount::findOne(['uid'=>$id]);
        $data['userPay'] = UserPay::findOne(['uid'=>$id]);
        $data['userAli'] = UserAliaccount::findOne(['uid'=>$id]);
        $data['userWealth'] = UserWealth::findOne(['uid'=>$id]);
        $data['userExtend'] = UserExtend::findOne(['uid'=>$id]);
//         var_dump($data['userExtend']);exit;
        $data['userExchange'] = UserExchange::findAll(['uid'=>$id]);

        $userGift  = LogGift::find()->where(['touid'=>$id])->orderBy('id desc')->limit(10)->all();
        $data['userGift'] = CommonService::model()->getAllUserInfo($userGift,'UserBase',true,true,true,'userInfo','fromuid');
        $data['userGiftCount'] = LogGift::find()->where(['touid'=>$id])->count();

        $sqlRank ="SELECT * ,sum(diamond) as sum FROM `tb_log_gift` where touid=".$id." group by fromuid  order by sum desc";
//         $data['userGiftRank'] = LogGift::findBySql($sqlRank)->orderBy('dad desc')->limit(10)->all();
//         $sqlRank ="  group by fromuid ";
        $data['userGiftRank'] = LogGift::find('sum(diamond) as dad')->where(['touid'=>$id])->groupBy('fromuid')->orderBy('id desc')->limit(10)->asArray()->all();
        $command = Yii::$app->db->createCommand($sqlRank);
        $giftRank= $command->queryAll();
        $data['userGiftRank'] = CommonService::model()->getAllUserExtInfo($giftRank,'UserBase',true,true,true,'userInfo','fromuid');
        $giftItem = Yii::$app->params['cdb']['GiftItem'];
//         var_dump($data['userGiftRank'][0]);exit;
//         var_dump($giftItem[$data['userGiftRank'][0]['itemid']]);exit;

        return $this->render('profile.twig',$data);
    }

    /**
     * 用户个人信息详情
     */
    public function actionEdit ($id)
    {
        $data = array();
        $data['userBase'] = UserBase::findOne(['uid'=>$id]);
        $data['userAli'] = UserAliaccount::findOne(['uid'=>$id]);
        $data['userPay'] = UserPay::findOne(['uid'=>$id]);
        return $this->render('profile.twig',$data);
    }

    /**
     * 修改用户密码
     */
    public function actionSetpasswd ($uid)
    {
        $data = array();
        $data['user'] = $userAuth = UserBase::findOne(['uid'=>$uid]);
        $data['userAuth'] = $userAuth = Auth::findOne(['uid'=>$uid]);
        $passwd = Yii::$app->request->post('passwd');
        $repasswd = Yii::$app->request->post('repasswd');
        if(!empty($passwd)  && !empty($userAuth) && $passwd==$repasswd ){
            $userAuth->password=md5($passwd.'baby');
            if($userAuth->save())
                return ApiService::reMessg('密码修改成功！',3,'/users/user/profile?id='.$uid);
            return ApiService::reMessg('密码修改失败！');
        }
        return $this->render('setpasswd.twig',$data);
    }

    /**
     * 用户视频管理
     */
    public function actionVideomanage ($uid)
    {
        $data = array();
        $data['user'] = UserBase::findOne(['uid'=>$uid]);
        $data['videos'] = UserVideo::find()->where(['uid'=>$uid])->orderBy('id desc')->asArray()->all();
        return $this->render('videomanage.twig',$data);

    }

    /**
     * 用户照片管理
     */
    public function actionPhotomanage ($uid)
    {
        $data = array();
        $data['user'] = UserBase::findOne(['uid'=>$uid]);
        $data['photos'] = UserPhoto::find()->where(['uid'=>$uid])->orderBy('id desc')->asArray()->all();
        return $this->render('photomanage.twig',$data);

    }



    //获取站内信类型
    public function actionSelectmessage(){
        $request=Yii::$app->request;
        if($request->isPost){
            $data=ManagementMessageService::model()->findStationmsgtype(['type'=>'0']);
            return ManagementMessageService::model()->ajaxData('','',$data);
        }
    }

    //根据站类类型  查询类型内容
    public function actionChangemessage(){
        $request=Yii::$app->request;
        if($request->isPost){
            $post=FunctionsUtil::parseData($request->post());
            $data=ManagementMessageService::model()->getOneMessagetype($post);;
            return ManagementMessageService::model()->ajaxData('','',$data);
        }
    }

    //个人用户短信  正式发送站内信
    public function actionSendmessage(){
        $request=Yii::$app->request;
        if($request->isPost){            
            $post=FunctionsUtil::parseData($request->post());
            $userid=Member::findOne(['id'=>$post['uid']]);
            
            $post['type']='0';  //表示用户信息
            $post['status']='0';   // 0 表示未读 1 表示已读  2 表示不未读也不已读
            $post['adminid']=(string)Yii::$app->user->id;    //站内信操作员的id
            $post['receiver']=$userid['id'];
            $post['title']='通知';
            $post['sendtime']=time();   //站内信发送时间
            $data=ManagementMessageService::model()->sendStationmsg($post);
            if($data){ return ManagementMessageService::model()->ajaxData(0,'站内信发送成功'); }
            return ManagementMessageService::model()->ajaxData(1,'站内信发送失败');
        }
    }

    /*
     * 用户详情页
     */
    public function actionUserinfo()
    {
        $get = FunctionsUtil::parseData(Yii::$app->request->get());
        $data = UserService::model()->getUserInfo($get['uid']);
        return $this->render('userinfo.twig', $data);
    }
    public function actionUseredit(){
        $data=array();
        $datas=array();
        if(Yii::$app->request->isGet){
            $id=Yii::$app->request->get();
            //$data=Member::findOne($id);
              $data=Member::find()->select('*')->where(['id'=>$id])->one();
              $datas=MemberProfile::find()->select('*')->where(['uid'=>$id])->one();
       }elseif(Yii::$app->request->isPost){
                $model=new Member;
                $models=new MemberProfile;
                $id=Yii::$app->request->post('id');
                $username=Yii::$app->request->post('username');
                $phone=Yii::$app->request->post('phone');
                $email=Yii::$app->request->post('email');
                $nickname=Yii::$app->request->post('nickname');
                $realname=Yii::$app->request->post('realname');
                $level=Yii::$app->request->post('level');
                $qq=Yii::$app->request->post('qq');
                $tel=Yii::$app->request->post('tel');
                $khcs=Yii::$app->request->post('khcs');
                $idcard=Yii::$app->request->post('idcard');
                $bank_card=Yii::$app->request->post('bank_card');
                $bank_person=Yii::$app->request->post('bank_person');
                $vitality=Yii::$app->request->post('vitality');
                $agent=Yii::$app->request->post('agent');
                $location=Yii::$app->request->post('location');
                $shouchong=Yii::$app->request->post('shouchong');
                $counts=$models->updateAll(array('qq'=>$qq,'tel'=>$tel,'khcs'=>$khcs,'idcard'=>$idcard,'bank_card'=>$bank_card,'bank_person'=>$bank_person,'vitality'=>$vitality,'agent'=>$agent,'location'=>$location,'shouchong'=>$shouchong),array('uid'=>$id));
                $count=$model->updateAll(array('username'=>$username,'phone'=>$phone,'email'=>$email,'nickname'=>$nickname,'realname'=>$realname,'level'=>$level),array('id'=>$id));
                if($count>0&&$counts>0){
                    echo"编辑失败";
                }else{
                    return $this->redirect('unlocked-user-list');
                }
            }
          $uid=Yii::$app->request->get();
        //var_dump($data);die();
        return $this->render('useredit.twig',['data'=>$data,'uid'=>$uid,'datas'=>$datas]);
    }
    /*
     * 修改key
     */
    public function actionModikey()
    {
    
        return $this->render('modikey.twig');
    }
    public function actionDomodikey()
    {
        $post = FunctionsUtil::parseData(Yii::$app->request->post());
        $operator=Yii::$app->user->identity;
        $data['operator_id']=$operator->id;
        $data['operator_realname']=$operator->realname;
        $data['uid']=$post['uid'];
        $data['key']=$post['key'];
        $data['time']=time();
        $mem=Member::findOne(['id'=>(int)$post['uid']]);
        $mem->key=$post['key'];
        if ($mem->save()){
            MongoService::model()->insert('debug_authkey_edit', $data);
            return FunctionsUtil::ajaxReturn(0,'修改成功');
        }else{
            return FunctionsUtil::ajaxReturn(1,'修改失败');
        }
    
    }
    //员工列表
    public function actionStafflist(){
        $data=MongoService::model()->getAllPage('staff_list',10);
        return $this->render('stafflist.twig',$data);
    }
    //添加员工
    public function actionAddstaff(){
        $data=MongoService::model()->getAllPage('staff_list',10);
        return $this->render('stafflist.twig',$data);
    }
}