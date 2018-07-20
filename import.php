<?php
/**
 * Created by PhpStorm.
 * User: king
 * Date: 2018/4/18
 * Time: 上午9:53
 */

$type = [
    '选择题'          => 1,
    '问题求解'        => 2,
    '阅读程序写结果'   => 3,
    '完善程序'        => 4
];

$dbms='mysql';     //数据库类型
$host='127.0.0.1'; //数据库主机名
$dbName='test';    //使用的数据库
$user='root';      //数据库连接用户名
$pass='123456';          //对应的密码
$dsn="$dbms:host=$host;dbname=$dbName";

//取文件夹下面所有文件
function my_dir($dir) {
    $files = array();
    if(@$handle = opendir($dir)) { //注意这里要加一个@，不然会有warning错误提示：）
        while(($file = readdir($handle)) !== false) {
            if($file != ".." && $file != ".") { //排除根目录；
                if(is_dir($dir."/".$file)) { //如果是子文件夹，就进行递归
                    $files[$file] = my_dir($dir."/".$file);
                } else { //不然就将文件的名字存入数组；
                    $files[] = $file;
                }

            }
        }
        closedir($handle);
        return $files;
    }
}

//$db = new PDO($dsn, $user, $pass, array(PDO::ATTR_PERSISTENT => true));
//$sql = "insert into xm_c_testlets(`title`, `subtitle`, `day`, `type`, `status`, `add_time`, `update_time`) VALUES
//                                (1, 1, 1, 1, 1, 1, 1) ";
//$db->exec($sql);
//echo $db->lastInsertId();exit;

//读取题目
function import($file, $type) {
    $dbms='mysql';     //数据库类型
    $host='127.0.0.1'; //数据库主机名
    $dbName='test';    //使用的数据库
    $user='root';      //数据库连接用户名
    $pass='123456';          //对应的密码
    $dsn="$dbms:host=$host;dbname=$dbName";
    $db = new PDO($dsn, $user, $pass, array(PDO::ATTR_PERSISTENT => true, PDO::MYSQL_ATTR_INIT_COMMAND => "set names utf8"));
    if (is_array($file)) {
        foreach ($file as $k => $v) {
            $fname = "./question/" . $v;
            $f = file_get_contents($fname);
            preg_match("/\d+/", $v, $matchs);
            $year = $matchs[0];
            $ti = time();
            $uid = 3179;

            //先添加试卷
            $exam_title = "小码王NOIP真题" . $year;
            $exam_sql = "insert into xm_c_exam(title, `type`, `year`, complexity, all_score, adder_id, task_date, all_times, add_time, update_time, `status`)
            VALUES('{$exam_title}', 1, {$year}, 4, 100, {$uid}, 0, 0, {$ti}, {$ti}, 1)";
            $db->exec($exam_sql);

            $exam_id = $db->lastInsertId();

            echo $exam_title;exit;

            $questions = json_decode($f, true);
            if (!empty($questions)) {
                foreach ($questions as $m => $n) {
                    $type_id = $type[$m];
                    //添加题组
                    $testlets_sql = "insert into xm_c_testlets(`title`, `subtitle`, `day`, `type`, `status`, `add_time`, `update_time`) 
                        values('{$m}', '{$m}', 0, 0, 1, {$ti}, {$ti})";
                    $db->exec($testlets_sql);
                    $t_id = $db->lastInsertId();

                    //添加试卷和题组关联表
                    $e_t_sql = "insert into xm_c_exam_testlets(`e_id`, `t_id`, `sort`, `adder_id`, `status`, `add_time`, `update_time`)
                      VALUES({$exam_id}, {$t_id}, {$type_id}, {$uid}, 1, {$ti}, {$ti})";
                    $db->exec($e_t_sql);

                    //添加题目
                    foreach ($n as $i => $j) {
                        $q_title = trim(str_replace($i . ".  ", '', $j['problem']));
                        $new_con = 


                    }


                }

            }


            exit;

        }


    } else {
        return false;
    }

}

$dirs = my_dir("./question/");
import($dirs, $type);
print_r($dirs);