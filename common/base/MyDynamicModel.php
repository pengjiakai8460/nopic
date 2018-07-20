<?php
/**
 * Created by PhpStorm.

 * Date: 2017/9/19
 * Time: 8:46
 */

namespace common\base;


use yii\base\DynamicModel;
use yii\base\UnknownPropertyException;

/**
 * EXTENDS FROM YII BASE DYNAMIC MODEL
 */
class MyDynamicModel extends DynamicModel
{
    public $targetAttribute;
    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        try {
            return parent::__get($name);
        } catch (UnknownPropertyException $exception) {
            return null;
        }
    }

    public function __set($name, $value)
    {
        try {
            return parent::__set($name, $value);
        } catch (UnknownPropertyException $exception) {
            $this->defineAttribute($name, $value);
        }
    }

    public function required_with($attribute, $params)
    {
        foreach ($params as $key => $param) {
            if (is_numeric($key)) {
                $name = $param;
                $dependAttributes = $this->getAttributes([$name]);

                if (empty($dependAttributes[$name])) {
                    $attrLabel = $this->getAttributeLabel($attribute);
                    $paramLabel = $this->getAttributeLabel($name);

                    $this->addError($attribute, "{$attrLabel} 依赖的 {$paramLabel} 不存在或者为空！");
                }
            } else {
                $name = $key;
                $dependAttributes = $this->getAttributes([$name]);

                if (is_array($param)) {
                    if (!in_array($dependAttributes[$name], $param)) {
                        $attrLabel = $this->getAttributeLabel($attribute);
                        $paramLabel = $this->getAttributeLabel($name);

                        $this->addError($attribute, "{$attrLabel} 依赖的 {$paramLabel} 的值不在给定范围");
                    }
                } else {
                    if ($param != $dependAttributes[$name]) {
                        $attrLabel = $this->getAttributeLabel($attribute);
                        $paramLabel = $this->getAttributeLabel($name);

                        $this->addError($attribute, "{$attrLabel} 依赖的 {$paramLabel} 的值不等于 {$param}");
                    }
                }
            }
        }
    }

    public function except_dirty_words($attribute, $params)
    {
        unset($params);

        $dependAttributes = $this->getAttributes([$attribute]);
        $value = $dependAttributes[$attribute];

        $dirty_words = file_get_contents(__DIR__ . '/minganci.txt');
        $dirty_words = mb_convert_encoding($dirty_words, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
        $dirty_words = preg_replace("/[\s]+/is", ",", $dirty_words);
        $dirty_words = explode(',', $dirty_words);

        if (isset(array_flip($dirty_words)[$value])) {
            $attrLabel = $this->getAttributeLabel($attribute);

            $this->addError($attribute, "您提交的内容包含敏感词！");
        } else {
            foreach ($dirty_words as $word) {
                if (!empty($word)) {
                    if (preg_match("/{$word}/i", $value, $match)) {
                        $attrLabel = $this->getAttributeLabel($attribute);

                        $this->addError($attribute, "您提交的内容包含敏感词“{$match[0]}”！");
                        break;
                    }
                }
            }
        }
    }
}