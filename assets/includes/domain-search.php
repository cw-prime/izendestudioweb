<?php
/**
 * Domain Search Form
 * Redirects to WHMCS domain search page
 */

// Configure WHMCS path
$whmcs_url = 'https://izendestudioweb.com/adminIzende'; // Your WHMCS URL
?>

<div class="domain-search-container">
  <div class="domain-search-box">
    <h2>Find Your Perfect Domain</h2>
    <iframe
      src="https://izendestudioweb.com/adminIzende/index.php?action=domainchecker&embedded=true"
      width="100%"
      height="500"
      frameborder="0"
      scrolling="no"
      class="domain-checker-iframe">
    </iframe>
  </div>
</div>

<style>
.domain-search-container {
  background: linear-gradient(135deg, rgba(92, 184, 116, 0.1) 0%, rgba(74, 157, 95, 0.05) 100%);
  padding: 40px 20px;
  border-radius: 12px;
  margin: 30px 0;
}

.domain-search-box {
  max-width: 600px;
  margin: 0 auto;
}

.domain-search-box h2 {
  text-align: center;
  margin-bottom: 20px;
  color: #2d673c;
  font-size: 24px;
}

.domain-checker-iframe {
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}


@media (max-width: 768px) {
  .domain-checker-iframe {
    height: 600px !important;
  }
}
</style>
