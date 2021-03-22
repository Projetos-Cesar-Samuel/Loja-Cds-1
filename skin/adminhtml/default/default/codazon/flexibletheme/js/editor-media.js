(function($j) {
    if (typeof window.Codazon === 'undefined') {
        window.Codazon = {};
    }
    
    if(typeof window.CdzMediabrowserUtility === 'undefined') {
        window.CdzMediabrowserUtility = {
            options: {
                parent: '[data-role=image-container]',
                targetElement: '[data-type=image]',
                imageUrl: false,
            },
            initialized: false,
            init: function() {
                var self = this, conf = this.options;                 
                if (typeof Codazon.imageUrl != 'undefined') {
                    this.options.imageUrl = Codazon.imageUrl;
                }
                this.targetElement = undefined;
            },
            windowId: 'browser_window',
            modal: false,
            openDialog: function(btn, width, height, title, options) {
                var self = this, conf = this.options;
                this.targetParent = $j(btn).parents(conf.parent).first();
                this.targetElement = this.targetParent.find(conf.targetElement).first().get(0);
                
                if (conf.imageUrl) {
                    var url = conf.imageUrl;
                    var windowId = this.windowId,
                        self = this;
                    if ($(windowId) && typeof(Windows) != 'undefined') {
                        Windows.focus(windowId);
                        return;
                    }
                    this.modal = Dialog.info(null, $j.extend({
                        closable:     true,
                        resizable:    false,
                        draggable:    true,
                        className:    'magento',
                        windowClassName:    'popup-window',
                        title:        title || 'Insert File...',
                        top:          50,
                        width:        width || 950,
                        height:       height || 600,
                        zIndex:       options && options.zIndex || 1000,
                        recenterAuto: false,
                        hideEffect:   Element.hide,
                        showEffect:   Element.show,
                        id:           windowId,
                        onClose:      this.closeDialog.bind(this)
                    }, options));
                    
                    new Ajax.Updater('modal_dialog_message', url, {evalScripts: true});
                }
            },
            closeDialog: function() {
                this.targetElement = undefined;
                this.targetParent = undefined;
                //this.modal.modal('closeModal');
                
                this.modal.close();
            }
        }
        $j(document).ready(function() {
            CdzMediabrowserUtility.init();
        });
    }
})(jQuery);

MediabrowserUtility = {
    openDialog: function(url, width, height, title, options) {
        if ($('browser_window') && typeof(Windows) != 'undefined') {
            Windows.focus('browser_window');
            return;
        }
        this.dialogWindow = Dialog.info(null, Object.extend({
            closable:     true,
            resizable:    false,
            draggable:    true,
            className:    'magento',
            windowClassName:    'popup-window',
            title:        title || 'Insert File...',
            top:          50,
            width:        width || 950,
            height:       height || 600,
            zIndex:       options && options.zIndex || 1000,
            recenterAuto: false,
            hideEffect:   Element.hide,
            showEffect:   Element.show,
            id:           'browser_window',
            onClose: this.closeDialog.bind(this)
        }, options || {}));
        new Ajax.Updater('modal_dialog_message', url, {evalScripts: true});
    },
    closeDialog: function(window) {
        if (!window) {
            window = this.dialogWindow;
        }
        if (window) {
            // IE fix - hidden form select fields after closing dialog
            WindowUtilities._showSelect();
            window.close();
        }
    }
};

