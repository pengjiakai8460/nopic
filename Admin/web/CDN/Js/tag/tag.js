/**
 * Created by XiaoMa on 2018/2/2.
 */
var itree;
var itreeNode ;
var labelId ;
var labelPath ;

var setting = {
    edit: {
        enable: true,
        editNameSelectAll: true,
        showRemoveBtn: false,
        showRenameBtn: false
    },
    data: {
        simpleData: {
            enable:true,
            idKey: "id",
            pIdKey: "pid",
            rootPId: "topid"
        }
    },
    callback: {
        //beforeClick:treeClick,
        onClick: treeClick
    }
};

//-----------------start知识点标签---------------
function loadLabelTree(data,obj,callbackfunc)
{

    itree = $.fn.zTree.init(obj, setting,data);
    itreeNode = itree.getNodes()[0];
    itree.selectNode(itreeNode);//选择点
    labelId = itreeNode.id;
    if(callbackfunc != undefined){
        callbackfunc(event,labelId, itreeNode);
    }
}

function loadLabelDetail(event,treeId, treeNode)
{
    var tdhtml = "<td>"+treeNode.id+"</td>";
    tdhtml += "<td>"+(treeNode.getParentNode()!=null?treeNode.getParentNode().name:"无上级")+"</td>";
    tdhtml += "<td>"+treeNode.name+"</td>";
    tdhtml += "<td>"+treeNode.add_time+"</td>";
    tdhtml += "<td>"+treeNode.update_time+"</td>";
    $("#treedetail").html(tdhtml);
}

//点击
function treeClick(event, treeId, treeNode)
{
    if (treeNode == undefined){
        return ;
    }
    itreeNode = treeNode ;
    labelId = treeNode.id ;
    labelPath = getTreePathText(treeNode) ;
    $("#path").html("/" + labelPath);
    loadLabelDetail(event, treeId, treeNode);
}

/**
 * 得到树的路径 a/b/c
 * @param node
 * @returns
 */
function getTreePathText(node){//关键代码，通过treeNode遍历父亲节点，根节点再次调用getParentNode得到null终止循环
    var s=node.name;
    while(node=node.getParentNode())s=node.name+'/'+s;
    return s;
}

function isVirtualNode(){

    if ((labelId == 0) || (labelId == "")){
        warning_prompt('根节点不允许编辑');
        return true;
    }
    return false ;
}

//======知识点标签相关=============================
function label_add()
{
    var url = getAjaxUrl("/tag/tag/add",'id='+itreeNode.id+"&pid="+itreeNode.pid) ;
    dialog(url);
}

function labelAddCallBack(res)
{

    if(res.code == 200){
        var newNode = {'id':res.data.id,'name':res.data.name,
            'pid':res.data.pid,'add_time':res.data.add_time,'update_time':res.data.update_time};
        itree.addNodes(itreeNode,newNode);
    }
}

function label_edit()
{
    //根结点不能修改
    if (isVirtualNode())
        return false;
    var parentlabelPath = labelPath ;

    var url = getAjaxUrl("/tag/tag/edit","id=" + labelId  ) ;
    dialog(url);
}

function labelEditCallBack(res)
{
    itreeNode.name = res.data.name;
    itreeNode.add_time = res.data.add_time;
    itreeNode.update_time = res.data.update_time;
    itree.updateNode(itreeNode);
}

function label_delete()
{
    //根结点不能修改
    if (isVirtualNode())
        return false;
    var fn = function(){
        var url = getAjaxUrl('/tag/tag/delete',"id=" + labelId) ;

        $.get(url, function(res){
            if (res.code == 200){
                labelDeleteCallBack(res);
                success_prompt(res.message);
            }else{
                fail_prompt(res.message);
            }

        });
    }
    myconfirm('你确认要删除吗?',fn);
}

function labelDeleteCallBack(res)
{
    var parentNode = itreeNode.getParentNode();//获取要删除节点的父节点
    itree.removeNode(itreeNode);
    itree.selectNode(parentNode);//重新定位到删除节点的父节点上
    treeClick(event,parentNode.id,parentNode);
}
//----------------end知识点标签--------------------



//--------------每日任务modal--------




//----------------每日任务 知识点 callback----------
var qUrl ;
var qsearchBaseUrl =  '/task/task/qajax';
var qsearchTagId,qsearchType,qsearchKey,qsearchKeyType;
function searchQuestion(event, treeId, treeNode) {

    itreeNode = treeNode ;
    loading($('#questions'));
    qsearchTagId = 'tag_id='+itreeNode.id;
    // if(qsearchBaseUrl.indexOf("?")!=-1){
    //     qsearchTagId = 'tag_id='+itreeNode.id;
    //     // qUrl = qsearchBaseUrl + '&tag_id='+itreeNode.id;
    // }else{
    //     qsearchTagId = '?tag_id=' + itreeNode.id;
    //
    // }
    qUrl = qsearchBaseUrl + '&' + qsearchTagId;
    if(qsearchType){
        qUrl += '&' + qsearchType;
    }
    if(qsearchKey){
        qUrl += '&' + qsearchKey;
    }
    if(qsearchKeyType){
        qUrl += '&' + qsearchKeyType;
    }
    $.get(qUrl,function(msg){
        $('#questions').html(msg);
        formatContainer($('#questions'));
    });
}

function qsearch(type){

    loading($('#questions'));
    qsearchType = 'type='+type;
    // if(qsearchBaseUrl.indexOf("?")!=-1){
    //     qsearchBaseUrl += '&type='+type;
    // }else{
    //     qsearchBaseUrl += '?type=' + type;
    // }
    qUrl = qsearchBaseUrl + '&' + qsearchType;
    if(qsearchTagId){
        qUrl += '&' + qsearchTagId;
    }
    if(qsearchKey){
        qUrl += '&' + qsearchKey;
    }
    if(qsearchKeyType){
        qUrl += '&' + qsearchKeyType;
    }
    $.get(qUrl,function(msg){
        $('#questions').html(msg);
    });

}

function qkeysearch(id){
    qsearchKey = 'searchkey=' + $('#'+id).val();
    qsearchKeyType = 'searchqtype=' + $("select[name=seachqtype]").val();
    qUrl = qsearchBaseUrl + '&' + qsearchKey + '&'+ qsearchKeyType;
    loading($('#questions'));
    if(qsearchTagId){
        qUrl += '&' + qsearchTagId;
    }
    if(qsearchType){
        qUrl += '&' + qsearchType;
    }
    // if(qsearchBaseUrl.indexOf("?")!=-1){
    //     qsearchBaseUrl += '&searchkey='+searchkey+ '&searchqtype=' + searchqtype;
    // }else{
    //     qsearchBaseUrl += '?searchkey=' + searchkey+ '&searchqtype=' + searchqtype;
    // }

    $.get(qUrl,function(msg){
        $('#questions').html(msg);
    });
}

function qkeyclear(id) {
    $('#'+id).val('');
    $("select[name=seachqtype]").val('0');
    loading($('#questions'));
    $.get(qsearchBaseUrl,function(msg){
        $('#questions').html(msg);
    });
}

function qselected(id,title){

}
function qdelete(qid) {

}


function getques() {
    var ques = '';
    if($('.e_content .q_body')){
        $('.e_content .q_body').each(function(e){
            ques += ques ? "," + $(this).attr('data-qid') : ($(this).attr('data-qid')) ;
        })
    }
    return ques;
}