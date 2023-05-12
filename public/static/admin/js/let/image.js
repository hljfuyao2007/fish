define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'let.image/index',
        add_url: 'let.image/add',
        edit_url: 'let.image/edit',
        delete_url: 'let.image/delete',
        export_url: 'let.image/export',
        modify_url: 'let.image/modify',
    };

    var Controller = {

        index: function () {
            ea.table.render({
                init: init,
                cols: [[
                    {type: 'checkbox'},
                    {field: 'id', title: 'id'},
                    {field: 'image', title: '图片', templet: ea.table.image},
                    {field: 'title', title: '标题'},
                    {field: 'type_id', title: '分类'},
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