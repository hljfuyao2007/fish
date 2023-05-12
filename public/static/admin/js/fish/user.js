define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'fish.user/index',
        add_url: 'fish.user/add',
        edit_url: 'fish.user/edit',
        delete_url: 'fish.user/delete',
        export_url: 'fish.user/export',
        modify_url: 'fish.user/modify',
    };

    var Controller = {

        index: function () {
            ea.table.render({
                init: init,
                cols: [[
                    {type: 'checkbox'},                    {field: 'id', title: 'id'},                    {field: 'nick_name', title: '昵称'},                    {field: 'wechart', title: '微信号'},                    {field: 'wechart_nickname', title: '微信昵称'},                    {field: 'phone', title: '电话号'},                    {field: 'platform_id', title: '平台'},                    {field: 'platform_account', title: '平台账号'},                    {field: 'platform_nickname', title: '平台昵称'},                    {field: 'realname', title: 'realname'},                    {field: 'idcard', title: '身份证号'},                    {field: 'star', title: '用户星级'},                    {field: 'notes', title: '备注'},                    {field: 'images', title: '备注图片', templet: ea.table.image},                    {field: 'create_time', title: 'create_time'},                    {field: 'type_id', title: '客户状态'},                    {field: 'status', title: '是否跟踪中', templet: ea.table.switch},                    {width: 250, title: '操作', templet: ea.table.tool},
                ]],
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