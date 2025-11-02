<?php
/**
 * Domain Search Form
 * Simple form that redirects to WHMCS domain search
 * No API calls = no ModSecurity blocking
 */
?>

<div class="domain-search-container">
  <div class="domain-search-box">
    <h2>Find Your Perfect Domain</h2>
    <form action="https://izendestudioweb.com/adminIzende/cart.php" method="get" class="domain-search-form">
      <input type="hidden" name="a" value="add">
      <input type="hidden" name="domain" value="register">

      <div class="domain-search-input-group">
        <input
          type="text"
          name="query"
          placeholder="Enter domain name (e.g., yourwebsite)"
          class="domain-search-input"
          required
          autocomplete="off"
        >
        <button type="submit" class="domain-search-btn">
          <i class="bi bi-search"></i> Search
        </button>
      </div>
    </form>
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

@media (max-width: 768px) {
  .domain-search-input-group {
    flex-direction: column;
  }

  .domain-search-btn {
    width: 100%;
  }
}
</style>
