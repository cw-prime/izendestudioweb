<?php
/**
 * Domain Search Form
 * Integrates with WHMCS domain search
 */

// Configure WHMCS path
$whmcs_url = 'https://izendestudioweb.com/adminIzende'; // Change to your WHMCS URL
$whmcs_path = __DIR__ . '/../../adminIzende'; // Adjust if WHMCS is in different location
?>

<div class="domain-search-container">
  <div class="domain-search-box">
    <h2>Find Your Perfect Domain</h2>
    <form id="domainSearchForm" class="domain-search-form">
      <div class="domain-search-input-group">
        <input
          type="text"
          id="domainInput"
          name="domain"
          placeholder="Enter domain name (e.g., yourwebsite.com)"
          class="domain-search-input"
          required
          autocomplete="off"
        >
        <button type="submit" class="domain-search-btn">
          <i class="bi bi-search"></i> Search
        </button>
      </div>
      <div class="domain-extensions">
        <label><input type="checkbox" name="ext" value="com" checked> .com</label>
        <label><input type="checkbox" name="ext" value="net" checked> .net</label>
        <label><input type="checkbox" name="ext" value="org" checked> .org</label>
        <label><input type="checkbox" name="ext" value="co" checked> .co</label>
        <label><input type="checkbox" name="ext" value="io"> .io</label>
        <label><input type="checkbox" name="ext" value="biz"> .biz</label>
      </div>
    </form>
    <div id="domainResults" class="domain-results" style="display:none;">
      <!-- Results will appear here -->
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

.domain-extensions {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  justify-content: center;
}

.domain-extensions label {
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  font-size: 14px;
  color: #555;
}

.domain-extensions input[type="checkbox"] {
  cursor: pointer;
  accent-color: #5cb874;
}

.domain-results {
  margin-top: 20px;
  padding: 20px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.domain-result-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid #eee;
}

.domain-result-item:last-child {
  border-bottom: none;
}

.domain-name {
  font-weight: 600;
  color: #2d673c;
}

.domain-status {
  padding: 4px 12px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
}

.domain-status.available {
  background: #d4edda;
  color: #155724;
}

.domain-status.unavailable {
  background: #f8d7da;
  color: #721c24;
}

.domain-register-btn {
  padding: 8px 16px;
  background: #5cb874;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
  transition: background 0.3s;
}

.domain-register-btn:hover {
  background: #4a9d5f;
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
}
</style>

<script>
document.getElementById('domainSearchForm').addEventListener('submit', async function(e) {
  e.preventDefault();

  const domain = document.getElementById('domainInput').value.trim();
  const extensions = Array.from(document.querySelectorAll('input[name="ext"]:checked')).map(el => el.value);

  if (!domain) {
    alert('Please enter a domain name');
    return;
  }

  if (extensions.length === 0) {
    alert('Please select at least one extension');
    return;
  }

  const resultsDiv = document.getElementById('domainResults');
  resultsDiv.innerHTML = '<p style="text-align: center;">Searching domains...</p>';
  resultsDiv.style.display = 'block';

  try {
    // Call check API with generic parameters to avoid ModSecurity blocking
    const response = await fetch('./api/check.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        q: domain,
        e: extensions
      })
    });

    const data = await response.json();

    if (data.success && data.results) {
      displayResults(data.results);
    } else {
      resultsDiv.innerHTML = '<p style="color: #d32f2f;">Unable to check domain availability. Please try again.</p>';
    }
  } catch (error) {
    console.error('Error:', error);
    resultsDiv.innerHTML = '<p style="color: #d32f2f;">Error checking domains. Please try again.</p>';
  }
});

function displayResults(results) {
  const resultsDiv = document.getElementById('domainResults');
  let html = '';

  results.forEach(result => {
    const statusClass = result.available ? 'available' : 'unavailable';
    const statusText = result.available ? 'Available' : 'Taken';
    const registerBtn = result.available ?
      `<button class="domain-register-btn" onclick="registerDomain('${result.item}')">Register</button>` :
      '';

    html += `
      <div class="domain-result-item">
        <div>
          <div class="domain-name">${result.item}</div>
          <div style="font-size: 12px; color: #999;">$${result.price || 'N/A'}/year</div>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
          <span class="domain-status ${statusClass}">${statusText}</span>
          ${registerBtn}
        </div>
      </div>
    `;
  });

  resultsDiv.innerHTML = html;
}

function registerDomain(domain) {
  // Redirect to WHMCS domain registration page
  const whmcsUrl = 'https://izendestudioweb.com/adminIzende/cart.php?a=add&domain=register&query=' + encodeURIComponent(domain);
  window.location.href = whmcsUrl;
}
</script>
