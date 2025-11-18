<?php
/**
 * arZoomPan – viewerSuccess.php
 * Clean standalone viewer for testing zoom/pan on any digital object
 */

// ========================================================
// 1. LOAD ID SAFELY
// ========================================================
$id = $sf_request->getParameter('id');

if (!$id) {
    echo "<pre style='background:#400;color:#fff;padding:10px;'>ERROR: No ID received by viewerSuccess.php</pre>";
    return;
}

//echo "<pre style='background:#033;color:#0f0;padding:10px;'>✔ ID RECEIVED: {$id}</pre>";

// ========================================================
// 2. LOAD DIGITAL OBJECT
// ========================================================
$digitalObject = QubitDigitalObject::getById($id);

if (!$digitalObject) {
    echo "<pre style='background:#400;color:#fff;padding:10px;'>ERROR: DigitalObject not found for ID {$id}</pre>";
    return;
}

//echo "<pre style='background:#020;color:#0f0;padding:10px;'>DIGITAL OBJECT LOADED:
//ID: {$digitalObject->id}
//Name: {$digitalObject->name}
//Path: {$digitalObject->path}
//Mime: {$digitalObject->mimeType}
//</pre>";

// ========================================================
// 3. BUILD PUBLIC URL
// ========================================================
$request = sfContext::getInstance()->getRequest();
$root = $request->getRelativeUrlRoot();

$publicUrl = $root . $digitalObject->path . '/' . $digitalObject->name;

//echo "<pre style='background:#113;color:#fff;padding:10px;'>PUBLIC URL:
//{$publicUrl}
//</pre>";

// ========================================================
// 4. SIMPLE OPENSEADRAGON VIEWER (WORKS FOR JPG/PNG/TIF)
// ========================================================
?>

<h2 style="margin:20px 0;font-family:Arial;color:#333;">Zoom / Pan Viewer Test</h2>

<div id="osd-viewer"
     style="width:100%; height:700px; background:#000; border:2px solid #333;">
</div>

<!-- OpenSeadragon -->
<script src="https://cdn.jsdelivr.net/npm/openseadragon@3.1.0/build/openseadragon/openseadragon.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    console.log("OSD init...");

    // Init viewer
    var viewer = OpenSeadragon({
        id: "osd-viewer",
        prefixUrl: "https://cdn.jsdelivr.net/npm/openseadragon@3.1.0/build/openseadragon/images/",
        tileSources: {
            type: "image",
            url: "<?php echo $publicUrl; ?>"  // load like normal image
        },
        showRotationControl: true,
        gestureSettingsMouse: {
            scrollToZoom: true,
            dblClickToZoom: true,
            clickToZoom: false,
            dragToPan: true,
            rotate: true
        },
        gestureSettingsTouch: {
            pinchToZoom: true,
            pinchRotate: true
        }
    });

});
</script>

<div style="margin-top:20px;font-size:13px;color:#666;">
    Zoom: Mouse wheel • Pan: Drag • Rotate: Shift + Drag • Touch: Pinch/Rotate
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    var iiifUrl = "<?php echo $request->getUriPrefix() . $root ?>/iiif/2/<?php echo $iiifIdentifier ?>/info.json";

    console.log("DEBUG: IIIF URL = " + iiifUrl);

    OpenSeadragon({
        id: "<?php echo $viewerId ?>",
        prefixUrl: "https://cdn.jsdelivr.net/npm/openseadragon@3.1.0/build/openseadragon/images/",
        tileSources: iiifUrl,
        showNavigator: true,
        useCanvas: true,
        debugMode: true
    });
});
</script>
