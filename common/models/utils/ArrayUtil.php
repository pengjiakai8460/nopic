<?php
namespace common\models\utils;

use yii\base\BaseObject;

class ArrayUtil
{

    /**
     * 获取Yii框架中 AR 获取的数据$model->getAttributes();
     * @param array $data
     * @return array
     * @author zhangzhicheng
     */
    public static function getArraysAttributes($data)
    {
        foreach ($data as $key => $value) {
            $data[$key] = $value->getAttributes();

        }
        return $data;
    }

    public static function getAllUserInfo($userList, $userModelName)
    {
        $userListArray = self::getArraysAttributes($userList);
        $uids = self::pullColumn('uid', $userListArray);
        $inUid = '" (' . implode("','", $uids) . "')";


    }


    //合并2个数组
    public static function mergeArray($array1, $array2)
    {
        if (is_array($array1) && is_array($array2))
            return array_merge($array1, $array2);
        elseif (empty($array1) && is_array($array2))
            return $array2;
        elseif (empty($array2) && is_array($array1))
            return $array1;
        elseif (empty($array2) && empty($array1))
            return null;
        return false;
    }

    //一个数组包含另外一个数组中任一值
    public static function inArray(array $data, array $inData)
    {
        foreach ($data as $key => $value) {
            if (in_array($value, $inData))
                return true;
        }
        return false;
    }

    /**
     * 获取数组中 key=$column 的值组成的一维数组
     * 常用于SQL： WHERE id IN expload(',',$result)
     * @param string $fieldName
     * @param array $items
     * @return array
     * @author zhangzhicheng
     */
    public static function pullColumn($column, array $dataArr)
    {
        $result = array();
        foreach ($dataArr as $item) {
            if (isset($item[$column])) {
                $result[] = $item[$column];
            }
        }
        return $result;
    }

    /**
     * 让数组某个字段值作为一维的key
     * @param string $column
     * @param array $data
     * @return multitype:unknown
     * @author zhangzhicheng
     */
    public static function pullColumnAsKey(array $dataArr, $column = 'id')
    {
        $result = array();
        foreach ($dataArr as $value) {
            $result[$value[$column]] = $value;
        }
        return $result;
    }

    /**
     * 合并指定key的两个二维数组
     * @param array $array1
     * @param array $array2
     * @param string $clum1
     * @param string $clum2
     * @return unknown
     * @author zhangzhicheng
     */
    public static function arrayMergeByClum(array $array1, array $array2, $clum1 = 'uid', $clum2 = 'uid')
    {
        foreach ($array1 as $k => &$arr) {
            foreach ($array2 as $i => $val) {
                if (isset($arr[$clum1]) && isset($val[$clum2]) && $arr[$clum1] == $val[$clum2]) {
                    $arr = array_merge($arr, $val);
                    continue;
                }

            }
        }
        return $array1;
    }


    /**
     * 获取数组1中指定字段 对应数组2中的值
     * @param array $fieldNames
     * @param array $items
     * @author zhangzhicheng
     */
    public static function getValuesByClum(array $data, array $ids, $clum = 'uid', $reClum = 'username')
    {
        $return = array();
        foreach ($data as $value) {
            foreach ($ids as $k => $item) {
                if ($value[$clum] == $item[$clum])
                    $return[$value[$clum]] = $item[$reClum];
            }
        }
        return $return;
    }

    /**
     * 计算多维数组中值二级数组key最多的数量
     * @param unknown $array
     * @return number
     * @author zhangzhicheng
     */
    public static function getMaxClum($array)
    {
        $num = 0;
        foreach ($array as $k => $value) {
            if (is_array($value) && count($value) > $num)
                $num = count($value);
        }
        return $num;
    }

    /**
     * 删除值为空的字段
     * @param array $data
     * @author zhangzhicheng
     */
    public static function removeEmptyClum(array $data)
    {
        foreach ($data as $key => $value) {
            if (empty($value))
                unset($data[$key]);
        }
        return $data;
    }

    public static function group($fieldName, array $items)
    {
        $result = array();
        foreach ($items as $item) {
            if (!isset($result[$item[$fieldName]])) {
                $result[$item[$fieldName]] = array();
                $result[$item[$fieldName]][] = $item;
            } else {
                $result[$item[$fieldName]][] = $item;
            }
        }
        return $result;
    }

    public static function filterColum($fieldNames, array $items)
    {
        $result = array();
        foreach ($items as $item) {
            $item2 = array();
            foreach ($fieldNames as $fieldName) {
                $result[] = $item[$fieldName];
            }
        }
        return $result;
    }

    /**
     * 过滤字段
     * @param array $fieldNames
     * @param array $items
     * @return multitype:multitype:unknown
     * @author zhangzhicheng
     */
    public static function filterColums(array $fieldNames, array $items)
    {
        $result = array();
        foreach ($items as $item) {
            $item2 = array();
            foreach ($fieldNames as $fieldName) {
                $item2[$fieldName] = $item[$fieldName];
            }
            $result[] = $item2;
        }
        return $result;
    }

    public static function filterKeys(array $keys, array $items)
    {
        $result = array();
        foreach ($keys as $key) {
            if (isset($items[$key])) {
                $result[$key] = $items[$key];
            }
        }
        return $result;
    }


