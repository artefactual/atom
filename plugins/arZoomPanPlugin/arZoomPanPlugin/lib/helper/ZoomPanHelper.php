<?php

/**
 * arZoomPan Helper Functions
 * 
 * Helper functions for easy integration of zoom/pan viewer in templates
 * 
 * @package    arZoomPanPlugin
 * @subpackage helper
 */

/**
 * Get zoom/pan viewer HTML
 * 
 * @param QubitDigitalObject $digitalObject The digital object to display
 * @param array $options Viewer options
 * @return string HTML for the viewer
 */
function get_zoom_pan_viewer($digitalObject, $options = array())
{
    if (!$digitalObject || !$digitalObject->id) {
        return '<div class="alert alert-info">No digital object available for viewing.</div>';
    }

    // Normalize base URL for ALL AtoM instances
    $baseUrl = rtrim(QubitSetting::getByName('siteBaseUrl'), '/');
    $installFolder = basename($baseUrl);
    $baseUrl = preg_replace('#(' . preg_quote($installFolder, '#') . '/)+#', $installFolder . '/', $baseUrl);
    $baseUrl = rtrim($baseUrl, '/');

    // Build zoom URL (works in ALL AtoM instances)
    $imageName = urlencode($digitalObject->name);
    $imagePath = urlencode($digitalObject->path);

    $zoomUrl = $baseUrl . "/plugins/arZoomPan/zoom.php?imageName={$imageName}&imagePath={$imagePath}";

    // Viewer size & ID
    $elementId = "zoom-pan-viewer-" . $digitalObject->id;
    $height = isset($options['height']) ? $options['height'] : '600px';

    // Simple iframe viewer
    return sprintf(
        '<iframe id="%s" src="%s" style="width:100%%; height:%s; border:0; background:#000;"></iframe>',
        htmlspecialchars($elementId),
        htmlspecialchars($zoomUrl),
        htmlspecialchars($height)
    );
}

/**
 * Detect viewer type based on digital object mime type
 * 
 * @param QubitDigitalObject $digitalObject
 * @return string Viewer type (image, pdf, or text)
 */
function detect_viewer_type($digitalObject)
{
  $mimeType = $digitalObject->getMimeType();
  
  // Image types
  if (strpos($mimeType, 'image/') === 0)
  {
    return 'image';
  }
  
  // PDF
  if ($mimeType === 'application/pdf')
  {
    return 'pdf';
  }
  
  // Text documents
  $textTypes = array(
    'text/plain',
    'text/html',
    'text/xml',
    'application/xml',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.oasis.opendocument.text'
  );
  
  if (in_array($mimeType, $textTypes))
  {
    return 'text';
  }
  
  // Default to image viewer for unknown types
  return 'image';
}

/**
 * Check if digital object is supported by zoom/pan viewer
 * 
 * @param QubitDigitalObject $digitalObject
 * @return boolean
 */
function is_zoom_pan_supported($digitalObject)
{
  if (!$digitalObject)
  {
    return false;
  }
  
  $settings = sfConfig::get('app_zoom_pan_settings', array());
  $supportedFormats = isset($settings['supported_formats']) ? $settings['supported_formats'] : array();
  
  $extension = strtolower(pathinfo($digitalObject->getName(), PATHINFO_EXTENSION));
  
  return in_array($extension, $supportedFormats);
}

/**
 * Get zoom/pan viewer thumbnail
 * 
 * @param QubitDigitalObject $digitalObject
 * @param array $options
 * @return string HTML for thumbnail with link to viewer
 */
function get_zoom_pan_thumbnail($digitalObject, $options = array())
{
  if (!$digitalObject || !$digitalObject->id)
  {
    return '';
  }
  
  $defaults = array(
    'size' => 'thumbnail',
    'class' => 'zoom-pan-thumbnail',
    'link' => true,
    'alt' => $digitalObject->getName()
  );
  
  $options = array_merge($defaults, $options);
  
  // Get thumbnail URL
  $thumbnailUrl = $digitalObject->getThumbnailUrl($options['size']);
  
  // Build image tag
  $img = sprintf(
    '<img src="%s" alt="%s" class="%s">',
    htmlspecialchars($thumbnailUrl),
    htmlspecialchars($options['alt']),
    htmlspecialchars($options['class'])
  );
  
  // Add link if requested
  if ($options['link'])
  {
    $viewerUrl = url_for(array($digitalObject, 'module' => 'digitalobject'));
    $img = sprintf(
      '<a href="%s" class="zoom-pan-thumbnail-link">%s</a>',
      htmlspecialchars($viewerUrl),
      $img
    );
  }
  
  return $img;
}

/**
 * Include zoom/pan viewer assets
 * 
 * Call this in your template's head section if auto-loading doesn't work
 */
function include_zoom_pan_assets()
{
  $html = '';
  
  // OpenSeadragon
  $html .= '<script src="https://cdn.jsdelivr.net/npm/openseadragon@4.1.0/build/openseadragon/openseadragon.min.js"></script>' . "\n";
  
  // PDF.js
  $html .= '<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>' . "\n";
  
  // Plugin CSS
  $html .= '<link rel="stylesheet" href="/plugins/arZoomPanPlugin/css/zoom-pan.css">' . "\n";
  
  // Plugin JS
  $html .= '<script src="/plugins/arZoomPanPlugin/js/zoom-pan.js"></script>' . "\n";
  
  return $html;
}

/**
 * Get viewer configuration as JavaScript
 * 
 * @param QubitDigitalObject $digitalObject
 * @param array $options
 * @return string JavaScript configuration
 */
function get_zoom_pan_config($digitalObject, $options = array())
{
  $config = array(
    'digitalObjectId' => $digitalObject->id,
    'viewerType' => detect_viewer_type($digitalObject),
    'tileSize' => 256,
    'maxZoom' => 10,
    'minZoom' => 0.5,
    'enableRotation' => true,
    'enableFullscreen' => true,
    'enableDownload' => true,
    'showNavigator' => true
  );
  
  $config = array_merge($config, $options);
  
  return json_encode($config, JSON_PRETTY_PRINT);
}

/**
 * Generate inline viewer initialization script
 * 
 * @param string $elementId
 * @param QubitDigitalObject $digitalObject
 * @param array $options
 * @return string JavaScript code
 */
function get_zoom_pan_init_script($elementId, $digitalObject, $options = array())
{
  $config = get_zoom_pan_config($digitalObject, $options);
  
  $script = <<<SCRIPT
<script>
document.addEventListener('DOMContentLoaded', function() {
  var config = {$config};
  $('#{$elementId}').zoomPanViewer(config);
});
</script>
SCRIPT;
  
  return $script;
}
