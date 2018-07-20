<?php
/**
 * Created by PhpStorm.

 * Date: 2017/9/18
 * Time: 15:41
 */

namespace common\base;

use Yii;
use yii\base\InlineAction;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class BaseController extends Common
{
    /**
     * format the response data as a json format and return it.
     *
     * @param array $data
     * @param string $format
     * @return array
     */
    final protected function success($data = [], $format = Response::FORMAT_JSON)
    {
        Yii::$app->response->format = $format;
        static::setCode(static::SUCCESS_CODE);
        static::setData($data);

        return self::getResult();
    }

    final protected static function error($errorCode = 0, $message = '')
    {
        $errorCode = intval($errorCode);

        if (!empty($message)) {
            $errorMessage = $message;
        } else {
            $errorMessage = static::$errors[$errorCode] ?? null;

            if (!$errorMessage) {
                return self::setSystemError(self::ERR_UNKNOWN_ERRNO);
            }
        }

        return self::setError($errorCode, $errorMessage);
    }


    final protected function result($code = 200, $message = '', $data = [] ,$format = Response::FORMAT_JSON)
    {
        Yii::$app->response->format = $format;
        static::setCode($code);
        static::setMessage($message);
        static::setData($data);

        return self::getResult();
    }

    /**
     * validate the input data, the function will throw an exception, but you will not to catch it, the app will correct
     * it as default.
     *
     * @param array $data
     * @param array $rules
     * @return bool true if the validate is success
     */
    final protected function validate(array $data, array $rules)
    {
        try {
            $m = MyDynamicModel::validateData($data, $rules);

            if ($m->hasErrors()) {
                $msg = $m->getFirstError(array_keys($m->getErrors())[0]);

                return self::setError(self::ERR_VALIDATE_ERROR, $msg);
            }
        } catch (\Exception $e) {
            if ($e->getCode() < 0) {
                self::setData($e->getTraceAsString());
            }

            return self::setError($e->getCode(), $e->getMessage());
        }

        return true;
    }

    /**
     * overwrite from parent bindActionParams
     *
     * @param \yii\base\Action $action
     * @param array $params
     * @return array the valid parameters that the action can run with.
     * @throws BadRequestHttpException if there are missing or invalid parameters.
     */
    public function bindActionParams($action, $params)
    {
        if ($action instanceof InlineAction) {
            $method = new \ReflectionMethod($this, $action->actionMethod);
        } else {
            $method = new \ReflectionMethod($action, 'run');
        }

        $args = [];
        $missing = [];
        $actionParams = [];
        foreach ($method->getParameters() as $param) {
            $name = $param->getName();
            if (isset($params[$name])) {
                if ($param->isArray()) {
                    $args[] = $actionParams[$name] = (array) $params[$name];
                } elseif (!is_array($params[$name])) {
                    $args[] = $actionParams[$name] = $params[$name];
                } else {
                    throw new BadRequestHttpException(Yii::t('yii', 'Invalid data received for parameter "{param}".', [
                        'param' => $name,
                    ]));
                }
                unset($params[$name]);
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $actionParams[$name] = $param->getDefaultValue();
            } else {
                // 重写父类的判断逻辑，通过类型来判断
                $guessObject = null;
                try {
                    if ($cls = $param->getClass()) {
                        $className = $cls->getName();
                        $guessObject = new $className(null, null);
                    }
                }
                catch (\Exception $exception) {
                    Yii::trace('not extends of \common\base\BaseService');
                }

                // 是从 \common\base\BaseService 继承来的类，处理初始化此参数
                if (!is_null($guessObject) && $guessObject instanceof BaseService) {
                    $args[] = $actionParams[$name] = $guessObject;
                }
                else {
                    unset($guessObject);
                    $missing[] = $name;
                }
            }
        }

        if (!empty($missing)) {
            throw new BadRequestHttpException(Yii::t('yii', 'Missing required parameters: {params}', [
                'params' => implode(', ', $missing),
            ]));
        }

        $this->actionParams = $actionParams;

        return $args;
    }
}