Mediabrowser = Class.create();
Mediabrowser.prototype = {
    targetElementId: null,
    contentsUrl: null,
    onInsertUrl: null,
    newFolderUrl: null,
    deleteFolderUrl: null,
    deleteFilesUrl: null,
    headerText: null,
    tree: null,
    currentNode: null,
    storeId: null,
    initialize: function (setup) {
        this.newFolderPrompt = setup.newFolderPrompt;
        this.deleteFolderConfirmationMessage = setup.deleteFolderConfirmationMessage;
        this.deleteFileConfirmationMessage = setup.deleteFileConfirmationMessage;
        this.targetElementId = setup.targetElementId;
        this.contentsUrl = setup.contentsUrl;
        this.onInsertUrl = setup.onInsertUrl;
        this.newFolderUrl = setup.newFolderUrl;
        this.deleteFolderUrl = setup.deleteFolderUrl;
        this.deleteFilesUrl = setup.deleteFilesUrl;
        this.headerText = setup.headerText;
    },
    setTree: function (tree) {
        this.tree = tree;
        this.currentNode = tree.getRootNode();
    },

    getTree: function (tree) {
        return this.tree;
    },

    selectFolder: function (node, event) {
        this.currentNode = node;
        this.hideFileButtons();
        this.activateBlock('contents');

        if(node.id == 'root') {
            this.hideElement('button_delete_folder');
        } else {
            this.showElement('button_delete_folder');
        }

        this.updateHeader(this.currentNode);
        this.drawBreadcrumbs(this.currentNode);

        this.showElement('loading-mask');
        new Ajax.Request(this.contentsUrl, {
            parameters: {node: this.currentNode.id},
            evalJS: true,
            onSuccess: function(transport) {
                try {
                    this.currentNode.select();
                    this.onAjaxSuccess(transport);
                    this.hideElement('loading-mask');
                    if ($('contents') != undefined) {
                        $('contents').update(transport.responseText);
                        $$('div.filecnt').each(function(s) {
                            Event.observe(s.id, 'click', this.selectFile.bind(this));
                            Event.observe(s.id, 'dblclick', this.insert.bind(this));
                        }.bind(this));
                    }
                } catch(e) {
                    alert(e.message);
                }
            }.bind(this)
        });
    },

    selectFolderById: function (nodeId) {
        var node = this.tree.getNodeById(nodeId);
        if (node.id) {
            this.selectFolder(node);
        }
    },

    selectFile: function (event) {
        var div = Event.findElement(event, 'DIV');
        $$('div.filecnt.selected[id!="' + div.id + '"]').each(function(e) {
            e.removeClassName('selected');
        });
        div.toggleClassName('selected');
        if(div.hasClassName('selected')) {
            this.showFileButtons();
        } else {
            this.hideFileButtons();
        }
    },

    showFileButtons: function () {
        this.showElement('button_delete_files');
        this.showElement('button_insert_files');
    },

    hideFileButtons: function () {
        this.hideElement('button_delete_files');
        this.hideElement('button_insert_files');
    },

    handleUploadComplete: function(files) {
        $$('div[class*="file-row complete"]').each(function(e) {
            $(e.id).remove();
        });
        this.selectFolder(this.currentNode);
    },

    insert: function(event) {
        var div;
        if (event != undefined) {
            div = Event.findElement(event, 'DIV');
        } else {
            $$('div.selected').each(function (e) {
                div = $(e.id);
            });
        }
        if ($(div.id) == undefined) {
            return false;
        }
        var targetEl = this.getTargetElement();
        if (! targetEl) {
            alert("Target element not found for content update");
            Windows.close('browser_window');
            return;
        }

        var params = {filename:div.id, node:this.currentNode.id, store:this.storeId};

        if (targetEl.tagName.toLowerCase() == 'textarea') {
            params.as_is = 1;
        }
        
        if (typeof window.CdzMediabrowserUtility.targetElement != 'undefined') {
            params.as_is = 1;
            var needFilter = true;
        } else {
            var needFilter = false;
        }

        new Ajax.Request(this.onInsertUrl, {
            parameters: params,
            onSuccess: function(transport) {
                try {
                    this.onAjaxSuccess(transport);
                    if (this.getMediaBrowserOpener()) {
                        self.blur();
                    }
                    Windows.close('browser_window');
                    if (targetEl.tagName.toLowerCase() == 'input') {
                        if (needFilter) {
                            var src = transport.responseText;
                            transport.responseText.gsub(/\{\{media(.*?)\}\}/i, function (match) {
                                src = match[0];
                            });
                            targetEl.value = src;
                            jQuery(targetEl).trigger('change');
                        } else {
                            targetEl.value = transport.responseText;
                        }
                    } else {
                        updateElementAtCursor(targetEl, transport.responseText);
                        if (varienGlobalEvents) {
                            varienGlobalEvents.fireEvent('tinymceChange');
                        }
                    }
                } catch (e) {
                    alert(e.message);
                }
            }.bind(this)
        });
    },

    /**
     * Find document target element in next order:
     *  in acive file browser opener:
     *  - input field with ID: "src" in opener window
     *  - input field with ID: "href" in opener window
     *  in document:
     *  - element with target ID
     *
     * return HTMLelement | null
     */
    getTargetElement: function() {
        if (typeof window.CdzMediabrowserUtility.targetElement != 'undefined') {
            return window.CdzMediabrowserUtility.targetElement;
        }
        
        if (typeof(tinyMCE) != 'undefined' && tinyMCE.get(this.targetElementId)) {
            if ((opener = this.getMediaBrowserOpener())) {
                var targetElementId = tinyMceEditors.get(this.targetElementId).getMediaBrowserTargetElementId();
                return opener.document.getElementById(targetElementId);
            } else {
                return null;
            }
        } else {
            return document.getElementById(this.targetElementId);
        }
    },

    /**
     * Return opener Window object if it exists, not closed and editor is active
     *
     * return object | null
     */
    getMediaBrowserOpener: function() {
         if (typeof(tinyMCE) != 'undefined'
             && tinyMCE.get(this.targetElementId)
             && typeof(tinyMceEditors) != 'undefined'
             && ! tinyMceEditors.get(this.targetElementId).getMediaBrowserOpener().closed) {
             return tinyMceEditors.get(this.targetElementId).getMediaBrowserOpener();
         } else {
             return null;
         }
    },

    newFolder: function() {
        var folderName = prompt(this.newFolderPrompt);
        if (!folderName) {
            return false;
        }
        new Ajax.Request(this.newFolderUrl, {
            parameters: {name: folderName},
            onSuccess: function(transport) {
                try {
                    this.onAjaxSuccess(transport);
                    if (transport.responseText.isJSON()) {
                        var response = transport.responseText.evalJSON();
                        var newNode = new Ext.tree.AsyncTreeNode({
                            text: response.short_name,
                            draggable:false,
                            id:response.id,
                            expanded: true
                        });
                        var child = this.currentNode.appendChild(newNode);
                        this.tree.expandPath(child.getPath(), '', function(success, node) {
                            this.selectFolder(node);
                        }.bind(this));
                    }
                } catch (e) {
                    alert(e.message);
                }
            }.bind(this)
        });
    },

    deleteFolder: function() {
        if (!confirm(this.deleteFolderConfirmationMessage)) {
            return false;
        }
        new Ajax.Request(this.deleteFolderUrl, {
            onSuccess: function(transport) {
                try {
                    this.onAjaxSuccess(transport);
                    var parent = this.currentNode.parentNode;
                    parent.removeChild(this.currentNode);
                    this.selectFolder(parent);
                }
                catch (e) {
                    alert(e.message);
                }
            }.bind(this)
        });
    },

    deleteFiles: function() {
        if (!confirm(this.deleteFileConfirmationMessage)) {
            return false;
        }
        var ids = [];
        var i = 0;
        $$('div.selected').each(function (e) {
            ids[i] = e.id;
            i++;
        });
        new Ajax.Request(this.deleteFilesUrl, {
            parameters: {files: Object.toJSON(ids)},
            onSuccess: function(transport) {
                try {
                    this.onAjaxSuccess(transport);
                    this.selectFolder(this.currentNode);
                } catch(e) {
                    alert(e.message);
                }
            }.bind(this)
        });
    },

    drawBreadcrumbs: function(node) {
        if ($('breadcrumbs') != undefined) {
            $('breadcrumbs').remove();
        }
        if (node.id == 'root') {
            return;
        }
        var path = node.getPath().split('/');
        var breadcrumbs = '';
        for(var i = 0, length = path.length; i < length; i++) {
            if (path[i] == '') {
                continue;
            }
            var currNode = this.tree.getNodeById(path[i]);
            if (currNode.id) {
                breadcrumbs += '<li>';
                breadcrumbs += '<a href="#" onclick="MediabrowserInstance.selectFolderById(\'' + currNode.id + '\');">' + currNode.text + '</a>';
                if(i < (length - 1)) {
                    breadcrumbs += ' <span>/</span>';
                }
                breadcrumbs += '</li>';
            }
        }

        if (breadcrumbs != '') {
            breadcrumbs = '<ul class="breadcrumbs" id="breadcrumbs">' + breadcrumbs + '</ul>';
            $('content_header').insert({after: breadcrumbs});
        }
    },

    updateHeader: function(node) {
        var header = (node.id == 'root' ? this.headerText : node.text);
        if ($('content_header_text') != undefined) {
            $('content_header_text').innerHTML = header;
        }
    },

    activateBlock: function(id) {
        //$$('div [id^=contents]').each(this.hideElement);
        this.showElement(id);
    },

    hideElement: function(id) {
        if ($(id) != undefined) {
            $(id).addClassName('no-display');
            $(id).hide();
        }
    },

    showElement: function(id) {
        if ($(id) != undefined) {
            $(id).removeClassName('no-display');
            $(id).show();
        }
    },

    onAjaxSuccess: function(transport) {
        if (transport.responseText.isJSON()) {
            var response = transport.responseText.evalJSON();
            if (response.error) {
                throw response;
            } else if (response.ajaxExpired && response.ajaxRedirect) {
                setLocation(response.ajaxRedirect);
            }
        }
    }
};

