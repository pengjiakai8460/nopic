<?php

namespace Addons\Aliyun\OSS;
use Addons\Aliyun\OSS\SDK2\OssClient;
/**
 *
 * @author s Your Name (you@example.org)
 *         @date 2014-07-11 13:42:25
 * @version $Id$
 */
class AliyunOSS {

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

	private $_Request_Uri;
	// OSS的内网请求地址
	private $_Request_Url;
	// OSS的外网请求地址
	private $_client;
	// OSS初始化的对象
	private $error = '';
	// 上传错误返回的信息
	public $savepath = '';
	// 上传到OSS中的路径（相对于该Bucket根）OSS支持自动创建文件夹,默认为该Bucket的根目录下
	public $saveRule = 'uniqid';
	// 上传文件命名规则，默认使用uniqid方法
	public $uploadReplace = false;
	// 同名文件是否覆盖，默认不覆盖
	public $allowExts = array ();
	// 允许上传的文件后缀 留空不作后缀检查
	public $allowTypes = array ();
	// 允许上传的文件类型 留空不做检查
	public $maxSize;
	// 文件上传的最大限制
	private $uploadFileInfo;
	// 上传成功的文件信息
	public $autoCheck = true;
	// 是否开启自动检测
	
	/**
	 * 构造函数，用于设置上传根路径
	 *
	 * @param array $config
	 *        	配置
	 */
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
	 * 手动更改默认的Bucket
	 *
	 * @param unknown_type $newBucket        	
	 * @return unknown
	 */
	public function resetBucket($newBucket) {
		return $this->config ['Bucket'] = $newBucket;
	}
	
	/**
	 * 保存指定文件
	 *
	 * @param array $file
	 *        	保存的文件信息
	 *        	注：$file为一维数组，表须包含的键：savepath、savename、tmp_name、size
	 * @param boolean $replace
	 *        	同名文件是否覆盖
	 * @return boolean 保存状态，true-成功，false-失败
	 *         $bucket默认为空，为空时取初始化时的Bucket
	 */
	private function save($file, $replace = false, $bucket = '') {
		// var_dump($file);
		// return false;
		if (empty ( $bucket ))
			$bucket = $this->config ['Bucket'];
		$filename = $file ['savepath'] . $file ['savename']; // 保存到OSS上的路径+文件名
		$this->uploadReplace = $replace;
		if (! $this->uploadReplace) { // 是否覆盖
		                              // 不覆盖同名文件
			$is = $this->getIsObject ( $filename ); // 捕获获取文件的异常，有异常说明文件不存在;
			if ($is) { // 无异常说明文件已经存在
				$this->error = '文件已经存在！';
				return false;
			}
		}
		$file_mine = null;
		// 如果是图像文件 检测文件格式
		if (in_array ( strtolower ( $file ['extension'] ), array (
				'gif',
				'jpg',
				'jpeg',
				'bmp',
				'png' 
		) )) {
			$info = getimagesize ( $file ['tmp_name'] );
			if (false === $info || ('gif' == strtolower ( $file ['extension'] ) && empty ( $info ['bits'] ))) {
				$this->error = '非法图像文件';
				return false;
			}
			$file_mine = $info ['mime'];
		} else if (in_array ( strtolower ( $file ['extension'] ), array (
				'swf',
				'pdf',
				'epub',
				'txt',
				'doc',
				'docx',
				'ppt',
				'pptx' 
		) )) {
			// $info = getimagesize($file['tmp_name']);
			
			$file_mine = $file ['type'];
		} else if (in_array ( strtolower ( $file ['extension'] ), array (
				'swf',
				'mp3',
				'mp4',
				'ogg',
				'amv' 
		) )) {
			
			$file_mine = $file ['type'];
		}
		$file_mine = ($file_mine ? $file_mine : 'application/octet-stream');
		$content = fopen ( $file ['tmp_name'], 'r' );
		return $this->execute_request ( $bucket, $filename, $content, $file ['size'], $file_mine );
	}
	public function onlySave($file, $content, $replace = false, $bucket = '') {
		if (empty ( $bucket ))
			$bucket = $this->_Bucket;
		$filename = $file ['savepath'] . $file ['savename']; // 保存到OSS上的路径+文件名
		$this->uploadReplace = $replace;
		if (! $this->uploadReplace) { // 是否覆盖
		                              // 不覆盖同名文件
			$is = $this->getIsObject ( $filename ); // 捕获获取文件的异常，有异常说明文件不存在;
			if ($is) { // 无异常说明文件已经存在
				$this->error = '文件已经存在！';
				return false;
			}
		}
		$ext = $this->getExt ( $filename );
		
		// 如果是图像文件 检测文件格式
		if (in_array ( strtolower ( $ext ), array (
				'gif',
				'jpg',
				'jpeg',
				'bmp',
				'png',
				'swf' 
		) )) {
			$info = getimagesize ( $content );
			if (false === $info || ('gif' == strtolower ( $ext ) && empty ( $info ['bits'] ))) {
				$this->error = '非法图像文件';
				return false;
			}
			$file_mine = $info ['mime'];
		} else {
			$file_mine = $file ['type'];
		}
		$file_mine = ($file_mine ? $file_mine : 'application/octet-stream');
		
		if ($this->execute_request ( $bucket, $filename, $content, $file ['size'], $file_mine )) {
			$this->onlySaveUrl = $this->_Request_Uri . $filename;
			return true;
		}
		return false;
	}
	private function execute_request($bucket, $filename, $content, $length, $mine) {
		try {
			/*
			$this->_client->putObject ( array (
				\Addons\Aliyun\OSS\SDK2\OssClient::BUCKET => $bucket,
				\Addons\Aliyun\OSS\SDK2\OssClient::KEY => $this->autoCharset ( $filename, 'utf-8', 'gbk' ),
				\Addons\Aliyun\OSS\SDK2\OssClient::CONTENT => $content,
				\Addons\Aliyun\OSS\SDK2\OssClient::CONTENT_LENGTH => $length,
				\Addons\Aliyun\OSS\SDK2\OssClient::CONTENT_TYPE => $mine
			) );*/
			$this->_client->putObject ( $bucket , $this->autoCharset ( $filename, 'utf-8', 'gbk' ),$content);
		} catch ( \Addons\Aliyun\OSS\SDK2\Core\OSSException $ex ) {
			$this->error = "OSSException: " . $ex->getErrorCode () . " Message: " . $ex->getMessage ();
			return false;
		} catch ( \ClientException $ex ) {
			$this->error = "ClientExcetpion, Message: " . $ex->getMessage ();
			return false;
		}
		return true;
	}
	
