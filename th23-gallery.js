jQuery(document).ready(function($) {

	// Add Photoswipe overlay
	$('body').append('<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true"><div class="pswp__bg"></div><div class="pswp__scroll-wrap"><div class="pswp__container"><div class="pswp__item"></div><div class="pswp__item"></div><div class="pswp__item"></div></div><div class="pswp__ui pswp__ui--hidden"><div class="pswp__top-bar"><div class="pswp__counter"></div><button class="pswp__button pswp__button--close"></button><button class="pswp__button pswp__button--share"></button><button class="pswp__button pswp__button--fs"></button><button class="pswp__button pswp__button--zoom"></button><div class="pswp__preloader"><div class="pswp__preloader__icn"><div class="pswp__preloader__cut"><div class="pswp__preloader__donut"></div></div></div></div></div><div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap"><div class="pswp__share-tooltip"></div></div><button class="pswp__button pswp__button--arrow--left"></button><button class="pswp__button pswp__button--arrow--right"></button><div class="pswp__caption"><div class="pswp__caption__center"></div></div></div></div></div>');
	// note: enable the following for including localized titles - requires respective changes in PHP
	// $('body').append('<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true"><div class="pswp__bg"></div><div class="pswp__scroll-wrap"><div class="pswp__container"><div class="pswp__item"></div><div class="pswp__item"></div><div class="pswp__item"></div></div><div class="pswp__ui pswp__ui--hidden"><div class="pswp__top-bar"><div class="pswp__counter"></div><button class="pswp__button pswp__button--close" title="' + th23_gallery_js['close'] + '"></button><button class="pswp__button pswp__button--share" title="' + th23_gallery_js['share'] + '"></button><button class="pswp__button pswp__button--fs" title="' + th23_gallery_js['fullscreen'] + '"></button><button class="pswp__button pswp__button--zoom" title="' + th23_gallery_js['zoom'] + '"></button><div class="pswp__preloader"><div class="pswp__preloader__icn"><div class="pswp__preloader__cut"><div class="pswp__preloader__donut"></div></div></div></div></div><div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap"><div class="pswp__share-tooltip"></div></div><button class="pswp__button pswp__button--arrow--left" title="' + th23_gallery_js['previous'] + '"></button><button class="pswp__button pswp__button--arrow--right" title="' + th23_gallery_js['next'] + '"></button><div class="pswp__caption"><div class="pswp__caption__center"></div></div></div></div></div>');

	// Opening function
	function openGallery(e, noPreview) {
		
		// Define options/ params
		var params = {
			galleryUID: e.data('gallery'),
			index: e.data('picture') - 1,
			showHideOpacity: true,
			getThumbBoundsFn: function(index){
				var rect = gdata[e.data('gallery') - 1][index].thumb.getBoundingClientRect();
				var pageYScroll = window.pageYOffset || document.documentElement.scrollTop;
				return { x:rect.left, y:rect.top + pageYScroll, w:rect.width };
			},
			closeOnScroll: false,
			closeOnVerticalDrag: true,
			history: false,
			errorMsg: '<div class="pswp__error-msg">' + th23_gallery_js['error_loading'] + '</div>',
			preload: [1, 2],
			loadingIndicatorDelay: 0,
			bgOpacity: 0.8,
			loop: false, // disables looping ONLY on touch devices/ swiping
			tapToToggleControls: false, // disables, as we take care of that globally across devices
			closeElClasses: [], // disable mouse click closing gallery
			clickToCloseNonZoomable: false, // disable click on image to close for smaller images
			counterEl: false,
			fullscreenEl: false,
			zoomEl: false,
			shareEl: false
		}
		
		// Init gallery
		var ps = new PhotoSwipe($('.pswp')[0], PhotoSwipeUI_Default, gdata[e.data('gallery') - 1], params);
		ps.init();

		// Do not "blowup" thumbnail as preview image (while loading big version), if it has different aspect ratio than the original
		if(noPreview) {
			$('.pswp__img--placeholder').css('opacity', 0);
		}

		// Trigger (optional) actions defined eg in themes, upon opening/closing  PhotoSwipe
		var gallery = (typeof $.th23gallery !== 'undefined' && $.isFunction($.th23gallery)) ? $.th23gallery : function(action) {
			if(action == 'open') {
				$('body').addClass('pswp--open');
			}
			else if(action == 'close') {
				$('body').removeClass('pswp--open');
			}
		};
		ps.listen('initialZoomIn', function(){ // vs doing it on "initialZoomInEnd" to separate from animation due to performance?
			gallery('open');
			ps.updateSize(true);
		});
		ps.listen('initialZoomOut', function(){
			gallery('close');
		});

		// Avoid scroll and site navigation via keys while PhotoSwipe is open
		$(document).bind('keydown', preventKeyPress);
		ps.listen('destroy', function(){
			$(document).unbind('keydown', preventKeyPress);
		});
		// Prevent function of backspace (8), space (32), page up/page down (33/34) and up/down (38/40) keys
		function preventKeyPress(e) {
			if([8, 32, 33, 34, 38, 40].indexOf(e.keyCode) > -1) {
				e.preventDefault();
				e.stopPropagation();
			}
			if(e.keyCode == 8) {
				ps.close();
			}
		}

		if(!ps.options.loop) {

			// Avoid loop by touch/click on buttons
			function preventLoopByButton() {
				var current = ps.getCurrentIndex() + 1;
				$('.pswp__button--arrow--left, .pswp__button--arrow--right').removeClass('pswp__element--disabled');
				if(current == 1) {
					$('.pswp__button--arrow--left').addClass('pswp__element--disabled');
				}
				if(current == ps.options.getNumItemsFn()) {
					$('.pswp__button--arrow--right').addClass('pswp__element--disabled');
				}
			}
			// Hide buttons upon init on first/last slide
			preventLoopByButton();
			// Update buttons visibility upon slide change
			ps.listen('beforeChange', function(){ preventLoopByButton(); });

			// Avoid loop by touch/click on buttons
			function preventLoopByKey(e) {
				var current = ps.getCurrentIndex() + 1;
				if(current == 1 && e.keyCode == 37) {
					e.preventDefault();
					e.stopPropagation();
				}
				else if(current == ps.options.getNumItemsFn() && e.keyCode == 39) {
					e.preventDefault();
					e.stopPropagation();
				}
			}
			$('.pswp').bind('keydown', preventLoopByKey);
			ps.listen('destroy', function(){
				$('.pswp').unbind('keydown', preventLoopByKey);
			});

		}

		// Hook into all clicks and taps...
		var uiSwitch = function(e) {
			// Tap on image zooms in/out - same behaviour as double taps and mouse clicks
			if(e.detail.pointerType == 'touch' && e.detail.target.className == 'pswp__img') {
				var initialZoomLevel = ps.currItem.initialZoomLevel;
				if(ps.getZoomLevel() !== initialZoomLevel) {
					ps.zoomTo(initialZoomLevel, e.detail.releasePoint, 333);
				} else {
					ps.zoomTo(ps.options.getDoubleTapZoom(false, ps.currItem), e.detail.releasePoint, 333);
				}
			}
			// Click on anything beside "pswp__img" (zoom) and "pswp__button" (buttons: close, prev, next, ...) should unhide controls
			// note: no need to filter buttons, as they don't pass through the click/tap event
			else if(e.detail.target.className != 'pswp__img' && $('.pswp__ui').hasClass('pswp__ui--idle')) {
				$('.pswp__ui').removeClass('pswp__ui--idle');
			}
			/*
			// Click on anything beside "pswp__img" (zoom) and "pswp__button" (buttons: close, prev, next, ...) should show/hide controls
			// note: might replace the previous "else if" clause
			// note: no need to filter buttons, as they don't pass through the click/tap event
			else if(e.detail.target.className != 'pswp__img') {
				if($('.pswp__ui').hasClass('th23-gallery-hide-ui')) {
					$('.pswp__ui').removeClass('pswp__ui--idle th23-gallery-hide-ui');
					$('.pswp__top-bar, .pswp__button--arrow--left, .pswp__button--arrow--right').attr('style', 'display: block;');
				}
				else if($('.pswp__ui').hasClass('pswp__ui--idle')) {
					$('.pswp__ui').removeClass('pswp__ui--idle');
				}
				else {
					$('.pswp__ui').addClass('pswp__ui--idle th23-gallery-hide-ui');
					$('.pswp__top-bar, .pswp__button--arrow--left, .pswp__button--arrow--right').attr('style', 'display: none;');
				}
			}
			*/
		}
		// Reset upon init
		$('.pswp__ui').removeClass('pswp__ui--idle th23-gallery-hide-ui');
		$('.pswp__top-bar, .pswp__button--arrow--left, .pswp__button--arrow--right').attr('style', 'display: block;');
		// Check for tap events
		ps.framework.bind(ps.scrollWrap, 'pswpTap', uiSwitch);
		ps.listen('destroy', function(){
			ps.framework.unbind(ps.scrollWrap, 'pswpTap', uiSwitch);			
		});

		// Handle custom transition class to animate change between images - without disturbing user executed swipe
		// Add custom transition class on key press left/right (37/39)
		$(document).bind('keydown', addAnimateChangeKey);
		ps.listen('destroy', function(){
			$(document).unbind('keydown', addAnimateChangeKey);
		});
		function addAnimateChangeKey(e) {
			if([37, 39].indexOf(e.keyCode) > -1) {
				$('.pswp__container').addClass('pswp__container_transition');
			}
		}
		// Remove custom transition class on anything that can be a dragging start event
		function removeAnimateChange(e) {
			$('.pswp__container').removeClass('pswp__container_transition');
		}
		$('body').on('mousedown pointerdown touchstart', '.pswp__scroll-wrap', removeAnimateChange);
		ps.listen('destroy', function(){
			$('body').off('mousedown pointerdown touchstart', '.pswp__scroll-wrap', removeAnimateChange);
		});
		// Add custom transition class on button left/right clicks - actually upon mouse/pointer up
		function addAnimateChangeButton(e) {
			$('.pswp__container').addClass('pswp__container_transition');
		}
		$('body').on('mouseup pointerup touchend', '.pswp__button--arrow--left, .pswp__button--arrow--right', addAnimateChangeButton);
		ps.listen('destroy', function(){
			$('body').off('mouseup pointerup touchend', '.pswp__button--arrow--left, .pswp__button--arrow--right', addAnimateChangeButton);
		});

	}

	// Collect gallery and picture data
	var gdata = [], g = 0;
	$('article, div.hentry').each(function(){ // treat all galleries within one post as one item (you can flip through all of them at once)
		gdata[g] = [];
		var p = 0;
		// all gallery links vs. $(this).find('a[href$=".jpg"], a[href$=".JPG"], a[href$=".gif"], a[href$=".GIF"], a[href$=".png"], a[href$=".PNG"]') for all linked images per article
		$(this).find('.gallery .gallery-item').each(function(){
			var thumb = $(this).find('img');
			gdata[g][p] = {
				src: $(this).attr('href'),
				w: $(this).data('width'),
				h: $(this).data('height'),
				title: $(this).data('title'),
				thumb: thumb[0],
				msrc: thumb.attr('src') // use thumbnail (if same side ratio) before larger version is loaded
			};
			$(this).data('gallery', g + 1);
			$(this).data('picture', p + 1);
			$(this).on('click', function(e){
				e.preventDefault();
				// Do not "blowup" thumbnail as preview image (while loading big version), if it has different aspect ratio than the original
				var noPreview = ($(this).closest('.gallery').hasClass('gallery-size-thumbnail')) ? true : false;
				openGallery($(this), noPreview);
			});
			p++;
		});
		g++;
	});

});
