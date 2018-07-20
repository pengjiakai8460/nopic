var examObj = {
    that:{},
    bindClick:function ($elm, funcName, getParam) {
        $elm.click(function (e) {
            var that = $(this);
            var params = {};
            var funParam = examObj[getParam]
            if (typeof funParam === 'function') {
                params = funParam(that)
            }else{
                params = {};
            }
            var fun = examObj[funcName]
            if (typeof fun === 'function') {
                 fun(params)
            }
        })
    },
    //编辑题组页面展示
    show_edit_testlet_params:function (that) {
        /**
         data-title="{{ t.title }}"
         data-subtitle="{{ t.subtitle }}"
         data-id="{{ t.id }}"
         data-sort="{{ t.sort }}"
         * @type {{title: string, subtitle: string, id: string, sort: string}}
         */
        var dataAttr = {title:'data-title',subtitle:'data-subtitle',id:'data-id',sort:'data-sort'}
        var params = {}
        $.each(dataAttr,function (k,v) {
            params[k] = that.attr(v);
        })
       return params;
    },
    show_edit_testlet : function (params) {
        $("#testlet_title").val(params.title);
        $("#testlet_subtitle").val(params.subtitle);
        $("#testlet_sort").val(params.sort);
        $("#edit_testlet").val(params.id);
    },

    //编辑题组
    edit_testlet_params:function (that) {
        var title = $("#testlet_title").val();
        var subtitle = $("#testlet_subtitle").val();
        var sort = $("#testlet_sort").val();
        var t_id = $("#edit_testlet").val();
        var e_id = $("#exam_id").val();
        return {t_title:title,t_subtitle:subtitle,t_sort:sort,t_id:t_id,e_id:e_id};
    },
    edit_testlet :function (params) {
        var title = params.t_title;
        var subtitle = params.t_subtitle;
        var sort = params.t_sort;
        var t_id = params.t_id;
        $.ajax({
            url: '/exams/exam/update-testlet',
            type: "GET",
            data: {
                exam_id:params.e_id,
                testlet_id: t_id,
                title: title,
                sort: sort,
                subtitle: subtitle
            },
            success: function (e) {
                location.reload();
            }
        })
    },


    //删除题组
    del_testlet_params:function (that) {
        /**
         * data-t-id="{{ t.id }}" data-e-id="{{ exam.id }}"
         */
        var dataAttr = {t_id:'data-t-id',e_id:'data-e-id'}
        var params = {}
        $.each(dataAttr,function (k,v) {
            params[k] = that.attr(v);
        })
        return params;
    },
    del_testlet : function (params) {
        if ( params.t_id == undefined || params.e_id == undefined ) {
            alert('参数错误'); return;
        }
        var r = confirm('确定要删除该题组吗？')
        if ( r == true ) {
            $.ajax({
                url:'/exams/exam/del-exam-testlet',
                type:"GET",
                data:{
                    t_id:params.t_id,
                    e_id:params.e_id
                },
                success:function(e){
                    // console.log(e);
                    if(e.success = 'success'){
                        alert(e.info);
                        location.reload();
                    }else{
                        alert(e.info);
                    }
                }
            })
        }
    },

    // 添加题组
    add_testlet_params:function (that) {
        var title = $('#add_testlet_title').val();
        var subtitle = $('#add_testlet_subtitle').val();
        var sort = $('#add_testlet_sort').val();
        var e_id = $('#exam_id').val();
        return {t_title:title,t_subtitle:subtitle,t_sort:sort,e_id:e_id};
    },
    add_testlet:function (params) {
        var title = params.t_title;
        var subtitle = params.t_subtitle;
        var sort = params.t_sort;
        if (title.length == 0) {
            alert('请输入标题');
            return;
        }
        if (subtitle.length == 0) {
            alert('请输入标题');
            return;
        }
        if (Number.isInteger(sort)) {
            alert('请输入正确的排序序号');
            return;
        }
        $.ajax({
            url: '/exams/exam/add-testlet',
            type: 'GET',
            data: {
                exam_id:params.e_id,
                title: title,
                subtitle: subtitle,
                sort: sort
            },
            success: function (e) {
                location.reload();
            }
        })
    },

    //编辑试卷
    show_edit_exam_params:function (that) {
        /*
        data-e-title="{{ exam.title }}"
        data-e-type="{{ exam.type }}"
        data-e-a-s="{{ exam.all_score }}"
        data-e-a-t="{{ exam.all_times }}"
        data-e-y="{{ exam.year }}"
        data-e-c="{{ exam.complexity }}"
        * */
        var dataAttr = {e_title:'data-e-title',e_type:'data-e-type',e_a_s:'data-e-a-s',e_a_t:'data-e-a-t',e_y:'data-e-y',e_c:'data-e-c'}
        var params = {}
        $.each(dataAttr,function (k,v) {
            params[k] = that.attr(v);
        })
        return params;

    },
    show_edit_exam:function (params) {
        $("#exam_title").val(params.e_title);
        if(params.e_title != 1){
            $("#show_exam_year").addClass("hidden");
        }else{
            $("#show_exam_year").removeClass("hidden");
        }
        $("#exam_type").find("option[value="+params.e_type+"]").attr('selected', 'selected');
        $("#exam_complexity").val(params.e_c);
        $("#exam_year").val(params.e_y);
        $("#exam_all_score").val(params.e_a_s);
        $("#exam_all_times").val( params.e_a_t/60 );
    },



    edit_exam_params:function (that) {
        var exam_type = $("#exam_type").val();
        var exam_title = $("#exam_title").val();
        var exam_id = $("#exam_id").val();
        var exam_all_score = $("#exam_all_score").val();
        var exam_all_times = $("#exam_all_times").val();
        var exam_complexity = $('#exam_complexity').val();
        return {e_type:exam_type,e_title:exam_title,e_id:exam_id,e_a_s:exam_all_score,e_a_t:exam_all_times,e_c:exam_complexity}
    },
    edit_exam:function (params) {
        var exam_id = params.e_id;
        // var exam_type = $("#exam_type").val();
        // var exam_title = $("#exam_title").val();
        // var exam_all_score = $("#exam_all_score").val();
        // var exam_all_times = $("#exam_all_times").val();
        // var exam_complexity = $('#exam_complexity').val();
        var exam_type = params.e_type;
        var exam_title = params.e_title;
        var exam_all_score = params.e_a_s;
        var exam_all_times = params.e_a_t;
        var exam_complexity = params.e_c
        //所有变量判定合法
        var ret_data = Object();
        if (exam_type == 1) {
            var exam_year = $("#exam_year").val();
            if (exam_year.length != 0) {
                ret_data['year'] = exam_year;
            } else {
                console.log(exam_type);
                alert('请选择正确的真题时间');
                return;
            }
        }
        ret_data['title'] = exam_title;
        ret_data['type'] = exam_type;
        ret_data['all_score'] = exam_all_score;
        ret_data['all_times'] = exam_all_times * 60;
        ret_data['complexity'] = exam_complexity;
        $.ajax({
            url: '/exams/exam/edit-exam',
            type: "GET",
            data: {
                'exam_id': exam_id,
                'data': ret_data
            },
            success: function (e) {
                location.reload();
            }
        })
    },

    form_datetime:function (className) {
        className.datetimepicker({format: 'yyyy-mm-dd',language:'zh-CN',minView: 2,});
    },


    //添加题目题目
    add_question_params:function (that) {
        var ques = '';
        // if($('.e_content .q_body')){
        //     $('.e_content .q_body').each(function(e){
        //         ques += ques ? "," + $(this).attr('data-qid') : ($(this).attr('data-qid')) ;
        //     })
        // }
        if(that.siblings('.q_body')){
            that.siblings('.q_body').each(function (e) {
                ques += ques ? "," + $(this).attr('data-qid') : ($(this).attr('data-qid')) ;
            })
        }
        
        $('.e-testle').each(function (e) {
            $(this).attr('data-active',0);
        })
        that.parent().attr('data-active',1);
        var e_id = $('#exam_id').val();
        return {qids:ques,e_id:e_id};
    },
    add_question:function (params) {
        var url = getAjaxUrl("/task/task/qadd?task_id="+params.e_id+"&qids="+params.qids);
        dialog(url);
    }
}