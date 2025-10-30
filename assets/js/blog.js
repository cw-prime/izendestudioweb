/**
 * Blog Page - Load and display blog posts
 */

// Detect the base path from the script tag location
function getBasePath() {
  // Try to get from script tag
  const scripts = document.getElementsByTagName('script');
  for (let script of scripts) {
    if (script.src && script.src.includes('blog.js')) {
      const scriptPath = script.src;
      const url = new URL(scriptPath);
      // Remove /assets/js/blog.js to get base path
      const basePath = url.pathname.replace(/\/assets\/js\/blog\.js.*$/, '/');
      console.log('Base path detected from script:', basePath);
      return basePath;
    }
  }

  // Fallback to pathname detection
  const pathname = window.location.pathname;
  const lastSlash = pathname.lastIndexOf('/');
  const basePath = pathname.substring(0, lastSlash + 1);
  console.log('Base path detected from pathname:', basePath);
  return basePath;
}

const basePath = getBasePath();

// Prevent multiple initializations
let blogInitialized = false;

document.addEventListener('DOMContentLoaded', function() {
  if (blogInitialized) {
    console.log('Blog already initialized, skipping...');
    return;
  }
  blogInitialized = true;
  console.log('Initializing blog...');
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

  const apiUrl = basePath + 'api/blog-posts.php?' + params.toString();
  console.log('Fetching from:', apiUrl);

  fetch(apiUrl)
    .then(r => {
      console.log('Response status:', r.status);
      return r.json();
    })
    .then(data => {
      console.log('Data received:', data);
      console.log('data.success:', data.success);
      console.log('data.data:', data.data);
      if (data.data && data.data.posts) {
        console.log('Posts array:', data.data.posts);
        console.log('Number of posts:', data.data.posts.length);
      }
      if (data.success) {
        displayBlogPosts(data.data.posts);
        displayPagination(data.data.total_pages, page);
      } else {
        console.log('API returned success=false');
        container.innerHTML = '<div class="col-12"><p class="text-center">No blog posts found.</p></div>';
      }
    })
    .catch(e => {
      console.error('Error:', e);
      container.innerHTML = '<div class="col-12"><p class="text-center text-danger">Error loading posts.</p></div>';
    });
}

function displayBlogPosts(posts) {
  console.log('displayBlogPosts called with:', posts);
  const container = document.getElementById('blog-posts-container');
  if (!container) {
    console.log('Container not found!');
    return;
  }
  console.log('Container found, posts length:', posts ? posts.length : 'posts is null/undefined');
  if (!posts || posts.length === 0) {
    console.log('No posts to display');
    container.innerHTML = '<div class="col-12"><p class="text-center">No posts found.</p></div>';
    return;
  }
  console.log('Mapping posts to HTML...');
  const html = posts.map(post => {
    const imageUrl = post.featured_image && post.featured_image.url ? post.featured_image.url : null;
    const imageAlt = post.featured_image && post.featured_image.alt ? post.featured_image.alt : post.title;
    const categoryName = post.categories && post.categories.length > 0 ? post.categories[0].name : '';

    return '<div class="col-lg-4 col-md-6 mb-4"><article class="blog-card">' +
      (imageUrl ? '<div class="blog-card-image"><img src="' + escapeHtml(imageUrl) + '" alt="' + escapeHtml(imageAlt) + '" class="img-fluid">' +
      (categoryName ? '<span class="blog-card-category">' + escapeHtml(categoryName) + '</span>' : '') + '</div>' : '') +
      '<div class="blog-card-content"><h3 class="blog-card-title"><a href="' + escapeHtml(post.link) + '">' + escapeHtml(post.title) + '</a></h3>' +
      '<p class="blog-card-excerpt">' + escapeHtml(post.excerpt || post.content.substring(0, 150)) + '</p>' +
      '<div class="blog-card-meta"><span class="blog-card-author">' + escapeHtml(post.author || 'Izende Studio') + '</span>' +
      '<span class="blog-card-date">' + formatDate(post.date) + '</span></div>' +
      '<a href="' + escapeHtml(post.link) + '" class="btn btn-link">Read More →</a></div></article></div>';
  }).join('');

  console.log('HTML length:', html.length);
  container.innerHTML = html;
  console.log('HTML inserted into container');

  // Check if it's still there after a brief delay
  setTimeout(() => {
    console.log('Container innerHTML length after 100ms:', container.innerHTML.length);
    if (container.innerHTML.length < 100) {
      console.error('Content was cleared! Restoring...');
      container.innerHTML = html;
    }
  }, 100);
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
  fetch(basePath + 'api/blog-categories.php')
    .then(r => r.json())
    .then(data => {
      if (data.success && data.data) displayCategories(data.data);
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
