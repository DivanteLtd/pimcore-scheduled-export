
pimcore.registerNS("pimcore.plugin.processmanager.executor.callback.scheduledExport");
pimcore.plugin.processmanager.executor.callback.scheduledExport = Class.create(pimcore.plugin.processmanager.executor.callback.abstractCallback, {
    name : 'scheduledExport',

    initialize : function(){
        this.settings.windowHeight = 600;
    },

    getFieldValue : function(fieldName){
        var value = '';
        if (this.rec) {
            value = this.rec.get('extJsSettings').values[fieldName];
        }
        return value;
    },

    getFormItems : function () {
        var storedThis = this;

        return [
            {
                xtype: 'textfield',
                fieldLabel: t('scheduledexport_objects_folder'),
                name: 'OBJECTS_FOLDER',
                value: this.getFieldValue('OBJECTS_FOLDER'),
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
                },
                allowBlank: false
            },
            {
                xtype: 'combo',
                fieldLabel: t('scheduledexport_grid_config'),
                name: 'GRID_CONFIG',
                displayField: 'name',
                valueField: 'id',
                store: this.getGridConfig(),
                emptyText: t('scheduledexport_select_grid_config'),
                value: this.getFieldValue('GRID_CONFIG'),
                allowBlank: false
            },
            {
                xtype: 'textfield',
                fieldLabel: t('scheduledexport_asset_folder'),
                name: 'ASSET_FOLDER',
                value: this.getFieldValue('ASSET_FOLDER'),
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
                },
                allowBlank: false
            },
            {
                xtype: 'textfield',
                fieldLabel: t('scheduledexport_asset_filename'),
                name: 'ASSET_FILENAME',
                value: this.getFieldValue('ASSET_FILENAME'),
                emptyText: t('scheduledexport_asset_filename_example')
            },
            {
                xtype: 'checkbox',
                fieldLabel: t('scheduledexport_only_changes'),
                name: 'ONLY_CHANGES',
                value: this.getFieldValue('ONLY_CHANGES'),
            },
            {
                xtype: 'checkbox',
                fieldLabel: t('scheduledexport_add_timestamp'),
                name: 'ADD_TIMESTAMP',
                value: this.getFieldValue('ADD_TIMESTAMP'),
            },
            {
                xtype: "form",
                bodyStyle: "padding: 10px; border: 1px;",
                style: "margin: 10px 0 10px 0",
                collapsible: true,
                collapsed: true,
                title: t('scheduledexport_advanced_settings'),
                items: [
                    {
                        xtype: 'textfield',
                        fieldLabel: t('scheduledexport_delimiter'),
                        name: 'DELIMITER',
                        value: this.getFieldValue('DELIMITER'),
                        emptyText: t('scheduledexport_delimiter_example')
                    }, {
                        xtype: 'textfield',
                        fieldLabel: t('scheduledexport_condition'),
                        name: 'CONDITION',
                        value: this.getFieldValue('CONDITION'),
                        emptyText: t('scheduledexport_condition_example')
                    },
                    {
                        xtype: 'textfield',
                        fieldLabel: t('scheduledexport_timestamp'),
                        name: 'TIMESTAMP',
                        value: this.getFieldValue('TIMESTAMP'),
                        emptyText: t('scheduledexport_timestamp_example')
                    },
                    {
                        xtype: 'textfield',
                        fieldLabel: t('scheduledexport_divide_file'),
                        name: 'DIVIDE_FILE',
                        value: this.getFieldValue('DIVIDE_FILE'),
                        emptyText: '1000'
                    },
                    {
                        xtype: 'textfield',
                        fieldLabel: t('scheduledexport_types'),
                        name: 'TYPES',
                        value: this.getFieldValue('TYPES'),
                        emptyText: t('scheduledexport_types_example'),
                    },
                    {
                        xtype: 'checkbox',
                        fieldLabel: t('scheduledexport_add_utf_bom'),
                        name: 'ADD_UTF_BOM',
                        value: this.getFieldValue('ADD_UTF_BOM'),
                    },
                ]
            }
            ,
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
