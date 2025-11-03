<?php
/**
 * Domain Search Form
 * Integrates with WHMCS via the local lookup proxy.
 */

if (!function_exists('loadEnvFile')) {
    require_once __DIR__ . '/../../config/env-loader.php';
}
loadEnvFile();

$whmcsUrl = rtrim((string) getEnv('WHMCS_PUBLIC_URL', 'https://izendestudioweb.com/adminIzende'), '/');
$defaultTlds = array_values(array_filter(array_map('trim', explode(',', (string) getEnv('WHMCS_LOOKUP_TLDS', 'com,net,org,co,io,info')))));
if (empty($defaultTlds)) {
    $defaultTlds = ['com'];
}
$currencyCode = (string) getEnv('WHMCS_DEFAULT_CURRENCY_CODE', 'USD');
?>

<div class="domain-search-container">
  <div class="domain-search-box">
    <h2>Find Your Perfect Domain</h2>
    <form
      method="post"
      action="<?php echo htmlspecialchars($whmcsUrl . '/domainchecker.php', ENT_QUOTES); ?>"
      id="frmDomainHomepage"
      class="domain-search-form"
      data-lookup-endpoint="/api/lookup.php"
      data-default-tlds="<?php echo htmlspecialchars(json_encode($defaultTlds), ENT_QUOTES); ?>"
      data-whmcs-url="<?php echo htmlspecialchars($whmcsUrl, ENT_QUOTES); ?>"
      data-currency-code="<?php echo htmlspecialchars($currencyCode, ENT_QUOTES); ?>"
    >
      <input type="hidden" name="transfer" value="">
      <div class="domain-search-input-group">
        <input
          type="text"
          name="domain"
          placeholder="Enter domain name (e.g., yourwebsite)"
          class="domain-search-input"
          required
          autocomplete="off"
        >
        <button type="submit" class="domain-search-btn" id="btnDomainSearch">
          <i class="bi bi-search"></i> Search
        </button>
        <button type="submit" class="domain-search-btn domain-transfer-btn" id="btnTransfer" data-domain-action="transfer">
          <i class="bi bi-arrow-left-right"></i> Transfer
        </button>
      </div>
    </form>

    <div class="domain-search-feedback" id="domainSearchFeedback" role="status" aria-live="polite"></div>

    <div class="domain-search-results" id="domainSearchResults" hidden>
      <ul class="domain-result-list" id="domainSearchResultsList"></ul>
      <p class="domain-search-footnote" id="domainSearchFootnote" hidden></p>
    </div>
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

.domain-search-form {
  margin-bottom: 20px;
}

.domain-search-input-group {
  display: flex;
  gap: 10px;
  margin-bottom: 15px;
}

.domain-search-input {
  flex: 1;
  padding: 12px 16px;
  border: 2px solid #5cb874;
  border-radius: 6px;
  font-size: 16px;
  transition: border-color 0.3s;
}

.domain-search-input:focus {
  outline: none;
  border-color: #2d673c;
  box-shadow: 0 0 0 3px rgba(92, 184, 116, 0.1);
}

.domain-search-btn {
  padding: 12px 24px;
  background: linear-gradient(135deg, #5cb874 0%, #4a9d5f 100%);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  gap: 8px;
}

.domain-search-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(92, 184, 116, 0.3);
}

