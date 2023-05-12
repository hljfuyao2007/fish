define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'let.video/index',
        add_url: 'let.video/add',
        edit_url: 'let.video/edit',
        delete_url: 'let.video/delete',
        export_url: 'let.video/export',
        modify_url: 'let.video/modify',
    };

    var Controller = {

        index: function () {
            ea.table.render({
                init: init,
                cols: [[
                    {type: 'checkbox'},
                    {field: 'id', title: 'id'},
                    {field: 'title', title: '标题'},
                    // {field: 'video', title: '视频'},
                    {field: 'image', title: '封面图片', templet: ea.table.image},
                    {field: 'create_time', title: 'create_time'},
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