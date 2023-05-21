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
                toolbar:["refresh","add","export"],
                cols: [[
                    {type: 'checkbox'},
                    {field: 'id', title: 'id'},
                    {field: 'title', title: '标题'},
                    {field: 'color', title: '颜色'},
                    // {field: 'star', title: '跟踪级别'},
                    {field: 'star', title: '跟踪级别',search: false, templet: function (d) {
                            return '<div id="score' + d.id + '"></div>'
                     }},
                    {field: 'sort', title: '排序', edit: 'text'},
                    {field: 'create_time', title: 'create_time'},
                    {field: 'status', title: '是否继续跟踪', templet: ea.table.switch},
                    {width: 250, title: '操作', templet: ea.table.tool,operat:["edit"]},
                ]],done: function (res) {
                    var rate=layui.rate;
                    var data = res.data
                    for (var item in data) {
                        rate.render({
                            elem: '#score' + data[item].id
                            , value: data[item].star
                            ,theme:"#F56D6D"
                            , readonly: true
                        })
                    }
                }
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