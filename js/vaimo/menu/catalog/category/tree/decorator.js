/** This is a global function instantiated on category edit page in admin. */
var _renderNewTree, _renderNewTreeOld, _vaimoMenuCreateNodeOld = undefined;
var VaimoMenuCatalogCategoryTreeDecorator = _vaimoExtendableBase.extend({
    callStack: [],
    construct: function() {
        if (typeof _vaimoMenuCreateNodeOld == 'undefined') {
            var _styleHandler = this._applyStyleToNode;
            Ext.onReady(function() {
                _vaimoMenuCreateNodeOld = categoryLoader.createNode;
                categoryLoader.createNode = function(config) {
                    var node = _vaimoMenuCreateNodeOld(config);

                    setTimeout(function() {
                        var _node = node.getUI().elNode;
                        if (_node && config.vm_style) {
                            _styleHandler($(_node), config.vm_style);
                        }
                    }, 100);

                    return node;
                };
            });
        }
    },
    initiateDelayedExecution: function() {
        if (typeof _renderNewTreeOld == 'undefined') {
            _renderNewTreeOld = _renderNewTree;
        }
        _renderNewTree = function(config, storeParam) {
            _renderNewTreeOld(config, storeParam);
            $(this.callStack).each(function(item) {
                item();
            });
            this.callStack = [];
        }.bind(this);
    },
    updateCategoryTreeItemClassesOnTreeRender: function(definitions, className) {
        this.callStack.push(function() {
            this.updateCategoryTreeItemClasses(definitions, className);
        }.bind(this));
    },
    _applyStyleToNode: function($node, style) {
        var $icon = $node.down('.x-tree-node-icon');
        var $link = $node.down('a');

        $icon.setStyle(style);
        $link.setStyle(style);
    },
    updateCategoryTreeItemClasses: function(definitions, decorator, delay) {
        var _styleHandler = this._applyStyleToNode;
        var updateHandler = function() {
            if (definitions) {
                $(Object.keys(definitions)).each(function(i) {
                    var treeNode = tree.getNodeById(i);
                    if (treeNode) {
                        var node = treeNode.getUI().elNode;
                        var $node = $(node);

                        if (typeof decorator == 'string') {
                            if (definitions[i]) {
                                $node.addClassName(decorator);
                            } else {
                                $node.removeClassName(decorator);
                            }
                        } else {
                            _styleHandler($node, decorator[definitions[i]]);
                        }
                    }
                });
            }
        }.bind(this);

        if (delay) {
            setTimeout(updateHandler, delay);
        } else {
            updateHandler();
        }
    }
});