    public static function cutArrayByPerNumber($array, $perNumber = 10)
    {
        $number = count($array);
        $all = $number % $perNumber == 0 ? intval($number / $perNumber) : intval($number / $perNumber) + 1;
        $returnArray = array();
        foreach ($array as $item) {
            $key = $number % $perNumber == 0 ? $all - intval($number / $perNumber) : $all - intval($number / $perNumber) - 1;
            $returnArray[$key][] = $item;
            $number--;
        }
        return $returnArray;
    }

    public static function arrayToString($array, $byType = ',')
    {
        $str = '';
        foreach ($array as $item) {
            $str .= $item . $byType;
        }
        $length = mb_strlen($byType, "utf-8");
        $str = substr($str, 0, -$length);
        return $str;
    }


    /**
     * 查询数组,取出数组中指定长度的数据
     * @param array $array
     * @param int $length
     * @param string $field
     * @param $string $value
     * @return array
     **/
    public static function findArrayList($array, $length = null, $field = null, $value = null)
    {
        if (!is_array($array)) {
            return $array;
        }
        $new_arr = array();
        if (!is_null($field)) {
            foreach ($array as $k => $v) {
                if ($array[$k][$field] == $value) {
                    $new_arr[$k] = $v;
                }
            }
            if (empty($new_arr)) {
                return;
            }
        }
        if (empty($new_arr)) {
            $new_arr = $array;
        }
        if (!is_null($length)) {
            $new_arr = array_slice($new_arr, 0, $length);//取出数组中指定的长度
        }
        return $new_arr;
    }

    /**
     * 二维数组排序
     * @param unknown $arr
     * @param unknown $keys
     * @param string $type
     * @return array
     */
    public static function array_sort($arr, $keys, $type = 'asc')
    {
        $keysvalue = $new_array = array();
        foreach ($arr as $k => $v) {
            $keysvalue[$k] = $v[$keys];
        }
        if ($type == 'asc') {
            asort($keysvalue);
        } else {
            arsort($keysvalue);
        }
        reset($keysvalue);
        foreach ($keysvalue as $k => $v) {
            $new_array[] = $arr[$k];
        }
        return $new_array;
    }




    /*$array = array(
        array('name'=>'手机','brand'=>'诺基亚','price'=>1050),
        array('name'=>'笔记本电脑','brand'=>'lenovo','price'=>4300),
        array('name'=>'剃须刀','brand'=>'飞利浦','price'=>3100),
        array('name'=>'跑步机','brand'=>'三和松石','price'=>4900),
        array('name'=>'手表','brand'=>'卡西欧','price'=>960),
        array('name'=>'液晶电视','brand'=>'索尼','price'=>6299),
        array('name'=>'激光打印机','brand'=>'惠普','price'=>1200)
    );

    $ShoppingList = array_sort($array,'price');
    print_r($ShoppingList);*/

    /*
    * @param array $array 要排序的数组
    * @param string $keyname 排序的键
    * @param int $dir 排序方向
    *
    * @return array 排序后的数组
    */
    static function sortByCol($array, $keyname, $sort = SORT_ASC, $start = 0, $limit = 0)
    {
        $data = self::sortByMultiCols($array, array($keyname => $sort));
        if ($start <= 0 && $limit <= 0)
            return $data;
        $num = 0;
        $return = array();
        foreach ($data as $k => $v) {
            if ($num >= $start && $num < ($start + $limit)) {
                $return[] = $v;
            }
            $num++;
        }
        return $return;
    }

    /**
     * 将一个二维数组按照多个列进行排序，类似 SQL 语句中的 ORDER BY
     *
     * 用法：
     * @code php
     * $rows = ArrayUtil::sortByMultiCols($rows, array(
     * 'parent' => SORT_ASC,
     * 'name' => SORT_DESC,
     * ));
     * @endcode
     *
     * @param array $rowset 要排序的数组
     * @param array $args 排序的键
     *
     * @return array 排序后的数组
     */
    static function sortByMultiCols($rowset, $args)
    {
        $sortArray = array();
        $sortRule = '';
        foreach ($args as $sortField => $sortDir) {
            foreach ($rowset as $offset => $row) {
                $sortArray[$sortField][$offset] = $row[$sortField];
            }
            $sortRule .= '$sortArray[\'' . $sortField . '\'], ' . $sortDir . ', ';
        }
        if (empty($sortArray) || empty($sortRule)) {
            return $rowset;
        }
        eval('array_multisort(' . $sortRule . '$rowset);');
        return $rowset;
    }

    /**
     * 将null转换为空字符串
     * @param $arr
     * @return mixed
     */
    public static function parseNull($arr)
    {
        if (is_array($arr)) {
            foreach ($arr as $k => $v) {
                $arr[$k] = self::parseNull($arr[$k]);
            }
        } elseif (is_null($arr)) {
            return '';
        } elseif ($arr === '{}') {
            return new Object();
        }
        return $arr;
    }

    public static function object_array($array) {
        if(is_object($array)) {
            $array = (array)$array;
        } if(is_array($array)) {
            foreach($array as $key=>$value) {
                $array[$key] = self::object_array($value);
            }
        }
        return $array;
    }
}