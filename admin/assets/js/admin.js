/**
 * Izende Studio CMS - Admin Panel JavaScript
 */

// Confirm delete actions
document.addEventListener('DOMContentLoaded', function() {
    // Confirm delete buttons
    const deleteButtons = document.querySelectorAll('[data-confirm-delete]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
                return false;
            }
        });
    });

    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // Image preview on file input
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    imageInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    let preview = input.parentElement.querySelector('.image-preview');
                    if (!preview) {
                        preview = document.createElement('img');
                        preview.className = 'image-preview';
                        input.parentElement.appendChild(preview);
                    }
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // Character counter for textareas
    const textareas = document.querySelectorAll('textarea[data-max-length]');
    textareas.forEach(textarea => {
        const maxLength = parseInt(textarea.getAttribute('data-max-length'));
        const counter = document.createElement('div');
        counter.className = 'form-text text-end';
        textarea.parentElement.appendChild(counter);

        const updateCounter = () => {
            const remaining = maxLength - textarea.value.length;
            counter.textContent = `${remaining} characters remaining`;
            counter.style.color = remaining < 0 ? 'red' : '';
        };

        textarea.addEventListener('input', updateCounter);
        updateCounter();
    });

    // YouTube URL to ID converter
    const youtubeInputs = document.querySelectorAll('input[data-youtube-url]');
    youtubeInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const url = input.value;
            const videoId = extractYouTubeID(url);
            if (videoId) {
                const idInput = document.querySelector('input[name="youtube_id"]');
                if (idInput) {
                    idInput.value = videoId;
                }
                // Show thumbnail preview
                showYouTubeThumbnail(videoId);
            }
        });
    });
});

/**
 * Extract YouTube video ID from URL
 */
function extractYouTubeID(url) {
    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
    const match = url.match(regExp);
    return (match && match[2].length === 11) ? match[2] : null;
}

/**
 * Show YouTube thumbnail preview
 */
function showYouTubeThumbnail(videoId) {
    const thumbnailUrl = `https://img.youtube.com/vi/${videoId}/maxresdefault.jpg`;
    let preview = document.querySelector('.youtube-thumbnail-preview');

    if (!preview) {
        preview = document.createElement('img');
        preview.className = 'image-preview youtube-thumbnail-preview';
        const youtubeInput = document.querySelector('input[data-youtube-url]');
        if (youtubeInput) {
            youtubeInput.parentElement.appendChild(preview);
        }
    }

    preview.src = thumbnailUrl;
    preview.style.display = 'block';
}

/**
 * Show loading spinner
 */
function showLoading() {
    const spinner = document.createElement('div');
    spinner.className = 'spinner-overlay';
    spinner.innerHTML = '<div class="spinner-border text-light" role="status"><span class="visually-hidden">Loading...</span></div>';
    document.body.appendChild(spinner);
}

/**
 * Hide loading spinner
 */
function hideLoading() {
    const spinner = document.querySelector('.spinner-overlay');
    if (spinner) {
        spinner.remove();
    }
}

/**
 * Copy to clipboard
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Copied to clipboard!');
    }).catch(err => {
        console.error('Failed to copy:', err);
    });
}

/**
 * Slug generator
 */
function generateSlug(text) {
    return text
        .toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

// Auto-generate slug from title
document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.querySelector('input[name="title"]');
    const slugInput = document.querySelector('input[name="slug"]');

    if (titleInput && slugInput) {
        titleInput.addEventListener('input', function() {
            if (!slugInput.value || slugInput.dataset.autoGenerate !== 'false') {
                slugInput.value = generateSlug(titleInput.value);
            }
        });

        slugInput.addEventListener('input', function() {
            slugInput.dataset.autoGenerate = 'false';
        });
    }
});
