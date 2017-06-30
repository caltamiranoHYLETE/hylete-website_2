jquery.gridster.min.js has been modified to remove conflict between Prototype and jQuery. Following code snippet has been replaced.

    //Old
    t.$||t.jQuery

    //New
    t.jQuery

    //Unminified version
    root.$ || root.jQuery