.domain-transfer-btn {
  background: linear-gradient(135deg, #17a2b8 0%, #0d6378 100%);
}

.domain-transfer-btn:hover {
  box-shadow: 0 4px 12px rgba(23, 162, 184, 0.3);
}

.domain-search-feedback {
  text-align: center;
  font-weight: 600;
  margin-bottom: 15px;
  min-height: 22px;
}

.domain-search-feedback--success {
  color: #2d673c;
}

.domain-search-feedback--error {
  color: #b02a37;
}

.domain-search-feedback--info {
  color: #0d6378;
}

.domain-search-results {
  background: #ffffff;
  border: 1px solid rgba(92, 184, 116, 0.2);
  border-radius: 8px;
  padding: 18px 20px;
  box-shadow: 0 8px 20px rgba(92, 184, 116, 0.12);
}

.domain-result-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.domain-result-item {
  border-bottom: 1px solid rgba(92, 184, 116, 0.15);
  padding-bottom: 14px;
}

.domain-result-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.domain-result-header {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}

.domain-result-title {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.domain-result-name {
  font-size: 18px;
  font-weight: 600;
  color: #1d4d2d;
  word-break: break-word;
}

.domain-result-status {
  font-weight: 600;
  font-size: 14px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.domain-result-status--available {
  color: #2d673c;
}

.domain-result-status--taken {
  color: #b02a37;
}

.domain-result-badge {
  display: inline-flex;
  align-items: center;
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0.4px;
  text-transform: uppercase;
  color: #0d6378 !important;
  background: rgba(13, 99, 120, 0.12);
  padding: 4px 10px;
  border-radius: 999px;
}

.domain-result-item--suggestion {
  background: rgba(13, 99, 120, 0.05);
  border-radius: 8px;
  border: 1px solid rgba(13, 99, 120, 0.15);
  padding: 16px;
  margin-top: 12px;
  border-bottom: none;
}

.domain-result-item--suggestion + .domain-result-item {
  border-top: none;
}

.domain-result-meta {
  margin-top: 6px;
  color: #4a5a50;
  font-size: 14px;
}

.domain-result-actions {
  margin-top: 12px;
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  justify-content: flex-start;
}

.domain-result-cta {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 10px 18px;
  border-radius: 6px;
  font-weight: 600;
  text-decoration: none;
  transition: transform 0.2s, box-shadow 0.2s;
  background: linear-gradient(135deg, #5cb874 0%, #4a9d5f 100%) !important;
  color: #ffffff !important;
}

.domain-result-cta:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(92, 184, 116, 0.3);
  color: #ffffff !important;
}

.domain-result-link {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 10px 16px;
  border-radius: 6px;
  font-weight: 600;
  text-decoration: none;
  color: #2d673c !important;
  background: rgba(45, 103, 60, 0.12);
  transition: transform 0.2s, box-shadow 0.2s, background 0.2s;
}

.domain-result-link:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(45, 103, 60, 0.15);
  background: rgba(45, 103, 60, 0.18);
  color: #2d673c !important;
}

.domain-result-cta--alt {
  background: linear-gradient(135deg, #17a2b8 0%, #0d6378 100%);
}

.domain-search-footnote {
  margin-top: 16px;
  font-size: 13px;
  color: #6c7a70;
  text-align: center;
}

.domain-search-spinner {
  display: inline-block;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  border: 2px solid rgba(255, 255, 255, 0.4);
  border-top-color: #ffffff;
  animation: domain-spin 0.8s linear infinite;
}

@keyframes domain-spin {
  to {
    transform: rotate(360deg);
  }
}

@media (max-width: 768px) {
  .domain-search-input-group {
    flex-direction: column;
  }

  .domain-search-btn {
    width: 100%;
  }

  .domain-extensions {
    font-size: 12px;
  }

  .domain-result-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .domain-result-actions {
    width: 100%;
  }

  .domain-result-cta {
    flex: 1;
    text-align: center;
  }
}
</style>

<script>
(function initDomainSearch() {
  const start = function() {
    const form = document.getElementById('frmDomainHomepage');
    const transferBtn = document.getElementById('btnTransfer');
    const searchBtn = document.getElementById('btnDomainSearch');
    if (!form || !transferBtn || !searchBtn) {
      return;
    }

    const domainInput = form.querySelector('input[name="domain"]');
    const transferInput = form.querySelector('input[name="transfer"]');
    const feedbackEl = document.getElementById('domainSearchFeedback');
    const resultsWrapper = document.getElementById('domainSearchResults');
    const resultsList = document.getElementById('domainSearchResultsList');
    const footnote = document.getElementById('domainSearchFootnote');

    const lookupEndpoint = form.getAttribute('data-lookup-endpoint') || '/api/lookup.php';
    const whmcsUrl = form.getAttribute('data-whmcs-url') || '';
    const currencyCode = form.getAttribute('data-currency-code') || 'USD';
    let defaultTlds;

    try {
      defaultTlds = JSON.parse(form.getAttribute('data-default-tlds') || '[]');
    } catch (err) {
      defaultTlds = ['com'];
    }
    if (!Array.isArray(defaultTlds) || defaultTlds.length === 0) {
      defaultTlds = ['com'];
    }

    const originalButtonMarkup = {
      search: searchBtn.innerHTML,
      transfer: transferBtn.innerHTML
    };

    let pendingIntent = 'search';

    transferBtn.addEventListener('click', () => {
      pendingIntent = 'transfer';
      transferInput.value = '1';
    });

    searchBtn.addEventListener('click', () => {
      pendingIntent = 'search';
      transferInput.value = '';
    });

    function escapeHtml(value) {
      return value.replace(/[&<>"']/g, function(char) {
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
        return map[char] || char;
      });
    }

    function setFeedback(message, type) {
      if (!feedbackEl) {
        return;
      }
      feedbackEl.className = 'domain-search-feedback';
      if (type) {
        feedbackEl.classList.add(`domain-search-feedback--${type}`);
      }
      feedbackEl.textContent = message || '';
    }

    function setLoading(isLoading, intent) {
      const activeButton = intent === 'transfer' ? transferBtn : searchBtn;
      [searchBtn, transferBtn].forEach(btn => {
        btn.disabled = isLoading;
      });

      if (isLoading) {
        activeButton.innerHTML = '<span class="domain-search-spinner" aria-hidden="true"></span> Checking...';
      } else {
        searchBtn.innerHTML = originalButtonMarkup.search;
        transferBtn.innerHTML = originalButtonMarkup.transfer;
      }
    }

    function resetResults() {
      if (resultsWrapper) {
        resultsWrapper.hidden = true;
      }
      if (resultsList) {
        resultsList.innerHTML = '';
      }
      if (footnote) {
        footnote.hidden = true;
        footnote.textContent = '';
      }
    }

    function formatPrice(value) {
      if (typeof value !== 'number' || !isFinite(value) || value <= 0) {
        return null;
      }
      try {
        return new Intl.NumberFormat(undefined, {
          style: 'currency',
          currency: currencyCode
        }).format(value);
      } catch (err) {
        return `$${value.toFixed(2)}`;
      }
    }

  function buildActionLink(domain, available, isTransferIntent, forceRegister = false) {
    if (!whmcsUrl) {
      return '#';
    }
    if (forceRegister || (available && !isTransferIntent)) {
      return `${whmcsUrl}/cart.php?a=add&domain=register&query=${encodeURIComponent(domain)}`;
    }
    return `${whmcsUrl}/cart.php?a=add&domain=transfer&query=${encodeURIComponent(domain)}`;
  }

    function renderResults(items, isTransferIntent, query, backendErrors) {
      if (!Array.isArray(items) || items.length === 0) {
        resetResults();
        setFeedback(`No results found for "${query}".`, 'info');
        return;
      }

      if (footnote) {
        footnote.hidden = true;
        footnote.textContent = '';
      }

      const fragments = items.map(item => {
        const domainName = item.domain || item.name || '';
        const available = !!item.avail;
        const isSuggestion = !!item.suggestion;
        const statusClass = available ? 'domain-result-status--available' : 'domain-result-status--taken';
        const statusLabel = available ? 'Available' : 'Taken';
        const registerPrice = item.pricing ? formatPrice(item.pricing.register) : null;
        const transferPrice = item.pricing ? formatPrice(item.pricing.transfer) : null;
        const tldLabel = item.tld ? String(item.tld).toUpperCase() : '';

        let metaText = available ? 'Available to register' : 'Currently registered';
        if (available && registerPrice) {
          metaText = `Register for ${registerPrice}`;
        } else if (!available && transferPrice) {
          metaText = `Transfer for ${transferPrice}`;
        }

        let ctaLabel = available ? 'Register' : 'Transfer';
        if (available && isTransferIntent) {
          ctaLabel = 'Register Instead';
        }

        if (isSuggestion) {
          ctaLabel = 'Register';
          if (available) {
            metaText = registerPrice
              ? `Also available as .${tldLabel} for ${registerPrice}`
              : `Also available as .${tldLabel}`;
          }
        }

        const badge = isSuggestion ? '<span class="domain-result-badge">Alternative TLD</span>' : '';
        const actionUrl = buildActionLink(domainName, available, isTransferIntent, isSuggestion);
        const ctaClass = available ? 'domain-result-cta' : 'domain-result-cta domain-result-cta--alt';

        let secondaryActionHtml = '';
        if (!isSuggestion && whmcsUrl) {
          if (available && !isTransferIntent) {
            const transferHref = `${whmcsUrl}/cart.php?a=add&domain=transfer&query=${encodeURIComponent(domainName)}`;
            secondaryActionHtml = `<a class="domain-result-link" href="${transferHref}">Transfer instead</a>`;
          } else if (!available) {
            const registerHref = `${whmcsUrl}/cart.php?a=add&domain=register&query=${encodeURIComponent(domainName)}`;
            secondaryActionHtml = `<a class="domain-result-link" href="${registerHref}">Register a different domain</a>`;
          }
        }

        return `
          <li class="domain-result-item${isSuggestion ? ' domain-result-item--suggestion' : ''}">
            <div class="domain-result-header">
              <div class="domain-result-title">
                <span class="domain-result-name">${escapeHtml(domainName)}</span>
                ${badge}
              </div>
              <span class="domain-result-status ${statusClass}">${statusLabel}</span>
            </div>
            <div class="domain-result-meta">${escapeHtml(metaText)}</div>
            <div class="domain-result-actions">
              <a class="${ctaClass}" href="${actionUrl}">${escapeHtml(ctaLabel)}</a>
              ${secondaryActionHtml}
            </div>
          </li>
        `;
      }).join('');

      resultsList.innerHTML = fragments;
      resultsWrapper.hidden = false;

      // Bring the results into view so the user can see the CTAs immediately.
      try {
        if (typeof resultsWrapper.scrollIntoView === 'function') {
          resultsWrapper.scrollIntoView({ behavior: 'smooth', block: 'start', inline: 'nearest' });
        } else {
          window.scrollTo({
            top: resultsWrapper.getBoundingClientRect().top + window.scrollY - 120,
            behavior: 'smooth'
          });
        }
      } catch (err) {
        // Non-fatal: ignore scroll issues.
      }

      const primaryAvailable = items[0]?.avail;
      const anyAvailable = items.some(item => item.avail);

      let successMessage;
      if (primaryAvailable) {
        successMessage = 'Great news! That domain is available.';
      } else if (anyAvailable) {
        successMessage = 'That domain is taken, but these alternatives are available.';
      } else {
        successMessage = 'That domain is already registered.';
      }
      setFeedback(successMessage, anyAvailable ? 'success' : 'info');

      if (backendErrors && backendErrors.length > 0) {
        footnote.hidden = false;
        footnote.textContent = `Some extensions could not be checked: ${backendErrors.join(', ')}`;
      }
    }

    async function handleSubmit(event) {
      event.preventDefault();
      const rawQuery = domainInput.value.trim();
      if (!rawQuery) {
        setFeedback('Please enter a domain name.', 'error');
        return;
      }

      const normalizedQuery = rawQuery.toLowerCase();
      const isTransferIntent = pendingIntent === 'transfer' || transferInput.value === '1';
      const payload = {};
      const hasExtension = normalizedQuery.includes('.');

      if (hasExtension) {
        payload.domain = normalizedQuery;
      } else {
        payload.item = normalizedQuery;
        payload.types = defaultTlds;
      }

      if (isTransferIntent) {
        payload.transfer = 1;
      }

      setFeedback('Checking availability...', 'info');
      resetResults();
      setLoading(true, isTransferIntent ? 'transfer' : 'search');

      try {
        const response = await fetch(lookupEndpoint, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });

        let body;
        try {
          body = await response.json();
        } catch (err) {
          body = null;
        }

        if (!response.ok || !body || body.ok !== true) {
          const errorMessage = (body && (body.error || body.details)) ? body.error || body.details : 'Domain lookup failed. Please try again.';
          setFeedback(errorMessage, 'error');
          return;
        }

        renderResults(body.data, isTransferIntent, rawQuery, body.errors);
      } catch (err) {
        setFeedback('Unable to contact the domain checker. Please try again shortly.', 'error');
      } finally {
        setLoading(false);
        pendingIntent = 'search';
        transferInput.value = '';
      }
    }

    form.addEventListener('submit', handleSubmit);
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
})();
</script>
