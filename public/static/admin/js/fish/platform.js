define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'fish.platform/index',
        add_url: 'fish.platform/add',
        edit_url: 'fish.platform/edit',
        delete_url: 'fish.platform/delete',
        export_url: 'fish.platform/export',
        modify_url: 'fish.platform/modify',
    };

    var Controller = {

        index: function () {
            ea.table.render({
                init: init,
                cols: [[
                    {type: 'checkbox'},                    {field: 'id', title: 'id'},                    {field: 'name', title: '平台名称'},                    {field: 'logo', title: '平台标识', templet: ea.table.image},                    {field: 'notes', title: '备注'},                    {field: 'create_time', title: 'create_time'},                    {width: 250, title: '操作', templet: ea.table.tool},
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