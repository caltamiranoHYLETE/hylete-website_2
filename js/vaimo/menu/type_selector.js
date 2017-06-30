var VaimoMenuTypeSelector = _vaimoExtendableBase.extend({
    _types: {},
    _selector: undefined,
    _originalNote: '',
    _mediaBasePath: undefined,
    _typeImageId: 'vaimo-menu-type-img',
    init: function(targetSelector, mediaBasePath) {
        self._selector = targetSelector;

        if (mediaBasePath) {
            this._mediaBasePath = mediaBasePath;
        }

        Event.observe(window, 'load', function() {
            Event.observe(self._selector, 'change', this.selectionChanged.bind(this));
            var $note = this.getNoteElement();

            if ($note) {
                this._originalNote = $note.innerHTML;
            }

            if (this._mediaBasePath) {
                this.addTypeImagePlaceholder(this._typeImageId);
            }

            this.selectionChanged();
        }.bind(this));
    },
    addTypeImagePlaceholder: function(id) {
        var lastCell = $(self._selector).up('tr').select('td').last();
        $(lastCell).update('<img id="' + id + '" style="display:none; margin-left: 20px" src="" />');
    },
    setMenuTypesData: function(data) {
        this._types = data;
    },
    getNoteElement: function() {
        var $select = $(self._selector);
        return $select.siblings('note')[0];
    },
    getTypeImagePath: function(typeCode) {
        return this._mediaBasePath + typeCode + '.png';
    },
    selectionChanged: function() {
        var $select = $(self._selector);
        var selectedValue = $(self._selector).value;
        var typeConfiguration = this._types[selectedValue];
        var $note = $select.siblings('note')[0];

        if ('description' in typeConfiguration) {
            $note.update(typeConfiguration['description']);
        } else {
            $note.update(this._originalNote);
        }

        if (this._mediaBasePath) {
            var imgPath = this.getTypeImagePath(selectedValue);
            var $imgElement = $(this._typeImageId);
            $imgElement.show();
            $imgElement.src = imgPath;
        }
    }
});