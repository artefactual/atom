/**
 * arZoomPan Plugin JavaScript
 * 
 * Enhanced viewer functionality for zoom, pan, and rotate operations
 */

(function($) {
  'use strict';
  
  /**
   * ZoomPan Viewer Class
   */
  var ZoomPanViewer = function(element, options) {
    this.element = element;
    this.$element = $(element);
    this.options = $.extend({}, ZoomPanViewer.DEFAULTS, options);
    this.init();
  };
  
  ZoomPanViewer.VERSION = '1.0.0';
  
  ZoomPanViewer.DEFAULTS = {
    digitalObjectId: null,
    viewerType: 'image', // image, pdf, text
    tileSize: 256,
    maxZoom: 10,
    minZoom: 0.5,
    zoomSpeed: 1.5,
    enableRotation: true,
    enableFullscreen: true,
    enableDownload: true,
    showNavigator: true,
    navigatorPosition: 'TOP_RIGHT',
    toolbar: true,
    statusBar: true
  };
  
  ZoomPanViewer.prototype.init = function() {
    var self = this;
    
    // Create viewer structure if not exists
    if (!this.$element.find('.zoom-pan-viewer').length) {
      this.createViewerStructure();
    }
    
    // Initialize based on viewer type
    switch(this.options.viewerType) {
      case 'image':
        this.initImageViewer();
        break;
      case 'pdf':
        this.initPdfViewer();
        break;
      case 'text':
        this.initTextViewer();
        break;
    }
    
    // Setup event handlers
    this.bindEvents();
    
    // Load document information
    this.loadDocumentInfo();
  };
  
  /**
   * Create viewer HTML structure
   */
  ZoomPanViewer.prototype.createViewerStructure = function() {
    var html = '';
    
    // Toolbar
    if (this.options.toolbar) {
      html += '<div class="zoom-pan-toolbar">';
      html += '  <div class="btn-group">';
      html += '    <button type="button" class="btn btn-sm btn-secondary zoom-pan-home" title="Reset View">';
      html += '      <i class="fas fa-home"></i>';
      html += '    </button>';
      html += '    <button type="button" class="btn btn-sm btn-secondary zoom-pan-zoom-in" title="Zoom In">';
      html += '      <i class="fas fa-search-plus"></i>';
      html += '    </button>';
      html += '    <button type="button" class="btn btn-sm btn-secondary zoom-pan-zoom-out" title="Zoom Out">';
      html += '      <i class="fas fa-search-minus"></i>';
      html += '    </button>';
      
      if (this.options.enableRotation) {
        html += '    <button type="button" class="btn btn-sm btn-secondary zoom-pan-rotate-left" title="Rotate Left">';
        html += '      <i class="fas fa-undo"></i>';
        html += '    </button>';
        html += '    <button type="button" class="btn btn-sm btn-secondary zoom-pan-rotate-right" title="Rotate Right">';
        html += '      <i class="fas fa-redo"></i>';
        html += '    </button>';
      }
      
      if (this.options.enableFullscreen) {
        html += '    <button type="button" class="btn btn-sm btn-secondary zoom-pan-fullscreen" title="Fullscreen">';
        html += '      <i class="fas fa-expand"></i>';
        html += '    </button>';
      }
      
      html += '  </div>';
      
      // PDF navigation
      if (this.options.viewerType === 'pdf') {
        html += '  <div class="btn-group ml-3">';
        html += '    <button type="button" class="btn btn-sm btn-secondary zoom-pan-prev-page" title="Previous Page">';
        html += '      <i class="fas fa-chevron-left"></i>';
        html += '    </button>';
        html += '    <span class="page-indicator mx-2">';
        html += '      Page <span class="zoom-pan-current-page">1</span> of <span class="zoom-pan-total-pages">?</span>';
        html += '    </span>';
        html += '    <button type="button" class="btn btn-sm btn-secondary zoom-pan-next-page" title="Next Page">';
        html += '      <i class="fas fa-chevron-right"></i>';
        html += '    </button>';
        html += '  </div>';
      }
      
      if (this.options.enableDownload) {
        html += '  <button type="button" class="btn btn-sm btn-primary ml-3 zoom-pan-download" title="Download">';
        html += '    <i class="fas fa-download"></i> Download';
        html += '  </button>';
      }
      
      html += '</div>';
    }
    
    // Main viewer
    html += '<div class="zoom-pan-viewer" style="height: 600px;"></div>';
    
    // Navigator
    if (this.options.showNavigator) {
      html += '<div class="zoom-pan-navigator"></div>';
    }
    
    // Status bar
    if (this.options.statusBar) {
      html += '<div class="zoom-pan-status">';
      html += '  <span class="zoom-pan-zoom-level">Zoom: 100%</span>';
      html += '  <span class="zoom-pan-rotation ml-3">Rotation: 0°</span>';
      html += '  <span class="zoom-pan-dimensions ml-3"></span>';
      html += '</div>';
    }
    
    this.$element.html(html);
  };
  
  /**
   * Initialize image viewer (OpenSeadragon)
   */
  ZoomPanViewer.prototype.initImageViewer = function() {
    var self = this;
    
    // Initialize OpenSeadragon
    this.viewer = OpenSeadragon({
      element: this.$element.find('.zoom-pan-viewer')[0],
      prefixUrl: 'https://cdn.jsdelivr.net/npm/openseadragon@4.1.0/build/openseadragon/images/',
      
      showNavigationControl: false,
      
      animationTime: 0.5,
      blendTime: 0.1,
      constrainDuringPan: true,
      maxZoomPixelRatio: this.options.maxZoom,
      minZoomLevel: this.options.minZoom,
      
      gestureSettingsMouse: {
        scrollToZoom: true,
        clickToZoom: true,
        dblClickToZoom: true,
        pinchToZoom: true
      }
    });
    
    // Show navigator if enabled
    if (this.options.showNavigator) {
      this.viewer.navigator = new OpenSeadragon.Navigator({
        element: this.$element.find('.zoom-pan-navigator')[0],
        viewer: this.viewer
      });
    }
    
    // Update status on zoom
    this.viewer.addHandler('zoom', function(event) {
      var zoom = Math.round(self.viewer.viewport.getZoom(true) * 100);
      self.$element.find('.zoom-pan-zoom-level').text('Zoom: ' + zoom + '%');
    });
  };
  
  /**
   * Initialize PDF viewer
   */
  ZoomPanViewer.prototype.initPdfViewer = function() {
    var self = this;
    
    this.currentPage = 1;
    this.totalPages = 0;
    this.pageCache = {};
    
    // Create canvas for PDF rendering
    var canvas = document.createElement('canvas');
    canvas.className = 'pdf-canvas';
    this.$element.find('.zoom-pan-viewer').append(canvas);
    
    // Initialize zoom/pan for canvas
    this.initCanvasZoomPan(canvas);
  };
  
  /**
   * Initialize text viewer
   */
  ZoomPanViewer.prototype.initTextViewer = function() {
    var self = this;
    
    // Create iframe for text content
    var iframe = document.createElement('iframe');
    iframe.className = 'text-iframe';
    iframe.style.width = '100%';
    iframe.style.height = '100%';
    iframe.style.border = 'none';
    
    this.$element.find('.zoom-pan-viewer').append(iframe);
    
    // Load text content
    iframe.src = '/zoompan/text/' + this.options.digitalObjectId;
    
    this.textZoom = 100;
    
    // Wait for iframe to load
    iframe.onload = function() {
      self.textDocument = iframe.contentDocument || iframe.contentWindow.document;
    };
  };
  
  /**
   * Initialize canvas zoom/pan functionality
   */
  ZoomPanViewer.prototype.initCanvasZoomPan = function(canvas) {
    var self = this;
    var ctx = canvas.getContext('2d');
    
    this.scale = 1;
    this.rotation = 0;
    this.offsetX = 0;
    this.offsetY = 0;
    
    var isDragging = false;
    var dragStartX, dragStartY;
    
    // Mouse wheel zoom
    $(canvas).on('wheel', function(e) {
      e.preventDefault();
      
      var delta = e.originalEvent.deltaY > 0 ? 0.9 : 1.1;
      self.scale *= delta;
      self.scale = Math.max(self.options.minZoom, Math.min(self.options.maxZoom, self.scale));
      
      self.renderCanvas();
      self.updateZoomLevel(self.scale * 100);
    });
    
    // Mouse drag pan
    $(canvas).on('mousedown', function(e) {
      isDragging = true;
      dragStartX = e.clientX - self.offsetX;
      dragStartY = e.clientY - self.offsetY;
      canvas.style.cursor = 'grabbing';
    });
    
    $(document).on('mousemove', function(e) {
      if (isDragging) {
        self.offsetX = e.clientX - dragStartX;
        self.offsetY = e.clientY - dragStartY;
        self.renderCanvas();
      }
    });
    
    $(document).on('mouseup', function() {
      isDragging = false;
      canvas.style.cursor = 'grab';
    });
  };
  
  /**
   * Bind event handlers
   */
  ZoomPanViewer.prototype.bindEvents = function() {
    var self = this;
    
    // Home button
    this.$element.find('.zoom-pan-home').on('click', function() {
      self.resetView();
    });
    
    // Zoom buttons
    this.$element.find('.zoom-pan-zoom-in').on('click', function() {
      self.zoomIn();
    });
    
    this.$element.find('.zoom-pan-zoom-out').on('click', function() {
      self.zoomOut();
    });
    
    // Rotation buttons
    this.$element.find('.zoom-pan-rotate-left').on('click', function() {
      self.rotateLeft();
    });
    
    this.$element.find('.zoom-pan-rotate-right').on('click', function() {
      self.rotateRight();
    });
    
    // Fullscreen button
    this.$element.find('.zoom-pan-fullscreen').on('click', function() {
      self.toggleFullscreen();
    });
    
    // Download button
    this.$element.find('.zoom-pan-download').on('click', function() {
      self.download();
    });
    
    // PDF navigation
    if (this.options.viewerType === 'pdf') {
      this.$element.find('.zoom-pan-prev-page').on('click', function() {
        self.previousPage();
      });
      
      this.$element.find('.zoom-pan-next-page').on('click', function() {
        self.nextPage();
      });
    }
    
    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
      if (!self.$element.is(':visible')) return;
      
      switch(e.key) {
        case '+':
        case '=':
          self.zoomIn();
          break;
        case '-':
        case '_':
          self.zoomOut();
          break;
        case '0':
          self.resetView();
          break;
        case 'ArrowLeft':
          if (e.ctrlKey) self.rotateLeft();
          else if (self.options.viewerType === 'pdf') self.previousPage();
          break;
        case 'ArrowRight':
          if (e.ctrlKey) self.rotateRight();
          else if (self.options.viewerType === 'pdf') self.nextPage();
          break;
        case 'f':
          if (e.ctrlKey) {
            e.preventDefault();
            self.toggleFullscreen();
          }
          break;
      }
    });
  };
  
  /**
   * Load document information
   */
  ZoomPanViewer.prototype.loadDocumentInfo = function() {
    var self = this;
    
    $.ajax({
      url: '/zoompan/info/' + this.options.digitalObjectId,
      type: 'GET',
      dataType: 'json',
      success: function(info) {
        self.documentInfo = info;
        
        // Update viewer based on document type
        if (info.type === 'image') {
          // Add tile source to OpenSeadragon
          self.viewer.addTiledImage({
            tileSource: {
              type: 'legacy-image-pyramid',
              getTileUrl: function(level, x, y) {
                return '/zoompan/tile/' + self.options.digitalObjectId + '/' + level + '/' + x + '/' + y + '.jpg';
              },
              width: info.width,
              height: info.height,
              tileSize: info.tileSize || 256,
              tileOverlap: 0,
              minLevel: 0,
              maxLevel: info.maxZoom || 10
            }
          });
          
          // Update dimensions display
          self.$element.find('.zoom-pan-dimensions').text('Size: ' + info.width + ' × ' + info.height + ' px');
        }
        else if (info.type === 'pdf') {
          self.totalPages = info.pages;
          self.$element.find('.zoom-pan-total-pages').text(info.pages);
          self.loadPdfPage(1);
        }
      },
      error: function() {
        self.showError('Failed to load document information');
      }
    });
  };
  
  /**
   * Load PDF page
   */
  ZoomPanViewer.prototype.loadPdfPage = function(pageNum) {
    var self = this;
    
    if (this.pageCache[pageNum]) {
      this.displayPdfPage(this.pageCache[pageNum]);
      return;
    }
    
    var img = new Image();
    img.onload = function() {
      self.pageCache[pageNum] = img;
      self.displayPdfPage(img);
    };
    img.src = '/zoompan/pdf/' + this.options.digitalObjectId + '/' + pageNum;
    
    this.currentPage = pageNum;
    this.$element.find('.zoom-pan-current-page').text(pageNum);
  };
  
  /**
   * Display PDF page on canvas
   */
  ZoomPanViewer.prototype.displayPdfPage = function(img) {
    var canvas = this.$element.find('.pdf-canvas')[0];
    var ctx = canvas.getContext('2d');
    
    canvas.width = img.width;
    canvas.height = img.height;
    
    this.currentImage = img;
    this.renderCanvas();
  };
  
  /**
   * Render canvas with transformations
   */
  ZoomPanViewer.prototype.renderCanvas = function() {
    if (!this.currentImage) return;
    
    var canvas = this.$element.find('.pdf-canvas')[0];
    var ctx = canvas.getContext('2d');
    
    // Clear canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    // Apply transformations
    ctx.save();
    ctx.translate(canvas.width / 2, canvas.height / 2);
    ctx.rotate(this.rotation * Math.PI / 180);
    ctx.scale(this.scale, this.scale);
    ctx.translate(-canvas.width / 2 + this.offsetX, -canvas.height / 2 + this.offsetY);
    
    // Draw image
    ctx.drawImage(this.currentImage, 0, 0);
    
    ctx.restore();
  };
  
  /**
   * Viewer control methods
   */
  ZoomPanViewer.prototype.zoomIn = function() {
    if (this.options.viewerType === 'image' && this.viewer) {
      this.viewer.viewport.zoomBy(this.options.zoomSpeed);
    }
    else if (this.options.viewerType === 'text') {
      this.textZoom = Math.min(this.textZoom + 25, 300);
      if (this.textDocument) {
        this.textDocument.body.style.zoom = this.textZoom + '%';
        this.updateZoomLevel(this.textZoom);
      }
    }
    else {
      this.scale *= this.options.zoomSpeed;
      this.scale = Math.min(this.scale, this.options.maxZoom);
      this.renderCanvas();
      this.updateZoomLevel(this.scale * 100);
    }
  };
  
  ZoomPanViewer.prototype.zoomOut = function() {
    if (this.options.viewerType === 'image' && this.viewer) {
      this.viewer.viewport.zoomBy(1 / this.options.zoomSpeed);
    }
    else if (this.options.viewerType === 'text') {
      this.textZoom = Math.max(this.textZoom - 25, 50);
      if (this.textDocument) {
        this.textDocument.body.style.zoom = this.textZoom + '%';
        this.updateZoomLevel(this.textZoom);
      }
    }
    else {
      this.scale /= this.options.zoomSpeed;
      this.scale = Math.max(this.scale, this.options.minZoom);
      this.renderCanvas();
      this.updateZoomLevel(this.scale * 100);
    }
  };
  
  ZoomPanViewer.prototype.resetView = function() {
    if (this.options.viewerType === 'image' && this.viewer) {
      this.viewer.viewport.goHome();
    }
    else if (this.options.viewerType === 'text') {
      this.textZoom = 100;
      if (this.textDocument) {
        this.textDocument.body.style.zoom = '100%';
        this.updateZoomLevel(100);
      }
    }
    else {
      this.scale = 1;
      this.rotation = 0;
      this.offsetX = 0;
      this.offsetY = 0;
      this.renderCanvas();
      this.updateZoomLevel(100);
      this.updateRotation(0);
    }
  };
  
  ZoomPanViewer.prototype.rotateLeft = function() {
    if (this.options.viewerType === 'image' && this.viewer) {
      var rotation = this.viewer.viewport.getRotation();
      this.viewer.viewport.setRotation(rotation - 90);
      this.updateRotation(rotation - 90);
    }
    else if (this.options.viewerType !== 'text') {
      this.rotation -= 90;
      this.renderCanvas();
      this.updateRotation(this.rotation);
    }
  };
  
  ZoomPanViewer.prototype.rotateRight = function() {
    if (this.options.viewerType === 'image' && this.viewer) {
      var rotation = this.viewer.viewport.getRotation();
      this.viewer.viewport.setRotation(rotation + 90);
      this.updateRotation(rotation + 90);
    }
    else if (this.options.viewerType !== 'text') {
      this.rotation += 90;
      this.renderCanvas();
      this.updateRotation(this.rotation);
    }
  };
  
  ZoomPanViewer.prototype.previousPage = function() {
    if (this.currentPage > 1) {
      this.loadPdfPage(this.currentPage - 1);
    }
  };
  
  ZoomPanViewer.prototype.nextPage = function() {
    if (this.currentPage < this.totalPages) {
      this.loadPdfPage(this.currentPage + 1);
    }
  };
  
  ZoomPanViewer.prototype.toggleFullscreen = function() {
    this.$element.toggleClass('fullscreen');
    
    if (this.options.viewerType === 'image' && this.viewer) {
      this.viewer.setFullScreen(this.$element.hasClass('fullscreen'));
    }
    
    // Update fullscreen button icon
    var icon = this.$element.find('.zoom-pan-fullscreen i');
    if (this.$element.hasClass('fullscreen')) {
      icon.removeClass('fa-expand').addClass('fa-compress');
    } else {
      icon.removeClass('fa-compress').addClass('fa-expand');
    }
  };
  
  ZoomPanViewer.prototype.download = function() {
    window.location.href = '/digitalobject/download/' + this.options.digitalObjectId;
  };
  
  /**
   * Update UI displays
   */
  ZoomPanViewer.prototype.updateZoomLevel = function(zoom) {
    this.$element.find('.zoom-pan-zoom-level').text('Zoom: ' + Math.round(zoom) + '%');
  };
  
  ZoomPanViewer.prototype.updateRotation = function(rotation) {
    this.$element.find('.zoom-pan-rotation').text('Rotation: ' + rotation + '°');
  };
  
  ZoomPanViewer.prototype.showError = function(message) {
    var error = $('<div class="zoom-pan-error">' + message + '</div>');
    this.$element.find('.zoom-pan-viewer').append(error);
  };
  
  /**
   * jQuery plugin definition
   */
  $.fn.zoomPanViewer = function(option) {
    return this.each(function() {
      var $this = $(this);
      var data = $this.data('zoom.pan.viewer');
      var options = typeof option == 'object' && option;
      
      if (!data) {
        $this.data('zoom.pan.viewer', (data = new ZoomPanViewer(this, options)));
      }
      
      if (typeof option == 'string') {
        data[option]();
      }
    });
  };
  
  $.fn.zoomPanViewer.Constructor = ZoomPanViewer;
  
  // Auto-initialize on document ready
  $(document).ready(function() {
    $('.zoom-pan-container[data-digital-object-id]').each(function() {
      var $this = $(this);
      var options = {
        digitalObjectId: $this.data('digital-object-id'),
        viewerType: $this.data('viewer-type') || 'image'
      };
      
      $this.zoomPanViewer(options);
    });
  });
  
})(jQuery);
