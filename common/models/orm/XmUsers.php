<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_users".
 *
 * @property int $id
 * @property string $account 用户名
 * @property string $password
 * @property string $name
 * @property string $nickname
 * @property string $phone
 * @property string $email
 * @property int $employee_id 员工账号 默认为0 
 * @property int $sex 性别：1男2女
 * @property int $birthday 生日
 * @property string $qq 用户qq
 * @property string $wechat 用户微信号
 * @property string $intention_level 意向级别：A/B/C/D/E
 * @property string $communtity_name 小区名称
 * @property string $address 地址
 * @property int $school_id 所属学校的id
 * @property int $admission_year 入学年份
 * @property int $xmschool_id 小码校区的id
 * @property int $from_type 来源：对应attr表的userTypeFrom
 * @property int $market_id 归属市场员工的id
 * @property int $market_time 市场人员信息导入时excel里的活动时间
 * @property string $active_place 活动地点
 * @property int $cc_id 归属cc员工的id
 * @property int $recommend_id 推荐人员（本表）的id
 * @property int $main_contacts 主要联系人：1、父亲、2、母亲
 * @property string $father_name 父亲名称
 * @property string $father_phone 父亲手机
 * @property string $father_job 父亲职业
 * @property string $father_qq 父亲QQ
 * @property string $father_email 父亲邮箱
 * @property string $father_wechat 父亲微信
 * @property string $mother_name 母亲名称
 * @property string $mother_phone 母亲手机
 * @property string $mother_job 母亲工作
 * @property string $mother_qq 母亲QQ
 * @property string $mother_email 母亲email
 * @property string $mother_wechat 母亲微信
 * @property string $programe_base 编程程度
 * @property string $is_join_organ 是否参加过社团
 * @property string $study_mark 在学校学习成绩：优秀、中上、一般、较差
 * @property string $school_duty 在学校担任的职务
 * @property string $hobby 业余爱好
 * @property string $play_game 玩游戏程度
 * @property string $transfer_level 接送难度
 * @property string $accept_level 接受难度
 * @property string $love_level 学生喜爱程度
 * @property string $father_love_level 父亲态度
 * @property string $mother_love_level 母亲态度
 * @property string $resist_point 销售拒绝点
 * @property string $remark 备注
 * @property int $is_student 是否是正式学员，0否1是，默认0
 * @property int $adder_id 录入员工id
 * @property int $add_time
 * @property int $update_time 更新时间
 * @property int $allot_cc_time 分配给cc的时间
 * @property int $status 状态：默认1，1为正常0为禁用可恢复，-1为删除
 * @property string $os_from 平台来源
 * @property string $reg_ip 注册ip
 * @property string $wechat_openid 公众平台的微信openid
 * @property string $autograph 个人签名
 * @property int $follow_count 粉丝总数（被关注人数）
 * @property string $avatar_img 头像图片地址
 * @property string $personal_background 个人中心背景图地址
 * @property string $teacher_name 老师名字
 * @property string $openid 微信的openid，在录播课微信端支付时加入，对应的是小码世界教学服务公众号
 * @property int $age 年龄（生日 针对用户自己填的）
 * @property int $province_code 省份code
 * @property int $city_code 市级code
 * @property int $area_code 区域code
 * @property string $wechat_openid_xmjyfw 小码王教学服务微信服务号oppenid
 * @property string $unionid 微信unionid
 */
