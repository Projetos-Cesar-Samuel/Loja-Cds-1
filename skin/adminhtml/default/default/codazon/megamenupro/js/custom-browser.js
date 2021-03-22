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
CdzMediabrowserUtility = {
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
		
		if(typeof targetEl.dataset.cdzbrowser !== 'undefined'){
			params.as_is = 1;
			var cdzbrowser = true;
		}else{
			var cdzbrowser = false;
			if (targetEl.tagName.toLowerCase() == 'textarea') {
				params.as_is = 1;
			}
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
					if(cdzbrowser){
						var patt = new RegExp('{{media url="\\S+"}}','g');
						transport.responseText = transport.responseText.match(patt)[0];
					}
					
                    if (targetEl.tagName.toLowerCase() == 'input') {
                        targetEl.value = transport.responseText;
                    } else {
                        updateElementAtCursor(targetEl, transport.responseText);
                        if (varienGlobalEvents) {
                            varienGlobalEvents.fireEvent('tinymceChange');
                        }
                    }
					jQuery(targetEl).trigger('change');
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

(function($){
	window.Icons = new function(){
		var defaultConfig = {
			modalId: 'icons-modal',
			modalTitle: 'Icon Chooser',
			templateId: 'menu-item-icons-tmpl'
		};
		var config = {};
		cdzIcons = new Object({
			modal: null,
			currentTarget: null,
			content: null,
			init: function(options){
				var self = this;
				config = $.extend({},defaultConfig,options);
			},
			getTmplHtml: function(tmplId,data){
				if(typeof data == 'undefined'){
					var data = {};	
				}
				var $content = $('<div />');
				$('#'+tmplId).tmpl(data).appendTo($content);
				return $content.html();
			},
			openIconChooser: function(targetId, templateId,title,data){
				var self = this;
				this.currentTarget = $('#'+targetId);
				if(!title){
					title = 'Insert Icons...';
				}
				if( (this.templateId != templateId) || (this.content == null) ){
					this.templateId = templateId;
					this.content = this.getTmplHtml(templateId,data);
				}
				this.dialogWindow = Dialog.info(this.content, {
					draggable:true,
					resizable:true,
					closable:true,
					className:"magento",
					windowClassName:"popup-window",
					title:title,
					width: 1200,
					height: 550,
					zIndex: 1000,
					recenterAuto: false,
					id: "menu-icon-chooser",
					buttonClass: "form-button",
					okLabel: "Submit",
					firedElementId: targetId
				});
			},
			insertIcon: function(value){
				this.currentTarget.val(value).trigger('change');
				//Dialog.closeInfo();
				this.dialogWindow.close();
			},
			insertTemplate: function(tmplId){
				var html = jQuery('#'+tmplId).html().trim();
				if(this.currentTarget.prop("tagName").toLowerCase() == 'textarea'){
					this.currentTarget.val(html).trigger('change');
				}
				this.dialogWindow.close();
			}
		});
		return cdzIcons;
	}
})(jQuery);



Window.keepMultiModalWindow = true;
var menuWysiwygEditor = {
    overlayShowEffectOptions : null,
    overlayHideEffectOptions : null,
    open : function(editorUrl, elementId) {
        if (editorUrl && elementId) {
            new Ajax.Request(editorUrl, {
                parameters: {
                    element_id: elementId+'_editor',
                    store_id: '0'
                },
                onSuccess: function(transport) {
                    try {
                        this.openDialogWindow(transport.responseText, elementId);
                    } catch(e) {
                        alert(e.message);
                    }
                }.bind(this)
            });
        }
    },
    openDialogWindow : function(content, elementId) {
        this.overlayShowEffectOptions = Windows.overlayShowEffectOptions;
        this.overlayHideEffectOptions = Windows.overlayHideEffectOptions;
        Windows.overlayShowEffectOptions = {duration:0};
        Windows.overlayHideEffectOptions = {duration:0};

        Dialog.confirm(content, {
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
            hideEffect:Element.hide,
            showEffect:Element.show,
            id:"catalog-wysiwyg-editor",
            buttonClass:"form-button",
            okLabel:"Submit",
            ok: this.okDialogWindow.bind(this),
            cancel: this.closeDialogWindow.bind(this),
            onClose: this.closeDialogWindow.bind(this),
            firedElementId: elementId
        });

        content.evalScripts.bind(content).defer();

        $(elementId+'_editor').value = $(elementId).value;
    },
    okDialogWindow : function(dialogWindow) {
        if (dialogWindow.options.firedElementId) {
            wysiwygObj = eval('wysiwyg'+dialogWindow.options.firedElementId+'_editor');
            wysiwygObj.turnOff();
            if (tinyMCE.get(wysiwygObj.id)) {
                $(dialogWindow.options.firedElementId).value = tinyMCE.get(wysiwygObj.id).getContent();
            } else {
                if ($(dialogWindow.options.firedElementId+'_editor')) {
                    $(dialogWindow.options.firedElementId).value = $(dialogWindow.options.firedElementId+'_editor').value;
                }
            }
        }
        this.closeDialogWindow(dialogWindow);
    },
    closeDialogWindow : function(dialogWindow) {
        // remove form validation event after closing editor to prevent errors during save main form
        if (typeof varienGlobalEvents != undefined && editorFormValidationHandler) {
            varienGlobalEvents.removeEventHandler('formSubmit', editorFormValidationHandler);
        }

        //IE fix - blocked form fields after closing
        $(dialogWindow.options.firedElementId).focus();

        //destroy the instance of editor
        wysiwygObj = eval('wysiwyg'+dialogWindow.options.firedElementId+'_editor');
        if (tinyMCE.get(wysiwygObj.id)) {
           tinyMCE.execCommand('mceRemoveControl', true, wysiwygObj.id);
        }

        dialogWindow.close();
        Windows.overlayShowEffectOptions = this.overlayShowEffectOptions;
        Windows.overlayHideEffectOptions = this.overlayHideEffectOptions;
    }
};
