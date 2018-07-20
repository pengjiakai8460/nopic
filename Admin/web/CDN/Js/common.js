/**
 * Created by XiaoMa on 2018/2/2.
 */
function formatContainer(container) {

    if (container == undefined)
        container = $('body');

    formatForm(container);
    formatAjax(container);
    formatModal(container);

    $('[action-type]', container).click(function () {
        var cmd = $(this).attr('action-type') + '(' + $(this).attr('action-data') + ')';
        eval(cmd);
    });
}

$(document).ready(function () {
    $(".left_nav div h3.selected").next().show();
    //leftnav收缩
    $(".left_nav div h3").bind("click", function () {
        if ($(this).hasClass("selected")) {
            return false;
        }
        $(".left_nav div h3").removeClass("selected");
        $(".left_nav div ul").slideUp();
        $(this).addClass("selected").next().slideDown();
    })

    $('[data-toggle="offcanvas"]').click(function () {
        $('body').toggleClass('toggled');
        $(".toggled .left_nav>div").click(function (e) {
            window.location = $(this).find('a').attr('href');
           // console.log($(this).find('a').attr('href'));
        })
    });


    formatContainer();
})


function formatForm(container)
{
    $("#apcheckall").bind("click", function () {
        var input = $("input[name='apcheck']");
        var id = document.getElementById("apcheckall");
        var name = document.getElementsByName("apcheck");
        SelectAll(id, name);
        seleall(id, name, input);
    })
    $("#azcheckall").bind("click", function () {
        var input = $("input[name='azcheck']");
        var id = document.getElementById("azcheckall");
        var name = document.getElementsByName("azcheck");
        SelectAll(id, name);
        seleall(id, name, input);
    })


    //如果没有加载验证控件，不处理
    if (jQuery.validator) {
        $("form",container)
            .validate({
                ignore: ".ignore",
                showErrors: function(errorMap, errorList) {
                    for ( i = 0; this.errorList[i]; i++ ) {
                        var msg = this.errorList[i].message;
                        var el = this.errorList[i].element ;
                    }
                    this.defaultShowErrors();
                },
                submitHandler:function(form){
                    if ($(form).hasClass("j-ajax-form")){
                        return ajaxSubmit(form);
                    }else{
                        return true;
                    }
                }
            });
    }
}

function formatAjax(container)
{
    $('.j-ajax-get',container).click(function(){
        var e = $(this);
        var fn = function(){
            var target = ($(e).attr('href')) || (target = $(e).attr('url'));
            var callBack = ($(e).attr('j-ajax-callback')) || (callBack = 'ajaxGetCallBack');
            $.get(target, function(res){
                if (res.code){
                    eval(callBack +'(res,e)');
                }
            });
        }

        if ($(this).hasClass('confirm')) {
            var confirm_info = $(this).attr('confirm-info');
            confirm_info=confirm_info?confirm_info:'确定要执行吗？';
            myconfirm(confirm_info,fn);
        }else{
            fn();
        }
        return false ;
    })
    //j-ajax-form
    //这个在 formatForm中处理
}


function formatModal(container) {

    //模态框
    $('.load-modal', container).attr('onclick', 'return false;');
    $('.load-modal', container).click(function () {
        var url = $(this).attr('href');
        dialog(url);
    });
}

//全选
function SelectAll(id, name) {
    if (id.checked) {
        var names = name;
        for (var i = 0; i < names.length; i++) {
            names[i].checked = "checked";
        }
    } else {
        var names = name;
        for (var i = 0; i < names.length; i++) {
            names[i].checked = "";
        }
    }
}

function seleall(id, name, input) {
    var inputname = name;
    var inputid = id;
    input.bind("click", function () {
        for (i = 0; i < inputname.length; i++) {
            if (inputname[i].checked) {
                inputid.checked = "checked";
            } else {
                inputid.checked = "";
                return;
            }
        }
    })
}


//////////////////////////////////////////////////////////////////////////////////////////////////////////
//ajax 相关
//////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * AJAX表单提交
 * @return 一定要return true/false  true为表单提交 false为不提交(ajax)
 */
function ajaxSubmit(_form){

    var target = $(_form).attr('action');

    //设置提交按钮为加载中状态
    var btn = $('.click',_form).length == 0 ? $('input[type=submit],button[type=submit]',_form) : $('.click',_form);
    if (btn)
        setBtnLoadingStatus(btn);

    var callBack = ($(_form).attr('j-ajax-callback')) || (callBack = 'ajaxFormCallBack');
    var data = $(_form).serialize();

    $.post(target, data,function(res){
        //设置提交按钮为可提交状态
        if (btn)
            setBtnSubmitStatus(btn);

        if (res.code){
            dialogClose();
            eval(callBack +"(res,_form)");
        }else{
            console.log('ajax error');
        }
        return false ;
    });
    return false ;
}

/**
 * 设置按钮加载中状态
 * @param btn
 * @param tip
 */
function setBtnLoadingStatus(btn,tip)
{
    if (tip == undefined)
        tip = '加载中..' ;
    currBtn = btn;
    if ($(btn).is("button"))
        $(btn).attr("data-name",$(btn).html()).attr("disabled",true).html(tip);
    else
        $(btn).attr("data-name",$(btn).val()).attr("disabled",true).val(tip);
}

/**
 * 设置按钮提交状态
 * @param btn
 * @param text
 */
function setBtnSubmitStatus(btn,text)
{
    if (btn == undefined)
        btn = currBtn ;
    if (text == undefined)
        text = $(btn).attr("data-name") ;

    if ($(btn).is("button"))
        $(btn).attr("disabled",false).html(text);
    else
        $(btn).attr("disabled",false).val(text);
}



