/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

(function($) {
     $.widget('codazon.ajaxlayerednavpro', {
         options: {
            ajaxSelector: '.block-layered-nav dd li > a, .block-layered-nav a.btn-remove, .toolbar a, .block-layered-nav .actions a'
         },
         _create: function() {
            var self = this, conf = this.options;
            this._prepareHtml();
            this._attacheEvents();
        },
        _prepareHtml: function() {
            var self = this, conf = this.options;
             var self = this, conf = this.options;
            $('[data-role=price-slider-container]').each(function(){
                var $container = $(this), $slider = $container.find('[data-role=price-slider]'),
                $min = $container.find('[data-role=min_price]'), $max = $container.find('[data-role=max_price]'),
                $form = $container.find('[data-role=price-form]').first(), $priceInput = $form.find('[name=price]'),
                min = parseFloat($min.val()), max = parseFloat($max.val()), curMin, curMax;
                
                var sliderOptions = {
                    range: true,
                    min: min,
                    max: max,
                    values: [min, max],
                    slide: function(event, ui) {
                        curMin = ui.values[0];
                        curMax = ui.values[1];
                        $min.val(curMin);
                        $max.val(curMax);
                        $priceInput.val(curMin + '-' + curMax);
                    },
                    stop: function(event, ui) {
                        $form.submit();
                    }
                };
                $form.on('submit', function(e) {
                    e.preventDefault();
                    curMin = $min.val();
                    curMax = $max.val();
                    $priceInput.val(curMin + '-' + curMax);
                    var ajaxUrl = $form.attr('action');
                    ajaxUrl += (ajaxUrl.search(/\?/) != -1) ? '&' : '?';
                    ajaxUrl += 'price=' + $priceInput.val();
                    self._ajaxLoad(ajaxUrl);
                });
                $.ui.slider(sliderOptions, $slider);
            });
        },
        _attacheEvents: function() {
            var self = this, conf = this.options;
            $(conf.ajaxSelector).on('click', function(e) {
                e.preventDefault();
                var $a = $(this);
                var ajaxUrl = $a.attr('href');
                self._ajaxLoad(ajaxUrl);
            });
            $('.toolbar select').each(function() {
                $select = $(this);
                if ($select.attr('onchange') == "setLocation(this.value)") {
                    $select.attr('onchange', 'ajaxLayeredNavLoad(this.value)');
                }
            });
            if (typeof window.ajaxLayeredNavLoad == 'undefined') {
                window.ajaxLayeredNavLoad = function(ajaxUrl) {
                    self._ajaxLoad(ajaxUrl);
                }
            }
        },
        _ajaxLoad: function(ajaxUrl) {
            var self = this, conf = this.options;
            if ((!ajaxUrl) || (ajaxUrl.search('javascript:') == 0) || (ajaxUrl.search('#') == 0)) {
                return;
            }
            $('#ajax-layered-nav-loader').show();
            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {ajax_nav: 1},
                showLoader: true,
                success: function(res) {
                    if (res.catalog_leftnav) {
                        $('.block.block-layered-nav').first().replaceWith(res.catalog_leftnav);
                    }
                    if (res.category_products) {
                        var $listContainer = $('#product-list-container');
                        $listContainer.replaceWith(res.category_products);
                        $(window).scrollTop($listContainer.offset().top - 60);
                    }
                    if (res.page_main_title) {
                        $('.page-title-wrapper').first().replaceWith(res.page_main_title);
                    }
                    if (res.updated_url) {
                        window.history.pushState(res.updated_url, document.title, res.updated_url);
                    } else {
                        window.history.pushState(ajaxUrl, document.title, ajaxUrl);
                    }
                    $('body').trigger('contentUpdated');
                    $('body').trigger('productlistUpdated');
                    $(document).trigger('product-media-loaded');
                    self._prepareHtml();
                    self._attacheEvents();
                }
            }).always(function() {
                $('#ajax-layered-nav-loader').hide();
            });
        }
     });
})(jQuery)