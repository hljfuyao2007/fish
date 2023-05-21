define(["jquery", "easy-admin"], function ($, ea) {
    var rate=layui.rate;
    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'fish.user/index',
        add_url: 'fish.user/add',
        edit_url: 'fish.user/edit',
        delete_url: 'fish.user/delete',
        export_url: 'fish.user/export',
        modify_url: 'fish.user/modify',
        follow_url: 'fish.follow/index',
        follow_add_url: 'fish.follow/add',
        follow_edit_url: 'fish.user/edit2',
        fp_url: 'fish.user/fp',//分配咨询
    };

    var Controller = {

        index: function () {
            ea.table.render({
                init: init,
                toolbar:['refresh','add','export',[
                    {
                        text: '分配',
                        url: init.fp_url,
                        method: 'open',
                        field: 'id',
                        auth: 'fp',
                        icon:'fa fa-fighter-jet',
                        class: 'layui-btn layui-btn-danger layui-btn-sm',
                        checkbox:true
                    }]],
                cols: [[
                    {type: 'checkbox'},
                    {field: 'id', title: 'id' ,width: 80},
                    {field: 'nick_name', title: '称呼'},
                    {field: 'sex', title: '性别',selectList:{0:"女",1:"男"},templet:ea.table.list , width: 50},
                    {field: 'age', title: '年龄',search: false, width: 50},

                    // {field: 'wechart', title: '微信号'},
                    // {field: 'wechart_nickname', title: '微信昵称'},
                    {field: 'phone', title: '电话号'},
                    {field: 'platform_id', title: '平台',selectList: platformList, templet: ea.table.list},
                    // {field: 'platform_account', title: '平台账号'},
                    // {field: 'platform_nickname', title: '平台昵称'},
                    // {field: 'realname', title: '真实姓名'},
                    // {field: 'idcard', title: '身份证号'},
                    {field: 'star', title: '用户星级',search: false, templet: function (d) {
                            return '<div id="score' + d.id + '" title="' + d.type.title + '"></div>'
                    },width: 150},
                    {field: 'admin.username', title: '录入人员',search: false},
                    {field: 'seek_admin.username', title: '客服人员',search: false},
                    // {field: 'notes', title: '备注'},
                    // {field: 'images', title: '备注图片', templet: ea.table.image},
                    {field: 'create_time', title: '创建时间' , search: 'range'},
                    {field: 'type_id', title: '客户状态',selectList: typeList, templet: ea.table.list},
                    {field: 'status', title: '跟踪状态',selectList:{0:"停止跟踪",1:"正在跟踪"},templet: function (d) {
                            var color1={0:"red",1:"green"}[d.status];
                            return '<div style="color:'+color1+'">'+{0:"停止跟踪",1:"正在跟踪"}[d.status]+'</div>';
                        }},
                    {width: 250, title: '操作', templet: ea.table.tool,
                        operat:['edit',
                            [
                                {
                                    text: '回访',
                                    url: init.follow_add_url,
                                    method: 'open',
                                    auth: 'edit',
                                    class: 'layui-btn layui-btn-xs layui-bg-red',
                                    // extend: 'data-full="true"',
                                },
                                {
                                    text: '完善信息',
                                    url: init.follow_edit_url,
                                    method: 'open',
                                    auth: 'edit',
                                    class: 'layui-btn layui-btn-xs layui-bg-orange',
                                    // extend: 'data-full="true"',
                                },{
                                    text: '回访记录',
                                    url: init.follow_url,
                                    method: 'open',
                                    auth: 'edit',
                                    class: 'layui-btn layui-btn-xs layui-bg-cyan',
                                    extend: 'data-full="true"',
                                },
                            ]
                        ]
                    },
                ]], done: function (res, curr, count) {
                    var rate=layui.rate;
                    //循环表数据根据flag状态给行上色
                    $.each(res['data'], function (i, j) {
                            var div =$("[class=layui-table] tr:eq(" + (i + 1) + ") [data-field=type_id]");
                            if (div != null){
                                div.css("color",j.type.color);
                            }
                            rate.render({
                                elem: '#score' + j.id
                                , value: j.star
                                , theme:"#F56D6D"
                                , readonly: true
                            })
                        if(j.b1 == 0){

                            $("[data-open=\"fish.user/edit?id=" + j.id + "\"]").remove();
                        }
                        if(j.b2 == 0){
                            $("[data-open=\"fish.follow/add?id=" + j.id + "\"]").remove();
                            $("[data-open=\"fish.user/edit2?id=" + j.id + "\"]").remove();
                            // $("[data-open=\"fish.follow/index?id=" + (i + 1) + "\"]").remove();
                        }

                    });

                }
            });

            ea.listen();
        },
        add: function () {
            ea.listen();
        },
        edit: function () {
            ea.listen();
        },
        edit2: function () {
            ea.listen();
        },
        fp: function () {
            ea.listen();
        },
    };
    return Controller;
});