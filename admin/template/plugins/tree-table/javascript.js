$(function () {
    var
        $table = $('.table-tree'),
        rows = $table.find('tr');

    rows.each(function (index, row) {
        var
            $row = $(row),
            level = $row.data('level'),
            id = $row.data('id'),
            $columnName = $row.find('td[data-column="name"]'),
            children = $table.find('tr[data-parent="' + id + '"]'),
            inactive_children = children.filter("tr.inactive"),
            active_children = children.not('tr.inactive');

        if (active_children.length > 0 && !$row.hasClass("inactive")){
            var link = $row.find("td").last().find("a");
            link.attr("class","confirm-tree-disactivate");
        }
        else if(inactive_children.length > 0 && $row.hasClass("inactive")){
            var link = $row.find("td").last().find("a");
            link.attr("class","confirm-tree-activate");
        }

        if (children.length) {
            var expander = $('<a class="treegrid-expander glyphicon glyphicon-chevron-right"></a>');
            $columnName.prepend(expander);

            children.hide();

            expander.on('click', function (e) {
                var $target = $(e.target);
                if ($target.hasClass('glyphicon-chevron-right')) {
                    $target
                        .removeClass('glyphicon-chevron-right')
                        .addClass('glyphicon-chevron-down');

                    children.show();
                } else {
                    $target
                        .removeClass('glyphicon-chevron-down')
                        .addClass('glyphicon-chevron-right');

                    reverseHide($table, $row);
                }
            });
        }

        $columnName.prepend('<span class="treegrid-indent" style="display:inline-block;width:' + 25 * level + 'px"></span>');
    });

    $('a.confirm-tree-disactivate').on("click", function(e){
        var a = $(this);
        var title = a.attr('title');
        var msg = GetTranslation('confirm-tree-disactivate').replace(/%Title%/g, title);
        ModalConfirm(msg, function(){
            window.location.href = a.attr('href');
        });
        e.preventDefault();
    });
    $('a.confirm-tree-activate').on("click", function(e){
        var a = $(this);
        var title = a.attr('title');
        var msg = GetTranslation('confirm-tree-activate').replace(/%Title%/g, title);
        ModalConfirm(msg, function(){
            window.location.href = a.attr('href');
        });
        e.preventDefault();
    });

    // Reverse hide all elements
    reverseHide = function (table, element) {
        var
            $element = $(element),
            id = $element.data('id'),
            children = table.find('tr[data-parent="' + id + '"]');

        if (children.length) {
            children.each(function (i, e) {
                reverseHide(table, e);
            });

            $element
                .find('.glyphicon-chevron-down')
                .removeClass('glyphicon-chevron-down')
                .addClass('glyphicon-chevron-right');

            children.hide();
        }
    };
});
