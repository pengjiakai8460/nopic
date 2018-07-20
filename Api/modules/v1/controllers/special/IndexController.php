<?php

namespace Api\modules\v1\controllers\special;

use Api\modules\v1\controllers\ApiBaseController;
use Api\services\v1\SpecialService;
use Yii;

class IndexController extends ApiBaseController
{

    /**
     * @apiDefine Special
     * 专项练习
     *
     */

    /**
     * @api {get} special/index/list  Index List
     * @apiVersion 1.0.0
     * @apiName Index List
     * @apiGroup Special
     *
     * @apiSuccess {String} t_id 试卷组ID
     * @apiSuccess {String} id 题目ID
     *
     * @apiSuccessExample Success-Response:
     *
     * {
    "code": 200,
    "message": "success",
    "data": {
    "qtype": {
    "types": {
    "1": {
    "qtype": 1,
    "name": "单项选择题",
    "desc": "四大题型中的第一个，大多数是四选一，每题有且仅有一个正确选项。"
    },
    "2": {
    "qtype": 1,
    "name": "问题求解",
    "desc": "四大题型中的第二个，根据题干描述的问题分析解题思路写出对应答案。"
    },
    "3": {
    "qtype": 1,
    "name": "阅读程序写结果",
    "desc": "四大题型中的第三个，阅读给出的程序，根据输入内容写出输出结果。"
    },
    "4": {
    "qtype": 1,
    "name": "完善程序",
    "desc": "四大题型中的第四个，根据题目要求将题干中的程序补充完整。"
    }
    },
    "exams": {
    "1": [
    {
    "name": "单项选择题第1套练习",
    "id": "24",
    "rep_id": 0
    },
    {
    "name": "单项选择题第2套练习",
    "id": "25",
    "rep_id": 0
    },
    {
    "name": "单项选择题第3套练习",
    "id": "26",
    "rep_id": 0
    },
    {
    "name": "单项选择题第1套练习——1",
    "id": "53",
    "rep_id": 0
    },
    {
    "name": "单项选择题第1套练习——1",
    "id": "54",
    "rep_id": 0
    },
    {
    "name": "单项选择题第1套练习——1",
    "id": "55",
    "rep_id": 0
    },
    {
    "name": "单项选择题第1套练习——1",
    "id": "56",
    "rep_id": 0
    },
    {
    "name": "单项选择题第1套练习",
    "id": "57",
    "rep_id": 0
    }
    ],
    "2": [
    {
    "name": "问题求解第1套练习",
    "id": "30",
    "rep_id": 0
    },
    {
    "name": "问题求解第2套练习",
    "id": "31",
    "rep_id": 0
    }
    ]
    }
    },
    "tag": {
    "list": {
    "1": {
    "id": "1",
    "pid": "0",
    "name": "计算机基本常识",
    "children": [
    {
    "id": "99",
    "pid": "1",
    "name": "计算机发展史",
    "children": [
    {
    "id": "103",
    "pid": "99",
    "name": "第一台电子计算机"
    },
    {
    "id": "104",
    "pid": "99",
    "name": "图灵机"
    },
    {
    "id": "105",
    "pid": "99",
    "name": "电子计算机的演变"
    }
    ]
    },
    {
    "id": "100",
    "pid": "1",
    "name": "计算机硬件",
    "children": [
    {
    "id": "106",
    "pid": "100",
    "name": "运算器"
    },
    {
    "id": "107",
    "pid": "100",
    "name": "控制器"
    },
    {
    "id": "108",
    "pid": "100",
    "name": "存储器"
    },
    {
    "id": "109",
    "pid": "100",
    "name": "输入输出设备"
    }
    ]
    },
    {
    "id": "101",
    "pid": "1",
    "name": "计算机软件基础",
    "children": [
    {
    "id": "110",
    "pid": "101",
    "name": "操作系统"
    },
    {
    "id": "111",
    "pid": "101",
    "name": "应用软件"
    }
    ]
    },
    {
    "id": "102",
    "pid": "1",
    "name": "信息的表达方式",
    "children": [
    {
    "id": "112",
    "pid": "102",
    "name": "二进制"
    },
    {
    "id": "113",
    "pid": "102",
    "name": "补码"
    },
    {
    "id": "114",
    "pid": "102",
    "name": "进制转换"
    }
    ]
    }
    ],
    "img": ""
    },
    "20": {
    "id": "20",
    "pid": "0",
    "name": "学科知识",
    "children": [
    {
    "id": "115",
    "pid": "20",
    "name": "排列组合",
    "children": [
    {
    "id": "117",
    "pid": "115",
    "name": "排列基础知识"
    },
    {
    "id": "118",
    "pid": "115",
    "name": "基本原理"
    },
    {
    "id": "119",
    "pid": "115",
    "name": "组合基础知识"
    },
    {
    "id": "120",
    "pid": "115",
    "name": "集中特殊的排列组合"
    },
    {
    "id": "121",
    "pid": "115",
    "name": "组合意义"
    },
    {
    "id": "122",
    "pid": "115",
    "name": "二项式定理"
    },
    {
    "id": "123",
    "pid": "115",
    "name": "排列生成算法与例程"
    }
    ]
    },
    {
    "id": "116",
    "pid": "20",
    "name": "高精度运算",
    "children": [
    {
    "id": "124",
    "pid": "116",
    "name": "高精度数的初始化"
    },
    {
    "id": "125",
    "pid": "116",
    "name": "高精度加法"
    },
    {
    "id": "126",
    "pid": "116",
    "name": "高精度减法"
    },
    {
    "id": "127",
    "pid": "116",
    "name": "高精度乘法"
    },
    {
    "id": "128",
    "pid": "116",
    "name": "高精度除法"
    }
    ]
    }
    ],
    "img": "http://oss.xiaoma.wang/Uploads/Picture/noipc/2.png"
    },
    "23": {
    "id": "23",
    "pid": "0",
    "name": "网络基本知识",
    "children": [
    {
    "id": "165",
    "pid": "23",
    "name": "网络基本常识",
    "children": [
    {
    "id": "167",
    "pid": "165",
    "name": "网络的物理组成"
    },
    {
    "id": "168",
    "pid": "165",
    "name": "网络的分类"
    },
    {
    "id": "169",
    "pid": "165",
    "name": "分支主题"
    }
    ]
    },
    {
    "id": "166",
    "pid": "23",
    "name": "网络常用软件",
    "children": [
    {
    "id": "170",
    "pid": "166",
    "name": "文件传输"
    },
    {
    "id": "171",
    "pid": "166",
    "name": "远程登陆"
    },
    {
    "id": "172",
    "pid": "166",
    "name": "邮件"
    }
    ]
    }
    ],
    "img": "http://oss.xiaoma.wang/Uploads/Picture/noipc/special-navbar3.ce8e6d9.png"
    },
    "26": {
    "id": "26",
    "pid": "0",
    "name": "算法基础知识",
    "children": [
    {
    "id": "129",
    "pid": "26",
    "name": "动态规划",
    "children": [
    {
    "id": "132",
    "pid": "129",
    "name": "动态规划概述"
    },
    {
    "id": "133",
    "pid": "129",
    "name": "动态规划经典问题"
    },
    {
    "id": "134",
    "pid": "129",
    "name": "集合动态规划"
    },
    {
    "id": "135",
    "pid": "129",
    "name": "树形动态规划"
    }
    ]
    },
    {
    "id": "130",
    "pid": "26",
    "name": "贪心算法",
    "children": [
    {
    "id": "136",
    "pid": "130",
    "name": "贪心算法概述"
    },
    {
    "id": "137",
    "pid": "130",
    "name": "哈夫曼编码"
    },
    {
    "id": "138",
    "pid": "130",
    "name": "贪心算法辅助"
    }
    ]
    },
    {
    "id": "131",
    "pid": "26",
    "name": "分治",
    "children": [
    {
    "id": "139",
    "pid": "131",
    "name": "分治算法概述"
    },
    {
    "id": "140",
    "pid": "131",
    "name": "归并排序"
    },
    {
    "id": "141",
    "pid": "131",
    "name": "大整数乘法"
    }
    ]
    }
    ],
    "img": "http://oss.xiaoma.wang/Uploads/Picture/noipc/3.png"
    },
    "29": {
    "id": "29",
    "pid": "0",
    "name": "数据结构",
    "children": [
    {
    "id": "148",
    "pid": "29",
    "name": "基本数据结构",
    "children": [
    {
    "id": "153",
    "pid": "148",
    "name": "栈"
    },
    {
    "id": "154",
    "pid": "148",
    "name": "队列"
    }
    ]
    },
    {
    "id": "149",
    "pid": "29",
    "name": "优先队列",
    "children": [
    {
    "id": "155",
    "pid": "149",
    "name": "二叉堆的结构"
    },
    {
    "id": "156",
    "pid": "149",
    "name": "二叉堆的维护"
    },
    {
    "id": "157",
    "pid": "149",
    "name": "二叉堆的应用"
    }
    ]
    },
    {
    "id": "150",
    "pid": "29",
    "name": "二叉搜索树",
    "children": [
    {
    "id": "158",
    "pid": "150",
    "name": "二叉搜索树的结构"
    },
    {
    "id": "159",
    "pid": "150",
    "name": "二叉搜索树的操作"
    }
    ]
    },
    {
    "id": "151",
    "pid": "29",
    "name": "哈希",
    "children": [
    {
    "id": "160",
    "pid": "151",
    "name": "散列函数"
    },
    {
    "id": "161",
    "pid": "151",
    "name": "再散列"
    },
    {
    "id": "162",
    "pid": "151",
    "name": "哈希表结构"
    }
    ]
    },
    {
    "id": "152",
    "pid": "29",
    "name": "图论中的数据结构",
    "children": [
    {
    "id": "163",
    "pid": "152",
    "name": "一般图的存储"
    },
    {
    "id": "164",
    "pid": "152",
    "name": "树的存储"
    }
    ]
    }
    ],
    "img": "http://oss.xiaoma.wang/Uploads/Picture/noipc/4.png"
    },
    "32": {
    "id": "32",
    "pid": "0",
    "name": "阅读分析程序",
    "children": [
    {
    "id": "142",
    "pid": "32",
    "name": "程序设计方法",
    "children": [
    {
    "id": "145",
    "pid": "142",
    "name": "C++语言"
    },
    {
    "id": "146",
    "pid": "142",
    "name": "各种新方法"
    },
    {
    "id": "147",
    "pid": "142",
    "name": "方法论的对立"
    }
    ]
    },
    {
    "id": "143",
    "pid": "32",
    "name": "阅读和分析程序"
    },
    {
    "id": "144",
    "pid": "32",
    "name": "完善程序"
    }
    ],
    "img": "http://oss.xiaoma.wang/Uploads/Picture/noipc/5.png"
    }
    },
    "exams": []
    }
    },
    "timestamp": 1523946730
    }
     *
     *
     * @apiError NotLogin This api need login.
     *
     * @apiErrorExample Error-Response:
     *     {
    code: 10021,
    message: "未登录",
    data: { },
    timestamp: 1523946707
    }
     */
    public function actionList()
    {
        $data['qtype'] = SpecialService::getQtypeList();
        $data['tag'] = SpecialService::getTagLists();
        return $this->success($data);
    }

