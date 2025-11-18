<?php
/**
 * arZoomPan Viewer Template
 * 
 * Template for displaying documents with zoom, pan, and rotate functionality
 * This replaces the default digital object viewer
 * 
 * @package    arZoomPanPlugin
 * @subpackage templates
 */

// Get the digital object
$digitalObject = isset($resource) ? $resource : $digitalObject;
if (!$digitalObject || !$digitalObject->id): ?>
  <div class="alert alert-info">
    <i class="fas fa-info-circle"></i> No digital object available for viewing.
  </div>
<?php return; endif; 

// Determine document type
$mimeType = $digitalObject->getMimeType();
$isImage = strpos($mimeType, 'image/') === 0;
$isPdf = $mimeType === 'application/pdf';
$isText = in_array($mimeType, array('text/plain', 'text/html', 'application/msword', 
          'application/vnd.openxmlformats-officedocument.wordprocessingml.document'));

$viewerId = 'zoom-pan-viewer-' . $digitalObject->id;
?>

<div class="zoom-pan-container" id="<?php echo $viewerId ?>-container">
  
  <!-- Viewer Toolbar -->
  <div class="zoom-pan-toolbar">
    <div class="btn-group" role="group">
      <button type="button" class="btn btn-sm btn-secondary" id="<?php echo $viewerId ?>-home" title="Reset View">
        <i class="fas fa-home"></i>
      </button>
      <button type="button" class="btn btn-sm btn-secondary" id="<?php echo $viewerId ?>-zoom-in" title="Zoom In">
        <i class="fas fa-search-plus"></i>
      </button>
      <button type="button" class="btn btn-sm btn-secondary" id="<?php echo $viewerId ?>-zoom-out" title="Zoom Out">
        <i class="fas fa-search-minus"></i>
      </button>
      <button type="button" class="btn btn-sm btn-secondary" id="<?php echo $viewerId ?>-rotate-left" title="Rotate Left">
        <i class="fas fa-undo"></i>
      </button>
      <button type="button" class="btn btn-sm btn-secondary" id="<?php echo $viewerId ?>-rotate-right" title="Rotate Right">
        <i class="fas fa-redo"></i>
      </button>
      <button type="button" class="btn btn-sm btn-secondary" id="<?php echo $viewerId ?>-fullscreen" title="Fullscreen">
        <i class="fas fa-expand"></i>
      </button>
    </div>
    
    <?php if ($isPdf): ?>
    <!-- PDF Navigation -->
    <div class="btn-group ml-3" role="group">
      <button type="button" class="btn btn-sm btn-secondary" id="<?php echo $viewerId ?>-prev-page" title="Previous Page">
        <i class="fas fa-chevron-left"></i>
      </button>
      <span class="page-indicator mx-2">
        Page <span id="<?php echo $viewerId ?>-current-page">1</span> of <span id="<?php echo $viewerId ?>-total-pages">?</span>
      </span>
      <button type="button" class="btn btn-sm btn-secondary" id="<?php echo $viewerId ?>-next-page" title="Next Page">
        <i class="fas fa-chevron-right"></i>
      </button>
    </div>
    <?php endif; ?>
    
    <!-- Download Button -->
    <button type="button" class="btn btn-sm btn-primary ml-3" id="<?php echo $viewerId ?>-download" title="Download">
      <i class="fas fa-download"></i> Download
    </button>
  </div>
  
  <!-- Main Viewer -->
  <div id="<?php echo $viewerId ?>" class="zoom-pan-viewer" style="width: 100%; height: 600px; background-color: #000;">
    <noscript>
      <div class="alert alert-warning">
        JavaScript is required for the document viewer. Please enable JavaScript to use zoom and pan features.
      </div>
    </noscript>
  </div>
  
  <!-- Minimap Navigator -->
  <div id="<?php echo $viewerId ?>-navigator" class="zoom-pan-navigator"></div>
  
  <!-- Status Bar -->
  <div class="zoom-pan-status">
    <span id="<?php echo $viewerId ?>-zoom-level">Zoom: 100%</span>
    <span class="ml-3" id="<?php echo $viewerId ?>-rotation">Rotation: 0°</span>
    <?php if ($isImage): ?>
    <span class="ml-3" id="<?php echo $viewerId ?>-dimensions"></span>
    <?php endif; ?>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  
  // Initialize viewer based on document type
  <?php if ($isImage): ?>
    // Initialize OpenSeadragon for images
    var viewer = OpenSeadragon({
      id: "<?php echo $viewerId ?>",
      prefixUrl: "https://cdn.jsdelivr.net/npm/openseadragon@4.1.0/build/openseadragon/images/",
      
      // Use custom tile source
      tileSources: {
        type: 'legacy-image-pyramid',
        getTileUrl: function(level, x, y) {
          return '/zoompan/tile/<?php echo $digitalObject->id ?>/' + level + '/' + x + '/' + y + '.jpg';
        },
        // These will be loaded dynamically
        width: 1,
        height: 1,
        tileSize: 256,
        tileOverlap: 0,
        minLevel: 0,
        maxLevel: 10
      },
      
      // Viewer options
      showNavigator: true,
      navigatorId: "<?php echo $viewerId ?>-navigator",
      navigatorPosition: 'ABSOLUTE',
      navigatorTop: 10,
      navigatorRight: 10,
      navigatorHeight: 120,
      navigatorWidth: 150,
      
      // Controls
      showNavigationControl: false, // We're using custom controls
      
      // Behavior
      animationTime: 0.5,
      blendTime: 0.1,
      constrainDuringPan: true,
      maxZoomPixelRatio: 5,
      minZoomLevel: 0.5,
      visibilityRatio: 0.5,
      
      // Gesture settings
      gestureSettingsMouse: {
        scrollToZoom: true,
        clickToZoom: true,
        dblClickToZoom: true,
        pinchToZoom: true
      }
    });
    
    // Load document info
    fetch('/zoompan/info/<?php echo $digitalObject->id ?>')
      .then(response => response.json())
      .then(info => {
        // Update tile source with actual dimensions
        viewer.addTiledImage({
          tileSource: {
            type: 'legacy-image-pyramid',
            getTileUrl: function(level, x, y) {
              return '/zoompan/tile/<?php echo $digitalObject->id ?>/' + level + '/' + x + '/' + y + '.jpg';
            },
            width: info.width,
            height: info.height,
            tileSize: info.tileSize,
            tileOverlap: 0,
            minLevel: 0,
            maxLevel: info.maxZoom
          }
        });
        
        // Update dimensions display
        document.getElementById('<?php echo $viewerId ?>-dimensions').textContent = 
          'Size: ' + info.width + ' × ' + info.height + ' px';
      });
    
    // Custom controls
    document.getElementById('<?php echo $viewerId ?>-home').onclick = function() {
      viewer.viewport.goHome();
    };
    
    document.getElementById('<?php echo $viewerId ?>-zoom-in').onclick = function() {
      viewer.viewport.zoomBy(1.5);
    };
    
    document.getElementById('<?php echo $viewerId ?>-zoom-out').onclick = function() {
      viewer.viewport.zoomBy(0.75);
    };
    
    var rotation = 0;
    document.getElementById('<?php echo $viewerId ?>-rotate-left').onclick = function() {
      rotation -= 90;
      viewer.viewport.setRotation(rotation);
      document.getElementById('<?php echo $viewerId ?>-rotation').textContent = 'Rotation: ' + rotation + '°';
    };
    
    document.getElementById('<?php echo $viewerId ?>-rotate-right').onclick = function() {
      rotation += 90;
      viewer.viewport.setRotation(rotation);
      document.getElementById('<?php echo $viewerId ?>-rotation').textContent = 'Rotation: ' + rotation + '°';
    };
    
    document.getElementById('<?php echo $viewerId ?>-fullscreen').onclick = function() {
      viewer.setFullScreen(!viewer.isFullPage());
    };
    
    // Update zoom level display
    viewer.addHandler('zoom', function(event) {
      var zoom = Math.round(viewer.viewport.getZoom(true) * 100);
      document.getElementById('<?php echo $viewerId ?>-zoom-level').textContent = 'Zoom: ' + zoom + '%';
    });
    
  <?php elseif ($isPdf): ?>
    // Initialize PDF viewer
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    
    var pdfDoc = null,
        pageNum = 1,
        pageRendering = false,
        pageNumPending = null,
        canvas = document.createElement('canvas'),
        ctx = canvas.getContext('2d'),
        viewer = null;
    
    document.getElementById('<?php echo $viewerId ?>').appendChild(canvas);
    
    // Load PDF info
    fetch('/zoompan/info/<?php echo $digitalObject->id ?>')
      .then(response => response.json())
      .then(info => {
        document.getElementById('<?php echo $viewerId ?>-total-pages').textContent = info.pages;
        
        // Initialize OpenSeadragon with canvas
        viewer = OpenSeadragon({
          id: "<?php echo $viewerId ?>",
          prefixUrl: "https://cdn.jsdelivr.net/npm/openseadragon@4.1.0/build/openseadragon/images/",
          tileSources: {
            type: 'image',
            url: '/zoompan/pdf/<?php echo $digitalObject->id ?>/1'
          },
          showNavigator: true,
          navigatorId: "<?php echo $viewerId ?>-navigator"
        });
        
        loadPage(1);
      });
    
    function loadPage(num) {
      pageNum = num;
      document.getElementById('<?php echo $viewerId ?>-current-page').textContent = num;
      
      // Update viewer with new page image
      if (viewer) {
        viewer.world.removeAll();
        viewer.addSimpleImage({
          url: '/zoompan/pdf/<?php echo $digitalObject->id ?>/' + num
        });
      }
    }
    
    // Page navigation
    document.getElementById('<?php echo $viewerId ?>-prev-page').onclick = function() {
      if (pageNum > 1) {
        loadPage(pageNum - 1);
      }
    };
    
    document.getElementById('<?php echo $viewerId ?>-next-page').onclick = function() {
      var totalPages = parseInt(document.getElementById('<?php echo $viewerId ?>-total-pages').textContent);
      if (pageNum < totalPages) {
        loadPage(pageNum + 1);
      }
    };
    
  <?php elseif ($isText): ?>
    // Initialize text document viewer with zoom capabilities
    var iframe = document.createElement('iframe');
    iframe.src = '/zoompan/text/<?php echo $digitalObject->id ?>';
    iframe.style.width = '100%';
    iframe.style.height = '100%';
    iframe.style.border = 'none';
    iframe.style.backgroundColor = 'white';
    
    document.getElementById('<?php echo $viewerId ?>').appendChild(iframe);
    
    var currentZoom = 100;
    
    // Zoom controls for text
    document.getElementById('<?php echo $viewerId ?>-zoom-in').onclick = function() {
      currentZoom = Math.min(currentZoom + 25, 300);
      iframe.contentWindow.document.body.style.zoom = currentZoom + '%';
      document.getElementById('<?php echo $viewerId ?>-zoom-level').textContent = 'Zoom: ' + currentZoom + '%';
    };
    
    document.getElementById('<?php echo $viewerId ?>-zoom-out').onclick = function() {
      currentZoom = Math.max(currentZoom - 25, 50);
      iframe.contentWindow.document.body.style.zoom = currentZoom + '%';
      document.getElementById('<?php echo $viewerId ?>-zoom-level').textContent = 'Zoom: ' + currentZoom + '%';
    };
    
    document.getElementById('<?php echo $viewerId ?>-home').onclick = function() {
      currentZoom = 100;
      iframe.contentWindow.document.body.style.zoom = '100%';
      document.getElementById('<?php echo $viewerId ?>-zoom-level').textContent = 'Zoom: 100%';
    };
    
    // Hide rotation controls for text documents
    document.getElementById('<?php echo $viewerId ?>-rotate-left').style.display = 'none';
    document.getElementById('<?php echo $viewerId ?>-rotate-right').style.display = 'none';
    document.getElementById('<?php echo $viewerId ?>-rotation').style.display = 'none';
    
  <?php endif; ?>
  
  // Download button
  document.getElementById('<?php echo $viewerId ?>-download').onclick = function() {
    window.location.href = '<?php echo url_for(array($digitalObject, 'module' => 'digitalobject', 'action' => 'download')) ?>';
  };
  
});
</script>

<!-- Viewer help text -->
<div class="viewer-help text-muted small mt-2">
  <strong>Controls:</strong> 
  <?php if ($isImage || $isPdf): ?>
    Mouse wheel to zoom, drag to pan, double-click to zoom in, shift+double-click to zoom out
  <?php else: ?>
    Use toolbar buttons to zoom in/out, Ctrl+F to search within document
  <?php endif; ?>
</div>
