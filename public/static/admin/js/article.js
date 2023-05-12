define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'article/index',
        add_url: 'article/add',
        edit_url: 'article/edit',
        delete_url: 'article/delete',
        export_url: 'article/export',
        modify_url: 'article/modify',
    };

    var Controller = {
        index: function () {
            ea.table.render({
                init: init,
                cols: [[
                    {type: 'checkbox'},
                    {field: 'id', title: 'id'},
                    {field: 'title', title: '标题'},
                    {field: 'author', title: '作者'},
                    {field: 'type_id', title: '分类' , search: 'select', selectList:typeJson},
                    {field: 'rd', title: '热点',search: 'select', selectList:["关","开"], templet: ea.table.switch},
                    {field: 'tj', title: '推荐',search: 'select', selectList:["关","开"], templet: ea.table.switch},
                    {field: 'sort', title: '排序', edit: 'number'},
                    {field: 'create_time', title: '上传时间'},
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