;var console = console || {};
console.error = console.error || function(){};

var vaimo = vaimo || {};
vaimo.extendableBaseObject=function(){};
vaimo.extendableBaseObject.prototype.construct = function() {};
vaimo.extendableBaseObject.extend = function(definitions) {
    var classDef = function() {
        if (arguments[0] !== Class) { this.construct.apply(this, arguments); }
    };

    var prototype = new this(Class);
    var superClass = this.prototype;

    for (var key in definitions) {
        var item = definitions[key];
        if (item instanceof Function) item.$ = superClass;
        prototype[key] = item;
    }

    classDef.prototype = prototype;
    classDef.extend = this.extend;

    return classDef;
};