class XmUsers extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_users';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db2');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['account', 'password', 'name', 'nickname', 'intention_level', 'xmschool_id', 'adder_id', 'add_time', 'update_time'], 'required'],
            [['employee_id', 'sex', 'birthday', 'school_id', 'admission_year', 'xmschool_id', 'from_type', 'market_id', 'market_time', 'cc_id', 'recommend_id', 'main_contacts', 'is_student', 'adder_id', 'add_time', 'update_time', 'allot_cc_time', 'status', 'follow_count', 'age', 'province_code', 'city_code', 'area_code'], 'integer'],
            [['remark'], 'string'],
            [['account'], 'string', 'max' => 40],
            [['password', 'father_name', 'mother_name', 'mother_job', 'school_duty'], 'string', 'max' => 80],
            [['name', 'nickname', 'wechat', 'active_place', 'father_job', 'father_email', 'father_wechat', 'mother_email', 'mother_wechat', 'love_level', 'father_love_level', 'mother_love_level', 'wechat_openid', 'autograph', 'openid', 'unionid'], 'string', 'max' => 200],
            [['phone', 'qq', 'intention_level', 'father_phone', 'father_qq', 'mother_phone', 'mother_qq', 'programe_base', 'study_mark', 'play_game', 'transfer_level', 'teacher_name'], 'string', 'max' => 20],
            [['email'], 'string', 'max' => 150],
            [['communtity_name', 'address', 'accept_level'], 'string', 'max' => 800],
            [['is_join_organ'], 'string', 'max' => 29],
            [['hobby', 'resist_point', 'avatar_img'], 'string', 'max' => 2000],
            [['os_from', 'reg_ip'], 'string', 'max' => 32],
            [['personal_background'], 'string', 'max' => 500],
            [['wechat_openid_xmjyfw'], 'string', 'max' => 50],
            [['account'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'account' => 'Account',
            'password' => 'Password',
            'name' => 'Name',
            'nickname' => 'Nickname',
            'phone' => 'Phone',
            'email' => 'Email',
            'employee_id' => 'Employee ID',
            'sex' => 'Sex',
            'birthday' => 'Birthday',
            'qq' => 'Qq',
            'wechat' => 'Wechat',
            'intention_level' => 'Intention Level',
            'communtity_name' => 'Communtity Name',
            'address' => 'Address',
            'school_id' => 'School ID',
            'admission_year' => 'Admission Year',
            'xmschool_id' => 'Xmschool ID',
            'from_type' => 'From Type',
            'market_id' => 'Market ID',
            'market_time' => 'Market Time',
            'active_place' => 'Active Place',
            'cc_id' => 'Cc ID',
            'recommend_id' => 'Recommend ID',
            'main_contacts' => 'Main Contacts',
            'father_name' => 'Father Name',
            'father_phone' => 'Father Phone',
            'father_job' => 'Father Job',
            'father_qq' => 'Father Qq',
            'father_email' => 'Father Email',
            'father_wechat' => 'Father Wechat',
            'mother_name' => 'Mother Name',
            'mother_phone' => 'Mother Phone',
            'mother_job' => 'Mother Job',
            'mother_qq' => 'Mother Qq',
            'mother_email' => 'Mother Email',
            'mother_wechat' => 'Mother Wechat',
            'programe_base' => 'Programe Base',
            'is_join_organ' => 'Is Join Organ',
            'study_mark' => 'Study Mark',
            'school_duty' => 'School Duty',
            'hobby' => 'Hobby',
            'play_game' => 'Play Game',
            'transfer_level' => 'Transfer Level',
            'accept_level' => 'Accept Level',
            'love_level' => 'Love Level',
            'father_love_level' => 'Father Love Level',
            'mother_love_level' => 'Mother Love Level',
            'resist_point' => 'Resist Point',
            'remark' => 'Remark',
            'is_student' => 'Is Student',
            'adder_id' => 'Adder ID',
            'add_time' => 'Add Time',
            'update_time' => 'Update Time',
            'allot_cc_time' => 'Allot Cc Time',
            'status' => 'Status',
            'os_from' => 'Os From',
            'reg_ip' => 'Reg Ip',
            'wechat_openid' => 'Wechat Openid',
            'autograph' => 'Autograph',
            'follow_count' => 'Follow Count',
            'avatar_img' => 'Avatar Img',
            'personal_background' => 'Personal Background',
            'teacher_name' => 'Teacher Name',
            'openid' => 'Openid',
            'age' => 'Age',
            'province_code' => 'Province Code',
            'city_code' => 'City Code',
            'area_code' => 'Area Code',
            'wechat_openid_xmjyfw' => 'Wechat Openid Xmjyfw',
            'unionid' => 'Unionid',
        ];
    }
}
