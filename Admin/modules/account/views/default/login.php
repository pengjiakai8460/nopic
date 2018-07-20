<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport">
<title>登录</title>
<link rel="stylesheet" type="text/css" href="/CDN/css/back_common.css" charset="utf-8">
<script type="text/javascript" src="/CDN/Js/jquery.js"></script>
 <style>
        body {
            margin: 50px 0;
            text-align: center;
        }
        .inp {
            border: 1px solid gray;
            padding: 0 10px;
            width: 200px;
            height: 30px;
            font-size: 18px;
        }
        .btn {
            border: 1px solid gray;
            width: 100px;
            height: 30px;
            font-size: 18px;
            cursor: pointer;
        }
        #embed-captcha {
            width: 300px;
            margin: 0 auto;
        }
        .show {
            display: block;
        }
        .hide {
            display: none;
        }
        #notice {
            color: red;
        }
        /* 以下遮罩层为demo.用户可自行设计实现 */
        #mask {
            display: none;
            position: fixed;
            text-align: center;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow: auto;
        }
        /* 可自行设计实现captcha的位置大小 */
        .popup-mobile {
            position: relative;
        }
        #popup-captcha-mobile {
            position: fixed;
            display: none;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            -webkit-transform: translate(-50%, -50%);
            z-index: 9999;
        }
    </style>
</head>
<body>
<div class="login">
  <div class="back_title"><img src="http://www.xiaoma.wang/img/LOGO.png"></div>

<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $form = ActiveForm::begin([ 'class' => 'login-form ', 'method' =>'post','id'=>"login",'action'=>'']); ?>
<div class='back_user back_input clearfix'><label><span></span><img src="/CDN/img/user.png"></label>
<?= $form->field($model, 'username',  [
                                  'template' => "{input}\n{hint}\n{error}"
                                ])->textInput(array('required'=>'required','autocomplete'=>"off",'maxlength'=>"16",'id'=>"username"  ));  ?>
<div>
<br/><br/>
<div class='back_password back_input clearfix'><label><span></span><img src='/CDN/img/psw.png'></label>
<?= $form->field($model, 'password', [
                                  'template' => "{input}\n{hint}\n{error}"
                                ])->passwordInput(array('required'=>'required','autocomplete'=>"off",'maxlength'=>"16",'id'=>"password"  ));  ?>
<div>
<br/><br/>
<div class='login_btn clearfix popup'>
<?= $form->field($model, 'submit', [
                                  'template' => "{input}\n{hint}\n{error}<div>"
                                ])->textInput(array('value'=>'登录','type'=>'submit' ));  ?>
<!--<div id="popup-captcha"></div>-->
</div>

<?php ActiveForm::end(); ?>
</div>
<script type="text/javascript" src="/CDN/Js/jquery.validate.js"></script>
<div class="login_text">Copyright © 2018 XiaoMa Technology</div>


</body>
</html>
