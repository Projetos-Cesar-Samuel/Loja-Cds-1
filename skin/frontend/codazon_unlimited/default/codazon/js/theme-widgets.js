/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
(function($) {
    var deskPrefix = 'desk_', mobiPrefix = 'mobi_';
    var deskEvent = 'cdz_desktop', mobiEvent = 'cdz_mobile';
    var $window = $(window);
    var rtl = codazon.rtl;
    var mBreakpoint = 768;
    var winWidthChangedEvent = 'cdz_window_width_changed';
    
    function itemEffect($parent, delayUnit) {
        $('.cdz-transparent', $parent).each(function(i, el) {
            var $item = $(el);
            setTimeout(function() {
                $item.removeClass('cdz-transparent').addClass('cdz-translator');
                setTimeout(function() {
                    $item.removeClass('cdz-translator');
                }, 1000);
            }, delayUnit*i);
        });
    }
    $(document).ready(function() {
        rtl = $('body').hasClass('rtl-layout');
    });
    
    $.widget('codazon.buyNow', {
        _create: function() {
            var self = this;
            var $form = self.element.parents('form').first();
            this.element.on('click', function(e) {
                $form.one('addToCartBegin', function() {
                    $form.attr('buy_now', 1);
                });
                $form.one('addToCartCompleted', function() {
                    $form.removeAttr('buy_now');
                    window.location = codazon.checkoutUrl;
                });
            });
        }
    });
    
    $.widget('codazon.autowidth', {
        options: {
            item: '[data-role=item]',
            itemsPerRow: [],
            margin: 0,
            sameHeight: [],
        },
        _sameHeight: function() {
            var self = this, conf = this.options;
            var maxHeight = 0;
            self.element.attr('data-sameheight', conf.sameHeight.join(','));
            $.each(conf.sameHeight, function(i, sameHeight) {
                self.element.find(sameHeight).css({minHeight: ''}).each(function() {
                    var $sItem = $(this);
                    var height = $sItem.outerHeight();
                    if (height > maxHeight) {
                        maxHeight = height;
                    }
                }).css({minHeight: maxHeight});
            });
        },
        _create: function() {
            var self = this, conf = this.options;
            if (!conf.itemsPerRow) {
                return true;
            }
            
            var i = 0;
            self.itemsPerRow = [];
            for(var point in conf.itemsPerRow) {
                self.itemsPerRow[i] = {};
                self.itemsPerRow[i]['breakPoint'] = point;
                self.itemsPerRow[i]['items'] = conf.itemsPerRow[point];
                i++;
            };
            self._setWidth();
            self._itemEffect();
            self._sameHeight();
            var ww = window.innerWidth, t = false;
            $(window).resize(function() {
                if(t) clearTimeout(t);
                t = setTimeout(function() {
                    if (window.innerWidth != ww) {
                        self._setWidth();
                        ww = window.innerWidth;
                    }
                },50);
            });
            $('body').on('contentUpdated', function() {
                self._setWidth();
                self._itemEffect();
            });
            self.element.parents('.no-loaded').first().removeClass('no-loaded');
        },
        _itemEffect: function() {
            itemEffect(this.element, 200);
        },
        _getBreakPoint: function() {
            var self = this, conf = this.options;
            var ww = window.innerWidth, breakPoint = 0, bpLength = self.itemsPerRow.length;
            for(var i = bpLength - 1; i >=0; i--) {
                if (self.itemsPerRow[i].breakPoint <= ww) {
                    breakPoint = self.itemsPerRow[i].breakPoint; break;
                }
            };
            return breakPoint;
        },
        _setWidth: function(itemsPerRow) {
            var self = this, conf = this.options;
            var bp = self._getBreakPoint();
            if (conf.itemsPerRow[bp] > 0) {
                var itemWidth = 100/conf.itemsPerRow[bp];
                if (window.innerWidth < mBreakpoint) {
                    var margin = 10, subtrahend = 11;
                } else {
                    var margin = conf.margin, subtrahend = conf.margin;
                }
                self.element.find(conf.item).each(function() {
                    var $item = $(this);
                    $item.css({width: 'calc(' + itemWidth + '% - ' + subtrahend + 'px)'});
                    if (conf.margin > 0) {
                        if (rtl) {
                            $item.css('margin-left', margin + 'px');
                            self.element.css('margin-left', -margin + 'px');
                        } else {
                            $item.css('margin-right', margin + 'px');
                            self.element.css('margin-right', -margin + 'px');
                        }
                    }
                });
            }
        }
    });
    
    
    $.widget('codazon.slider', {
        options: {
            mbMargin: 10,
            sameHeight: ['.product-details', '.product-item-details'],
            pageNumber: false,
            divider: '/',
            pullDrag: true,
            noLoadedClass: false
        },
        _create: function() {
            var self = this, conf = this.options;
            if (typeof conf.sliderConfig.responsive !== 'undefined') {
                $.each(conf.sliderConfig.responsive, function(breakPoint, el) {
                    if (conf.sliderConfig.margin > conf.mbMargin) {
                        if (breakPoint < mBreakpoint) {
                            conf.sliderConfig.responsive[breakPoint] = $.extend({}, {margin: conf.mbMargin}, conf.sliderConfig.responsive[breakPoint]);
                        }
                    }
                    if ((conf.sliderConfig.responsive[breakPoint].items%1) > 0) {
                        conf.sliderConfig.responsive[breakPoint].loop = true;
                    } else {
                        conf.sliderConfig.responsive[breakPoint].loop = conf.sliderConfig.loop || false;
                    }
                });
            }
            self.totalItem = self.element.children().length;
            if (conf.noLoadedClass) {
                self.element.parents('.' + conf.noLoadedClass).removeClass(conf.noLoadedClass);
            }
            self.element.addClass('owl-carousel');
            conf.sliderConfig.rtl = rtl;
            conf.sliderConfig.lazyLoad = true;
            conf.sliderConfig.pullDrag = conf.pullDrag;
            conf.sliderConfig.navElement = 'div';
            self.element.owlCarousel(conf.sliderConfig);
            if (conf.pageNumber) {
                self._addPageNumber();
            }
            self._sameHeight();
            self._itemEffect();
        },
        _addPageNumber: function()
        {
            var self = this, conf = this.options;
            var owlData = self.element.data('owl.carousel');
            this.$pageNumber = $('<div class="owl-page">').html('<span class="current-page"></span>'+conf.divider+'<span class="total-page"></span>');
            this.$pageNumber.insertBefore(self.element.find('.owl-nav').first());
            var $current = self.$pageNumber.find('.current-page');
            $current.text(owlData._current + 1);
            self.$pageNumber.find('.total-page').text(self.totalItem);
            self.element.on('changed.owl.carousel', function(event) {
                $current.text(owlData._current + 1);
            });
        },
        _sameHeight: function() {
            var self = this, conf = this.options;
            self.element.attr('data-sameheight', conf.sameHeight.join(','));
            $.each(conf.sameHeight, function(i, sameHeight) {
                var maxHeight = 0;
                self.element.find(sameHeight).css({minHeight: ''}).each(function() {
                    var $sItem = $(this);
                    var height = $sItem.outerHeight();
                    if (height > maxHeight) {
                        maxHeight = height;
                    }
                }).css({minHeight: maxHeight});
            });
        },
        _itemEffect: function() {
            itemEffect(this.element, 200);
        }
    });
    
    $.widget('codazon.slideshow', {
        options: {
            
        },
        _create: function() {
            var self = this, conf = this.options;
            self.element.addClass('owl-carousel');
            conf.sliderConfig.rtl = rtl;
            conf.sliderConfig.lazyLoad = true;
            conf.sliderConfig.navElement = 'div';
            self.element.owlCarousel(conf.sliderConfig);
            self.element.parents('[data-role=cdz-slideshow]').first().removeClass('no-loaded');
        }
    });
    
    $.widget('codazon.minicountdown', {
        options: {
            nowDate: false,
            startDate: false,
            stopDate: false,
            dayLabel: 'Day(s)',
            hourLabel: 'Hour(s)',
            minLabel: 'Minute(s)',
            secLabel: 'Second(s)',
            delay: 1000
        },
        _formatDate: function(dateStr) {
            dateStr = dateStr.replace(/-/g, '/');
            return dateStr;
        },
        _create: function() {
            var self = this, conf = this.options;
            if (conf.stopDate) {
                if (!conf.nowDate) {
                    conf.nowDate = window.codazon.now;
                }
                conf.nowDate = self._formatDate(conf.nowDate);
                conf.stopDate = self._formatDate(conf.stopDate);                
                var now = (new Date(conf.nowDate)).getTime();
                if (conf.startDate) {
                    conf.startDate = self._formatDate(conf.startDate);
                    self.startDate = new Date(conf.startDate).getTime();
                    if (self.startDate > now) {
                        return true;
                    }
                }
                self.delta = (new Date()).getTime() - (new Date(conf.nowDate)).getTime();
                
                self.stopDate = (new Date(conf.stopDate)).getTime();
                if (self.stopDate > now) {
                    self.$wrapper = $('<div class="deal-items">').appendTo(self.element).hide();
                    self.$days = $('<div class="deal-item days"><span class="value"></span> <span class="label">' + conf.dayLabel + '</span></div>').appendTo(self.$wrapper).find('.value');
                    self.$hours = $('<div class="deal-item hours"><span class="value"></span> <span class="label">' + conf.hourLabel + '</span></div>').appendTo(self.$wrapper).find('.value');
                    self.$mins = $('<div class="deal-item mins"><span class="value"></span> <span class="label">' + conf.minLabel + '</span></div>').appendTo(self.$wrapper).find('.value');
                    self.$secs = $('<div class="deal-item secs"><span class="value"></span> <span class="label">' + conf.secLabel + '</span></div>').appendTo(self.$wrapper).find('.value');
                    self.interval = setInterval(function() {
                        self._countDown();
                    }, conf.delay);
                    self.$wrapper.fadeIn(300, 'linear', function() { self.$wrapper.css({display: ''}); });
                } else {
                    console.log(self.stopDate);
                    console.log(self.startDate);
                }
            }
        },
        _countDown: function() {
            var self = this, conf = this.options;
            var now = new Date().getTime() - self.delta, distance = self.stopDate - now;
            var days = Math.floor(distance / (1000 * 60 * 60 * 24)), hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
            mins = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)), secs = Math.floor((distance % (1000 * 60)) / 1000);
            self.$days.text(days); self.$hours.text(hours); self.$mins.text(mins); self.$secs.text(secs);
            if (distance < 0) {
                self.$wrapper.hide();
                clearInterval(self.interval);
            }
        }
    });
    
    $.widget('codazon.searchtrigger', {
        options: {
            searchContainer: '#header-search-container',
            toggleClass: 'search-opened'
        },
        _create: function() {
            var self = this, conf = this.options;
            var $searchContainer = $(conf.searchContainer);
            var mbSearch = function() {
                $searchContainer.removeClass(conf.toggleClass);
            };
            var dtSearch = function() {};
			var hasFullBox = false;
			self.element.on('click.triggersearch', function(e) {
				e.preventDefault();
				if ($('.full-box-trigger').length) {
					$('.full-box-trigger').first().trigger('click');
					hasFullBox = true;
				} else {
					$searchContainer.toggleClass(conf.toggleClass);
					if ($searchContainer.hasClass(conf.toggleClass)) {
						self.element.addClass(conf.toggleClass);
					} else {
						self.element.removeClass(conf.toggleClass);
					}
					hasFullBox = false;
				}
			});
			
			$('body').on('click', function(e) {
				if (!hasFullBox) {
					if ($searchContainer.hasClass(conf.toggleClass)) {
						var $target = $(e.target);
						var cond1 = $searchContainer.is($target),
						cond2 = ($searchContainer.find($target).length > 0),
						cond3 = self.element.is($target),
						cond4 = (self.element.find($target).length > 0);
						if(!(cond1 || cond2 || cond3 || cond4)) {
							$searchContainer.removeClass(conf.toggleClass);
							self.element.removeClass(conf.toggleClass);
						}
					}
				}
			});
			$window.on(deskEvent, dtSearch);
			$window.on(mobiEvent, mbSearch);
        }
    });
    $.widget('codazon.searchtoggle', {
        options: {
            toggleBtn: '[data-role=search_toggle]',
            searchForm: '[data-role=search_form]',
            toggleClass: 'input-opened',
            mbClass: 'mb-search',
            onlyMobi: true,
            hoverOnDesktop: false
        },
        _create: function () {
            var $element = this.element, conf = this.options,
            $searchForm = $(conf.searchForm, $element),
            $searchBtn = $(conf.toggleBtn, $element);
            
            var mbSearch = function() {
                $element.addClass(conf.mbClass);
                $searchForm.removeClass('hidden-xs');
            };
            var dtSearch = function() {
                $element.removeClass(conf.mbClass);
                $searchForm.addClass('hidden-xs');
            };
            if (conf.onlyMobi) {
                if (themecore.isMbScreen()) {
                    mbSearch();
                } else {
                    dtSearch();
                }
                $window.on(deskEvent, dtSearch);
                $window.on(mobiEvent, mbSearch);
            } else {
                mbSearch();
                if (conf.hoverOnDesktop) {
                     $element.hover(
                        function() {
                            if (!themecore.isMbScreen()) {
                                $element.addClass(conf.toggleClass);
                            }
                        },
                        function() {
                            if (!themecore.isMbScreen()) {
                                $element.removeClass(conf.toggleClass);
                            }
                        }
                    );
                }
            }
            $searchBtn.on('click', function() {
                if (conf.hoverOnDesktop) {
                    if (themecore.isMbScreen()) {
                        $element.toggleClass(conf.toggleClass);
                    }
                } else {
                    $element.toggleClass(conf.toggleClass);
                }
            });
        }
    });
    
    $.widget('codazon.isogrid', {
        options: {
            groupStyle: '1,2,2',
            item: '.product-item',
            useDataGrid: true,
            breakPoint: mBreakpoint,
            sameHeight: ['.product-item-details','.product-details'],
            sliderConfig: {},
            colWidth: {
                1: '40%',
                2: '20%',
                3: '20%',
                4: '20%'
            }
        },
        _create: function() {
            var self = this, conf = this.options, t = false, ww = window.innerWidth;
            this._assignVariables();
            this._groupItems();
            this._itemEffect();
            if ((conf.groupStyle == '1,3,3,3') || (conf.groupStyle == '3,3,3,1')) {
                $window.on('adaptchange_1200', function() {
                    if (window.innerWidth > conf.breakPoint && ww >= conf.breakPoint) {
                        self._groupItems();
                    }
                    ww = window.innerWidth
                });
            }
            $window.on('adaptchange', function() {
                ww = window.innerWidth;
                self._groupItems();
            });
            $window.on(winWidthChangedEvent, function() {
                setTimeout(function() {
                    self._sameHeight();
                }, 300);
            });
        },
        _sumArray: function(array) {
            return array.reduce(function(a, b){return parseFloat(a) + parseFloat(b)});
        },
        _assignVariables: function() {
            var conf = this.options;
            if(conf.useDataGrid && this.element.parents('[data-grid]').length) {
                conf.groupStyle = this.element.parents('[data-grid]').data('grid');
            }
            this.subGroup = conf.groupStyle.split(',');
            this.iPG = this._sumArray(this.subGroup);
            this.colPG = this.subGroup.length;
            this.totalItems = this.element.children().length;
            this.totalGroup = Math.floor(this.totalItems/this.iPG);
            this.$allItems = this.element.find('.product-item');
        },
        _groupItems: function() {
            if (window.innerWidth < this.options.breakPoint) {
                this._groupItemsOnMb();
            } else {
                this._groupItemsOnPC();
            }
        },
        _itemEffect: function() {
            itemEffect(this.element, 100);
        },
        _groupItemsOnMb: function() {
            var conf = this.options;
            var $inner = $('<div class="mb-group-inner">').appendTo(this.element);
            this.$allItems.each(function(i, el) {
                $(el).appendTo($inner);
            });
            this.element.find('[data-smallimage]').each(function() {
                var $img = $(this);
                $img.attr('src', $img.attr('data-smallimage'));
            });
            this.element.removeClass('hidden').children('.group-inner').trigger('destroy.owl.carousel').remove();
            $.codazon.slider({sliderConfig: conf.sliderConfig, sameHeight: []}, $inner);
        },
        _groupItemsOnPC: function() {
            var self = this, conf = this.options;
            this.Group = [];
            this.$allItems.each(function(i, el) {
                var $item = $(this), groupId = Math.floor(i/self.iPG);
                if (typeof self.Group[groupId] === 'undefined') {
                    self.Group[groupId] = [];
                }
                self.Group[groupId].push($item);
            });
            this.element.children('.group-inner').addClass('old').trigger('destroy.owl.carousel');
            var $inner = $('<div class="group-inner">').appendTo(this.element);
            if ((window.innerWidth < 1200) && ((conf.groupStyle == '1,3,3,3') || (conf.groupStyle == '3,3,3,1'))) {
                var subGroup = [1,2,2,2,2,2];
            } else {
                var subGroup = self.subGroup;
            }
            this.element.removeClass('hidden');
            $.each(this.Group, function(i, group) {
                var $group = $('<div class="item-group flex-grid">').appendTo($inner), itemIndex = 0;
                $.each(subGroup, function(ii, iPC) {
                    if (iPC == 1) {
                        var width = (typeof conf.colWidth[iPC] != 'undefined')?(conf.colWidth[iPC]):'50%',
                        colClass = 'large-col';
                    } else {
                        var width = (typeof conf.colWidth[iPC] != 'undefined')?(conf.colWidth[iPC]):'25%',
                        colClass = 'small-col';
                    }
                    var $col = $('<div class="group-col">').appendTo($group).css({width: width}).addClass(colClass);
                    for(var j=0; j < iPC; j++) {
                        if (typeof group[itemIndex] != 'undefined') {
                            group[itemIndex].find('[data-smallimage]').each(function() {
                                var $img = $(this), $gallery = group[itemIndex].find('[data-gallery]');
                                if (iPC == 1) {
                                    if ($gallery.length) {
                                        $gallery.hide();
                                        $.codazon.horizontalThumbsSlider($gallery.data('gallery'), $gallery);
                                        $gallery.removeAttr('data-gallery');
                                    }
                                    $img.attr('src', $img.attr('data-largeimg'));
                                } else {
                                    if ($gallery.length) {
                                        $gallery.remove();
                                    }
                                    $img.attr('src', $img.attr('data-smallimage'));
                                }
                            });
                            group[itemIndex].appendTo($col);
                            itemIndex++;
                        }
                    }
                });
                if ((conf.groupStyle == '1,3,3,3') || (conf.groupStyle == '3,3,3,1')) {
                    var groupColWidth = 100 - parseFloat(conf.colWidth[1]);
                    var $mergedSubGroup = $('<div class="merged-sub-group">').css('width', groupColWidth + '%');
                    if ((conf.groupStyle == '1,3,3,3')) {
                        $mergedSubGroup.appendTo($group);
                    } else {
                        $mergedSubGroup.prependTo($group);
                    }
                    $group.find('.small-col').css('width', '').appendTo($mergedSubGroup);
                    $.codazon.slider({sliderConfig: {
                        margin: 0, dots: false, nav: false,
                        responsive: {
                            768: {items: 2, nav: true}, 1200: {items: 3, pullDrag: false}
                        }},
                        sameHeight: []
                    }, $mergedSubGroup);
                }
                
            });
            this.element.children('.group-inner.old').remove();
            if (self.Group.length > 1) {
                $.codazon.slider({sliderConfig: conf.sliderConfig, sameHeight: []}, $inner.addClass('owl-carousel cdz-grid-slider'));
            }
            this._sameHeight();
            this.element.find('.img-gallery').css({display: ''});
            this.element.find('.mb-group-inner').trigger('destroy.owl.carousel').remove();
        },
        _sameHeight: function() {
            var conf = this.options;
            if (window.innerWidth >= conf.breakPoint) {
                this.element.find('.item-group').each(function() {
                    var $group = $(this);
                    $.each(conf.sameHeight, function(i, sameHeight) {
                        var maxHeight = 0;
                        $group.find('.small-col ' + sameHeight).css({height: '', minHeight: ''}).each(function() {
                            var $sItem = $(this);
                            var height = $sItem.outerHeight();
                            if (height > maxHeight) {
                                maxHeight = height;
                            }
                        }).css({minHeight: maxHeight});
                    });
                });
            } else {
                this.element.find(conf.sameHeight).css({height: ''});
            }
        }
    });
    
    $.widget('codazon.horizontalThumbsSlider', {
        options: {
            parent: '.product-item',
            mainImg: '.product-image-wrapper .product-image-photo:last',
            itemCount: 4,
            activeClass: 'item-active',
            loadingClass: 'swatch-option-loading',
            moreviewSettings: {},
            images: [],
        },
        _create: function(){
            var self = this, config = this.options;
            if(config.images.length == 0) {
                return false;
            }
            this.$parent = this.element.parents(config.parent).first();
            this.$mainImg = $(config.mainImg, this.$parent);
            this.images = config.images;
            this.initHtml();
            this.bindHoverEvent();
            this.element.css({minHeight:''});
        },
        initHtml: function(){
            var self = this, config = this.options;
            this.$slider = $(this.getHtml(this.images));
            this.$slider.appendTo(this.element);
            this.initSlider();
            this.element.css({display: ''});
        },
        initSlider: function() {
            var self = this, config = this.options;
            var sliderConfig = $.extend({}, {items: 4, nav: true, dots: false, mouseDrag: false, touchDrag: false}, config.moreviewSettings);
            sliderConfig.responsiveRefreshRate = 200;
            $.codazon.slider({sliderConfig: sliderConfig}, this.$slider);
        },
        bindHoverEvent: function(){
            var self = this, config = this.options;
            $('.gitem', this.$slider).each(function(){
                var $gitem = $(this), $link = $('.img-link', $gitem), $img = $('img', $link), mainSrc = $link.attr('href');
                $link.on('click',function(e){
                    e.preventDefault();
                }).hover(
                    function(){
                        if ($gitem.parents('.owl-carousel.media-slider').length) {
                            $gitem.addClass(config.activeClass).parent().siblings().children().removeClass(config.activeClass);
                        } else {
                            $gitem.addClass(config.activeClass).siblings().removeClass(config.activeClass);
                        }
                        if(typeof $link.data('loaded') === 'undefined') {
                            var mainImg = new Image();
                            self.$mainImg.addClass(config.loadingClass);
                            $(mainImg).load(function(){
                                self.$mainImg.removeClass(config.loadingClass);
                                self.$mainImg.attr('src', mainSrc);
                                $link.data('loaded', true);
                            });
                            mainImg.src = mainSrc;
                        }else{
                            self.$mainImg.attr('src', mainSrc);
                        }
                    }
                );
            });
        },
        getHtml: function(images){
            var self = this, config = this.options;
            var html =  '<div class="gitems media-slider">';
            $.each(images,function(id,img){
                html += '<div class="gitem">';
                html +=     '<a class="img-link" href="'+ img.large +'"><img class="img-responsive" src="'+ img.small +'" /></a>';
                html += '</div>';
            });
            html += '</div>';
            return html;
        }
    });
    
    $.widget('codazon.stickyMenu', {
        options: {
            threshold: 300,
            enableSticky: codazon.enableStikyMenu
        },
        _create: function () {
            var self = this, config = this.options;
            if (!config.enableSticky) {
                return false;
            }
            var $win = $(window);
            var $parent = self.element.parent();
            var parentHeight = $parent.height();
            $parent.css({minHeight: parentHeight});
            
            var t = false, w = $win.prop('innerWidth');
            $win.on('resize less_complete',function () {
                if (t) {
                    clearTimeout(t);
                }
                t = setTimeout(function () {
                    var newWidth = $win.prop('innerWidth');
                    if (w != newWidth) {
                        self.element.removeClass('active');
                        $parent.css({minHeight:''});
                        t = setTimeout(function () {
                            parentHeight = $parent.height();
                            $parent.css({minHeight:parentHeight});
                        }, 50);
                        w = newWidth;
                    }
                }, 200);
            });
            //$win.on('load',function () {
                setTimeout(function () {
                    $parent.css({minHeight:''});
                    parentHeight = $parent.height();
                    $parent.css({minHeight:parentHeight});
                    var stickyNow = false, currentState = false;
                    $win.scroll(function () {
                        var curWinTop = $win.scrollTop();
                        if (curWinTop > config.threshold) {
                            self.element.addClass('active');
                            currentState = true;
                        } else {
                            self.element.removeClass('active');
                            currentState = false;
                        }
                        if (currentState != stickyNow) {
                            $win.trigger('changeHeaderState');
                            stickyNow = currentState;
                        }
                    });
                }, 300);
            //});
        }
    });
    
	$.widget('codazon.categorySearch', {
        options: {
            trigger: '[data-role="trigger"]',
            dropdown: '[data-role="dropdown"]',
            catList: '[data-role="category-list"]',
            activeClass: 'open',
            currentCat: false,
            allCatText: 'All Categories',
            ajaxUrl: false
        },
        _create: function() {
            this._assignVariables();
            this._assignEvents();
        },
        _assignVariables: function() {
            var self = this, conf = this.options;
            this.$trigger = this.element.find(conf.trigger);
            this.$triggerLabel = this.$trigger.children('span');
            this.$dropdown = this.element.find(conf.dropdown);
            this.$catList = this.element.find(conf.catList);
            this.$searchForm = this.element.parents('form').first();
            this.$searchForm.addClass('has-cat');
            this.$catInput = this.$searchForm.find('[name=cat]');
            this.$qInput = this.$searchForm.find('[name=q]');
            if (this.$catInput.length == 0) {
                this.$catInput = $('<input type="hidden" id="search-cat-input" name="cat">').appendTo(this.$searchForm);
            }
            if (conf.currentCat) {
                this.$catInput.val(conf.currentCat);
                var catText = this.$catList.find('[data-id=' + conf.currentCat + ']').text();
                this.$triggerLabel.text(catText);
            } else {
                this.$catInput.attr('disabled', 'disabled');
            }
            this.element.insertBefore(self.$searchForm);
        },
        _assignEvents: function() {
            var self = this, conf = this.options;
            
            $('body').on('click', '#suggest > li:first > a, .searchsuite-autocomplete .see-all', function(e) {
                e.preventDefault();
                self.$searchForm.submit();
            });
            
            this.$trigger.on('click', function() {
                self.element.toggleClass(conf.activeClass);
            });
            this.$catList.find('a').on('click', function(e) {
                e.preventDefault();
                var $cat = $(this), id = $cat.data('id'), label = $cat.text();
                if (id) {
                    self.$catInput.removeAttr('disabled').val(id).trigger('change');
                    self.$triggerLabel.text(label);
                } else {
                    self.$catInput.attr('disabled', 'disabled').val('').trigger('change');
                    self.$triggerLabel.text(conf.allCatText);
                }
                self.$qInput.trigger('input');
                self.element.removeClass(conf.activeClass);
            });
            $('body').on('click', function(e) {
                if (self.element.has($(e.target)).length == 0) {
                    self.element.removeClass(conf.activeClass);
                }
            });
        }
    });
	
	$.widget('codazon.fullsearchbox', {
		options: {
			enable: true
		},
		_create:  function() {
			var self = this, conf = this.options;
			if (conf.enable) {
				this._assignVariables();
				this._bindEvents();
                $('.js-search-trigger').removeAttr('data-role');
                $('.js-search-trigger').on('click', function(e) {
                    e.preventDefault();
                    self.$fullTrigger.click();
                });
			}
		},
		_assignVariables: function() {
			var self = this, conf = this.options;
			self.element.addClass('full-search-box');
			this.$clone = false;
			this._initHtml();
		},
		_initHtml: function() {
			var self = this, conf = this.options;
			this.$placeholder = $('<div class="full-box-placeholder" style="display:none;">').insertBefore(this.element);
			this.$fullTrigger = $('<div class="full-box-trigger">').appendTo(this.element);
			this.$box = $('<div class="search-box-area">').appendTo('body');
			this.$boxInner = $('<div class="search-box-inner">').appendTo(this.$box);
			this.$boxBaceface = $('<div class="search-box-backface" data-role="close_full_box">').appendTo(this.$boxInner);
			this.$boxContent = $('<div class="search-box-content">').appendTo(this.$boxInner);
			this.$closeBox = $('<div class="search-box-close" data-role="close_full_box">').appendTo(this.$boxInner);
            
            if ($('#search-by-category-tmpl').length) {
                var catHtml = $('#search-by-category-tmpl').html();
                this.$catSearch = $(catHtml);
                this.$catSearch.appendTo(this.element.find('form'));
                var dataSearch = this.$catSearch.data('search');
                $.codazon.categorySearch(dataSearch, this.$catSearch);
				self.$catSearch.find('.dropdown-inner').niceScroll({cursorcolor:'#9E9E9E', cursorborder:'#747070'});
				self.$box.on('searchBoxOpened', function() {
					self.$catSearch.find('.dropdown-inner').getNiceScroll().resize();
				});
            }
		},
		_bindEvents: function() {
			var self = this, conf = this.options;
			this.$fullTrigger.on('click', function() {
				$('body').addClass('search-box-opened');
				self.$clone = self.element.clone().insertAfter(self.$placeholder);
				self.$clone.find('[id]').removeAttr('id');
				//self.$clone.find('input[type=text]').attr('type', 'tel');
				self.element.appendTo(self.$boxContent);
				setTimeout(function() {
					self.element.find('[name=q]').focus();
					self.$box.trigger('searchBoxOpened');
				}, 400);
				self.$fullTrigger.hide();
			});
			this.$box.on('click', '[data-role="close_full_box"]', function() {
				$('body').removeClass('search-box-opened');
				if (self.$clone !== false) {
					self.$clone.remove();
				}
				self.$clone = false;
				self.element.insertAfter(self.$placeholder);
				self.$fullTrigger.show();
			});
		}
	});
	
	$.widget('codazon.validation', {
		_create: function() {
			var self = this;
			require(['validation', 'domReady'], function() {
				self.element.validation({
					submitHandler: function() {
						if (self.element.valid()) {
							self.element.submit();
						}
					}
				})
			});
		}
	});
	
    $.widget('codazon.toggleList', {
        options: {
            item: 'li',
            itemList: 'ul',
            link: 'a'
        },
        _create: function() {
            var self = this, conf = this.options;
            self.element.children(conf.item).addClass('level-top');
            $(conf.item, self.element).each(function() {
                var $item = $(this);
                var $a = $item.children(conf.link);
                if ($item.children(conf.itemList).length) {
                    $item.addClass('parent');
                    var $itemList = $item.children(conf.itemList).hide();
                    var $toggle = $('<span class="menu-toggle">');
                    $toggle.insertAfter($a).on('click', function() {
                        $itemList.slideToggle(300);
                        $item.toggleClass('active');
                    });
                }
            });
        }
    });
    
    $.widget('codazon.ratingSummary', {
        options: {
            tmpl: '#rating-summary-tmpl'
        },
        _create: function() {
            var self = this, conf = this.options;
            
            require(['mage/template', 'underscore'], function(mageTemplate) {
                self.tmpl = mageTemplate(conf.tmpl);
                self.$parent = $('.product-info-main .product-reviews-summary .rating-summary');
                if (self.$parent.length) {
                    $(self.tmpl({data: conf.data})).appendTo(self.$parent);
                }
            });
        }
    });
    
    $.widget('codazon.productMedia', {
        _create: function() {
            this._assignVariables();
            if (this.$moreview.length) {
                this._initHTML();
            }
        },
        _assignVariables: function() {
            var self = this, conf = this.options;
            this.$moreview = self.element.find(conf.moreViewSlider);
            this.$main = self.element.find(conf.main);
            this.$mainImage = self.element.find(conf.mainImage);
            this.type = conf.type;
        },
        _getGalleryConfig:  function() {
            return {
                selector: '.gallery-image',
                exThumbImage: 'data-src',
                download: false
            }
        },
        _initHTML: function() {
            var self = this, conf = this.options;
            var mainImage = new Image();
            $(mainImage).on('load', function() {
                setTimeout(function() {
                    self._createSlider();
                    $(window).trigger('resize');
                    var $gallery = self.$main.find('.product-image-gallery');
                    $gallery.find('.cdz-lazy').each(function() {
                        var $img = $(this);
                        $img.attr('src', $img.attr('data-src'));
                    });
                    if (typeof $.fn.lightGallery === 'function') {
                        $gallery.lightGallery(self._getGalleryConfig());
                        $gallery.on('swapImageCompleted', function() {
                            $gallery.data('lightGallery').destroy(true);
                            $gallery.lightGallery(self._getGalleryConfig());
                        });
                    }
                }, 0);
            });
            this.$moreview.on('click touchstart', '[data-role=thumb-item]', function() {
                $(this).addClass('selected-thumb').siblings().removeClass('selected-thumb');
            })
            mainImage.src = this.$mainImage.attr('src');
        },
        _getMedia: function(){
            if(window.innerWidth < mBreakpoint){
                return 'mobile';
            }else{
                return 'desktop';
            }
        },
        _createGallery: function() {
            
        },
        _createSlider: function() {
            var self = this, conf = this.options;
            var config = this._getSliderSettings({});
            this.$moreview.lightSlider(config);
            var curMedia = this._getMedia();
            var t = false;
            $(window).on('resize', function() {
                if (t) clearTimeout(t);
                t = setTimeout(function() {
                    if (curMedia != self._getMedia()) {
                        curMedia = self._getMedia();
                        self.$moreview.destroy();
                        self.$moreview.removeClass('lSSlide');
                        self.$moreview.lightSlider = $.fn.lightSlider;
                        self.$moreview.lightSlider(self._getSliderSettings());
                    } else {
                        self.$moreview.setConfig(self._getSliderSettings());
                        self.$moreview.refresh();
                    }
                }, 100);
            });
        },
        _getSliderSettings: function(config) {
            var self = this, conf = this.options;
            if (!config) {
                var config = {};
            }
            
            config.slideMargin = conf.slideMargin;
            
            config.vertical = (conf.type == 'vertical') && (window.innerWidth >= mBreakpoint);
            if (config.vertical) {
                config.slideHeight = this.$moreview.find('a').first().innerHeight();
                config.verticalHeight = this.$main.outerHeight();
                config.item = (config.verticalHeight - config.slideMargin) / (config.slideHeight + config.slideMargin);
            } else {
                config.item = (self.$main.outerWidth() - config.slideMargin) / (conf.thumbWidth + config.slideMargin);
            }
            config.pager = false;
            return config;
        }
        
    });
    
    $.widget('codazon.videoframe', {
        options: {
            url: false,
            dimensionRatio: 0.562,
            playBtn: '[data-role=play-video]',
            loader: '[data-role=video-loader]',
            placeholder: '[data-role=video-placeholder]'
        },
        _create: function() {
            this._assignVariable();
            this._initHTML();
            this.element.find(this.options.loader).remove();
            this.element.removeClass('video-no-loaded');
            this._events();
        },
        _assignVariable: function() {
            var self = this, conf = this.options;
            this.$playBtn = $(conf.playBtn, this.element);
            this.$placeholder = $(conf.placeholder, this.element);
            this.$frameVideo = $('#cdz-video-frame');
            this.paddingBottom =  (conf.dimensionRatio*100) + '%';
            this.video = this._getVideo(conf.url);
            if (this.video.type == 'youtube') {
                this.video.url = '//www.youtube.com/embed/' + this.video.id + '?autoplay=1';
            } else {
                this.video.url = '//player.vimeo.com/video/' + this.video.id + '?autoplay=1';
            }
            this.scrollBarWidth = this._getScrollBarWidth();
        },
        _getScrollBarWidth: function () {
            var $outer = $('<div>').css({visibility: 'hidden', width: 100, overflow: 'scroll'}).appendTo('body'),
                widthWithScroll = $('<div>').css({width: '100%'}).appendTo($outer).outerWidth();
            $outer.remove();
            return 100 - widthWithScroll;
        },
        _initHTML: function() {
            var self = this, conf = this.options;
            if (!this.$frameVideo.length) {
                this.$frameVideo = $('<div class="cdz-video-frame" id="cdz-video-frame" data-cdzpopup>').appendTo($('body'));
                $('body').trigger('cdzBuildPopup');
            }
            var phdSrc = this.$placeholder.data('src');
            if (!phdSrc || conf.use_df_placeholder) {
                if (this.video.type === 'youtube') {
                    phdSrc = "//img.youtube.com/vi/" + this.video.id + "/hqdefault.jpg";
                    this.$placeholder.attr('src', phdSrc);
                } else if (this.video.type === 'vimeo') {
                    $.ajax({
                        type: 'GET',
                        url: '//vimeo.com/api/v2/video/' + self.video.id + '.json',
                        jsonp: 'callback',
                        dataType: 'jsonp',
                        success: function (data) {
                            phdSrc = data[0].thumbnail_large;
                            self.$placeholder.attr('src', phdSrc);
                        }
                    });
                }
            } else {
                this.$placeholder.attr('src', phdSrc);
            }
        },
        _events: function() {
            var self = this, conf = this.options;
            this.$playBtn.on('click', function() {
                var frame = '<iframe frameborder="0" allowfullscreen="1" class="abs-frame-inner" src="' + self.video.url + '"></iframe>';
                var $frameInner = $('<div class="frame-inner abs-frame">');
                $frameInner.html(frame);
                $frameInner.css({paddingBottom: self.paddingBottom});
                self.$frameVideo.html($frameInner);
                self.$frameVideo.trigger("triggerPopup");
            });
            self.$frameVideo.on('popupClosed', function() {
                self.$frameVideo.html('');
            });
        },
        _getVideo: function (url) {
            if (url) {
                id = url.match(/(http:|https:|)\/\/(player.|www.)?(vimeo\.com|youtu(be\.com|\.be|be\.googleapis\.com))\/(video\/|embed\/|watch\?v=|v\/)?([A-Za-z0-9._%-]*)(\&\S+)?/);
                if (id[3].indexOf('youtu') > -1) {
                    type = 'youtube';
                } else if (id[3].indexOf('vimeo') > -1) {
                    type = 'vimeo';
                } else {
                    throw new Error('Video URL not supported.');
                }
                id = id[6];
            } else {
                throw new Error('Missing video URL.');
            }
            return {
                type: type,
                id: id
            };
        }
    });
    $.widget('codazon.ajaxcmsblock', {
        options: {
            ajaxUrl: false
        },
        _create: function() {
            var self = this, conf = this.options;
            if (conf.ajaxUrl && conf.blockIdentifier) {
                $.ajax({
                    url: conf.ajaxUrl,
                    cache: true,
                    data: {block_identifier: conf.blockIdentifier},
                    method: 'get',
                    success: function(rs) {
                        self.element.html(rs);
                        if (typeof conf.afterLoaded == 'function') {
                            conf.afterLoaded();
                        };
                        self.element.trigger('contentLoaded');
                        self.element.trigger('contentUpdated');
                    }
                });
            }
        }
    });
    $.widget('codazon.newsletterPopup', {
        _create: function() {
            var self = this, conf = this.options;
            $(window).load(function() {
                var cookieName = conf.cookieName;
                var checkCookie = Mage.Cookies.get(cookieName);
                if (!checkCookie) {
                    var date = new Date();
                    var minutes = conf.frequency;
                    date.setTime(date.getTime() + (minutes * 60 * 1000));
                    Mage.Cookies.set(cookieName, '1', date);
                    setTimeout(function() {
                        var $popup = self.element;
                        $.codazon.ajaxcmsblock({
                            ajaxUrl: conf.ajaxUrl,
                            blockIdentifier: conf.blockIdentifier,
                            afterLoaded: function() {
                                $popup.trigger('triggerPopup');
                            }
                        }, $popup);
                    }, conf.delay);
                }
            });
        }
    });
    
    $.widget('codazon.themewidgets', {
        _create: function(){
            var self = this;
            $.each(this.options, function(fn, options){
                var namespace = fn.split(".")[0];
                var name = fn.split(".")[1];
                if (typeof $[namespace] !== 'undefined') {
                    if(typeof $[namespace][name] !== 'undefined') {
                        $[namespace][name](options, self.element);
                    }
                }
            });
        }
    });
    return $.codazon.themewidgets;
})(jQuery);