	/**
	 * 上传单个上传字段中的文件 支持多附件
	 *
	 * @access public
	 * @param array $file
	 *        	上传文件信息，一般就直接是$_FILES
	 * @param string $savePath
	 *        	上传文件保存路径
	 * @return string
	 */
	public function uploadOne($file, $savePath = '', $bucket = '', $replace = false, $extension = '') {
		if ($savePath)
			$this->savePath = $savePath;
			// ****存储路径改为 ： /ActionName/年/月/日/文件名 ,更改时间：2015-1-28
		$tcheackarr = explode ( '/', $this->savePath );
		
		if ((count ( $tcheackarr ) < 4) && (! in_array ( 'userfaces', $tcheackarr ))) { // 小于4说明没有年月日 , 头像除外
			$this->savePath = $this->savePath . date ( 'Y' ) . '/' . date ( 'm' ) . '/' . date ( 'd' ) . '/';
		}
		// *****
		
		// 过滤无效的上传
		if (! empty ( $file ['name'] )) {
			$fileArray = array ();
			if (is_array ( $file ['name'] )) {
				$keys = array_keys ( $file );
				$count = count ( $file ['name'] );
				for($i = 0; $i < $count; $i ++) {
					foreach ( $keys as $key )
						$fileArray [$i] [$key] = $file [$key] [$i];
				}
			} else {
				$fileArray [] = $file;
			}
			$info = array ();
			foreach ( $fileArray as $key => $file ) {
				// 登记上传文件的扩展信息
				($extension == '') ? $file ['extension'] = $this->getExt ( $file ['name'] ) : $file ['extension'] = $extension;
				$file ['savepath'] = $this->savePath;
				$file ['savename'] = $this->getSaveName ( $file );
				// 自动检查附件
				if ($this->autoCheck) {
					if (! $this->check ( $file ))
						return false;
				}
				// 保存上传文件
				if (! $this->save ( $file, $replace, $bucket ))
					return false;
				unset ( $file ['tmp_name'], $file ['error'] );
				$file ['url'] = ($this->_Request_Url) . $file ['savepath'] . $file ['savename'];
				$info [] = $file;
				$this->uploadFileInfo = $info;
			}
			// 返回上传的文件信息
			return true;
		} else {
			$this->error = '没有选择上传文件';
			return false;
		}
	}
	
