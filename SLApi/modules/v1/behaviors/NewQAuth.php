<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace SLApi\modules\v1\behaviors;

use yii\filters\auth\QueryParamAuth;

class NewQAuth extends QueryParamAuth
{
    /**
     * @var string the parameter name for passing the access token
     */
    public $tokenParam = 'access-token';
    
    
    /**
     * {@inheritdoc}
     */
    public function authenticate($user, $request, $response)
    {
        if ($request->isGet || $request->isDelete) {
            $accessToken = $request->get($this->tokenParam);
        } else {
            $accessToken = $request->post($this->tokenParam);
        }
        if (is_string($accessToken)) {
            $identity = $user->loginByAccessToken($accessToken, get_class($this));
            if ($identity !== null) {
                return $identity;
            }
        }
        if ($accessToken !== null) {
            $this->handleFailure($response);
        }
        
        return null;
    }
}
