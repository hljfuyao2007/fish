define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'image/index',
        add_url: 'image/add',
        edit_url: 'image/edit',
        delete_url: 'image/delete',
        export_url: 'image/export',
        modify_url: 'image/modify',
    };

    var Controller = {

        index: function () {
            ea.table.render({
                init: init,
                cols: [[
                    {type: 'checkbox'},
                    {field: 'id', title: 'id'},
                    {field: 'img',minWidth: 80, search: false, title: '图片' , templet: ea.table.image},

                    {field: 'title', title: '图片说明'},
                    {field: 'type', title: '图片分类',templet:function(e){
                            if(e.type==1){
                                return "主页图片";
                            }else{
                                return "展示页图片";
                            }
                        }
                    },
                    {field: 'order', title: '排序', edit: 'number'},
                    {field: 'create_time', title: '时间'},

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