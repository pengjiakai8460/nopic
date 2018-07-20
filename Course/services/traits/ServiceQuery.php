<?php

namespace Course\services\traits;

use yii\db\ActiveQuery;

trait ServiceQuery
{
    protected static function commonQuery(ActiveQuery $query, $relations, $fields, $conditions = [], $sort = [])
    {
        $query = self::querySelect($query, $fields);
        $query = self::queryCondition($query, $conditions);
        $query = self::queryRelation($query, $relations);

        //$sort = ['id' => 'SORT_ASC', 'name' => 'SORT_DESC']
        $query = self::querySort($query, $sort);

        return $query;
    }

    protected static function querySelect($query, $fields){
        return $query->select($fields ? $fields : ['*']);
    }

    protected static function queryCondition($query, $conditions){
        if ($conditions && is_array($conditions) && (count($conditions) > 0)) {
            foreach ($conditions as $condition) {
                $query = $query->andWhere($condition);
            }
        }
        return $query;
    }

    protected static function queryRelation($query, $relations){
        if ($relations){
            if (count($relations) > 1) {
                //[['relation'], ['relation' => callback(){}]]
                foreach ($relations as $relation) {
                    $query = self::queryWith($query, $relation);
                }
            } else {
                //['relation']  或 ['relation' => callback(){}]
                $query = self::queryWith($query, $relations);
            }
        }
        return $query;
    }

    protected static function queryWith(ActiveQuery $query, $relation)
    {
        if (is_object(array_values($relation)[0])) {
            $query = $query->with([array_keys($relation)[0] => array_values($relation)[0]]);
        } else {
            $query = $query->with(array_keys($relation)[0]);
        }

        return $query;
    }

    protected static function querySort($query, $sort)
    {
        if (is_array($sort) && (count($sort) > 0)) {
            $query = $query->addOrderBy($sort);
        }

        return $query;
    }

}