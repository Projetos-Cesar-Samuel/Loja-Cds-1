(function($) {
if (typeof window.codazon == 'undefined') {
    window.codazon = {"enableAjaxCart":true,"enableAjaxWishlist":true,"enableAjaxCompare":true};
}
var CodazonAjaxCartPro = {};
CodazonAjaxCartPro.options = {
    informModalId: 'ajaxcart-modal',
    buttonCart: '.btn-cart',
    buttonWishlist: '.link-wishlist, .js-remove-wl-item',
    message: {
        wishlistRemoveConfirmMsg: (codazon.wishlistRemoveConfirmMsg ? codazon.compareClearConfirmMsg : 'Are you sure you would like to remove this item from the wishlist?'),
        compareRemoveConfirmMsg: (codazon.compareRemoveConfirmMsg ? codazon.compareClearConfirmMsg : 'Are you sure you would like to remove this item from the compare products?'),
        compareClearConfirmMsg: (codazon.compareClearConfirmMsg ? codazon.compareClearConfirmMsg : 'Are you sure you would like to remove all products from your comparison?'),
    }
};
CodazonAjaxCartPro.matchingUrls = [
    'checkout/cart/add',
    'checkout/cart/delete',
];
 CodazonAjaxCartPro.matchingWishlistUrls = [
    'wishlist/index/add',
    'wishlist/index/remove',
];
CodazonAjaxCartPro.matchingCompareUrls = [
    'catalog/product_compare/add',
    'catalog/product_compare/remove',
    'catalog/product_compare/clear'
];
window.oldSetLocation = window.setLocation;
window.oldSetPLocation = window.setPLocation;
window.setLocation = function(url) {
    var match = false;
    $.each(CodazonAjaxCartPro.matchingUrls, function(i, term) {
        if (url.search(term) > -1) {
            match = true;
            return;
        }
    });

    if (!match) {
        window.oldSetLocation(url);
    } else {
        CodazonAjaxCartPro.processCart(url);
    }
}
window.setPLocation = function(url) {
    var match = false;
    $.each(CodazonAjaxCartPro.matchingUrls, function(i, term) {
        if (url.search(term) > -1) {
            match = true;
            return;
        }
    });

    if (!match) {
        window.oldSetPLocation(url);
    } else {
        CodazonAjaxCartPro.processCart(url, window.parent);
    }
}

CodazonAjaxCartPro.currentTarget = false;
CodazonAjaxCartPro.cartButton = function() {
    var self = this, conf = this.options;
    $(conf.buttonCart).each(function() {
        var $buttonCart = $(this);
        if (!$buttonCart.hasClass('js-add-to-cart')) {
            $buttonCart.addClass('js-add-to-cart');
            $buttonCart.hover(function() {
                self.currentTarget = $buttonCart;
            }, function() {
                self.currentTarget = false;
            });
            if ($buttonCart.parents('form').length > 0) {
                var form = $buttonCart.parents('form').first().get(0);
                if (!form.hasClassName('js-add-to-cart-form')) {
                    $(form).addClass('js-add-to-cart-form');
                    form.oldsubmit = form.submit;
                    
                    if ($(form).hasClass('js-mini-form')) {
                        $(form).on('submit', function(e) {
                            e.preventDefault();
                            self.processCart(form);
                        });
                    } else {
                        form.submit = function() {
                            self.processCart(form);
                        }
                    }                    
                }
            }
        }
    });
};
CodazonAjaxCartPro.init = function() {
    var self = this, conf = this.options;
    if ($('#' + conf.informModalId).length == 0) {
        var $modal = $('<div data-cdzpopup id="' + conf.informModalId + '" class="cdz-ajaxcart-container ' + conf.informModalId + '">').appendTo('body');
        cdzUtilities.buildPopup();
    }
    if (codazon.enableAjaxCart) this.cartButton();
    if (codazon.enableAjaxWishlist) this.wishlistButton();
    if (codazon.enableAjaxCompare) this.compareButton();
    $('body').on('contentUpdated', function() {
        if (codazon.enableAjaxCart) self.cartButton();
        if (codazon.enableAjaxWishlist) self.wishlistButton();
        if (codazon.enableAjaxCompare) self.compareButton();
    });
}
CodazonAjaxCartPro.wishlistButton = function() {
    var self = this, conf = this.options;
    $(conf.buttonWishlist).each(function() {
        var $button = $(this);
        if (!$button.hasClass('js-wishlist')) {
            $button.addClass('js-wishlist');
            var url = $button.attr('href');
            var match = false;
            $.each(self.matchingWishlistUrls, function(i, term) {
                if (url.search(term) > -1) {
                    match = true;
                    return;
                }
            });
            if (match) {
                var onclick = $button.attr('onclick');
                var isRemoveBtn = $button.hasClass('js-remove-wl-item');
                if (isRemoveBtn) {
                    $button.removeAttr('onclick');
                }
                $button.click(function(e) {
                    e.preventDefault();
                    if (isRemoveBtn) {
                        var agree = confirm(conf.message.wishlistRemoveConfirmMsg);
                        url = url.replace('wishlist/index/remove', 'quickviewpro/wishlist/remove');
                    } else {
                        var agree = true;
                        url = url.replace('wishlist/index/add', 'quickviewpro/wishlist/add');
                    }
                    if (agree) {
                        $.ajax({
                            url: url,
                            type: 'post',
                            data: {is_ajax: 1},
                            success: function(rs) {
                                self.updateBlocks(rs);
                            }
                        });
                    }
                });
            }
        }
    });
}
CodazonAjaxCartPro.compareButton = function() {
    var self = this, conf = this.options;
    $('a[href*="catalog/product_compare/add"], a[href*="catalog/product_compare/remove"],  a[href*="catalog/product_compare/clear"]').each(function() {
        var $button = $(this);
        if (!$button.hasClass('js-compare')) {
            $button.addClass('js-compare');
            var url = $button.attr('href');
            var match = false;
            $.each(self.matchingCompareUrls, function(i, term) {
                if (url.search(term) > -1) {
                    match = true;
                    return;
                }
            });
            if (match) {
                var onclick = $button.attr('onclick');
                var isRemoveBtn = $button.hasClass('btn-remove');
                var isClear = ($button.attr('href').search('catalog/product_compare/clear') > -1);
                if (isRemoveBtn || isClear) {
                    $button.removeAttr('onclick');
                }
                $button.click(function(e) {
                    e.preventDefault();
                    var agree = true;
                    
                    if (isRemoveBtn) {
                        var agree = confirm(conf.message.compareRemoveConfirmMsg);
                        url = url.replace('catalog/product_compare/remove', 'quickviewpro/compare/remove');
                    } else if (isClear) {
                        var agree = confirm(conf.message.compareClearConfirmMsg);
                        url = url.replace('catalog/product_compare/clear', 'quickviewpro/compare/clear');
                    } else {
                        var agree = true;
                        url = url.replace('catalog/product_compare/add', 'quickviewpro/compare/add');
                    }
                    if (agree) {
                        $.ajax({
                            url: url,
                            type: 'post',
                            data: {is_ajax: 1},
                            success: function(rs) {
                                self.updateBlocks(rs);
                            }
                        });
                    }
                });
            }
        }
    });
}
CodazonAjaxCartPro.processCart = function(obj, win) {
    if (typeof win == 'undefined') {
        win = window;
    }
    var self = this, conf = this.options, $buttonCart = false;
    var $informPopup = $('#' + conf.informModalId);

    if (typeof obj === 'string') {
        if (self.currentTarget) {
            $buttonCart = self.currentTarget;
            $buttonCart.addClass('disabled').attr('disabled', 1);
        }
        var url = obj;
        url = url.replace('checkout/cart', 'quickviewpro/cart');
        $.ajax({
            url: url,
            type: 'post',
            data: {is_ajax: 1},
            success: function(rs) {
                if ($buttonCart) {
                    $buttonCart.removeClass('disabled').removeAttr('disabled');
                }
                self.updateBlocks(rs, win);
            }
        })
        
    } else if (typeof obj === 'object') {
        if (self.currentTarget) {
            $buttonCart = self.currentTarget;
            $buttonCart.addClass('disabled').attr('disabled', 1);
        }
        var form = obj;
        var $form = $(obj);
        var oldAction = form.action;
        var url = form.action.replace('checkout/cart', 'quickviewpro/cart');
        var $iframe = $('<iframe style="display:none;" id="cdz_ajax_cart_form_target" name="cdz_ajax_cart_form_target">').appendTo($form);
        var $isAjaxField = $('<input type="hidden" name="is_ajax" value="1">').appendTo($form);
        
        $iframe.off('load');
        $iframe.on('load', function() {
            var rs = $iframe.contents().find('body pre').text().evalJSON();
            self.updateBlocks(rs, win);
            form.action = oldAction;
            $iframe.remove();
            $isAjaxField.remove();
            if ($buttonCart) {
                $buttonCart.removeClass('disabled').removeAttr('disabled');
            }
        });
        form.action = url;
        form.target = "cdz_ajax_cart_form_target";
        form.oldsubmit();
    }
}
CodazonAjaxCartPro.updateBlocks = function(rs, win) {
    if (typeof win == 'undefined') {
        win = window;
    }
    var self = this, conf = this.options;
    var $informPopup = $('#' + conf.informModalId, win.document);
    if (rs.update_blocks) {
        $.each(rs.update_blocks, function(i, el) {
            $(el.key, win.document).replaceWith(el.value);
            if (el.key == '.block.block-compare') {
                if ((el.value) && ($(el.key).length == 0)) {
                    if ($('.col-left.sidebar', win.document).length) {
                        $(el.value).prependTo($('.col-left.sidebar', win.document).last());
                    }
                }
            }
        })
    }
    if (rs.popup_width) {
        $('.cdz-popup.popup-' + conf.informModalId, win.document).css('width', parseFloat(rs.popup_width));
    } else {
        $('.cdz-popup.popup-' + conf.informModalId, win.document).css('width', '');
    }
    if (rs.ajax_result_content) {
        $informPopup.html(rs.ajax_result_content);
        win.cdzUtilities.triggerPopup(conf.informModalId);
    } else if (rs.message) {
        var msgClass = 'notice';
        if (typeof rs.success != 'undefined') {
            msgClass = rs.success ? 'success' : 'error';
        }
        $informPopup.html('<div class="message ' + msgClass + '-msg">' + rs.message + '</div>');
        win.cdzUtilities.triggerPopup(conf.informModalId);
    }
    if (typeof rs.qty != 'undefined') {
        $('.header-minicart span.count, .js-cart-qty', win.document).html(rs.qty);
    }
    if (typeof rs.subtotal != 'undefined') {
        $('.header-minicart span.price, .js-cart-subtotal', win.document).html(rs.subtotal);
    }
    if (typeof rs.items_count != 'undefined') {
        $('.js-cart-items-count', win.document).html(rs.items_count);
    }
    if (typeof rs.wishlist_count != 'undefined') {
        $('.js-wishlist-count', win.document).html(rs.wishlist_count);
    }
    $('body', win.document).trigger('contentUpdated');
}
CodazonAjaxCartPro.getConfigurableOptions = function(url) {
    window.CodazonQuickview.loadView(url);
}
window.CodazonAjaxCartPro = CodazonAjaxCartPro;
$(document).ready(function() {
    CodazonAjaxCartPro.init();
});
})(jQuery);