(function($j) {
    window.CdzEditor = {}; 
    CdzEditor.wysiwyg = {
        options: {
            parent: '[data-role=editor-container]',
            editorQuery: '[data-type=editor]',
            element_id: 'codazon_tmp_editor',
            editorUrl: false,
            overlayShowEffectOptions : null,
            overlayHideEffectOptions : null,
        },
        init: function() {
            var self = this, conf = this.options;
            if (typeof Codazon.editorUrl != 'undefined') {
                this.options.editorUrl = Codazon.editorUrl;
            }
            self.editorLoaded = false;
            self.content = '';
        },
        open: function(btn) {
            var self = this, conf = this.options;
            self.$curEditor = jQuery(btn).parents(conf.parent).first().find(conf.editorQuery);
            var  editorUrl = conf.editorUrl;
            
            if (conf.editorUrl && self.editorLoaded == false) {
                new Ajax.Request(editorUrl, {
                    parameters: {
                        element_id: conf.element_id  + '_editor',
                        store_id: '0'
                    },
                    onSuccess: function(transport) {
                        try {
                            self.content = transport.responseText;
                            this.openDialogWindow(self.content);
                        } catch(e) {
                            alert(e.message);
                        }
                    }.bind(this)
                });
                self.editorLoaded = true;
            } else {
                this.openDialogWindow(self.content);
            }
        },
        
        openDialogWindow: function(content) {
            var self = this, conf = this.options;
            this.overlayShowEffectOptions = Windows.overlayShowEffectOptions;
            this.overlayHideEffectOptions = Windows.overlayHideEffectOptions;
            Windows.overlayShowEffectOptions = {duration:0};
            Windows.overlayHideEffectOptions = {duration:0};
            this.modal = Dialog.confirm(content, {
                draggable:true,
                resizable:true,
                closable:true,
                className:"magento",
                windowClassName:"popup-window",
                title:'WYSIWYG Editor',
                width:950,
                height:555,
                zIndex:1000,
                recenterAuto:false,
                hideEffect: Element.hide,
                showEffect: Element.show,
                id: "catalog-wysiwyg-editor",
                buttonClass:"form-button",
                okLabel:"Submit",
                ok: this.okDialogWindow.bind(this),
                cancel: this.closeDialogWindow.bind(this),
                onClose: this.closeDialogWindow.bind(this),
                firedElementId: conf.element_id
            }); 
            
            content.evalScripts.bind(content).defer();
            if (typeof window.editForm != 'undefined') {
                this.editorOldSubmit = window.editForm.submit;
            }
            $j('#' + conf.element_id + '_editor').val(self.$curEditor.val());
        },
        okDialogWindow: function(dialogWindow) {
            if (dialogWindow.options.firedElementId) {
                var self = this, conf = this.options;
                var wysiwygObj = eval('wysiwyg' + conf.element_id + '_editor');
                wysiwygObj.turnOff();
                if (tinyMCE.get(wysiwygObj.id)) {
                   tinyMCE.execCommand('mceRemoveControl', true, wysiwygObj.id);
                } else {
                    if ($(dialogWindow.options.firedElementId + '_editor')) {
                        self.$curEditor.get(0).value = $(dialogWindow.options.firedElementId + '_editor').value;
                        self.$curEditor.trigger('change');
                    }
                }
            }
            
            varienGlobalEvents.fireEvent('tinymceSubmit', wysiwygObj);
            self.$curEditor.val($j('#' + conf.element_id + '_editor').val());
            this.closeDialogWindow();
        },
        closeDialogWindow: function(dialogWindow) {
            if (typeof window.editForm != 'undefined') {
                window.editForm.submit = this.editorOldSubmit;
            }
            
            var self = this, conf = this.options;
            
            if (typeof varienGlobalEvents != undefined && editorFormValidationHandler) {
                varienGlobalEvents.removeEventHandler('formSubmit', editorFormValidationHandler);
            }
            
            try {
                self.$curEditor.focus();
            } catch (e) {
                
            }
            this.modal.close();
            Windows.overlayShowEffectOptions = conf.overlayShowEffectOptions;
            Windows.overlayHideEffectOptions = conf.overlayHideEffectOptions;
        }
    };
    
    
    
    $j(document).ready(function() {
        CdzEditor.wysiwyg.init();
    });
    
    
    
})(jQuery);


