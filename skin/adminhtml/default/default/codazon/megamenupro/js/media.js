;(function($){
window.CdzMediabrowserUtility = new function(){
	var defaultConfig = {
		modalId: 'media-browser-modal',
		modalTitle: 'Select Image'
	};
	var config = {};
	var cdzImage = new Object({
		imgIframe: function(id,title){
			var html = '';
			html +=	'<div class="image-modal modal fade" id="'+id+'">';
			html +=		'<div class="modal-dialog modal-lg">';
			html +=			'<div class="modal-content">';
			html +=				'<div class="mce-window-head"><div class="mce-title">'+title+'</div><button type="button" class="mce-close" data-dismiss="modal">×</button></div>';
			html +=				'<div class="mce-container-body mce-abs-layout"><iframe class="image-browser-iframe" style="width:100%; height:500px" src="'+config.browserUrl+'" /></div>';
			html +=			'</div>';
			html +=		'</div>';
			html +=	'</div>';
			return html;
		},
		modal: null,
		init: function(options){
			config = $.extend({},defaultConfig,options);
			var self = this;
			this.modal = $(self.imgIframe(config.modalId, config.modalTitle));
			this.modal.appendTo($('body'));
			this.modal.modal("hide");
		},
		openDialog: function(targetId){
			window.mediaTargetId = targetId;
			window.$mediaTarget = $('#'+targetId);
			window.$mediaModal = this.modal;
			this.modal.modal();
			
		}
	});
	return cdzImage;
}

window.CdzCategoryChooser = new function(){
	var defaultConfig = {
		modalId: 'category-modal',
		formId: 'category_chooser_form'
	};
	var config = {};
	var cdzCategory = new Object({
		modal: null,
		currentTarget: null,
		init: function(options){
			config = $.extend({},defaultConfig,options);
			var self = this;
			this.modal = $('#'+config.modalId);
			this.modal.modal("hide");
			this.form = $('#'+config.formId);
			this.form.on('submit',function(e){
				e.preventDefault();
				var $form = $(this);
				var data = $form.serializeArray();
				var choosedCat = false;
				$.each(data,function(i,field){
					if(field.name == 'category'){
						choosedCat = field.value;
					}
				});
				if(choosedCat !== false){
					self.currentTarget.val(choosedCat).trigger('change');
				}
				self.modal.modal('hide');
			});
		},
		openDialog: function(targetId){
			this.currentTarget = $('#'+targetId);
			this.modal.modal();
			var cat = this.currentTarget.val();
			if(cat){
				var $catNode = this.form.find('[name="category"][value="'+cat+'"]').first();
				if($catNode.length > 0){
					this.form.find('.tree-selected').removeClass('tree-selected');
					$catNode.attr('checked',true).trigger('change');
					$catNode.parent().addClass('tree-selected');
				}
			}
		}
	});
	return cdzCategory;
}
	
window.CdzWysiwygEditor = new function(){
	var defaultConfig = {
		modalId: 'wysiwyg-editor-modal',
		modalTitle: 'Wysiwyg Editor',
		editorId: 'cdz-wysiwyg-editor',
		formId: 'wysiwyg_editor_form'
	};
	var config = {};
	var cdzEditor = new Object({
		modal: null,
		currentTarget: null,
		init: function(options){
			var self = this;
			config = $.extend({},defaultConfig,options);
			this.modal = $('#'+config.modalId);
			this.modal.modal("hide");
			this.form = $('#'+config.formId);
			this.form.on('submit',function(e){
				e.preventDefault();
				var $form = $(this);
				var content = tinyMCE.get(config.editorId).getContent();
				self.currentTarget.val(decodeWidgets(content));
				self.modal.modal("hide");
			});
		},
		openDialog: function(targetId){
			this.currentTarget = $('#'+targetId);
			var content = this.currentTarget.val();
			this.modal.find('[name="editor"]').val(content).trigger('change');
			tinyMCE.get(config.editorId).setContent(encodeWidgets(content));
			this.modal.modal("show");
		}
	});
	return cdzEditor;
}
window.CdzIcons = new function(){
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
			this.modal = $('#'+config.modalId);
			this.modal.modal("hide");
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
			if( (this.templateId != templateId) || (this.content == null) ){
				this.templateId = templateId;
				this.content = this.getTmplHtml(templateId,data);
				this.modal.find('.modal-content').first().html(this.content);
			}
			this.currentTarget = $('#'+targetId);
			this.modal.modal('show');
		},
		insertIcon: function(value){
			this.currentTarget.val(value).trigger('change');
			this.modal.modal('hide');
		},
		insertTemplate: function(tmplId){
			var html = jQuery('#'+tmplId).html().trim();
			if(this.currentTarget.prop("tagName").toLowerCase() == 'textarea'){
				this.currentTarget.val(html).trigger('change');
			}
			this.modal.modal('hide');
		}
	});
	return cdzIcons;
}
window.CdzWidgetTools = new function(){
	var defaultConfig = {
		editorId: 'cdz-wysiwyg-editor'
	};
	var config = {};
	czdWidgetTools = new Object({
		init: function(options){
			var self = this;
			config = $.extend({},defaultConfig,options);
		},
		openDialog: function(targetId){
			tinyMCE.get(config.editorId).showWidgetDialog(targetId);
		}
	});
	return czdWidgetTools;
}
	
})(jQuery);