define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'let.user/index',
        add_url: 'let.user/add',
        edit_url: 'let.user/edit',
        delete_url: 'let.user/delete',
        export_url: 'let.user/export',
        modify_url: 'let.user/modify',
    };

    var Controller = {

        index: function () {
            ea.table.render({
                init: init,
                toolbar:['refresh','delete','export'],
                cols: [[
                    {type: 'checkbox'},
                    {field: 'id', title: 'id'},
                    {field: 'nickname', title: '昵称'},
                    {field: 'avatar', title: '头像',templet: ea.table.image},
                    // {field: 'openid', title: 'openid'},
                    // {field: 'openid_app', title: 'openid_app'},
                    // {field: 'unionid', title: 'unionid'},
                    {field: 'phone', title: '手机号'},
                    // {field: 'password', title: '密码'},
                    // {field: 'sex', title: '性别'},
                    // {field: 'province', title: '省'},
                    // {field: 'city', title: '市'},
                    // {field: 'area', title: '区'},
                    // {field: 'address', title: '详细地址'},
                    // {field: 'realname', title: '真实姓名'},
                    // {field: 'id_card', title: '身份证'},
                    {field: 'create_time', minWidth: 80, title: '创建时间', search: 'range'},
                    {width: 250, title: '操作', templet: ea.table.tool},
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