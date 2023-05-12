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