/**
 * Blog Page - Load and display blog posts
 */
document.addEventListener('DOMContentLoaded', function() {
  loadBlogPosts();
  loadBlogCategories();
  setupBlogFilters();
});

function loadBlogPosts(page = 1, category = '') {
  const container = document.getElementById('blog-posts-container');
  if (!container) return;
  const params = new URLSearchParams();
  params.append('page', page);
  params.append('per_page', 9);
  if (category) params.append('category', category);
  const searchInput = document.getElementById('blog-search');
  if (searchInput && searchInput.value) {
    params.append('search', searchInput.value);
  }
  fetch('/api/blog-posts.php?' + params.toString())
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        displayBlogPosts(data.posts);
        displayPagination(data.total_pages, page);
      } else {
        container.innerHTML = '<div class="col-12"><p class="text-center">No blog posts found.</p></div>';
      }
    })
    .catch(e => {
      console.error('Error:', e);
      container.innerHTML = '<div class="col-12"><p class="text-center text-danger">Error loading posts.</p></div>';
    });
}

function displayBlogPosts(posts) {
  const container = document.getElementById('blog-posts-container');
  if (!container) return;
  if (posts.length === 0) {
    container.innerHTML = '<div class="col-12"><p class="text-center">No posts found.</p></div>';
    return;
  }
  container.innerHTML = posts.map(post => '<div class="col-lg-4 col-md-6 mb-4"><article class="blog-card">' + (post.featured_image ? '<div class="blog-card-image"><img src="' + escapeHtml(post.featured_image) + '" alt="' + escapeHtml(post.title) + '" class="img-fluid">' + (post.category ? '<span class="blog-card-category">' + escapeHtml(post.category) + '</span>' : '') + '</div>' : '') + '<div class="blog-card-content"><h3 class="blog-card-title"><a href="/blog/' + escapeHtml(post.slug) + '.php">' + escapeHtml(post.title) + '</a></h3><p class="blog-card-excerpt">' + escapeHtml(post.excerpt || post.content.substring(0, 150)) + '</p><div class="blog-card-meta"><span class="blog-card-author">' + escapeHtml(post.author || 'Izende Studio') + '</span><span class="blog-card-date">' + formatDate(post.created_at) + '</span></div><a href="/blog/' + escapeHtml(post.slug) + '.php" class="btn btn-link">Read More →</a></div></article></div>').join('');
}

function displayPagination(totalPages, currentPage) {
  const pagination = document.getElementById('blog-pagination');
  if (!pagination || totalPages <= 1) {
    if (pagination) pagination.innerHTML = '';
    return;
  }
  let html = '<nav><ul class="pagination justify-content-center">';
  if (currentPage > 1) {
    html += '<li class="page-item"><button class="page-link" onclick="loadBlogPosts(' + (currentPage - 1) + ')">Previous</button></li>';
  }
  for (let i = 1; i <= totalPages; i++) {
    if (i === currentPage) {
      html += '<li class="page-item active"><span class="page-link">' + i + '</span></li>';
    } else {
      html += '<li class="page-item"><button class="page-link" onclick="loadBlogPosts(' + i + ')">' + i + '</button></li>';
    }
  }
  if (currentPage < totalPages) {
    html += '<li class="page-item"><button class="page-link" onclick="loadBlogPosts(' + (currentPage + 1) + ')">Next</button></li>';
  }
  html += '</ul></nav>';
  pagination.innerHTML = html;
}

function loadBlogCategories() {
  const container = document.getElementById('blog-categories-container');
  if (!container) return;
  fetch('/api/blog-categories.php')
    .then(r => r.json())
    .then(data => {
      if (data.success && data.categories) displayCategories(data.categories);
    })
    .catch(e => console.error('Error:', e));
}

function displayCategories(categories) {
  const container = document.getElementById('blog-categories-container');
  if (!container) return;
  container.innerHTML = categories.map(cat => '<div class="col-lg-3 col-md-4 col-sm-6 mb-3"><button class="category-card" onclick="filterByCategory(\'' + escapeHtml(cat.slug) + '\')"><h4>' + escapeHtml(cat.name) + '</h4><p class="text-muted">' + (cat.count || 0) + ' posts</p></button></div>').join('');
}

function setupBlogFilters() {
  const searchInput = document.getElementById('blog-search');
  const categoryFilter = document.getElementById('category-filter');
  if (searchInput) {
    let searchTimeout;
    searchInput.addEventListener('input', function() {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => loadBlogPosts(1), 300);
    });
  }
  if (categoryFilter) {
    categoryFilter.addEventListener('change', function() {
      loadBlogPosts(1, this.value);
    });
  }
}

function filterByCategory(categorySlug) {
  const categoryFilter = document.getElementById('category-filter');
  if (categoryFilter) categoryFilter.value = categorySlug;
  loadBlogPosts(1, categorySlug);
}

function formatDate(dateString) {
  const options = { year: 'numeric', month: 'short', day: 'numeric' };
  return new Date(dateString).toLocaleDateString('en-US', options);
}

function escapeHtml(text) {
  const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
  return text.replace(/[&<>"']/g, m => map[m]);
}
