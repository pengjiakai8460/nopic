<?php

namespace common\models\orm;

use Yii;

/**
 * This is the model class for table "xm_compose".
 *
 * @property int $id 主键自增
 * @property int $user_id 用户/学生id
 * @property string $title 作品标题
 * @property string $img 封面
 * @property string $file sb2文件
 * @property string $description 描述
 * @property int $status 状态：-1删除 0禁用 1已审核
 * @property int $admin_id 审核人员id
 * @property int $is_public 是否对外公开,1公开0不公开
 * @property int $is_home 是否在首页显示：0否，1是，默认0
 * @property int $is_ssue 是否 发布 1发布 0 不发布
 * @property int $mobile_support  手机模式 0不支持手机模式 1手机模式 2 3 4
 * @property string $mobile_notice 操作说明
 * @property int $add_time 添加时间
 * @property int $update_time 更新时间
 * @property int $release_time 作品发布时间
 * @property int $type 作品类型 1课堂案例 2课后作业 0课外作品
 * @property int $xmcgi_id 单课ID，对应单课的ID，默认为0
 * @property int $page_view 浏览量
 * @property int $like_count 点赞数
 * @property int $comment_count 评论数
 */
class XmCompose extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xm_compose';
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
            [['user_id', 'title', 'img', 'file', 'add_time', 'update_time', 'type'], 'required'],
            [['user_id', 'status', 'admin_id', 'is_public', 'is_home', 'is_ssue', 'mobile_support', 'add_time', 'update_time', 'release_time', 'type', 'xmcgi_id', 'page_view', 'like_count', 'comment_count'], 'integer'],
            [['description'], 'string'],
            [['title'], 'string', 'max' => 200],
            [['img', 'file'], 'string', 'max' => 2000],
            [['mobile_notice'], 'string', 'max' => 250],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'title' => 'Title',
            'img' => 'Img',
            'file' => 'File',
            'description' => 'Description',
            'status' => 'Status',
            'admin_id' => 'Admin ID',
            'is_public' => 'Is Public',
            'is_home' => 'Is Home',
            'is_ssue' => 'Is Ssue',
            'mobile_support' => 'Mobile Support',
            'mobile_notice' => 'Mobile Notice',
            'add_time' => 'Add Time',
            'update_time' => 'Update Time',
            'release_time' => 'Release Time',
            'type' => 'Type',
            'xmcgi_id' => 'Xmcgi ID',
            'page_view' => 'Page View',
            'like_count' => 'Like Count',
            'comment_count' => 'Comment Count',
        ];
    }
}
