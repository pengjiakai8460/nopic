<?php
namespace SLApi\models\form;

use Yii;
use yii\base\Model;
use common\models\orm\XmBUser;
use SLApi\services\v1\RedisService;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $account;
    public $password;
    public $school_id;
    public $account_type;

    private $_user;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['account', 'password', 'school_id', 'account_type'], 'required'],
            //['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    /*public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, '账号或者密码错误.');
            }
        }
    }*/

    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {        
        $this->getUser();
        if (empty($this->_user)) {
            $this->addError('account', '用户不存在');
            return false;
        }

        if ($this->_user->password != md5($this->password)) {
            $this->addError('password', '账号或者密码错误');
            return false;
        }
        
        if ($this->_user->status != 1) {
            $this->addError('status', '该用户被禁用');
            return false;
        }
        
        if ($this->getUser()) {
            $accessToken = $this->_user->generateAccessToken();    
            $this->_user->last_login = time();
            $return = [
                'token'=> [
                    'token' =>$accessToken,
                ],
                'user'=> [
                    'uid' => $this->_user->id,
                    'school_id' => $this->_user->school_id,
                    'role' => $this->__getTypeText($this->_user->account_type),
                    'fullname' => $this->_user->name,
                    'avatar' => $this->_user->headimg,
                ],
            ];
            $value = json_encode($return);
            //$this->_user->save();
            return $return;
        }
    }

    private function __getTypeText($type) {
        switch ($type) {
            case constant('SCHOOL_TYPE'):
                return 'school';
                break;
            case constant('TEACHER_TYPE'):
                return 'teacher';
                break;
            default:
                return 'student';
        }
    }
    

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = XmBUser::findByUsername($this->account, $this->school_id, $this->account_type);
        }

        return $this->_user;
    }
}
