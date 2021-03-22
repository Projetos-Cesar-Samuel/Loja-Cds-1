define(['jquery'], function($) {
	$.widget('codazon.googlemap', {
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
			require([config.jsSource],function(){
				var $wrapper = $('<div class="cdz-googe-map-inner" style="opacity:0;position:absolute; top:0; left:0; z-index:0;"></div>');
				$wrapper.height(self.element.height());
				$wrapper.width(self.element.width());
				$wrapper.appendTo('body');
				var myLatlng = new google.maps.LatLng(config.mapLat, config.mapLong);
				var mapOptions = {
					zoom: config.mapZoom,
					center: myLatlng
				};
				var map = new google.maps.Map($wrapper.get(0), mapOptions);
				
				var infowindow = new google.maps.InfoWindow({
					content: config.mapAddress
				});
				
				var marker = new google.maps.Marker({
					position: myLatlng,
					map: map,
					title: config.markerTitle
				});
				google.maps.event.addListener(marker, 'click', function() {
					infowindow.open(map, marker);
				});
				google.maps.event.addListenerOnce(map, 'idle', function(){
					if(typeof $wrapper.data('attached') === 'undefined'){
						$wrapper.data('attached',true);
						$wrapper.css({'opacity':'','position':'','left':'','top':'','width':'','z-index':'','height':''});
						$wrapper.appendTo(self.element);
						if($wrapper.parents('.cdz-menu').length > 0){
							var $menu = $wrapper.parents('.cdz-menu').first(),
							$li = $wrapper.parents('li.level0').first(),
							$ul = $li.find('> .groupmenu-drop');
							$ul.on('animated',function(){
								google.maps.event.trigger(map, 'resize');
							});
							$li.hover(function(){
								setTimeout(function(){
									google.maps.event.trigger(map, 'resize');
								},450);
							},function(){
								
							});
						}
					}
				});
			});
		}
	});
	return $.codazon.googlemap;
});