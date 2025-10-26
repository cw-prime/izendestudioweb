<div class="mb-3">
    <label class="form-label">Banner Title <span class="text-danger">*</span></label>
    <input type="text" class="form-control" name="title" required
           placeholder="e.g., Limited Time Offer!">
</div>

<div class="mb-3">
    <label class="form-label">Message <span class="text-danger">*</span></label>
    <textarea class="form-control" name="message" rows="3" required
              placeholder="Your promotional message here..."></textarea>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Link URL</label>
        <input type="url" class="form-control" name="link_url"
               placeholder="https://yoursite.com/promo">
        <small class="form-text text-muted">Optional: Where should the banner link to?</small>
    </div>
    <div class="col-md-6">
        <label class="form-label">Link Text</label>
        <input type="text" class="form-control" name="link_text" value="Learn More"
               placeholder="Learn More">
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-4">
        <label class="form-label">Banner Type <span class="text-danger">*</span></label>
        <select class="form-select" name="banner_type" required>
            <option value="info">Info (Blue)</option>
            <option value="success">Success (Green)</option>
            <option value="warning">Warning (Yellow)</option>
            <option value="danger">Danger (Red)</option>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Position <span class="text-danger">*</span></label>
        <select class="form-select" name="position" required>
            <option value="top">Top of Page</option>
            <option value="bottom">Bottom of Page</option>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Display Order</label>
        <input type="number" class="form-control" name="display_order" value="0" min="0">
        <small class="form-text text-muted">Lower numbers show first</small>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Start Date (Optional)</label>
        <input type="datetime-local" class="form-control" name="start_date">
        <small class="form-text text-muted">When to start showing this banner</small>
    </div>
    <div class="col-md-6">
        <label class="form-label">End Date (Optional)</label>
        <input type="datetime-local" class="form-control" name="end_date">
        <small class="form-text text-muted">When to stop showing this banner</small>
    </div>
</div>

<div class="form-check">
    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
    <label class="form-check-label" for="is_active">
        Active (Show this banner on the website)
    </label>
</div>
