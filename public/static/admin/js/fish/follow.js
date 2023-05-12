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
                    {type: 'checkbox'},
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