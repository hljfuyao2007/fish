define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'fish.illness/index',
        add_url: 'fish.illness/add',
        edit_url: 'fish.illness/edit',
        delete_url: 'fish.illness/delete',
        export_url: 'fish.illness/export',
        modify_url: 'fish.illness/modify',
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