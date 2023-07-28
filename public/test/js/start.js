pimcore.registerNS("pimcore.plugin.TestBundle");

pimcore.plugin.TestBundle = Class.create({
    initialize: function () {
        // document.addEventListener(pimcore.events.postSaveObject, this.postSaveObject.bind(this));
        document.addEventListener(pimcore.events.preSaveObject, this.preSaveObject.bind(this));
    },

    preSaveObject: function (object, type) {
        // let colors = object.detail.object.data.data.color;
        let colors = object.detail.object.edit.dataFields.color.component.lastValue;
        console.log("Colors:", colors);

        let count = colors.length;
        console.log("count", count);

        // if (count > 2) {
            Ext.Msg.alert('Colors should not be more than 2.');
           
            throw new pimcore.error.ValidationException('Validation Error');
            return false;

        // }
    },

   

    pimcoreReady: function (e) {
    }
});

var TestBundlePlugin = new pimcore.plugin.TestBundle();
