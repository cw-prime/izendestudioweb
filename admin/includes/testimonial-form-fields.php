<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label">Client Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="client_name" required
               placeholder="John Smith">
    </div>
    <div class="col-md-6">
        <label class="form-label">Company</label>
        <input type="text" class="form-control" name="client_company"
               placeholder="ABC Corporation">
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Position/Title</label>
    <input type="text" class="form-control" name="client_position"
           placeholder="CEO, Marketing Director, etc.">
</div>

<div class="mb-3">
    <label class="form-label">Testimonial <span class="text-danger">*</span></label>
    <textarea class="form-control" name="testimonial_text" rows="4" required
              placeholder="What did the client say about your service?"></textarea>
</div>

<div class="row mb-3">
    <div class="col-md-4">
        <label class="form-label">Rating <span class="text-danger">*</span></label>
        <select class="form-select" name="rating" required>
            <option value="5" selected>5 Stars - Excellent</option>
            <option value="4">4 Stars - Great</option>
            <option value="3">3 Stars - Good</option>
            <option value="2">2 Stars - Fair</option>
            <option value="1">1 Star - Poor</option>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Project Type</label>
        <input type="text" class="form-control" name="project_type"
               placeholder="Web Development, SEO, etc.">
    </div>
    <div class="col-md-4">
        <label class="form-label">Display Order</label>
        <input type="number" class="form-control" name="display_order" value="0" min="0">
        <small class="form-text text-muted">Lower shows first</small>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Client Logo URL</label>
    <input type="url" class="form-control" name="client_logo"
           placeholder="https://yoursite.com/logos/client-logo.png">
    <small class="form-text text-muted">Optional: Company logo image</small>
</div>

<div class="mb-3">
    <label class="form-label">Client Photo URL</label>
    <input type="url" class="form-control" name="client_photo"
           placeholder="https://yoursite.com/photos/client.jpg">
    <small class="form-text text-muted">Optional: Client headshot/photo</small>
</div>

<div class="form-check mb-2">
    <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured">
    <label class="form-check-label" for="is_featured">
        <strong>Featured</strong> - Display prominently on homepage
    </label>
</div>

<div class="form-check">
    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
    <label class="form-check-label" for="is_active">
        Active - Show on website
    </label>
</div>
