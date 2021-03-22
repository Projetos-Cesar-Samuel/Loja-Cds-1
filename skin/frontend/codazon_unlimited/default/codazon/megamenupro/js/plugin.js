(function($){
if(typeof $.cdzwidget === 'undefined'){
	$.cdzwidget = function(name,widgetHandler){
		$.fn[name] = function(options){
			return this.each(function(){
				var $element = $(this);
				var handler = $.extend({},widgetHandler);
				handler.init = function(){
					handler.element = $element;
					if(typeof handler.options == 'undefined'){
						handler.options = {};
					}
					handler.options = $.extend({},handler.options,options);
					if(typeof handler._create === 'function'){
						handler._create();	
					}
				}
				handler.init();
			});	
		}
	}
	$(document).ready(function(){
		function createWidget($context){
			$('[data-cdzwidget]',$context).each(function(){
				var $element = $(this),
				widget = $element.data('cdzwidget');
				
				for(var name in widget){
					var options = widget[name];
					if(typeof $.fn[name] === 'function'){
						$element[name](options);
						$element.on('contentUpdated',function(){
							createWidget($element);
						});
					}
				}
				$element.removeAttr('data-cdzwidget');
			});
		}
		createWidget($('body'));
		$('*').on('contentUpdated',function(){
			var $context = $(this);
			createWidget($context);
		});
	});
}
$.cdzwidget('cdz_googlemap', {
	options: {
		mapLat: 45.6107667,
		mapLong: -73.6108024,
		mapZoom: 10,
		mapAddress: '',
		markerTitle: '',
		jsSource: '//maps.googleapis.com/maps/api/js?v=3.17&signed_in=true&key=AIzaSyByF5Th99QzkJtIhod9awRaDK2CGSNB43o',
		
	},
	_create: function(){
		var self  = this, config = this.options;
		var initMap = function(){
			var myLatlng = new google.maps.LatLng(config.mapLat, config.mapLong);
			var mapOptions = {
				zoom: config.mapZoom,
				center: myLatlng
			};
			
			
			var infowindow = new google.maps.InfoWindow({
				content: config.mapAddress
			});
			var map = null;
			function createMap(){
				var map = new google.maps.Map(self.element.get(0), mapOptions);
				var marker = new google.maps.Marker({
					position: myLatlng,
					map: map,
					title: config.markerTitle
				});
				google.maps.event.addListener(marker, 'click', function() {
					infowindow.open(map, marker);
				});
				google.maps.event.addListenerOnce(map, 'idle', function(){});
				return map;
			}
			
			if(self.element.parents('.cdz-menu').length > 0){
				var $menu = self.element.parents('.cdz-menu').first(),
				$li = self.element.parents('li.level0').first(),
				$ul = $li.find('> .groupmenu-drop');
				if(self.element.parents('.cdz-slide').length || self.element.parents('.cdz-fade').length || self.element.parents('.cdz-normal').length){
					$ul.on('animated',function(){
						if(map === null){
							map = createMap();
						}else{
							google.maps.event.trigger(map, 'resize');
						}
					});
					$li.hover(function(){
						setTimeout(function(){
							if(map === null){
								map = createMap();
							}else{
								google.maps.event.trigger(map, 'resize');
							}
						},450);
					},function(){
						
					});
				}else{
					$li.hover(function(){
						setTimeout(function(){
							if(map === null){
								map = createMap();
							}else{
								google.maps.event.trigger(map, 'resize');
							}
						},450);
					},function(){
						
					});
				}
			}else{
				map = createMap();
			}
		}
		var $jsMap = $('#widget_google_script');
		if($jsMap.length == 0){
			var googlecript = document.createElement('script'); 
			googlecript.id = 'widget_google_script';
			googlecript.type = 'text/javascript';
			googlecript.src = config.jsSource;
			$(googlecript).load(function() { 
			  	initMap();
				$(this).data('completed',true);
			});
			document.head.appendChild(googlecript);
		}else{
			if($jsMap.data('completed')){
				initMap();
			}else{
				$jsMap.load(function(){
					initMap();
				});
			}
		}
	}
});
})(jQuery);