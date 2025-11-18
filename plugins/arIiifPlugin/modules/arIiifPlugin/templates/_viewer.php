<?php echo "<!-- LOADED DEBUG VIEWER -->"; ?>

<?php
/**
 * Ultra-debug IIIF viewer for AtoM + Cantaloupe
 * -------------------------------------------------
 * Shows every step of the IIIF path, encoding, and
 * viewer lifecycle. Nothing is hidden.
 */

header("X-Debug-Viewer: loaded");

// Input from arIiifPlugin
$raw = isset($iiifUrl) ? trim($iiifUrl) : "";
$viewerId = isset($viewerId) ? $viewerId : "iiif-debug-viewer";
$viewerHeight = isset($viewerHeight) ? $viewerHeight : 600;

// DEBUG: Show raw input
echo "<!-- RAW iiifUrl: $raw -->\n";

// Normalize
$clean = preg_replace('#^/uploads/#', '', $raw);
$clean = trim($clean, "/");

// Extract
$filename = basename($clean);
$folder = dirname($clean);

// Encode folder
$encodedFolder = str_replace('/', '_SL_', $folder);

// Final ID
$encoded = $encodedFolder . "_SL_" . $filename;

// IIIF URL
$base = rtrim(sfConfig::get("app_base_url"), "/");
$iiifInfo = $base . "/iiif/2/" . $encoded . "/info.json";

// DEBUG dump
echo "<!-- CLEAN=$clean -->\n";
echo "<!-- FOLDER=$folder -->\n";
echo "<!-- FILE=$filename -->\n";
echo "<!-- ENCODED=$encoded -->\n";
echo "<!-- IIIF INFO=$iiifInfo -->\n";
?>

<div style="background:#222;color:#0f0;padding:6px;font-size:12px;">
  <b>IIIF DEBUG PANEL</b><br>
  RAW: <?=htmlspecialchars($raw)?><br>
  CLEAN: <?=htmlspecialchars($clean)?><br>
  FOLDER: <?=htmlspecialchars($folder)?><br>
  FILE: <?=htmlspecialchars($filename)?><br>
  ENCODED ID: <?=htmlspecialchars($encoded)?><br>
  IIIF INFO.JSON: <a style="color:#0f0;" href="<?=$iiifInfo?>" target="_blank"><?=$iiifInfo?></a>
</div>

<div id="<?=$viewerId?>" style="width:100%; height:<?=$viewerHeight?>px; background:#000;">
  <div style="color:white;padding:10px" id="<?=$viewerId?>-debug-msg">
    Initializing IIIF Viewer…
  </div>
</div>

<!-- Load OSD -->
<script src="https://cdn.jsdelivr.net/npm/openseadragon@3.1.0/build/openseadragon/openseadragon.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {

    const viewerId = "<?=$viewerId?>";
    const infoJson = "<?=$iiifInfo?>";

    debug("Viewer starting");
    debug("Info.json: " + infoJson);

    // Test fetch info.json before starting viewer
    debug("Fetching info.json manually...");
    fetch(infoJson).then(async res => {
        debug("info.json HTTP status = " + res.status);
        let text = await res.text();
        debug("info.json RAW response body:");
        console.log(text);

        try {
            let parsed = JSON.parse(text);
            debug("info.json JSON parsed OK");
            console.log(parsed);
        } catch(e) {
            debug("info.json JSON parse ERROR: " + e);
        }
    }).catch(err => {
        debug("info.json FETCH FAILED: " + err);
    });

    if (typeof OpenSeadragon === "undefined") {
        debug("OpenSeadragon missing!");
        document.getElementById(viewerId).innerHTML =
            "<div style='color:red;padding:20px;'>OSD NOT LOADED</div>";
        return;
    }

    debug("OpenSeadragon is present. Initializing viewer...");

    let viewer = OpenSeadragon({
        id: viewerId,
        prefixUrl: "https://cdn.jsdelivr.net/npm/openseadragon@3.1.0/build/openseadragon/images/",
        tileSources: infoJson,
        showNavigationControl: true,
        showNavigator: true
    });

    viewer.addHandler("open", function(){
        debug("OSD EVENT: open");
        document.getElementById(viewerId+"-debug-msg").innerHTML = "Viewer loaded.";
    });

    viewer.addHandler("open-failed", function(ev){
        debug("OSD EVENT: OPEN FAILED");
        debug("EVENT OBJECT:");
        console.log(ev);

        document.getElementById(viewerId).innerHTML =
            "<div style='color:red;padding:20px;'>OSD OPEN FAILED<br><br>" +
            "tileSources: "+infoJson+"<br><br>" +
            "<a href='"+infoJson+"' target='_blank' style='color:#0f0;'>Open INFO.JSON manually</a></div>";
    });

    viewer.addHandler("tile-load-failed", function(ev){
        debug("OSD EVENT: TILE LOAD FAILED");
        debug("TILE URL: " + ev.tile.url);
        console.log(ev);

        document.getElementById(viewerId+"-debug-msg").innerHTML =
            "Tile load FAILED: <br>" + ev.tile.url +
            "<br><a href='"+ev.tile.url+"' target='_blank' style='color:#0f0;'>Open Tile manually</a>";
    });

    viewer.addHandler("tile-loaded", function(ev){
        debug("OSD EVENT: TILE LOADED OK: " + ev.tile.url);
    });

    function debug(msg) {
        console.log("%c[IIIF DEBUG] " + msg, "color: #0f0; font-weight:bold;"); 
        let dbg = document.getElementById(viewerId+"-debug-msg");
        if (dbg) dbg.innerHTML += "<br>" + msg;
    }

});
</script>
