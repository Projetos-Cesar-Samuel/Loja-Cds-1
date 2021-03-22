(function($) {
    window.CodazonQuickview = {
        options: {
            modalId: 'quickview-modal',
            loaderHtml: '<div class="double-bounce-spinner"><div class="double-bounce1"><div class="double-bounce2"></div></div></div>'
        },
        init: function() {
            this._prepareHtml();
            this._bindEvents();
        },
        _prepareHtml: function() {
            var conf = this.options;
            if ($('#' + conf.modalId).length == 0) {
                var $modal = $('<div data-cdzpopup id="' + conf.modalId + '" class="quickview-container ' + conf.modalId + '">').appendTo('body');
                cdzUtilities.buildPopup();
            }
            this.$quickviewModal = $('#' + conf.modalId);
        },
        loadView: function(url, cache) {
            var self = this, conf = this.options;
            var $quickviewModal = this.$quickviewModal;
            $quickviewModal.html(conf.loaderHtml);
            $quickviewModal.addClass('loading');
            cdzUtilities.triggerPopup(conf.modalId);
            $.ajax({
                url: url,
                cache: cache ? true : false,
                success: function(rs) {
                    $quickviewModal.html(rs);
                    $('body').trigger('contentUpdated');
                }
            }).always(function() {
                $quickviewModal.removeClass('loading');
            });
        },
        _bindEvents: function() {
            var self = this, conf = this.options;
            var $quickviewModal = this.$quickviewModal;
            $('body').on('click', '.js-quickview', function() {
                var $button = $(this);
                var quickViewData = $button.data('quickview');
                self.loadView(quickViewData.url);
            });
            $quickviewModal.on('popupClosed', function() {
                $quickviewModal.html('');
                $('[qv-price-id]').each(function() {
                    $(this).attr('id', $(this).attr('qv-price-id'));
                    $(this).removeAttr('qv-price-id');
                });
            });
        }
    }
    $(document).ready(function() {
        CodazonQuickview.init();
    });
})(jQuery);