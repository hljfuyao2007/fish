define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'fish.follow/index',
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
                cols: [[
                    {type: 'checkbox'},                    {field: 'id', title: 'id'},                    {field: 'user_id', title: 'user_id'},                    {field: 'star', title: '回访质量(星级)'},                    {field: 'type_id', title: '标记客户状态'},                    {field: 'status', title: '是否继续跟踪', templet: ea.table.switch},                    {field: 'images', title: '图片说明', templet: ea.table.image},                    {field: 'notes', title: '回访备注'},                    {field: 'create_time', title: 'create_time'},                    {width: 250, title: '操作', templet: ea.table.tool},
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