    /**
     * @api {get} special/inex/typeexam  Index Typeexam
     * @apiVersion 1.0.0
     * @apiName Index Typeexam
     * @apiGroup Special
     *
     *
     * @apiParam {Number} qtype 1:表示安装题型  2:表示按照知识点
     * @apiParam {Number} type  当qtype 为1时 表示4个题型  2时表示知识点ID
     * @apiParam {Number} e_id 试卷ID
     * @apiParam {Number} rep_id 答题记录ID
     *
     * @apiSuccess {String} t_id 试卷组ID
     * @apiSuccess {String} id 题目ID
     *
     * @apiSuccessExample Success-Response:
     *     {
    code: 200,
    message: "success",
    data: {
    exam: {
    e_id: "32",
    rep_id: 831,
    data: {
    1: {
    lists: [
    {
    t_id: "33",
    id: "35",
    title: "<p><span style=\"\\\">已知 6 个结点的二叉树的先根遍历是 1 2 3 4 5 6（数字为结点的编号，以下同），后根遍历是 3 2 5 6 4 1，则该二叉树的可能的中根遍历是（ &nbsp; &nbsp; &nbsp; &nbsp;）</span></p>",
    type: "1",
    content: "[{"n":"A","c":"<p><span style=\\\"\\\\\\\">3 2 1 4 6 5<\/span><\/p>","s":"4"},{"n":"B","c":"<p><span style=\\\"\\\\\\\">3 2 1 5 4 6<\/span><\/p>","s":"4"},{"n":"C","c":"<p><span style=\\\"\\\\\\\">2 1 3 5 4 6<\/span><\/p>","s":"4"},{"n":"D","c":"<p><span style=\\\"\\\\\\\">2 3 1 4 6 5<\/span><\/p>","s":"4"}]",
    complexity: "2",
    from_type: "1",
    from_yearannual: null,
    answer_count: "1",
    explain: "&lt;p&gt;&lt;span style=\&quot;\\\&quot;&gt;3 2 1 5 4 6&lt;/span&gt;&lt;/p&gt;",
    score: "4",
    adder_id: "10133",
    add_time: "1522294750",
    update_time: "1522654609",
    status: "1",
    my_answer: ""
    },
    {
    t_id: "33",
    id: "35",
    title: "<p><span style=\"\\\">已知 6 个结点的二叉树的先根遍历是 1 2 3 4 5 6（数字为结点的编号，以下同），后根遍历是 3 2 5 6 4 1，则该二叉树的可能的中根遍历是（ &nbsp; &nbsp; &nbsp; &nbsp;）</span></p>",
    type: "1",
    content: "[{"n":"A","c":"<p><span style=\\\"\\\\\\\">3 2 1 4 6 5<\/span><\/p>","s":"4"},{"n":"B","c":"<p><span style=\\\"\\\\\\\">3 2 1 5 4 6<\/span><\/p>","s":"4"},{"n":"C","c":"<p><span style=\\\"\\\\\\\">2 1 3 5 4 6<\/span><\/p>","s":"4"},{"n":"D","c":"<p><span style=\\\"\\\\\\\">2 3 1 4 6 5<\/span><\/p>","s":"4"}]",
    complexity: "2",
    from_type: "1",
    from_yearannual: null,
    answer_count: "1",
    explain: "&lt;p&gt;&lt;span style=\&quot;\\\&quot;&gt;3 2 1 5 4 6&lt;/span&gt;&lt;/p&gt;",
    score: "4",
    adder_id: "10133",
    add_time: "1522294750",
    update_time: "1522654609",
    status: "1",
    my_answer: ""
    },
    {
    t_id: "33",
    id: "34",
    title: "<p><span style=\"font-family: Consolas, &quot;Lucida Console&quot;, &quot;Courier New&quot;, monospace; font-size: 12px; white-space: pre;\">设栈S的初始状态为空，元素a, b, c, d, e 依次入栈，以下出栈序列不可能出现的有（ &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;）。</span></p>",
    type: "1",
    content: "[{"n":"A","c":"<p><span style=\\\"color: #212121; font-family: Consolas, &quot;Lucida Console&quot;, &quot;Courier New&quot;, monospace; font-size: 12px;\\\">a, b, c, e, d&nbsp;<\/span><\/p>","s":"4"},{"n":"B","c":"<p><span style=\\\"color: #212121; font-family: Consolas, &quot;Lucida Console&quot;, &quot;Courier New&quot;, monospace; font-size: 12px;\\\">b, c, a, e, d\u7531OIFans.cn\u6536\u96c6<\/span><\/p>","s":"4"},{"n":"C","c":"<p><span style=\\\"color: #212121; font-family: Consolas, &quot;Lucida Console&quot;, &quot;Courier New&quot;, monospace; font-size: 12px;\\\">a, e, c, b, d&nbsp;<\/span><\/p>","s":"4"},{"n":"D","c":"<p><span style=\\\"font-family: Consolas, &quot;Lucida Console&quot;, &quot;Courier New&quot;, monospace; font-size: 12px; white-space: pre;\\\">d, c, e, b, a<\/span><\/p>","s":"4"}]",
    complexity: "4",
    from_type: "2",
    from_yearannual: null,
    answer_count: "1",
    explain: "&lt;p&gt;&lt;span style=\&quot;color: #212121; font-family: Consolas, &amp;quot;Lucida Console&amp;quot;, &amp;quot;Courier New&amp;quot;, monospace; font-size: 12px;\&quot;&gt;a, e, c, b, d&amp;nbsp;&lt;/span&gt;&lt;/p&gt;",
    score: "4",
    adder_id: "3",
    add_time: "1522294673",
    update_time: "1522294673",
    status: "1",
    my_answer: ""
    }
    ],
    title: "单项选择题"
    },
    2: {
    lists: [
    {
    t_id: "37",
    id: "39",
    title: "<p><span style=\"text-decoration: none;\">（取石子游戏） 现有 5 堆石子,石子数依次为 3，5，7，19，50，甲乙两人轮流从任一堆中任取（每次只能取自一堆，不能不取）, 取最后一颗石子的一方获胜。甲先取，问甲有没有获胜策略（即无论 乙怎样取，甲只要不失误，都能获胜）？如果有，甲第一步应该在哪一堆里取多少？请写出你的结果：由OIFans.cn收集&nbsp; &nbsp; </span><span style=\"\">①&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;。</span></p>",
    type: "2",
    content: "[{"n":"","s":""}]",
    complexity: "4",
    from_type: "2",
    from_yearannual: null,
    answer_count: "1",
    explain: "&lt;pre style=\&quot;\\\&quot;&gt;有获胜策略(1&amp;nbsp;分)，第&amp;nbsp;1&amp;nbsp;次在第&amp;nbsp;5&amp;nbsp;堆中取&amp;nbsp;32&amp;nbsp;颗石子(4&amp;nbsp;分)&lt;/pre&gt;&lt;p&gt;&lt;br/&gt;&lt;/p&gt;",
    score: "5",
    adder_id: "3",
    add_time: "1522653003",
    update_time: "1523171580",
    status: "1",
    my_answer: ""
    },
    {
    t_id: "37",
    id: "38",
    title: "<p><span style=\"font-family: Consolas, &quot;Lucida Console&quot;, &quot;Courier New&quot;, monospace; font-size: 12px; white-space: pre;\">（寻找假币） 现有 80 枚硬币，其中有一枚是假币，其重量稍轻，所有真币的重量都相同，如果使 用不带砝码的天平称重，最少需要称几次，就可以找出假币？你还要指出第 1 次的称重方法。请写出你的 结果：_________________________________________________。 &nbsp;</span></p>",
    type: "2",
    content: "[{"n":"","s":"5"}]",
    complexity: "4",
    from_type: "1",
    from_yearannual: null,
    answer_count: "1",
    explain: "&lt;pre style=\&quot;word-wrap: break-word; white-space: pre-wrap;\&quot;&gt;第一步：分成&amp;nbsp;3&amp;nbsp;组：27，27，26，将前&amp;nbsp;2&amp;nbsp;组放到天平上（4&amp;nbsp;分）。由OIFans.cn收集&lt;/pre&gt;&lt;p&gt;&lt;br/&gt;&lt;/p&gt;",
    score: "5",
    adder_id: "3",
    add_time: "1522652930",
    update_time: "1522652930",
    status: "1",
    my_answer: ""
    }
    ],
    title: "问题求解"
    },
    3: {
    lists: [
    {
    t_id: "38",
    id: "43",
    title: "<style type=\"\\\">p.p1 {margin: 0.0px 0.0px 0.0px 0.0px; line-height: 19.0px; font: 13.0px \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\'Helvetica Neue\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\'}</style><p class=\"\\\">#include &lt;iostream.h&gt;<span class=\"\\\">&nbsp; &nbsp;</span></p><p class=\"\\\">#include &lt;iomanip.h&gt;<span class=\"\\\">&nbsp;</span></p><p class=\"\\\"><span class=\"\\\">&nbsp;</span>void digit(long n,long m) <span class=\"\\\">&nbsp; </span>{</p><p class=\"\\\"><span class=\"\\\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span>if(m&gt;0)<span class=\"\\\">&nbsp; &nbsp; </span>cout &lt;&lt;setw(2)&lt;&lt;n%10;<span class=\"\\\">&nbsp; &nbsp;</span></p><p class=\"\\\"><span class=\"\\\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span>if(m&gt;1)<span class=\"\\\">&nbsp; &nbsp; </span>digit(n/10,m/10);<span class=\"\\\">&nbsp; &nbsp;</span></p><p class=\"\\\"><span class=\"\\\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span>cout &lt;&lt;setw(2)&lt;&lt;n%10; <span class=\"\\\">&nbsp;</span></p><p class=\"\\\"><span class=\"\\\">&nbsp;</span>}<span class=\"\\\">&nbsp;</span></p><p class=\"\\\">void main()<span class=\"\\\">&nbsp; </span>{</p><p class=\"\\\"><span class=\"\\\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span>long x,x2;<span class=\"\\\">&nbsp;</span></p><p class=\"\\\"><span class=\"\\\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span>cout &lt;&lt;&quot;Input a number:&quot;&lt;&lt;endl;<span class=\"\\\">&nbsp;</span></p><p class=\"\\\"><span class=\"\\\">&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span>cin &gt;&gt;x; <span class=\"\\\">&nbsp;</span></p><p class=\"\\\"><span class=\"\\\">&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span>x2=1; <span class=\"\\\">&nbsp; </span>while(x2&lt;x)<span class=\"\\\">&nbsp; </span>x2*=10; <span class=\"\\\">&nbsp;</span></p><p class=\"\\\"><span class=\"\\\">&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span>x2/=10; <span class=\"\\\">&nbsp; </span>digit(x,x2); <span class=\"\\\">&nbsp;</span></p><p class=\"\\\"><span class=\"\\\">&nbsp;</span>cout &lt;&lt;endl;<span class=\"\\\">&nbsp;</span></p><p class=\"\\\">}<span class=\"\\\">&nbsp;</span></p><p class=\"\\\">输入：9734526 输出：______________________________</p><p><br/></p>",
    type: "3",
    content: "[{"s":"5","n":""}]",
    complexity: "4",
    from_type: "1",
    from_yearannual: null,
    answer_count: "1",
    explain: "&lt;p&gt;6&amp;nbsp; &amp;nbsp; 2&amp;nbsp; &amp;nbsp; 5&amp;nbsp; &amp;nbsp; 4&amp;nbsp; &amp;nbsp; 3&amp;nbsp; &amp;nbsp; 7&amp;nbsp; &amp;nbsp; 9&amp;nbsp; &amp;nbsp; 9&amp;nbsp; &amp;nbsp; 7&amp;nbsp; &amp;nbsp; 3&amp;nbsp; &amp;nbsp; 4&amp;nbsp; &amp;nbsp; 5&amp;nbsp; &amp;nbsp; 2&amp;nbsp; &amp;nbsp; 6&lt;/p&gt;",
    score: "5",
    adder_id: "3",
    add_time: "1522653188",
    update_time: "1523525493",
    status: "1",
    my_answer: ""
    },
    {
    t_id: "38",
    id: "42",
    title: "<p><span style=\"font-family: Consolas, &quot;Lucida Console&quot;, &quot;Courier New&quot;, monospace; font-size: 12px; white-space: pre;\"> #include &quot;iostream.h&quot; #define N &nbsp; &nbsp;7 int fun(char s[],char a,int n) &nbsp;{int j; &nbsp; j=n; while(a&lt;s[j] &amp;&amp; j&gt;0) j--; &nbsp; return j; &nbsp;} void main() &nbsp;{char s[N+1]; &nbsp; int k; &nbsp; for(k=1;k&lt;=N;k++) &nbsp;s[k]=&#39;A&#39;+2*k+1; &nbsp; cout &lt;&lt;fun(s,&#39;M&#39;,N)&lt;&lt;endl; &nbsp;} 输出：_____________ </span></p>",
    type: "3",
    content: "[{"n":"","s":"5"}]",
    complexity: "4",
    from_type: "1",
    from_yearannual: null,
    answer_count: "1",
    explain: "&lt;p&gt;5&lt;/p&gt;",
    score: "5",
    adder_id: "3",
    add_time: "1522653139",
    update_time: "1522653139",
    status: "1",
    my_answer: ""
    }
    ],
    title: "阅读程序写结果"
    },
    4: {
    lists: [
    {
    t_id: "39",
    id: "37",
    title: "<p><span style=\"\\\">由键盘输入一个奇数 P (P&lt;100,000,000)，其个位数字不是 5，求一个整数 S，使 P×S = 1111...1 ( 在给定的条件下，解 S 必存在)。要求在屏幕上依次输出以下结果:（1）S 的全部数字。除最后一行外，每行输出 50 位数字。 （2） 乘积的数字位数。 例 1：输入 p=13，由于 13*8547=111111，则应输出（1）8547，（2）6 例 2：输入 p=147，则输出结果应为（1）755857898715041572184429327286470143613 （2）42，即等式的右端有 42 个 1。由OIFans.cn收集 &nbsp;程序： #include &lt;iostream.h&gt; #include &lt;iomanip.h&gt; void main() &nbsp;{long p,a,b,c,t,n; &nbsp; while (1) &nbsp;{ cout &lt;&lt;&quot;输入 p, 最后一位为 1 或 3 或 7 或 9:&quot;&lt;&lt;endl; &nbsp; &nbsp;cin &gt;&gt;p; &nbsp; &nbsp;if ((p%2!=0)&amp;&amp;(p%5!=0)) &nbsp;// 如果输入的数符合要求，结束循环 ⑥ &nbsp; ; &nbsp; } &nbsp; &nbsp;a=0; n=0; &nbsp; &nbsp;while (a&lt;p) &nbsp;　 &nbsp; {a=a*10+1; n++; // 变量 a 存放部分右端项，n 为右端项的位数 &nbsp;} &nbsp; &nbsp;t=0; &nbsp; &nbsp;do &nbsp;{b=a/p; &nbsp; cout &lt;&lt;setw(1)&lt;&lt;b; &nbsp; t++; &nbsp; if ( &nbsp; &nbsp;⑦ &nbsp;) &nbsp; cout &lt;&lt;endl;由OIFans.cn收集 &nbsp;c=&nbsp; </span><span style=\"\\\">&nbsp; &nbsp; &nbsp;&nbsp;</span><span style=\"\\\">⑧&nbsp; &nbsp;</span><span style=\"\\\"> &nbsp;; &nbsp;a= &nbsp; &nbsp; ⑨ &nbsp; ; n++; } while (c&gt;0); &nbsp; &nbsp;cout&lt;&lt;endl&lt;&lt;&quot;n=&quot;&lt;&lt; &nbsp; &nbsp; &nbsp; &nbsp; ⑩ &nbsp; &lt;&lt;endl; &nbsp;}</span></p>",
    type: "4",
    content: "[{"s":"3","n":""},{"s":"3","n":""},{"s":"3","n":""},{"s":"3","n":""},{"s":"3","n":""}]",
    complexity: "5",
    from_type: "1",
    from_yearannual: null,
    answer_count: "5",
    explain: "&lt;p&gt;&lt;span style=\&quot;\\\&quot;&gt;程序： #include &amp;lt;iostream.h&amp;gt; #include &amp;lt;iomanip.h&amp;gt; void main() &amp;nbsp;{long p,a,b,c,t,n; &amp;nbsp; while (1) &amp;nbsp;{ cout &amp;lt;&amp;lt;&amp;quot;输入 p, 最后一位为 1 或 3 或 7 或 9:&amp;quot;&amp;lt;&amp;lt;endl; &amp;nbsp; &amp;nbsp;cin &amp;gt;&amp;gt;p; &amp;nbsp; &amp;nbsp;if ((p%2!=0)&amp;amp;&amp;amp;(p%5!=0)) &amp;nbsp;// 如果输入的数符合要求，结束循环 ⑥ &amp;nbsp; ; &amp;nbsp; } &amp;nbsp; &amp;nbsp;a=0; n=0; &amp;nbsp; &amp;nbsp;while (a&amp;lt;p) &amp;nbsp;　 &amp;nbsp; {a=a*10+1; n++; // 变量 a 存放部分右端项，n 为右端项的位数 &amp;nbsp;} &amp;nbsp; &amp;nbsp;t=0; &amp;nbsp; &amp;nbsp;do &amp;nbsp;{b=a/p; &amp;nbsp; cout &amp;lt;&amp;lt;setw(1)&amp;lt;&amp;lt;b; &amp;nbsp; t++; &amp;nbsp; if ( &amp;nbsp; &amp;nbsp;⑦ &amp;nbsp;) &amp;nbsp; cout &amp;lt;&amp;lt;endl;由OIFans.cn收集 &amp;nbsp;c= &amp;nbsp; &amp;nbsp;⑧ &amp;nbsp;; &amp;nbsp;a= &amp;nbsp; &amp;nbsp; ⑨ &amp;nbsp; ; n++; } while (c&amp;gt;0); &amp;nbsp; &amp;nbsp;cout&amp;lt;&amp;lt;endl&amp;lt;&amp;lt;&amp;quot;n=&amp;quot;&amp;lt;&amp;lt; &amp;nbsp; &amp;nbsp; &amp;nbsp; &amp;nbsp; ⑩ &amp;nbsp; &amp;lt;&amp;lt;endl; &amp;nbsp;}&lt;/span&gt;&lt;/p&gt;",
    score: "15",
    adder_id: "3",
    add_time: "1522652811",
    update_time: "1523522457",
    status: "1",
    my_answer: ""
    },
    {
    t_id: "39",
    id: "36",
    title: "<p><span style=\"\\\">（全排列）下面程序的功能是利用递归方法生成从 1 到 n(n&lt;10)的 n 个数的全部可能的排列（不一 定按升序输出）。例如，输入 3，则应该输出（每行输出 5 个排列）： 123 132 213 &nbsp; &nbsp; 231 321 312 程序： #include &lt;iostream.h&gt; #include &lt;iomanip.h&gt; int n,a[10];// a[1],a[2],…,a[n]构成 n 个数的一个排列 long count=0; &nbsp;// 变量 count 记录不同排列的个数，这里用于控制换行 void perm(int k) {int j,p,t; if( &nbsp; ① &nbsp; ) &nbsp; {count++; &nbsp; &nbsp;for(p=1;p&lt;=n;p++) &nbsp;cout &lt;&lt;setw(1)&lt;&lt;a[p]; 　 &nbsp; cout &lt;&lt;&quot; &nbsp;&quot;; &nbsp; &nbsp;if( &nbsp; &nbsp; ② &nbsp; ) &nbsp;cout &lt;&lt;endl; &nbsp;return; &nbsp; &nbsp;} &nbsp;for(j=k;j&lt;=n;j++) &nbsp; &nbsp;{t=a[k];a[k]=a[j];a[j]=t; ③ &nbsp; ; &nbsp; &nbsp;t=a[k]; &nbsp; &nbsp; &nbsp;④ &nbsp; ; &nbsp; &nbsp;} &nbsp;} void main() {int i; &nbsp; cout &lt;&lt;&quot;Entry n:&quot;&lt;&lt;endl; &nbsp; cin &gt;&gt;n; &nbsp; for(i=1;i&lt;=n;i++) &nbsp;a[i]=i; ⑤ &nbsp; &nbsp;; } </span></p>",
    type: "4",
    content: "[{"s":"3","n":""},{"s":"3","n":""},{"s":"3","n":""},{"s":"3","n":""},{"s":"3","n":""}]",
    complexity: "5",
    from_type: "1",
    from_yearannual: "2006",
    answer_count: "5",
    explain: "&lt;p&gt;&lt;span style=\&quot;\\\&quot;&gt;// a[1],a[2],…,a[n]构成 n 个数的一个排列 long count=0; &amp;nbsp;// 变量 count 记录不同排列的个数，这里用于控制换行 void perm(int k) {int j,p,t; if( &amp;nbsp; ① &amp;nbsp; ) &amp;nbsp; {count++; &amp;nbsp; &amp;nbsp;for(p=1;p&amp;lt;=n;p++) &amp;nbsp;cout &amp;lt;&amp;lt;setw(1)&amp;lt;&amp;lt;a[p]; 　 &amp;nbsp; cout &amp;lt;&amp;lt;&amp;quot; &amp;nbsp;&amp;quot;; &amp;nbsp; &amp;nbsp;if( &amp;nbsp; &amp;nbsp; ② &amp;nbsp; ) &amp;nbsp;cout &amp;lt;&amp;lt;endl; &amp;nbsp;return; &amp;nbsp; &amp;nbsp;} &amp;nbsp;for(j=k;j&amp;lt;=n;j++) &amp;nbsp; &amp;nbsp;{t=a[k];a[k]=a[j];a[j]=t; ③ &amp;nbsp; ; &amp;nbsp; &amp;nbsp;t=a[k]; &amp;nbsp; &amp;nbsp; &amp;nbsp;④ &amp;nbsp; ; &amp;nbsp; &amp;nbsp;} &amp;nbsp;} void main() {int i; &amp;nbsp; cout &amp;lt;&amp;lt;&amp;quot;Entry n:&amp;quot;&amp;lt;&amp;lt;endl; &amp;nbsp; cin &amp;gt;&amp;gt;n; &amp;nbsp; for(i=1;i&amp;lt;=n;i++) &amp;nbsp;a[i]=i; ⑤ &amp;nbsp; &amp;nbsp;; }&lt;/span&gt;&lt;/p&gt;",
    score: "15",
    adder_id: "3",
    add_time: "1522652544",
    update_time: "1523525423",
    status: "1",
    my_answer: ""
    }
    ],
    title: "完善程序"
    }
    },
    title: "完善程序的专项练习题, 共9题目"
    }
    },
    timestamp: 1523934629
    }
     *
     * @apiError RecordNotFound The id of the record was not found.
     *
     * @apiErrorExample Error-Response:
     * {
    code: 10021,
    message: "未登录",
    data: { },
    timestamp: 1523946707
    }
     */
    public function actionTypeexam()
    {
        $data = Yii::$app->getRequest()->get();
        $rules = [
            [['qtype', 'e_id', 'rep_id'], 'integer'],
            ['type', 'required'],
        ];
        $data['page'] = isset($data['page']) ? $data['page'] : 1;
        $this->validate($data, $rules);
        $data = SpecialService::getQuestionByType($data);
        return $this->success($data);
    }

