/**
 * Blog functionality - fetch and display posts from WordPress API
 */

(function() {
    'use strict';

    // Configuration
    const POSTS_PER_PAGE = 9;
    let currentPage = 1;
    let currentCategory = '';
    let currentSearch = '';

    // DOM Elements
    const postsContainer = document.getElementById('blog-posts-container');
    const paginationContainer = document.getElementById('blog-pagination');
    const categoryFilter = document.getElementById('category-filter');
    const searchInput = document.getElementById('blog-search');
    const categoriesContainer = document.getElementById('blog-categories-container');

    /**
     * Initialize blog
     */
    function init() {
        // Load categories
        loadCategories();

        // Load initial posts
        loadPosts();

        // Set up event listeners
        if (categoryFilter) {
            categoryFilter.addEventListener('change', handleCategoryChange);
        }

        if (searchInput) {
            // Debounce search input
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(handleSearchChange, 500);
            });
        }
    }

    /**
     * Load blog posts
     */
    function loadPosts() {
        if (!postsContainer) return;

        // Build API URL
        const params = new URLSearchParams({
            per_page: POSTS_PER_PAGE,
            page: currentPage
        });

        if (currentCategory) {
            params.append('category', currentCategory);
        }

        if (currentSearch) {
            params.append('search', currentSearch);
        }

        const apiUrl = `/api/blog-posts.php?${params.toString()}`;

        // Show loading state
        showLoadingState();

        // Fetch posts
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayPosts(data.data.posts);
                    displayPagination(data.data.total_pages);
                } else {
                    showError('Failed to load blog posts. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error loading posts:', error);
                showError('Failed to load blog posts. Please try again.');
            });
    }

    /**
     * Load categories
     */
    function loadCategories() {
        fetch('/api/blog-categories.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    populateCategoryFilter(data.data);
                    displayCategoryBoxes(data.data);
                }
            })
            .catch(error => {
                console.error('Error loading categories:', error);
            });
    }

    /**
     * Display posts in grid
     */
    function displayPosts(posts) {
        if (!postsContainer) return;

        if (posts.length === 0) {
            postsContainer.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="bx bx-search" style="font-size: 48px; color: #ccc;"></i>
                    <p class="mt-3">No posts found. Try adjusting your search or filters.</p>
                </div>
            `;
            return;
        }

        let html = '';

        posts.forEach(post => {
            const categories = post.categories.map(cat => cat.name).join(', ');
            const categoryBadge = post.categories.length > 0
                ? `<span class="blog-category-badge">${post.categories[0].name}</span>`
                : '';

            html += `
                <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up">
                    <article class="blog-card">
                        <div class="blog-card-image">
                            <a href="${post.link}">
                                <img src="${post.featured_image.url}"
                                     alt="${escapeHtml(post.featured_image.alt)}"
                                     class="img-fluid"
                                     loading="lazy">
                            </a>
                            ${categoryBadge}
                        </div>
                        <div class="blog-card-content">
                            <div class="blog-card-meta">
                                <span><i class="bx bx-calendar"></i> ${formatDate(post.date)}</span>
                                <span><i class="bx bx-time-five"></i> ${post.reading_time} min read</span>
                            </div>
                            <h3 class="blog-card-title">
                                <a href="${post.link}">${post.title}</a>
                            </h3>
                            <p class="blog-card-excerpt">${post.excerpt}</p>
                            <a href="${post.link}" class="blog-card-link">
                                Read More <i class="bx bx-right-arrow-alt"></i>
                            </a>
                        </div>
                    </article>
                </div>
            `;
        });

        postsContainer.innerHTML = html;

        // Re-initialize AOS if it exists
        if (typeof AOS !== 'undefined') {
            AOS.refresh();
        }
    }

    /**
     * Display pagination
     */
    function displayPagination(totalPages) {
        if (!paginationContainer || totalPages <= 1) {
            paginationContainer.innerHTML = '';
            return;
        }

        let html = '<nav aria-label="Blog pagination"><ul class="pagination justify-content-center">';

        // Previous button
        html += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
        `;

        // Page numbers
        const maxVisiblePages = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

        if (endPage - startPage < maxVisiblePages - 1) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }

        for (let i = startPage; i <= endPage; i++) {
            html += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        }

        // Next button
        html += `
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        `;

        html += '</ul></nav>';

        paginationContainer.innerHTML = html;

        // Add click handlers to pagination links
        const pageLinks = paginationContainer.querySelectorAll('.page-link');
        pageLinks.forEach(link => {
            link.addEventListener('click', handlePageClick);
        });
    }

    /**
     * Populate category filter dropdown
     */
    function populateCategoryFilter(categories) {
        if (!categoryFilter) return;

        let html = '<option value="">All Categories</option>';

        categories.forEach(cat => {
            html += `<option value="${cat.slug}">${cat.name} (${cat.count})</option>`;
        });

        categoryFilter.innerHTML = html;
    }

    /**
     * Display category boxes
     */
    function displayCategoryBoxes(categories) {
        if (!categoriesContainer || categories.length === 0) {
            if (categoriesContainer) {
                categoriesContainer.closest('section').style.display = 'none';
            }
            return;
        }

        // Show top 6 categories by count
        const topCategories = categories
            .sort((a, b) => b.count - a.count)
            .slice(0, 6);

        let html = '';

        topCategories.forEach(cat => {
            html += `
                <div class="col-lg-4 col-md-6 mb-3" data-aos="fade-up">
                    <div class="category-box" data-category="${cat.slug}">
                        <i class="bx bx-folder"></i>
                        <h4>${cat.name}</h4>
                        <p>${cat.count} article${cat.count !== 1 ? 's' : ''}</p>
                    </div>
                </div>
            `;
        });

        categoriesContainer.innerHTML = html;

        // Add click handlers
        const categoryBoxes = categoriesContainer.querySelectorAll('.category-box');
        categoryBoxes.forEach(box => {
            box.addEventListener('click', function() {
                const catSlug = this.getAttribute('data-category');
                categoryFilter.value = catSlug;
                currentCategory = catSlug;
                currentPage = 1;
                loadPosts();
                // Scroll to posts
                document.getElementById('blog-posts-container').scrollIntoView({ behavior: 'smooth' });
            });
        });
    }

    /**
     * Show loading state
     */
    function showLoadingState() {
        if (!postsContainer) return;

        let html = '';
        for (let i = 0; i < 3; i++) {
            html += `
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="blog-skeleton">
                        <div class="blog-skeleton-image"></div>
                        <div class="blog-skeleton-content">
                            <div class="blog-skeleton-category"></div>
                            <div class="blog-skeleton-title"></div>
                            <div class="blog-skeleton-excerpt"></div>
                            <div class="blog-skeleton-meta"></div>
                        </div>
                    </div>
                </div>
            `;
        }

        postsContainer.innerHTML = html;
    }

    /**
     * Show error message
     */
    function showError(message) {
        if (!postsContainer) return;

        postsContainer.innerHTML = `
            <div class="col-12">
                <div class="alert alert-danger" role="alert">
                    <i class="bx bx-error"></i> ${message}
                </div>
            </div>
        `;
    }

    /**
     * Handle category filter change
     */
    function handleCategoryChange(e) {
        currentCategory = e.target.value;
        currentPage = 1;
        loadPosts();
    }

    /**
     * Handle search input change
     */
    function handleSearchChange() {
        currentSearch = searchInput.value.trim();
        currentPage = 1;
        loadPosts();
    }

    /**
     * Handle pagination click
     */
    function handlePageClick(e) {
        e.preventDefault();

        const page = parseInt(this.getAttribute('data-page'));

        if (isNaN(page) || page < 1) {
            return;
        }

        currentPage = page;
        loadPosts();

        // Scroll to top of posts
        document.getElementById('blog-posts-container').scrollIntoView({ behavior: 'smooth' });
    }

    /**
     * Format date
     */
    function formatDate(dateString) {
        const date = new Date(dateString);
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return date.toLocaleDateString('en-US', options);
    }

    /**
     * Escape HTML
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
