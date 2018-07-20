var questionObj = {
    ue:{},
    init:function (e) {
        this.initUedit();
        this.initUeditToggle();
        this.initanswerChange();
        this.initQtypeChange();
        this.initTagChage();
        this.initQueSubmit();
    },
    //初始化编辑器
    initUedit:function (e) {
        questionObj.ue = UE.getEditor('editor');
        questionObj.ue.ready(function(){
            //阻止工具栏的点击向上冒泡
            $(this.container).click(function(e){
                e.stopPropagation()
            })

        });

    },
    //初始化编辑器切换事件
    initUeditToggle:function () {
        $('.uedit-toggle').click(function(e){
            var ueditopen = $(this).attr('data-uedit-open') == 1 ? 0 : 1;
            var editid = $(this).attr('data-uedit-id');
            var $target = $(this).next();

            if(ueditopen){
                $('.uedit-toggle').each(function (e) {
                    if($(this).attr('data-uedit-open') == 1 && editid !=$(this).attr('data-uedit-id') ){
                        var content = questionObj.ue.getContent();
                        questionObj.ue.container.parentNode.style.display= 'none';
                        $(this).next().children('textarea').val(content).show();
                        $(this).attr('data-uedit-open',ueditopen == 1 ? 0 : 1)
                    }
                })

                var content = $target.children('textarea').hide().val();
                questionObj.ue.container.parentNode.style.display= 'block';
                $target.append(questionObj.ue.container.parentNode);
                questionObj.ue.reset();
                setTimeout(function(){
                    questionObj.ue.setContent(content);
                },200)
                questionObj.ue.execCommand("source");
                $(this).attr('data-uedit-open',ueditopen)
            }else{
                var content = questionObj.ue.getContent();
                questionObj.ue.container.parentNode.style.display= 'none';
                $target.children('textarea').val(content).show();
                $(this).attr('data-uedit-open',ueditopen)
                questionObj.ue.execCommand("source");
            }
        })
    },
    //初始化答案修改事件
    initanswerChange:function () {
        $("#answer_count").change(function (e) {
            if($(".qtype").val() == 1){
                return;
            }
            var forNum = $(this).val();
            if(checkNum(forNum) ){
                var answerhtml = '';
                for(var i = 1; i <= forNum; i++){
                    answerhtml += '<div class="clearfix answer-item">\n' +
                        // '<input type="text" class="form-control" placeholder="第' + i + '个空答案" style="width: 200px;float: left;" >\n' +
                        '<textarea  class="form-control"  style="width: 200px;float: left;" ></textarea>\n' +
                        '<label for="" style="width: 50px">分数:</label>\n' +
                        '<input type="text" class="form-control" name="scores" style="width: 50px;">\n' +
                        '</div>'
                }
                $(".answer-con").html(answerhtml);
            }
        })
        $('.answers_sel').each(function(){
            $(this).click(function(){
                if($(this).prop('checked')){
                    $('.answers_sel').prop('checked',false);
                    $(this).prop('checked',true);
                }
            });
        });
    },
    //初始化问题类型改变事件
    initQtypeChange:function () {
        $('.qtype').change(function (e) {
            var val = $(this).val();
            if(val > 1){
                var forNum = $("#answer_count").val();
                if(checkNum(forNum) && forNum >= 1){
                    var answerhtml = '';
                    for(var i = 1; i <= forNum; i++){
                        answerhtml += '<div class="clearfix answer-item">\n' +
                            // '<input type="text" class="form-control" placeholder="第' + i + '个空答案" style="width: 200px;float: left;" >\n' +
                            '<textarea  class="form-control"  style="width: 200px;float: left;" ></textarea>\n' +
                            '<label for="" style="width: 50px">分数:</label>\n' +
                            '<input type="text" class="form-control" name="scores" style="width: 50px;">\n' +
                            '</div>'
                    }
                    $(".answer-con").html(answerhtml);
                }
            }
            if (val == 1) {
                $(".selectQuest").show();
                $(".selectOther").hide();
            } else {
                $(".selectQuest").hide();
                $(".selectOther").show();
            }
        })

    },
    //初始化标签修改事件
    initTagChage:function () {
        //难点标签
        $('.q_comp').click(function (e) {
            dialog('/question/question/loadmodal?type=comp');
        })
        $('.q_tag').click(function (e) {
            dialog('/question/question/loadmodal?type=tag');
        })
        $('.q_from').click(function (e) {
            dialog('/question/question/loadmodal?type=from');
        })
        //标签保存
        $(document).on('click',"button[type=submit]",function () {
            var savetype = $(this).attr('data-save');
            num = $("#rating-input").val();
            if(savetype == 'comp'){
                questionObj.setComp(num);
            }
            if(savetype == 'tag'){
                if($('.tag_item')){
                    questionObj.setTag("tag_item");
                }
            }
            if(savetype == 'from'){
                var fromval = $('.q_from_s').val();
                var fromyear = $('.q_from_y').val();
                questionObj.setFrom(fromval, fromyear);
            }
            dialogClose();
        })
    },

    initQueSubmit:function (e) {
        $("#addmansub").click(function () {
            $(this).attr("disabled", true);
            var qtype = $(".qtype");
            var uev = $('textarea[name=title]');
            var qname = $('#qname');
            var exp = $('textarea[name=explain]');
            var score = $('input[name=score]');

            if(!questionObj.checkQueSubmit(qname) || !questionObj.checkQueSubmit(qtype) || !questionObj.checkQueSubmit(uev) || !questionObj.checkQueSubmit(score) || !questionObj.checkQueSubmit(exp)){
                return false;
            }

            if (qtype.val() == 1) {
                var selectqdata = [];
                for(i = 1; i <= 4; i++) {
                    if (!$(".select" + i).val()) {
                        alert("选择题选项不能为空!");
                        $("#addmansub").removeAttr("disabled")
                        return false;
                    }else{
                        var itemN = i;
                        var itemC = $(".select" + i).val()
                        var itemisR = $('.answers_sel'+i).prop('checked') ? 1 : 0;
                        var temp = {c:itemC,n:itemN,is_r:itemisR};
                        selectqdata.push(temp)
                    }
                }
                $("input[name=q_select]").val(JSON.stringify(selectqdata));
            } else {
                var ans = [];
                var ansisNull = false
                $('.answer-item').each(function (e) {
                    var itemC = $(this).children("textarea").first().val();
                    var itemS = $(this).children("input").last().val();
                    if(itemC.trim() == "" || itemS.trim() == ""){
                        ansisNull = true
                    }
                    var temp = {c:itemC,s:itemS};
                    ans.push(temp)
                })
                if(ansisNull){
                    alert("答案或分数不能为空!");
                    $("#addmansub").removeAttr("disabled")
                    return false;
                }
                $(".answer").val(JSON.stringify(ans));
            }
            $.ajax({
                type : "POST",
                url : "/question/question/addquest",
                data : $(".formBlock").serialize(),
                success : function (data) {
                    data = $.parseJSON(data);
                    if (!data.code) {
                        success_prompt(data.msg,2000);
                        setTimeout(window.location.href='/question/question/index',2200)
                        // alert(data.msg);
                        // window.location.reload();
                    } else {
                        alert(data.msg);
                        $("#addmansub").removeAttr("disabled")
                    }
                }
            });
        });
    },


    setComp:function (num) {
        var html = '<label for="">难度:</label>';
        html = html +'<span class="q_comp_item" data-compid=' + num +' style="width:auto;margin:5px 10px;">'+ num+'星</span>';
        $('.showcomp').html(html).show();
        $('input[name=q_comp]').val(num);
    },

    setTag:function(tag_name) {
        var html = '<label for="">知识点:</label>';
        var tagid = '';
        $('.' + tag_name).each(function(){
            tagid += tagid?('|' + $(this).attr('data-tid')) : $(this).attr('data-tid');
            html = html +'<span class="q_tag_item" data-tid="'+ $(this).attr('data-tid') +'" style="width:auto;margin:5px 10px;">'+$(this).html()+'</span>';
        })

        $('.showtag').html(html).show();
        $('input[name=q_tag]').val(tagid);
    },

    setFrom:function(fromval, fromyear) {
        var html = '<label for="">题型:</label>';
        if(fromval){
            var fromname ;
            if(fromval == 1){
                fromname = '真题(' + fromyear +')';
            }else{
                fromname = '模拟';
            }
            html += '<span class="q_from_item" data-fromtype="' + fromval +'" data-fromyear="'+fromyear+'" style="width:auto;margin:0px 10px;" >'+ fromname +'</span>';
            $('.showfrom').html(html).show();
            $('input[name=q_from]').val(fromval + '|' + fromyear);
        }
    },

    setQuedit:function (obj) {
        obj.siblings().each(function (e) {
            $(this).next().children('textarea').show();
        })
    },
    checkQueSubmit:function(obj) {
        if(!obj.val()){
            alert(obj.attr('data-require-msg'));
            $("#addmansub").removeAttr("disabled")
            return false;
        }else{
            return true
        }
    },

}