    /**
     * @api {get} special/inex/submit  Index Submit
     * @apiVersion 1.0.0
     * @apiName Index Submit
     * @apiGroup Special
     *
     *
     * @apiParam {Number} e_id 试卷ID
     * @apiParam {Number} rep_id  回复记录ID
     * @apiParam {Number} white_qids 1_22,2_33,3_44  题组ID_题目ID
     * @apiParam {Number} times 总共时长
     *
     *
     * @apiSuccessExample Success-Response:
     *
     * {
    "code": 200,
    "message": "success",
    "data": true,
    "timestamp": 1523947994
    }
     *
     * @apiError RecordIdNotFound The id of the record can not empty.
     *
     * @apiErrorExample Error-Response:
     * {
    "code": 10034,
    "message": "Rep Id不能为空。",
    "data": {},
    "timestamp": 1523948126
    }
     */
    public function actionSubmit() {
        $data = Yii::$app->getRequest()->post();
        $rules = [
            [['e_id', 'rep_id', 'times'], 'integer'],
            [['e_id', 'rep_id', 'times'], 'required'],
        ];
        $data['page'] = isset($data['page']) ? $data['page'] : 1;
        $this->validate($data, $rules);
        $data = SpecialService::submit($data);
        return $this->success($data);
    }

    /**
     * @api {get} special/inex/addquestion  Index Addquestion
     * @apiVersion 1.0.0
     * @apiName Index Addquestion
     * @apiGroup Special
     *
     *
     * @apiParam {Number} e_id 试卷ID
     * @apiParam {Number} rep_id  回复记录ID
     * @apiParam {Number} t_id 题组ID
     * @apiParam {Number} q_id 问题ID
     * @apiParam {Number} times 时长
     * @apiParam {Number} answer 答案
     *
     *
     * @apiSuccessExample Success-Response:
     *
     * {
    "code": 200,
    "message": "success",
    "data": true,
    "timestamp": 1523948295
    }
     *
     * @apiError RecordIdNotFound The id of the record can not empty.
     *
     * @apiErrorExample Error-Response:
     * {
    "code": 10034,
    "message": "Rep Id不能为空。",
    "data": {},
    "timestamp": 1523948314
    }
     */
    public function actionAddquestion() {
        $data = Yii::$app->getRequest()->post();
        $rules = [
            [['e_id', 'rep_id'], 'integer'],
            [['e_id', 'rep_id'], 'required'],
        ];
        $data['page'] = isset($data['page']) ? $data['page'] : 1;
        $this->validate($data, $rules);
        $data = SpecialService::addQuestion($data);
        return $this->success($data);
    }

    /**
     * @api {get} special/inex/newrecord  Index Newrecord
     * @apiVersion 1.0.0
     * @apiName Index Newrecord
     * @apiGroup Special
     *
     * @apiParam {Number} e_id 试卷ID
     *
     *
     * @apiSuccessExample Success-Response:
     *
     * {
    "code": 200,
    "message": "success",
    "data": {
    "rep_id": 3369
    },
    "timestamp": 1525402961
    }
     *
     * @apiError RecordIdNotFound The id of the record can not empty.
     *
     * @apiErrorExample Error-Response:
     *{
    "code": 0,
    "message": "该试卷不存在！",
    "data": {},
    "timestamp": 1525402917
    }
     */
    public function actionNewrecord() {
        $data = Yii::$app->getRequest()->post();
        $rules = [
            [['e_id'], 'integer'],
            [['e_id'], 'required'],
        ];
        $this->validate($data, $rules);
        $ret = SpecialService::newRecord($data);
        return $this->success($ret);
    }



}