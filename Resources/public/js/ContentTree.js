$(function(){

    var $ContentTree = $('#ContentTree');

    $ContentTree.height($('body').height() - $ContentTree.offset().top);

    $ContentTree
        .on('create_node.jstree', function(e, data){
            var node = $.extend(true, {}, data.node);
            node.type = "default";
            node.name = node.text;
            node.parentId = data.node.parent;

            $.ajax({
                url: $ContentTree.data('add'),
                data: node,
                dataType: 'json',
                success: function (result) {
                    data.instance.set_id(node, result.id);
                },
                error: function () {
                    alert($ContentTree.data('addError'));
                }
            });
        })
        .on('rename_node.jstree', function(e, data){
            $.ajax({
                url: $ContentTree.data('edit'),
                data: {
                    "id": data.node.id,
                    "name": data.node.text
                },
                dataType: 'json',
                success: function (result) {},
                error: function () {
                    alert($ContentTree.data('renameError'));
                }
            });
        })
        .on('delete_node.jstree', function(e, data){
            $.ajax({
                url: $ContentTree.data('delete'),
                data: {
                    "id": data.node.id
                },
                dataType: 'json',
                success: function (result) {
                    showNodeDetails();
                },
                error: function () {
                    alert($ContentTree.data('deleteError'));
                }
            });
        })
        .on('move_node.jstree', function(e, data){
            $.ajax({
                url: $ContentTree.data('move'),
                data: {
                    "id": data.node.id,
                    "parent": data.parent,
                    "position": data.position
                },
                dataType: "json",
                success: function(){},
                error: function(){
                    alert($ContentTree.data('moveError'));
                }
            });
        })
        .on('search.jstree', function(e, data){
            var $result = $(this).find('.jstree-search:eq(0)');
            if ($result.length > 0) {
                $result[0].scrollIntoView();
            }
        })
        .on('dblclick.jstree', function(event){
            var $node = $(event.target).closest("li");
            if ($node.hasClass('jstree-leaf')) {
                $('#EditContentTree').trigger('click');
            }
        })
        .on('click.jstree', function(event){
            showNodeDetails();
        })
        .on('ready.jstree', function(){
            showNodeDetails();
        });

    $ContentTree.jstree({
        "core" : {
            "themes": {
                "variant": "large"
            },
            "check_callback": function (op, node, par, pos, more) {
                if(op === "delete_node") {
                    return confirm(
                        $ContentTree.data('deleteConfirm')
                            .replace("%object%", node.text)
                    );
                }
            },
            "state": {
                "key": "ContentTree"
            },
            "data": {
                "url": $ContentTree.data('source')
            }
        },
        "types" : {
            "#" : {
                "max_children" : 1,
                "valid_children" : ["root"]
            },
            "root" : {
                "icon": "glyphicon glyphicon-folder-open",
                "valid_children" : ["default", "module"]
            },
            "default": {
                "icon": "glyphicon glyphicon-file",
                "valid_children" : ["default", "module"]
            },
            "module" : {
                "icon": "glyphicon glyphicon-cog",
                "valid_children" : []
            }
        },
        "contextmenu" : {
            "items": function($node) {
                return {
                    "Create": {
                        "separator_before": false,
                        "separator_after": false,
                        "label": $('#AddContentTree').text().trim(),
                        "action": function () {
                            $('#AddContentTree').trigger('click');
                        }
                    },
                    "Rename": {
                        "separator_before": false,
                        "separator_after": false,
                        "label": $('#EditContentTree').text().trim(),
                        "action": function () {
                            $('#EditContentTree').trigger('click');
                        }
                    },
                    "Remove": {
                        "separator_before": false,
                        "separator_after": false,
                        "label": $('#DeleteContentTree').text().trim(),
                        "action": function () {
                            $('#DeleteContentTree').trigger('click');
                        }
                    }
                };
            }
        },
        "plugins": [ "wholerow", "dnd", "search", "state", "contextmenu", "types" ]
    });

    var to = false;
    $('#SearchContentTree').keyup(function () {
        if(to) { clearTimeout(to); }
        to = setTimeout(function () {
            var v = $('#SearchContentTree').val();
            $ContentTree.jstree(true).search(v);
        }, 250);
    });

    $('#AddContentTree').click(function(){
        var ref = $ContentTree.jstree(true),
            sel = ref.get_selected();
        if(!sel.length) { return false; }
        sel = sel[0];
        sel = ref.create_node(sel, { "type": "default" }, 'first');
        if(sel) {
            ref.edit(sel);
        }
    });

    $('#EditContentTree').click(function(){
        var ref = $ContentTree.jstree(true),
            sel = ref.get_selected();
        if(!sel.length) { return false; }

        window.location = $(this).data('href').replace('-page-id-', sel);
    });

    $('#DeleteContentTree').click(function(){
        var ref = $ContentTree.jstree(true),
            sel = ref.get_selected();
        if(!sel.length) { return false; }
        ref.delete_node(sel);
    });

    var showNodeDetails = function(){
        $('#ContentNodeDetailsPanel').html('');
        setTimeout(function(){
            var $node = $('.jstree-clicked').closest('.jstree-node');

            if (!$node.attr('id')) return false;

            $.ajax({
                url: $ContentTree.data('details'),
                data: {
                    "id": $node.attr('id')
                },
                dataType: "json",
                success: function(result){
                    $('#ContentNodeDetailsPanel').html(result.html);
                },
                error: function(){
                    $('#ContentNodeDetailsPanel').html('');
                }
            });
        }, 10);
    };

});