	/**
	 * 取得上传文件的后缀
	 *
	 * @access private
	 * @param string $filename
	 *        	文件名
	 * @return boolean
	 */
	private function getExt($filename) {
		$pathinfo = pathinfo ( $filename );
		return $pathinfo ['extension'];
	}
	
	/**
	 * 根据上传文件命名规则取得保存文件名
	 *
	 * @access private
	 * @param string $filename
	 *        	数据
	 * @return string
	 */
	private function getSaveName($filename) {
		$rule = $this->saveRule;
		if (empty ( $rule )) { // 没有定义命名规则，则保持文件名不变
			$saveName = $filename ['name'];
		} else {
			if (function_exists ( $rule )) {
				// 使用函数生成一个唯一文件标识号
				$saveName = $rule () . "." . $filename ['extension'];
			} else {
				// 使用给定的文件名作为标识号
				$saveName = $rule . "." . $filename ['extension'];
			}
		}
		return $saveName;
	}
	
	/**
	 * 取得上传文件的信息
	 *
	 * @access public
	 * @return array
	 */
	public function getUploadFileInfo() {
		return $this->uploadFileInfo;
	}
	
	/**
	 * 检查上传的文件类型是否合法
	 *
	 * @access private
	 * @param string $type
	 *        	数据
	 * @return boolean
	 */
	private function checkType($type) {
		if (! empty ( $this->allowTypes ))
			return in_array ( strtolower ( $type ), $this->allowTypes );
		return true;
	}
	
	/**
	 * 检查上传的文件后缀是否合法
	 *
	 * @access private
	 * @param string $ext
	 *        	后缀名
	 * @return boolean
	 */
	private function checkExt($ext) {
		if (! $ext) {
			return false;
		}
		if (! empty ( $this->allowExts ))
			return in_array ( strtolower ( $ext ), $this->allowExts, true );
		return true;
	}
	
	/**
	 * 检查文件大小是否合法
	 *
	 * @access private
	 * @param integer $size
	 *        	数据
	 * @return boolean
	 */
	private function checkSize($size) {
		return ! ($size > $this->maxSize) || (- 1 == $this->maxSize);
	}
	
	/**
	 * 检查文件是否非法提交
	 *
	 * @access private
	 * @param string $filename
	 *        	文件名
	 * @return boolean
	 */
	private function checkUpload($filename) {
		// return is_uploaded_file($filename);
		return true;
	}
	
	/**
	 * 检查上传的文件
	 *
	 * @access private
	 * @param array $file
	 *        	文件信息
	 * @return boolean
	 */
	private function check($file) {
		if ($file ['error'] !== 0) {
			// 文件上传失败
			// 捕获错误代码
			$this->error = $file ['error'];
			return false;
		}
		// 文件上传成功，进行自定义规则检查
		// 检查文件大小
		if (! $this->checkSize ( $file ['size'] )) {
			$this->error = '上传文件大小不符！';
			return false;
		}
		
		// 检查文件Mime类型
		if (! $this->checkType ( $file ['type'] )) {
			$this->error = '上传文件MIME类型不允许！';
			return false;
		}
		// 检查文件类型
		if (! $this->checkExt ( $file ['extension'] )) {
			$this->error = '上传文件类型不允许';
			return false;
		}
		
		// 检查是否合法上传
		if (! $this->checkUpload ( $file ['tmp_name'] )) {
			$this->error = '非法上传文件！';
			return false;
		}
		return true;
	}
	
