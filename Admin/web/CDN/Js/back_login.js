$(document).ready(function(e) {
	//验证码刷新
	$('#captcha').bind('click', function(){
		$(this).attr('src', "/sys/default/code?" + Math.random() + ".html");
	});
	//登录验证
	$("#login").validate({
		debug:true,
		onsubmit:false,
		onkeyup:false,
		rules:{
			username:{required:true,rangelength:[4,16]},
			password:{required:true,rangelength:[6,16]},
			yzm:{required:true,rangelength:[4,4]},
		},
		messages:{
			username:{required:"用户名不能为空",rangelength:"用户名请输入4-16个字符"},
			password:{required:"密码不能为空",rangelength:"密码请输入6-16个字符"},
			yzm:{required:"验证码不能为空",rangelength:"验证码格式错误"}
		},
		errorPlacement: function(error, element) {
			error.appendTo( element.parent());
		},
		highlight:function(element){
	  	$(".login_error").html("");
	  }
	});

		//登录提交
    $.ajaxSettings.async = false;
	$('#login').submit(function(){
		if($("#login").valid() === true){
			$.ajax({
				url			:		"/account/default/loginajax",
				type		:		"post",
				data		:		{username:$("#username").val(),password:$("#password").val(),captcha:$("#yzm").val()},
				dataType	:		'json',
				
				success		:		function(data){
					if(data.status == 0){
						alert(data.info);
						window.location.href = "/account/default/index";
					}else{
						$(".login_error").html(data.info);
						$('#captcha').attr('src', "/account/default/code?" + Math.random() + ".html");
					}
				}
			});
		}
		return false;
	});
})