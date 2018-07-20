<?php

namespace Admin\service;

use Admin\service\BaseService;
use common\models\orm\Member;

/**
 * Content 内容管理
 */
class UsersManageService extends BaseService{

	/**
     * 初始化，每个Service都必须执行此方法
     * @param string $className
     * @return UsersManageService
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

/*=========================================================================
||===========================     广告管理       =============================||
=========================================================================*/

	/**
	 * 查询符合条件的用户基本信息
	 * @param      int      $pagestart 分页编号，基于零开始
	 * @param      array    $islock    查询条件，包括 islock 和 keywork
	 * @param      int      $pageLength 分页长度
     * @param $or
     * @param $type
     * @param $order
	 * @return     bool                成功 true , 失败 false
	 */
	public static function selectUsers($pagestart = 0, $islock, $pageLength = 15 ,$or = null,$order=null,$type=0)
	{
	    //@todo ,m.epay,m.point 数据表修改了，需要修改这里的代码
       //$sqlQueryUserInfo = 'SELECT m.id,m.username,m.realname,m.phone,m.created,m.islock,p.email FROM member AS m LEFT JOIN member_profile AS p ON m.id=p.uid ';
       $sqlQueryUserInfo = 'SELECT m.id,m.username,m.nickname,m.login_time,m.realname,m.phone,m.created,m.islock,p.email,p.qq,p.idcard,p.from,w.money,w.point FROM member AS m LEFT JOIN member_profile AS p ON m.id=p.uid ';
        $sqlQueryUserInfo .= 'LEFT JOIN member_wealth AS w ON m.id=w.uid ';


        $params = [':start' => $pagestart * $pageLength, ':pageLength' => $pageLength];

        $sqlQueryUserInfo .= ' WHERE m.islock = :islock ';
        $params[':islock'] = $islock ? 1 : 0;

		if ($type==0&&$or){
			$sqlQueryUserInfo .= " AND (m.id like '%".$or."%'";
			$sqlQueryUserInfo .= " OR m.username like '%".$or."%'";
			$sqlQueryUserInfo .= " OR m.nickname like '%".$or."%'";
			$sqlQueryUserInfo .= " OR m.phone like '%".$or."%'";
			$sqlQueryUserInfo .= " OR p.email like '%".$or."%')";
		}

		if($type==1&&$or){
		    //uid精确查询
		    $sqlQueryUserInfo .=" AND m.id=$or";
		}

		if($type==2&&$or){
		    //username精确查询
		    $sqlQueryUserInfo .=" AND (m.username='$or' OR m.nickname='$or')";
		}

        if ($type == 3 && $or) {
            //username精确查询
            $sqlQueryUserInfo .=" AND m.phone='$or'";
        }

		if($order==2){//1升序 2降序
		    $sqlQueryUserInfo .= " ORDER BY w.money desc limit :start,:pageLength ";
		}
		elseif($order==1){
		    $sqlQueryUserInfo .= " ORDER BY w.money asc limit :start,:pageLength ";
		}
		else{
		    $sqlQueryUserInfo .= ' ORDER BY m.id desc limit :start,:pageLength ';
		}

        $mempro = \Yii::$app->db->createCommand($sqlQueryUserInfo)->bindValues($params)->queryAll();
//         var_dump($mempro);exit;

	    return $mempro;
	}

	/**
	 * 获取会员总数
	 * @param      bool            false获取所有正常会员，true获取所有被封停会员,null获取所有用户
	 * @return     int             返回特定会员总数总数
	 */
	public static function getCount($islock,$or = null,$type=0)
	{
	    $sqlQueryUserTotal = 'SELECT COUNT(*) AS total FROM member AS m LEFT JOIN member_profile AS p ON m.id=p.uid';

        $islock = $islock ? 1 : 0;
        $sqlQueryUserTotal .= " WHERE islock = {$islock} ";

		if ($or&&$type==0){
			$sqlQueryUserTotal .= " AND (m.id like '%".$or."%'";
			$sqlQueryUserTotal .= " OR m.username like '%".$or."%'";
			$sqlQueryUserTotal .= " OR m.nickname like '%".$or."%'";
			$sqlQueryUserTotal .= " OR m.phone like '%".$or."%'";
			$sqlQueryUserTotal .= " OR p.email like '%".$or."%')";
		}

		if($type==1&&$or){
		    $sqlQueryUserTotal .= " AND id=$or";
		}

		if($type==2&&$or){
		    $sqlQueryUserTotal .= " AND username='$or'";
		}

        if ($type == 3 && $or) {
            //username精确查询
            $sqlQueryUserTotal .=" AND m.phone='$or'";
        }

	    $result = \Yii::$app->db->createCommand($sqlQueryUserTotal)->queryOne();
	    return $result['total'];
	}

	/**
	 * 获取所有记录总数
	 * @return     int              返回记录总数
	 */


}