/* start ajax upload image */
(function($) {
    window.CodazonMedia = {
        init: function() {
            if ($('#cdz-img-uploader').length) {
                $('#cdz-img-uploader').remove();
            }
            this.$formWrap = $('<div id="cdz-img-uploader">').hide().appendTo('body');
            this.$iframe = $('<iframe>', {
                name: 'codazon_upload_iframe'
            }).appendTo(this.$formWrap);
            this.$form = $('<form>', {
                target: 'codazon_upload_iframe',
                method: 'post',
                enctype: 'multipart/form-data',
                class: 'ignore-validate',
                action: ''
            }).appendTo(this.$formWrap);
            this.$inputFile = $('<input>', {
                type:   'file',
                name:   'datafile'
            }).appendTo(this.$form);
            $('<input>', {
                type: 'hidden',
                name: 'form_key',
                value: FORM_KEY
            }).appendTo(this.$form);
            this._bindEvents();
        },
        ajaxUploadImage: function(url, targetId) {
            this.uploadUrl = url;
            this.$target = $('#' + targetId);
            this.$inputFile.click();
        },
        updatePreview: function(obj, imageDirUrl) {
            var $preview = $(obj).parent().find('.small-image-preview');
            if ($preview.length) {
                if (obj.value) {
                    $preview.attr('src', imageDirUrl + obj.value);
                } else {
                    $preview.attr('src', '');
                }
                    
            }
        },
        _bindEvents: function(){
            var self = this;
            this.$inputFile.on('change', function() {
                $('#loading-mask').show();
                var iframeHandler = function () {
                    $('#loading-mask').hide();
                    var imageParams = $.parseJSON($(this).contents().find('body').html()),
                    fullMediaUrl = imageParams['dir_path'] + imageParams['file_path'];
                    if (typeof self.$target != 'undefined') {
                        self.$target.val(imageParams['file_path']);
                        if (self.$target.parent().find('.small-image-preview').length) {
                            self.$target.parent().find('.small-image-preview').attr('src', fullMediaUrl);
                        }
                        self.$target = undefined;
                    }
                };
                self.$form.attr('action', self.uploadUrl);
                self.$iframe.off('load');
                self.$iframe.load(iframeHandler);
                self.$form.submit();
                $(this).val('');
            });
        }
    };
    $(document).ready(function() {
        CodazonMedia.init();
    });
})(jQuery);
/* end ajax upload image */