	/**
	 * 返回文件是否存在
	 *
	 * @param unknown_type $filePathAndName        	
	 * @param unknown_type $bucket        	
	 * @return boolean
	 */
	private function getIsObject($filePathAndName, $bucket = '') {
		if (! $bucket)
			$bucket = $this->_Bucket;
		try {
			return $this->_client->doesObjectExist ($bucket, $filePathAndName );
		} catch ( OSSException $ex ) {
			$this->error = "OSSException: " . $ex->getErrorCode () . " Message: " . $ex->getMessage ();
			return false;
		} catch ( ClientException $ex ) {
			$this->error = "ClientExcetpion, Message: " . $ex->getMessage ();
			return false;
		}
		return true;
	}
	public function isObject($filePathAndName, $bucket = '') {
		return $this->getIsObject ( $filePathAndName, $bucket = '' );
	}
	
	/**
	 * 获取一个已经存在的object
	 */
	public function getObject($filePathAndName, $bucket = '') {
		if (! $bucket)
			$bucket = $this->config ['Bucket'];
			// 判断文件是否存在
		if (false == $this->getIsObject ( $filePathAndName )) {
			exit ( $this->throwError () );
		}
		$obj = $this->_client->getObjectMetadata ( array (
				'Bucket' => $bucket,
				'Key' => $filePathAndName 
		) );
		
		if ($obj) {
			var_dump ( $obj );
		}
	}
	
	// 自动转换字符集 支持数组转换
	private function autoCharset($fContents, $from = 'gbk', $to = 'utf-8') {
		$from = strtoupper ( $from ) == 'UTF8' ? 'utf-8' : $from;
		$to = strtoupper ( $to ) == 'UTF8' ? 'utf-8' : $to;
		if (strtoupper ( $from ) === strtoupper ( $to ) || empty ( $fContents ) || (is_scalar ( $fContents ) && ! is_string ( $fContents ))) {
			// 如果编码相同或者非字符串标量则不转换
			return $fContents;
		}
		if (function_exists ( 'mb_convert_encoding' )) {
			return mb_convert_encoding ( $fContents, $to, $from );
		} elseif (function_exists ( 'iconv' )) {
			return iconv ( $from, $to, $fContents );
		} else {
			return $fContents;
		}
	}
	
	/**
	 * 删除一个对象
	 */
	public function deleteObject($filePathAndName, $bucket = '') {
		if (! $bucket)
			$bucket = $this->_Bucket;
		$is = $this->getIsObject ( $filePathAndName,$bucket );
		if (! $is) {
			$this->error = '文件不存在';
			return false;
		} else {
			$obj = $this->_client->deleteObject ($bucket,$filePathAndName);
			return true;
		}
	}
	
	/**
	 * 返回上传的错误信息
	 *
	 * @return string
	 */
	public function throwError() {
		return $this->error;
	}
	
	/**
	 * throwError的别名
	 *
	 * @return string
	 */
	public function getErrorMsg() {
		return $this->throwError ();
	}
	public function getPresignedUrl($name, $time = '') {
		$options ['Bucket'] = $this->config ['Bucket'];
		$options ['Key'] = $name;
		if ($time) {
			$options ['Expires'] = $time;
		} else {
			$options ['Expires'] = new \DateTime ( "+5 minutes" );
		}
		
		return $this->_client->generatePresignedUrl ( $options );
	}
	
	/**
	 * 检测上传根目录(OSS上传时支持自动创建目录，直接返回)
	 *
	 * @param string $rootpath
	 *        	根目录
	 * @return boolean true-检测通过，false-检测失败
	 */
	public function checkRootPath($rootpath) {
		/* 设置根目录 */
		$this->rootPath = trim ( $rootpath, './' ) . '/';
		return true;
	}
	
	/**
	 * 检测上传目录(OSS上传时支持自动创建目录，直接返回)
	 *
	 * @param string $savepath
	 *        	上传目录
	 * @return boolean 检测结果，true-通过，false-失败
	 */
	public function checkSavePath($savepath) {
		return true;
	}
	
	/**
	 * 创建文件夹 (OSS上传时支持自动创建目录，直接返回)
	 *
	 * @param string $savepath
	 *        	目录名称
	 * @return boolean true-创建成功，false-创建失败
	 */
	public function mkdir($savepath) {
		return true;
	}
	
	/**
	 * 获取最后一次上传错误信息
	 *
	 * @return string 错误信息
	 */
	public function getError() {
		return $this->client->errorStr;
	}
}