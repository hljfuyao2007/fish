define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'reservation/index',
        add_url: 'reservation/add',
        edit_url: 'reservation/edit',
        delete_url: 'reservation/delete',
        export_url: 'reservation/export',
        modify_url: 'reservation/modify',
    };

    var Controller = {

        index: function () {
            ea.table.render({
                init: init,
                cols: [[
                    {type: 'checkbox'},                    {field: 'id', title: 'id'},                    {field: 'name', title: '姓名'},                    {field: 'phone', title: '电话'},                    {field: 'images', title: '图片', templet: ea.table.image},                    {field: 'create_time', title: '创建时间'},                    {width: 250, title: '操作', templet: ea.table.tool},
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