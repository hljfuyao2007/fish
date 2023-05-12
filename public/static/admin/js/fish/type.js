define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'fish.type/index',
        add_url: 'fish.type/add',
        edit_url: 'fish.type/edit',
        delete_url: 'fish.type/delete',
        export_url: 'fish.type/export',
        modify_url: 'fish.type/modify',
    };

    var Controller = {

        index: function () {
            ea.table.render({
                init: init,
                cols: [[
                    {type: 'checkbox'},                    {field: 'id', title: 'id'},                    {field: 'title', title: 'title'},                    {field: 'color', title: 'color'},                    {field: 'star', title: '0不再跟踪 数字越高级别越强'},                    {field: 'sort', title: '排序', edit: 'text'},                    {field: 'create_time', title: 'create_time'},                    {field: 'status', title: '0关闭 1继续跟踪', templet: ea.table.switch},                    {width: 250, title: '操作', templet: ea.table.tool},
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