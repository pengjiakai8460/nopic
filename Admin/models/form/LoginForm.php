<?php
namespace Admin\models\form;

use common\models\orm\Admin;
use common\models\orm\XmRole;
use common\models\orm\XmRoleUser;
use common\models\orm\XmUser;
use Yii;
use yii\base\Model;
use common\models\orm\User;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;
    public $code;

    private $_user;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, '账号或者密码错误.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        $post=Yii::$app->request->post()['LoginForm'];

        $admin_info = XmUser::findOne(['account'=>$post['username']]);

        if (empty($admin_info)) {
            $this->addError('username', '用户不存在');
            return false;
        }

        if ($admin_info->password != md5($post['password'])) {
            $this->addError('password', '账号或者密码错误');
            return false;
        }

        if ($admin_info->type_id != 9) {
            $userRole = XmRoleUser::find()->where(['user_id' => $admin_info->id])->asArray()->all();
            if (empty($userRole)) {
                $this->addError('username', '无权访问,如需访问请联系郭老师~~');
                return false;
            }
            $roles = array_column($userRole, 'role_id');
            $role = XmRole::find()->where(['name' => 'NOIP系统'])->asArray()->one();
            if (!empty($role)) {
                if (!in_array($role['id'], $roles)) {
                    $this->addError('username', '无权访问,如需访问请联系郭老师~~');
                    return false;
                }
            }
        }
        $_SESSION['uid'] = $admin_info->id;
        $_SESSION['username'] = $admin_info->account;
        $_SESSION['realname'] = $admin_info->nickname;
        $_SESSION['nickname'] = $admin_info->nickname;
        return true;

        //return Yii::$app->user->login($this->getUser($post['username']), $this->rememberMe ? 3600 * 24 : 0);

    }


    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    protected function getUser($username)
    {
        if ($this->_user === null) {
            $this->_user = User::findByUsername($username);
        }

        return $this->_user;
    }
}
