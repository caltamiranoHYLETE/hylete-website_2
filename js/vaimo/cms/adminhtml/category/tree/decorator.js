;vaimo.categoryTreeDecorator = vaimo.extendableBaseObject.extend({
    treeNodes: null,
    createNodeOld: undefined,
    renderNewTreeOld: undefined,
    /**
     * Class to update the Ext Tree object with tree node appearances.
     * Used for setting and updating the icons on the categories.
     *
     * @constructor
     */
    construct: function() {
    },
    /**
     * Initiate the appearances of the tree nodes in the tree.
     * The expected format of the treeNodes param is: {'<node_id>': '<css_class>'}
     *
     * Example:
     *
     * var treeDecorator = new CategoryTreeDecorator(tree);
     * treeDecorator.initiateTreeNodesAppearances({'2': 'vcms-icon-startpage', '45': 'vcms-icon-cms'})
     *
     * @param treeNodes
     */
    initiateTreeNodesAppearances: function(treeNodes) {
        this.treeNodes = treeNodes;

        Ext.onReady(function() {
            this.setTreeNodeAppearances(treeNodes);

            if (typeof this.createNodeOld == 'undefined') {
                this.createNodeOld = categoryLoader.createNode;
                categoryLoader.createNode = function(config) {
                    var node = this.createNodeOld(config);

                    setTimeout(function() {
                        this.updateTreeNodeAppearanceById(node.id);
                    }.bind(this), 100);

                    return node;
                }.bind(this);
            }

            if (typeof this.renderNewTreeOld == 'undefined') {
                this.renderNewTreeOld = _renderNewTree;

                _renderNewTree = function(config, storeParam) {
                    this.renderNewTreeOld(config, storeParam);
                    this.setTreeNodeAppearances(this.treeNodes);
                }.bind(this);
            }
        }.bind(this));
    },
    setTreeNodeAppearances: function(treeNodes) {
        for (var id in treeNodes) {
            this.setTreeNodeAppearance(id, treeNodes[id]);
        }
    },
    setTreeNodeAppearance: function(id, className) {
        var treeNode = tree.getNodeById(id);

        if (typeof treeNode == 'undefined') {
            return;
        }

        var node = treeNode.getUI().elNode;
        var $node = $(node);

        if (className == '') {
            this.clearTreeNodeClasses($node);
        } else {
            $node.addClassName(className);
        }

    },
    getTreeNodeById: function(id) {
        if (id in this.treeNodes) {
            return this.treeNodes[id];
        }

        return null;
    },
    updateTreeNodeAppearanceById: function(id) {
        var treeNode = this.getTreeNodeById(id);

        if (treeNode != null) {
            this.setTreeNodeAppearance(id, treeNode);
        }
    },
    clearTreeNodeClasses: function($node) {
        for (var i=0; i <  $node.classList.length; ++i) {
            if(/^vcms-icon-.*/.test($node.classList[i])){
                $node.classList.remove($node.classList[i]);
            }
        }
    }
});