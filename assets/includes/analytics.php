<?php
/**
 * Google Analytics / Google Tag Manager Integration
 * Include this file in the <head> section of your pages
 */

// Get analytics settings - with fallback if CMSData is not available
$analyticsEnabled = '1';
$ga4Id = '';
$gtmId = '';

if (class_exists('CMSData')) {
    $analyticsEnabled = CMSData::getSetting('analytics_enabled') ?? '1';
    $ga4Id = CMSData::getSetting('google_analytics_id') ?? '';
    $gtmId = CMSData::getSetting('google_tag_manager_id') ?? '';
}

// Only output tracking code if enabled and IDs are configured
if ($analyticsEnabled != '1') {
    return;
}
?>

<?php if (!empty($gtmId)): ?>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','<?php echo htmlspecialchars($gtmId); ?>');</script>
<!-- End Google Tag Manager -->
<?php endif; ?>

<?php if (!empty($ga4Id)): ?>
<!-- Google Analytics 4 -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo htmlspecialchars($ga4Id); ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?php echo htmlspecialchars($ga4Id); ?>', {
    'send_page_view': true,
    'anonymize_ip': true
  });
</script>
<!-- End Google Analytics 4 -->
<?php endif; ?>
