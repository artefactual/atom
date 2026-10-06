/**
 * IIIF Image Carousel for AtoM
 * Rotating IIIF images display using OpenSeadragon
 */

(function($) {
  'use strict';

  var IIIFCarousel = function(element, options) {
    this.element = element;
    this.$element = $(element);
    this.options = $.extend({}, IIIFCarousel.DEFAULTS, options);
    this.currentIndex = 0;
    this.viewer = null;
    this.autoRotateInterval = null;
    this.init();
  };

  IIIFCarousel.DEFAULTS = {
    autoRotate: true,
    rotateInterval: 5000, // 5 seconds
    showNavigation: true,
    showThumbnails: false,
    images: [] // Array of IIIF manifest URLs or info.json URLs
  };

  IIIFCarousel.prototype = {
    init: function() {
      this.buildCarousel();
      this.initializeViewer();
      this.bindEvents();
      
      if (this.options.autoRotate) {
        this.startAutoRotate();
      }
    },

    buildCarousel: function() {
      var html = '<div class="iiif-carousel-wrapper">' +
                   '<div class="iiif-viewer-container" id="iiif-viewer-' + Date.now() + '"></div>';
      
      if (this.options.showNavigation) {
        html += '<div class="iiif-carousel-nav">' +
                  '<button class="iiif-nav-btn iiif-prev" aria-label="Previous image">' +
                    '<span class="icon-left-arrow"></span>' +
                  '</button>' +
                  '<div class="iiif-carousel-counter">' +
                    '<span class="current-slide">1</span> / ' +
                    '<span class="total-slides">' + this.options.images.length + '</span>' +
                  '</div>' +
                  '<button class="iiif-nav-btn iiif-next" aria-label="Next image">' +
                    '<span class="icon-right-arrow"></span>' +
                  '</button>' +
                  '<button class="iiif-play-pause" aria-label="Toggle autoplay">' +
                    '<span class="icon-pause"></span>' +
                  '</button>' +
                '</div>';
      }

      if (this.options.showThumbnails) {
        html += '<div class="iiif-carousel-thumbnails"></div>';
      }

      html += '</div>';

      this.$element.html(html);
      this.$viewerContainer = this.$element.find('.iiif-viewer-container');
      this.viewerId = this.$viewerContainer.attr('id');
    },

    initializeViewer: function() {
      if (typeof OpenSeadragon === 'undefined') {
        console.error('OpenSeadragon library is required for IIIF viewer');
        return;
      }

      this.viewer = OpenSeadragon({
        id: this.viewerId,
        prefixUrl: '/plugins/arDominionPlugin/images/openseadragon/',
        tileSources: this.getTileSource(0),
        showNavigationControl: true,
        navigationControlAnchor: OpenSeadragon.ControlAnchor.TOP_RIGHT,
        showRotationControl: true,
        showHomeControl: true,
        showFullPageControl: true,
        showZoomControl: true,
        sequenceMode: false,
        preserveViewport: false,
        constrainDuringPan: true,
        visibilityRatio: 1.0,
        minZoomImageRatio: 0.8,
        maxZoomPixelRatio: 2
      });

      this.loadThumbnails();
    },

    getTileSource: function(index) {
      if (!this.options.images[index]) {
        return null;
      }

      var imageUrl = this.options.images[index];
      
      // Check if it's a full info.json URL or just the base IIIF URL
      if (imageUrl.indexOf('info.json') === -1) {
        imageUrl = imageUrl + '/info.json';
      }

      return imageUrl;
    },

    loadThumbnails: function() {
      if (!this.options.showThumbnails) {
        return;
      }

      var $thumbnailContainer = this.$element.find('.iiif-carousel-thumbnails');
      var self = this;

      this.options.images.forEach(function(imageUrl, index) {
        var thumbnailUrl = imageUrl.replace('/info.json', '') + '/full/150,/0/default.jpg';
        
        var $thumb = $('<div class="iiif-thumbnail' + (index === 0 ? ' active' : '') + '">' +
                        '<img src="' + thumbnailUrl + '" alt="Thumbnail ' + (index + 1) + '">' +
                      '</div>');
        
        $thumb.on('click', function() {
          self.goToSlide(index);
        });

        $thumbnailContainer.append($thumb);
      });
    },

    bindEvents: function() {
      var self = this;

      this.$element.find('.iiif-prev').on('click', function(e) {
        e.preventDefault();
        self.prev();
      });

      this.$element.find('.iiif-next').on('click', function(e) {
        e.preventDefault();
        self.next();
      });

      this.$element.find('.iiif-play-pause').on('click', function(e) {
        e.preventDefault();
        self.toggleAutoRotate();
      });

      // Keyboard navigation
      $(document).on('keydown', function(e) {
        if (self.$element.is(':visible')) {
          if (e.keyCode === 37) { // Left arrow
            self.prev();
          } else if (e.keyCode === 39) { // Right arrow
            self.next();
          }
        }
      });
    },

    next: function() {
      this.currentIndex = (this.currentIndex + 1) % this.options.images.length;
      this.updateViewer();
    },

    prev: function() {
      this.currentIndex = (this.currentIndex - 1 + this.options.images.length) % this.options.images.length;
      this.updateViewer();
    },

    goToSlide: function(index) {
      this.currentIndex = index;
      this.updateViewer();
    },

    updateViewer: function() {
      if (this.viewer) {
        this.viewer.open(this.getTileSource(this.currentIndex));
        this.updateCounter();
        this.updateThumbnails();
      }
    },

    updateCounter: function() {
      this.$element.find('.current-slide').text(this.currentIndex + 1);
    },

    updateThumbnails: function() {
      if (this.options.showThumbnails) {
        this.$element.find('.iiif-thumbnail').removeClass('active');
        this.$element.find('.iiif-thumbnail').eq(this.currentIndex).addClass('active');
      }
    },

    startAutoRotate: function() {
      var self = this;
      this.autoRotateInterval = setInterval(function() {
        self.next();
      }, this.options.rotateInterval);
      
      this.$element.find('.iiif-play-pause .icon-pause').removeClass('icon-pause').addClass('icon-play');
    },

    stopAutoRotate: function() {
      if (this.autoRotateInterval) {
        clearInterval(this.autoRotateInterval);
        this.autoRotateInterval = null;
      }
      
      this.$element.find('.iiif-play-pause .icon-play').removeClass('icon-play').addClass('icon-pause');
    },

    toggleAutoRotate: function() {
      if (this.autoRotateInterval) {
        this.stopAutoRotate();
      } else {
        this.startAutoRotate();
      }
    },

    destroy: function() {
      this.stopAutoRotate();
      if (this.viewer) {
        this.viewer.destroy();
      }
      this.$element.empty();
    }
  };

  // jQuery plugin definition
  $.fn.iiifCarousel = function(option) {
    return this.each(function() {
      var $this = $(this);
      var data = $this.data('iiif.carousel');
      var options = typeof option === 'object' && option;

      if (!data) {
        $this.data('iiif.carousel', (data = new IIIFCarousel(this, options)));
      }
      
      if (typeof option === 'string') {
        data[option]();
      }
    });
  };

  $.fn.iiifCarousel.Constructor = IIIFCarousel;

})(jQuery);

// Initialize on document ready
jQuery(document).ready(function($) {
  // Auto-initialize any elements with data-iiif-carousel attribute
  $('[data-iiif-carousel]').each(function() {
    var $this = $(this);
    var images = $this.data('iiif-images') || [];
    
    $this.iiifCarousel({
      images: images,
      autoRotate: $this.data('auto-rotate') !== false,
      rotateInterval: $this.data('rotate-interval') || 5000,
      showNavigation: $this.data('show-navigation') !== false,
      showThumbnails: $this.data('show-thumbnails') === true
    });
  });
});
