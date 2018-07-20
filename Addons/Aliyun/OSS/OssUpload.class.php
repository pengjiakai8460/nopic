<?php
/**
 * Created by PhpStorm.
 * User: chenhc
 * Date: 2016/12/23
 * Time: 下午6:24
 */

namespace Addons\Aliyun\OSS;
//require_once __DIR__ . '/SDK2/OssClient.class.php';
use Addons\Aliyun\OSS\SDK2\OssClient;

/**
 * 专门的OSS 上传类
 *
 * @author chc
 *
 */
class OssUpload {
    private $rootPath;
    // 上传文件根目录
    private $_AccessKeyId = '';
    // OSS用户
    private $_AccessKeySecret = '';
    // OSS密码
    private $_Bucket = '';
    // 空间名称
    private $_Endpoint = '';
    // 节点
    private $_domian = true;
    // OSS空间路径
    private $_Request_Uri;
    // OSS的内网请求地址
    private $_Request_Url;
    // OSS的外网请求地址
    private $error = '';
    // 上传错误信息
    private $_client;
    // OSS实例化
    public function __construct($config) {
        $this->_AccessKeyId = $config ['AccessKeyId'];
        $this->_AccessKeySecret = $config ['AccessKeySecret'];
        $this->_Bucket = $config ['Bucket'];
        $this->_Endpoint = $config ['Endpoint'];
        $this->_Request_Uri = $config ['Request_Uri'];
        $this->_Request_Url = $config ['Request_Url'];

        $this->_set_oss_client ();
    }

    /**
     * 实例化OSS
     */
    protected function _set_oss_client() {
        $this->_client = new OssClient( $this->_AccessKeyId, $this->_AccessKeySecret, $this->_Endpoint );
        return $this->_client;
    }

    /**
     * 获取OSS实例
     *
     * @return \ALIOSS
     */
    public function get_oss_client() {
        return $this->_client;
    }

    /**
     * 检测上传根目录(阿里云的根目录就是Bucket下的目录，直接返回)
     *
     * @param string $rootpath
     *        	根目录
     * @return boolean true-检测通过，false-检测失败
     */
    public function checkRootPath($rootpath = '') {
        return true;
    }

    /**
     * 检测上传目录(如果没有直接创建)
     *
     * @param string $savepath
     *        	上传目录
     * @return boolean 检测结果，true-通过，false-失败
     */
    public function checkSavePath($savepath = '') {
        return true; // 阿里云支持自动创建文件夹
        $result = $this->_client->is_object_exist ( $this->_Bucket, $savepath );
        if (200 == $result->status) { // 存在
            return true;
        } else {
            // 创建
            $savepath = rtrim ( $savepath, '/' ); // sdk会自动在后面加'/',所以去掉目录的最后的'/'
            $result = $this->_client->create_object_dir ( $this->_Bucket, $savepath );
            if (200 == $result->status) { // 成功
                return true;
            } else {
                $this->error = '上传目录不存在，并且创建失败';
                return false;
            }
        }
    }

    /**
     * 创建文件夹 (如果没有子目录直接生成子目录)
     *
     * @param string $savepath
     *        	目录名称
     * @return boolean true-创建成功，false-创建失败
     */
    public function mkdir($savepath) {
        return true; // /阿里云支持自动创建文件夹
        // 创建子目录
        $savepath = rtrim ( $savepath, '/' );
        $result = $this->_client->create_object_dir ( $this->_Bucket, $savepath );
        if ($result->status === 200) { // 成功
            return true;
        } else {
            $this->error = '子目录创建失败';
            return false;
        }
    }

    /**
     * 保存指定文件
     *
     * @param array $file
     *        	保存的文件信息
     * @param boolean $replace
     *        	同名文件是否覆盖
     * @return boolean 保存状态，true-成功，false-失败
     */
    public function save(&$file, $replace = false) {
        $file ['name'] = $file ['savepath'] . $file ['savename'];
        // 判断上传文件的大小
        if ($file ['size'] <= (20 * 1024 * 1024)) { // 小于2M用upload_file_by_content方法
            $options ['length'] = $file ['size'];
            $content = file_get_contents ( $file ['tmp_name'] );
            $result = $this->_client->putObject ( $this->_Bucket, $file ['name'], $content ,$options );
        } else if (($file ['size'] > (2 * 1024 * 1024)) && ($file ['size'] < (100 * 1024 * 1024))) { // 小于2M~100M用upload_file_by_content方法
            $options = array ();
            $result = $this->_client->uploadFile ( $this->_Bucket, $file ['name'], $file ['tmp_name'], $options );
        } else { // 大于100M使用part的上传方法
            $result = true;
        }
        if ('200' == $result['info']['http_code']) {
            return true;
        } else {
            $this->error = '上传失败';
            return false;
        }
    }

    /**
     * 获取最后一次上传错误信息
     *
     * @return string 错误信息
     */
    public function getError() {
        return $this->_client->errorStr;
    }
}