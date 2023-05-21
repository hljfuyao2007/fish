define(["jquery", "easy-admin"], function ($, ea) {
    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'fish.follow/index?user_id='+getQueryString("id"),
        add_url: 'fish.follow/add',
        edit_url: 'fish.follow/edit',
        delete_url: 'fish.follow/delete',
        export_url: 'fish.follow/export',
        modify_url: 'fish.follow/modify',
    };

    var Controller = {

        index: function () {
            ea.table.render({
                init: init,
                toolbar:['refresh','export'],
                cols: [[
                    {type: 'checkbox'},
                    {field: 'id', title: 'id'},
                    {field: 'user.nick_name', title: '用户',search: false,},

                    {field: 'admin.username', title: '回访人',search: false,},
                    {field: 'star', title: '回访质量(星级)',search: false, templet: function (d) {
                        return '<div id="score' + d.id + '" title="' + d.type.title + '"></div>'
                    }},
                    {field: 'type.title', title: '标记客户状态',search: false},
                    {field: 'status', title: '是否继续跟踪', templet: function(e){return e.status==0?"否":"是";},search: false,},
                    // {field: 'images', title: '图片说明', templet: ea.table.image},
                    {field: 'create_time', title: 'create_time',search: 'range'},
                    {width: 250, title: '操作', templet: ea.table.tool,operat:[
                        [
                            {
                                text: '查看',
                                url: init.edit_url,
                                method: 'open',
                                auth: 'edit',
                                class: 'layui-btn layui-btn-xs layui-btn-success',

                            }
                        ]
                        ]},
                ]],done: function (res) {
                    var rate=layui.rate;

                    var data = res.data
                    console.log("data",data)
                    for (var item in data) {
                        rate.render({
                            elem: '#score' + data[item].id
                            , value: data[item].star
                            , readonly: true
                        })
                    }
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
    };
    return Controller;
});
function getQueryString(name,mr) {
    if(!mr)mr=null;
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return decodeURI(r[2]);
    return mr;
}