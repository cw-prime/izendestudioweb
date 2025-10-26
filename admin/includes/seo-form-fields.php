<!-- Basic Information -->
<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Page Identifier <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="page_identifier" required
               placeholder="e.g., homepage, service-web-dev, blog-post-123">
        <small class="form-text text-muted">Unique identifier for this page (no spaces)</small>
    </div>
    <div class="col-md-6">
        <label class="form-label">Page Type <span class="text-danger">*</span></label>
        <select class="form-select" name="page_type" required>
            <option value="">Select type...</option>
            <option value="page">Static Page</option>
            <option value="service">Service Page</option>
            <option value="blog">Blog Post</option>
            <option value="portfolio">Portfolio Item</option>
        </select>
    </div>
</div>

<!-- SEO Title & Description -->
<div class="mb-3">
    <label class="form-label">SEO Page Title <span class="text-danger">*</span></label>
    <input type="text" class="form-control" name="page_title" required maxlength="70"
           placeholder="Your Page Title | Brand Name">
    <small class="form-text text-muted">50-60 characters recommended. Shows in Google search results and browser tab.</small>
</div>

<div class="mb-3">
    <label class="form-label">Meta Description <span class="text-danger">*</span></label>
    <textarea class="form-control" name="meta_description" rows="3" required maxlength="200"
              placeholder="A compelling description of your page that will appear in search results..."></textarea>
    <small class="form-text text-muted">150-160 characters recommended. This is what people see under your title in Google.</small>
</div>

<div class="mb-3">
    <label class="form-label">Meta Keywords</label>
    <input type="text" class="form-control" name="meta_keywords"
           placeholder="web development, SEO, digital marketing">
    <small class="form-text text-muted">Comma-separated keywords (optional, less important for modern SEO)</small>
</div>

<!-- Open Graph (Facebook/LinkedIn) -->
<hr>
<h6 class="mb-3"><i class="bi bi-share"></i> Social Media Sharing (Open Graph)</h6>

<div class="row mb-3">
    <div class="col-md-8">
        <label class="form-label">OG Title</label>
        <input type="text" class="form-control" name="og_title"
               placeholder="Title when shared on Facebook/LinkedIn">
        <small class="form-text text-muted">Leave blank to use SEO Page Title</small>
    </div>
    <div class="col-md-4">
        <label class="form-label">OG Type</label>
        <select class="form-select" name="og_type">
            <option value="website">Website</option>
            <option value="article">Article</option>
            <option value="service">Service</option>
            <option value="product">Product</option>
        </select>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">OG Description</label>
    <textarea class="form-control" name="og_description" rows="2"
              placeholder="Description when shared on social media..."></textarea>
    <small class="form-text text-muted">Leave blank to use Meta Description</small>
</div>

<div class="mb-3">
    <label class="form-label">OG Image URL</label>
    <input type="url" class="form-control" name="og_image"
           placeholder="https://yoursite.com/images/social-share.jpg">
    <small class="form-text text-muted">Recommended: 1200x630px. This image appears when sharing on Facebook/LinkedIn.</small>
</div>

<!-- Twitter Card -->
<hr>
<h6 class="mb-3"><i class="bi bi-twitter"></i> Twitter Card</h6>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Twitter Card Type</label>
        <select class="form-select" name="twitter_card">
            <option value="summary">Summary</option>
            <option value="summary_large_image">Summary with Large Image</option>
            <option value="app">App</option>
            <option value="player">Player</option>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Twitter Title</label>
        <input type="text" class="form-control" name="twitter_title"
               placeholder="Title for Twitter shares">
        <small class="form-text text-muted">Leave blank to use OG Title</small>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Twitter Description</label>
    <textarea class="form-control" name="twitter_description" rows="2"
              placeholder="Description for Twitter shares..."></textarea>
    <small class="form-text text-muted">Leave blank to use OG Description</small>
</div>

<div class="mb-3">
    <label class="form-label">Twitter Image URL</label>
    <input type="url" class="form-control" name="twitter_image"
           placeholder="https://yoursite.com/images/twitter-share.jpg">
    <small class="form-text text-muted">Leave blank to use OG Image</small>
</div>

<!-- Advanced SEO Settings -->
<hr>
<h6 class="mb-3"><i class="bi bi-gear"></i> Advanced Settings</h6>

<div class="mb-3">
    <label class="form-label">Canonical URL</label>
    <input type="url" class="form-control" name="canonical_url"
           placeholder="https://yoursite.com/page">
    <small class="form-text text-muted">Preferred URL for this page (prevents duplicate content issues)</small>
</div>

<div class="mb-3">
    <label class="form-label">Robots Meta Tag</label>
    <select class="form-select" name="robots">
        <option value="index,follow">Index, Follow (Default - Let Google index this page)</option>
        <option value="noindex,follow">No Index, Follow (Hide from Google, but follow links)</option>
        <option value="index,nofollow">Index, No Follow (Show in Google, don't follow links)</option>
        <option value="noindex,nofollow">No Index, No Follow (Hide completely)</option>
    </select>
</div>

<div class="form-check">
    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
    <label class="form-check-label" for="is_active">
        Active (Enable this SEO configuration)
    </label>
</div>
