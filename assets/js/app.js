/**
 * Sagar Advertising CRM - Core Application Scripts
 */

// Global AJAX Helper
async function apiRequest(url, method = 'GET', data = null) {
    const headers = {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    };

    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (csrfMeta) {
        headers['X-CSRF-TOKEN'] = csrfMeta.getAttribute('content');
    }

    const options = {
        method: method,
        headers: headers
    };

    if (data) {
        if (data instanceof FormData) {
            options.body = data;
        } else {
            headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(data);
        }
    }

    try {
        const response = await fetch(url, options);
        const json = await response.json();
        return { ok: response.ok, status: response.status, data: json };
    } catch (err) {
        console.error('API Request Error:', err);
        return { ok: false, status: 500, error: err.message };
    }
}

// Toast Notifications
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const toastId = 'toast_' + Math.random().toString(36).substr(2, 9);
    const bgClass = type === 'success' ? 'bg-success' : (type === 'error' ? 'bg-danger' : 'bg-warning');
    const icon = type === 'success' ? 'bi-check-circle-fill' : (type === 'error' ? 'bi-x-circle-fill' : 'bi-exclamation-triangle-fill');

    const html = `
        <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i class="bi ${icon}"></i>
                    <span>${message}</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', html);
    const elem = document.getElementById(toastId);
    if (window.bootstrap && bootstrap.Toast) {
        const toast = new bootstrap.Toast(elem, { delay: 4000 });
        toast.show();
        elem.addEventListener('hidden.bs.toast', () => elem.remove());
    }
}

// Global Live Search Dropdown
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('globalSearchInput');
    const resultsBox = document.getElementById('globalSearchResults');

    if (searchInput && resultsBox) {
        let debounceTimer;

        searchInput.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            const query = e.target.value.trim();

            if (query.length < 2) {
                resultsBox.style.display = 'none';
                resultsBox.innerHTML = '';
                return;
            }

            debounceTimer = setTimeout(async () => {
                const res = await apiRequest(`${window.APP_URL || ''}/api/search?q=${encodeURIComponent(query)}`);
                if (res.ok && res.data && res.data.length > 0) {
                    let html = '';
                    res.data.forEach(item => {
                        html += `
                            <a href="${item.url}" class="search-result-item">
                                <div>
                                    <div class="fw-bold text-dark">${item.title}</div>
                                    <div class="small text-muted">${item.subtitle}</div>
                                </div>
                                <span class="badge ${item.badge_class || 'bg-light text-dark'}">${item.type}</span>
                            </a>
                        `;
                    });
                    resultsBox.innerHTML = html;
                    resultsBox.style.display = 'block';
                } else {
                    resultsBox.innerHTML = '<div class="p-3 text-muted text-center small">No matches found for "' + query + '"</div>';
                    resultsBox.style.display = 'block';
                }
            }, 250);
        });

        // Close on click outside
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
                resultsBox.style.display = 'none';
            }
        });
    }

    // Mobile Sidebar Drawer Management
    const toggleBtn = document.getElementById('mobileSidebarToggle');
    const closeBtn = document.getElementById('mobileSidebarClose');
    const backdrop = document.getElementById('sidebarBackdrop');
    const sidebar = document.querySelector('.app-sidebar');

    function openSidebar() {
        if (sidebar) sidebar.classList.add('open');
        if (backdrop) backdrop.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        if (sidebar) sidebar.classList.remove('open');
        if (backdrop) backdrop.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (sidebar && sidebar.classList.contains('open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            closeSidebar();
        });
    }

    if (backdrop) {
        backdrop.addEventListener('click', () => {
            closeSidebar();
        });
    }

    // Auto-close drawer on mobile when clicking a nav link
    if (sidebar) {
        sidebar.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 992) {
                    closeSidebar();
                }
            });
        });
    }
});
