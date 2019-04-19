/**
 * @date      05/01/18 13:42
 * @author    Anna Zavodian <azavodian@divante.pl>
 * @copyright Copyright (c) 2017 Divante Ltd. (https://divante.co)
 */

pimcore.registerNS("pimcore.plugin.DivanteScheduledExportBundle");

pimcore.plugin.DivanteScheduledExportBundle = Class.create(pimcore.plugin.admin, {
    getClassName: function () {
        return "pimcore.plugin.DivanteScheduledExportBundle";
    },

    initialize: function () {
        pimcore.plugin.broker.registerPlugin(this);
    },

    pimcoreReady: function (params, broker) {
        // alert("DivanteScheduledExportBundle ready!");
    }
});

var DivanteScheduledExportBundlePlugin = new pimcore.plugin.DivanteScheduledExportBundle();