/**
 * Created by XiaoMa on 2018/2/2.
 */
//////////////////////////////////////////////////////////////
//Public  ajax请求rul
/////////////////////////////////////////////////////////////
function getAjaxUrl(url, param) {

    if (param != undefined)
        url += "?" + param;
    //window.prompt("",url);
    return url;
}


////////////////////////////////////////////////////
//dialog
////////////////////////////////////////////////////

//防止连接点击打开多个MODEL
var isDialogCreate = 0;

/**
 * 显示模态框
 * @param url 可以是URL ，也可以是内容
 * @param isUrl 是否是URL
 */
function dialog(url, isUrl) {

    //防止连续点击
    if (isDialogCreate == 1)
        return;
    //表示在创建中
    isDialogCreate = 1;
    $('#myModal').modal('hide');
    var modal = $("#modal");
    //如果不存在，自动添加
    if ($(modal).html() == undefined) {
        modal = $('<div id="modal" style="z-index:100;"></div>');
        $('body').append(modal);
    }
    if (isUrl == undefined) isUrl = true;
    if (isUrl) {
        $(modal).load(url, function () {
            $('#myModal').modal('show');
            formatContainer(this);
        }).on({//关闭回调,避免多次点击加载
            'hidden.bs.modal': function () {
                isDialogCreate = 0;
            }
        });
    } else {
        var html = url;
        html = '<div class="modal fade" id="myModal" style="overflow:hidden;">' +
            '<div class="modal-dialog">' +
            ' <div class="modal-content">' + html + ' </div>' +
            '</div>' +
            '</div>';
        $(modal).html(html).on({//关闭回调,避免多次点击加载
            'hidden.bs.modal': function () {
                isDialogCreate = 0;
            }
        });
        $('#myModal').modal('show');
    }
}

function dialogClose() {
    //$('#myModal .close').click();
    //关闭回调,避免多次点击加载
    isDialogCreate = 0;
    $('#myModal').modal('hide').remove();
}

////////////////////////////////////////////////////
//enddialog
////////////////////////////////////////////////////

/**
 * 得到搜索控件的值
 */
function getUserSearchData() {

    var input = $("#userkey");
    var realValue = $(input).attr("real-value");

    if (realValue == undefined)
        return {"id": "", "name": ""};

    var arr = realValue.split(sp_item);
    return {"id": arr[0], "name": arr[1]};
}



Array.prototype.indexOf = function (val) {
    for (var i = 0; i < this.length; i++) {
        if (this[i] == val) return i;
    }
    return -1;
};
Array.prototype.remove = function (val) {
    var index = this.indexOf(val);
    if (index > -1) {
        this.splice(index, 1);
    }
};

Array.prototype.contains = function ( needle ) {
    for (i in this) {
        if (this[i] == needle) return true;
    }
    return false;
}


function checkIE8() {
    var browser = navigator.appName
    var b_version = navigator.appVersion
    var version = b_version.split(";");
    var trim_Version = version[1].replace(/[ ]/g, "");
    if (browser == "Microsoft Internet Explorer" && trim_Version == "MSIE8.0") {
        return true;
    }
    return false;
}

/**
 * 弹出式提示框，默认1.2秒自动消失
 * @param message 提示信息
 * @param style 提示样式，有alert-success、alert-danger、alert-warning、alert-info
 * @param time 消失时间
 */
var prompt = function (message, style, time)
{
    style = (style === undefined) ? 'alert-success' : style;
    time = (time === undefined) ? 1200 : time;
    $('<div>')
        .appendTo('body')
        .addClass('alert ' + style)
        .html(message)
        .show()
        .delay(time)
        .fadeOut();
};

// 成功提示
var success_prompt = function(message, time)
{
    prompt(message, 'alert-success', time);
};

// 失败提示
var fail_prompt = function(message, time)
{
    prompt(message, 'alert-danger', time);
};

// 提醒
var warning_prompt = function(message, time)
{
    prompt(message, 'alert-warning', time);
};

// 信息提示
var info_prompt = function(message, time)
{
    prompt(message, 'alert-info', time);
};

var myconfirm = function (message,fn) {
    var res = window.confirm(message);
    if(res){
        fn();
    }
}

//ajax 请求 loading效果
function loading(obj,size){
    if(size == undefined){size = 2}
    var html = '<div class="loading" style="margin-top:20px;text-align: center "><i class="icon-spinner icon-spin icon-'+size+'x"></i></div>';
    $(obj).html(html);
}

function checkNum(str) {
    if(!/^\d+$/.test(str)){
        return false;
    }else{
        return true;
    }
}

function getQueryString(name,url) {

    var reg = new RegExp('(^|&)' + name + '=([^&]*)(&|$)', 'i');
    if(url == undefined){
        url = window.location.search;
    }else{
        url = url.split('?')[1];
    }
    var r = url.match(reg);
    if (r != null) {
        return unescape(r[2]);
    }
    return null;
}

function changeQueryString(arg,arg_val,url) {
    var pattern=arg+'=([^&]*)';
    var replaceText=arg+'='+arg_val;
    if(url.match(pattern)){
        var tmp='/('+ arg+'=)([^&]*)/gi';
        tmp=url.replace(eval(tmp),replaceText);
        return tmp;
    }else{
        if(url.match('[\?]')){
            return url+'&'+replaceText;
        }else{
            return url+'?'+replaceText;
        }
    }
    return url+'\n'+arg+'\n'+arg_val;
}

