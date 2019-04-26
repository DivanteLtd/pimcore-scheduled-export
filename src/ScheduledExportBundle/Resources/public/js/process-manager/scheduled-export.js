/**
 * @date      05/01/18 13:57
 * @author    Anna Zavodian <azavodian@divante.pl>
 * @copyright Copyright (c) 2017 Divante Ltd. (https://divante.co)
 */

$(document).on("processmanager.ready", function() {
    processmanager.executable.types.scheduledexport = Class.create(pimcore.plugin.processmanager.executable.abstractType, {
        getItems: function () {
            var storedThis = this;
            return [
                {
                    xtype: 'textfield',
                    fieldLabel: t('scheduledexport_objects_folder'),
                    name: 'objects_folder',
                    value: this.data.settings.objects_folder,
                    cls: 'input_drop_target',
                    canDrop: function(data)
                    {
                        return storedThis.isObjectFolder(data.records[0].data);
                    },
                    listeners: {
                        'render': function (el) {
                            new Ext.dd.DropZone(el.getEl(), {
                                reference: this,
                                ddGroup: 'element',
                                getTargetFromEvent: function (e) {
                                    return this.getEl();
                                }.bind(el),

                                onNodeOver: function (target, dd, e, data) {
                                    if (this.canDrop(data)) {
                                        return Ext.dd.DropZone.prototype.dropAllowed;
                                    } else {
                                        return Ext.dd.DropZone.prototype.dropNotAllowed;
                                    }
                                }.bind(el),

                                onNodeDrop: function (target, dd, e, data) {
                                    if (this.canDrop(data)) {
                                        this.setValue(data.records[0].data.path);
                                        return true;
                                    }
                                    return false;
                                }.bind(el)
                            });
                        },
                        'change': function (el) {
                            console.log(el);
                        }
                    }
                }, {
                    xtype: 'combo',
                    fieldLabel: t('scheduledexport_grid_config'),
                    name: 'grid_config',
                    displayField: 'name',
                    valueField: 'id',
                    store: this.getGridConfig(),
                    emptyText: t('scheduledexport_select_grid_config'),
                    value: this.data.settings.grid_config,
                    allowBlank: false
                }, {
                    xtype: 'textfield',
                    fieldLabel: t('scheduledexport_asset_folder'),
                    name: 'asset_folder',
                    value: this.data.settings.asset_folder,
                    cls: 'input_drop_target',
                    canDrop: function(data)
                    {
                        return storedThis.isAssetFolder(data.records[0].data);
                    },
                    listeners: {
                        'render': function (el) {
                            new Ext.dd.DropZone(el.getEl(), {
                                reference: this,
                                ddGroup: 'element',
                                getTargetFromEvent: function (e) {
                                    return this.getEl();
                                }.bind(el),

                                onNodeOver: function (target, dd, e, data) {
                                    if (this.canDrop(data)) {
                                        return Ext.dd.DropZone.prototype.dropAllowed;
                                    } else {
                                        return Ext.dd.DropZone.prototype.dropNotAllowed;
                                    }
                                }.bind(el),

                                onNodeDrop: function (target, dd, e, data) {
                                    if (this.canDrop(data)) {
                                        this.setValue(data.records[0].data.path);
                                        return true;
                                    }
                                    return false;
                                }.bind(el)
                            });
                        }
                    }
                }, {
                    xtype: 'textfield',
                    fieldLabel: t('scheduledexport_asset_filename'),
                    name: 'asset_filename',
                    value: this.data.settings.asset_filename,
                    emptyText: t('scheduledexport_asset_filename_example')
                }, {
                    xtype: 'textfield',
                    fieldLabel: t('scheduledexport_condition'),
                    name: 'condition',
                    value: this.data.settings.condition,
                    emptyText: t('scheduledexport_condition_example')
                }, {
                    xtype: 'checkbox',
                    fieldLabel: t('scheduledexport_only_changes'),
                    name: 'only_changes',
                    value: this.data.settings.only_changes,
                }, {
                    xtype: 'checkbox',
                    fieldLabel: t('scheduledexport_add_timestamp'),
                    name: 'add_timestamp',
                    value: this.data.settings.add_timestamp,
                }
            ];
        },

        getGridConfig: function () {
            var gridConfigStore = Ext.create("Ext.data.JsonStore", {
                id: "gridConfigStore",
                proxy: {
                    type: "ajax",
                    url: "/admin/scheduled-export/grid-config/get-list",
                    reader: {
                        type: 'json',
                        rootProperty: 'result'
                    }
                },
                fields: ['id', 'name']
            });

            return gridConfigStore.load();
        },

        isAssetFolder: function(data) {
            return data.elementType === 'asset' && data.type === 'folder';
        },

        isObjectFolder: function(data) {
            return data.elementType === 'object' && data.type === 'folder';
        }
    });
});
