(function($) {
    $.widget('codazon.productAjaxLoad', {
        options: {
            trigger: '.cdz-ajax-trigger',
            itemsWrap: '.product-items',
            ajaxLoader: '.ajax-loader',
            ajaxUrl: null,
            jsonData: null,
            currentUrl: '' 
        },
        _currentPage: 1,
        _checkVisible: function(){
            var $element = this.element;
            var cond1 = ($element.get(0).offsetWidth > 0) && ($element.get(0).offsetHeight > 0);
            var cond2 = ($element.is(':visible'));
            var winTop = $(window).scrollTop(),
            winBot = winTop + window.innerHeight,
            elTop = $element.offset().top,
            elHeight = $element.outerHeight(true),
            elBot = elTop + elHeight;
            var cond3 = (elTop <= winTop) && (elBot >= winTop);
            var cond4 = (elTop >= winTop) && (elTop <= winBot);
            var cond5 = (elTop >= winTop) && (elBot <= winBot);
            var cond6 = (elTop <= winTop) && (elBot >= winBot);
            var cond7 = true;
            if ($element.parents('md-tab-content').length > 0) {
                cond7 = $element.parents('md-tab-content').first().hasClass('md-active');
            }
            
            return cond1 && cond2 && (cond3 || cond4 || cond5 || cond6) && cond7;
        },
        _create: function() {
            var self = this;
            if(self._checkVisible()) {
                self._ajaxLoad();
            } else {
                setTimeout(function(){
                    self._create();
                },500);
            }
        },
        _ajaxLoad: function() {
            var self = this;
            var config = this.options;
            config.jsonData.current_url = config.currentUrl;
            $.ajax({
                url: config.ajaxUrl,
                type: "POST",
                data: config.jsonData,
                cache: false,
                success: function(res){
                    if (typeof res.now !== 'undefined') {
                        window.codazon.now = res.now;
                    }
                    if (typeof res.html !== 'undefined') {
                        self.element.html(res.html).removeClass('no-loaded');
                        if (typeof window.angularCompileElement !== 'undefined') {
                            window.angularCompileElement(self.element);
                        }
                    }
                    self.element.trigger('contentUpdated');
                },
                error: function(XMLHttpRequest, textStatus, errorThrown){
                    console.error(textStatus);
                }
            });
        }
    });
    
    
    $.widget('codazon.infiniteLoad', {
        options: {
            trigger: '[data-role=ajax_trigger]',
            itemsWrap: '.product-items',
            ajaxLoader: '[data-role=ajax_loader]',
            ajaxUrl: null,
            currentUrl: '' 
        },
        _currentPage: 1,
        _create: function(){
            console.log(this.options);
            var self = this;
            self.element.find(self.options.trigger).click(function(){
                self._ajaxLoadProducts();
            });
        },
        _ajaxLoadProducts: function(){
            var self = this;
            var config = this.options;
            var $trigger = self.element.find(config.trigger);
            var $ajaxLoader = self.element.find(config.ajaxLoader);
            var hasLastPage = false;
            var startOffset = self.element.find('.product-item').length;
            $trigger.hide();
            $ajaxLoader.show();
            self._currentPage++;
            config.jsonData.cur_page = self._currentPage;
            config.jsonData.current_url = config.currentUrl;
            
            jQuery.ajax({
                url: config.ajaxUrl,
                type: "POST",
                data: config.jsonData,
                cache: false,
                success: function(res){
                    if (typeof res.now !== 'undefined') {
                        window.codazon.now = res.now;
                    }
                    if(res.html) {
                        $(config.itemsWrap, self.element).append(res.html);
                        if (typeof window.angularCompileElement !== 'undefined') {
                            window.angularCompileElement(self.element);
                        }
                    }
                    if(res.last_page == self._currentPage){
                        hasLastPage = true;
                    }
                    $('body').trigger('contentUpdated');
                },
                error: function(XMLHttpRequest, textStatus, errorThrown){
                    self._currentPage--;
                    console.error(textStatus);
                }
            }).always(function(){
                $ajaxLoader.hide();
                if(!hasLastPage){
                    $trigger.show();
                }else{
                    $trigger.hide();
                }
            });
        }
    });
})(jQuery);