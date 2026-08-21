<?php
$staticAlias = rtrim(sfConfig::get('app_static_alias'), '/');
$staticPath = sfConfig::get('app_static_path');

$customSvgPath = $staticPath.DIRECTORY_SEPARATOR.'favicon.svg';
$customIcoPath = $staticPath.DIRECTORY_SEPARATOR.'favicon.ico';

if (file_exists($customSvgPath)) {
    $faviconHref = $staticAlias.'/favicon.svg';
    $faviconType = 'image/svg+xml';
} elseif (file_exists($customIcoPath)) {
    $faviconHref = $staticAlias.'/favicon.ico';
    $faviconType = 'image/x-icon';
} else {
    $faviconHref = public_path('favicon.svg');
    $faviconType = 'image/svg+xml';
}
?>
<link rel="icon" type="<?php echo $faviconType; ?>" href="<?php echo htmlspecialchars($faviconHref, ENT_QUOTES, 'UTF-8'); ?>">
