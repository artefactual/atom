<?php if ('script' === $code) { ?>

<?php if ('' === sfConfig::get('csp_nonce', '')) { ?>

<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','<?php echo $containerId; ?>');</script>
<!-- End Google Tag Manager -->

<?php } else { ?>

<!-- Google Tag Manager -->
<script <?php echo __(sfConfig::get('csp_nonce', '')); ?>>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;var n=d.querySelector('[nonce]');
n&&j.setAttribute('nonce',n.nonce||n.getAttribute('nonce'));f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','<?php echo $containerId; ?>');</script>
<!-- End Google Tag Manager -->

<?php } ?>

<?php } elseif ('noscript' === $code) { ?>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo $containerId; ?>"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<?php } ?>
