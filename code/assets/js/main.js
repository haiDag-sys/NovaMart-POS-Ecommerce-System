document.addEventListener('DOMContentLoaded', function () {
    setupAddToCartButtons();
    setupScrollTopButton();
    setupConfirmLinks();
    setupPrintButtons();
    setupCustomerNotifications();
    setupCategoryRibbonOverflow();
    setupAdminRealtimeOrders();
});

function setupAddToCartButtons() {
    document.addEventListener('click', function (event) {
        const button = event.target.closest('.add-to-cart-btn');
        if (!button) return;

        event.preventDefault();

        const formData = new FormData();
        formData.append('sp_id', button.getAttribute('data-id') || '');
        formData.append('sp_ten', button.getAttribute('data-ten') || '');
        formData.append('sp_giaban', button.getAttribute('data-gia') || '');
        formData.append('sp_hinhanh', button.getAttribute('data-hinh') || '');

        fetch('add_to_cart_ajax.php', {
            method: 'POST',
            body: formData
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (data.success) {
                    const badge = document.getElementById('cart-badge');
                    if (badge) {
                        badge.innerText = data.total_items;
                        badge.style.display = 'inline-block';
                    }

                    const originalHTML = button.innerHTML;
                    button.innerHTML = '<i class="fas fa-check"></i> Đã thêm';
                    button.disabled = true;

                    setTimeout(function () {
                        button.innerHTML = originalHTML;
                        button.disabled = false;
                    }, 1000);
                    return;
                }

                if (data.requires_login && data.redirect) {
                    alert(data.message || 'Vui lòng đăng nhập trước khi mua hàng.');
                    window.location.href = data.redirect;
                    return;
                }

                if (data.message) {
                    alert(data.message);
                }
            })
            .catch(function (error) {
                console.error('Lỗi thêm giỏ hàng:', error);
            });
    });
}

function setupScrollTopButton() {
    const topButton = document.getElementById('top-button');
    if (!topButton) return;

    const toggleButton = function () {
        topButton.style.display = window.scrollY > 250 ? 'inline-flex' : 'none';
    };

    toggleButton();
    window.addEventListener('scroll', toggleButton);
}

function setupConfirmLinks() {
    document.addEventListener('click', function (event) {
        const element = event.target.closest('[data-confirm-message]');
        if (!element) return;

        const message = element.getAttribute('data-confirm-message') || 'Bạn có chắc không?';
        if (!window.confirm(message)) {
            event.preventDefault();
        }
    });
}

function setupPrintButtons() {
    document.addEventListener('click', function (event) {
        const element = event.target.closest('[data-print-window="true"]');
        if (!element) return;

        event.preventDefault();
        window.print();
    });
}


function setupCustomerNotifications() {
    const config = document.getElementById('customer-notification-config');
    if (!config) return;

    const endpoint = config.getAttribute('data-endpoint') || 'notifications_poll.php';
    const markReadEndpoint = config.getAttribute('data-mark-read-endpoint') || 'notifications_mark_read.php';
    const pollInterval = parseInt(config.getAttribute('data-poll-interval') || '4000', 10);
    const badge = document.getElementById('customer-notification-badge');
    const list = document.getElementById('customer-notification-list');
    const markReadButton = document.getElementById('customer-mark-notifications-read');
    const toggle = document.getElementById('customer-notification-toggle');

    if (!badge || !list || !toggle) return;

    let lastSignature = '';
    let isFetching = false;

    const escapeHtml = function (value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };

    const renderNotifications = function (data) {
        const unreadCount = Number(data.unread_count || 0);
        const items = Array.isArray(data.notifications) ? data.notifications : [];

        if (unreadCount > 0) {
            badge.textContent = unreadCount;
            badge.classList.remove('d-none');
        } else {
            badge.textContent = '0';
            badge.classList.add('d-none');
        }

        if (markReadButton) {
            markReadButton.classList.toggle('d-none', items.length === 0);
        }

        if (items.length === 0) {
            list.innerHTML = '<div class="px-3 py-4 text-center text-muted small">Chưa có thông báo nào.</div>';
            return;
        }

        const html = items.map(function (item) {
            const unreadClass = Number(item.tb_dadoc || 0) === 0 ? ' notification-item-unread' : '';
            const link = escapeHtml(item.link || 'profile.php');
            const message = escapeHtml(item.tb_noidung || '');
            const time = escapeHtml(item.tb_thoigian_hienthi || '');
            return '<a href="' + link + '" class="dropdown-item px-3 py-3 border-bottom notification-item' + unreadClass + '">'
                + '<div class="small text-dark mb-1">' + message + '</div>'
                + '<div class="small text-muted">' + time + '</div>'
                + '</a>';
        }).join('');

        list.innerHTML = html;
    };

    const fetchNotifications = function (forceRender) {
        if (isFetching) return;
        isFetching = true;

        fetch(endpoint, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Cache-Control': 'no-cache'
            },
            credentials: 'same-origin'
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Không thể tải thông báo');
                }
                return response.json();
            })
            .then(function (data) {
                if (!data || !data.success) {
                    return;
                }

                const signature = JSON.stringify({
                    unread_count: data.unread_count || 0,
                    notifications: data.notifications || []
                });

                if (forceRender || signature !== lastSignature) {
                    renderNotifications(data);
                    lastSignature = signature;
                }
            })
            .catch(function (error) {
                console.error('Lỗi tải thông báo:', error);
            })
            .finally(function () {
                isFetching = false;
            });
    };

    fetchNotifications(true);
    window.setInterval(function () {
        if (document.visibilityState === 'visible') {
            fetchNotifications(false);
        }
    }, Math.max(3000, pollInterval));

    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') {
            fetchNotifications(true);
        }
    });

    toggle.addEventListener('click', function () {
        window.setTimeout(function () {
            fetchNotifications(true);
        }, 100);
    });

    if (markReadButton) {
        markReadButton.addEventListener('click', function () {
            fetch(markReadEndpoint, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                credentials: 'same-origin',
                body: 'mark_all=1'
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Không thể đánh dấu đã xem');
                    }
                    return response.json();
                })
                .then(function () {
                    fetchNotifications(true);
                })
                .catch(function (error) {
                    console.error('Lỗi đánh dấu đã xem:', error);
                });
        });
    }
}


function setupCategoryRibbonOverflow() {
    const ribbon = document.querySelector('[data-category-ribbon]');
    const moreWrapper = document.querySelector('[data-category-more-wrapper]');
    const moreMenu = document.querySelector('[data-category-more-menu]');

    if (!ribbon || !moreWrapper || !moreMenu) return;

    const chips = Array.from(ribbon.querySelectorAll('[data-category-chip]'));
    if (chips.length === 0) return;

    const buildMoreItem = function (chip) {
        const link = document.createElement('a');
        link.className = 'dropdown-item category-more-item';
        link.href = chip.getAttribute('data-category-url') || '#';

        const imageUrl = chip.getAttribute('data-category-image') || '';
        const iconClass = chip.getAttribute('data-category-icon-class') || 'fas fa-tags';
        const iconColor = chip.getAttribute('data-category-icon-color') || '#6366f1';
        const name = chip.getAttribute('data-category-name') || '';

        if (imageUrl) {
            const img = document.createElement('img');
            img.src = imageUrl;
            img.alt = name;
            img.className = 'category-ribbon-image';
            link.appendChild(img);
        } else {
            const icon = document.createElement('span');
            icon.className = 'category-ribbon-icon';
            icon.style.color = iconColor;
            icon.innerHTML = '<i class="' + iconClass + '"></i>';
            link.appendChild(icon);
        }

        const text = document.createElement('span');
        text.className = 'category-ribbon-text';
        text.textContent = name;
        link.appendChild(text);

        return link;
    };

    const recalculate = function () {
        moreMenu.innerHTML = '';
        chips.forEach(function (chip) {
            chip.classList.remove('d-none');
        });
        moreWrapper.classList.add('d-none');
        moreWrapper.style.visibility = 'hidden';
        moreWrapper.classList.remove('d-none');

        const ribbonStyles = window.getComputedStyle(ribbon);
        const gap = parseFloat(ribbonStyles.gap || ribbonStyles.columnGap || '0') || 0;
        const availableWidth = ribbon.clientWidth;
        const moreWidth = moreWrapper.offsetWidth;

        moreWrapper.classList.add('d-none');
        moreWrapper.style.visibility = '';

        let usedWidth = 0;
        let hiddenStart = chips.length;

        chips.forEach(function (chip, index) {
            const chipWidth = chip.offsetWidth;
            const reserveMore = index < chips.length - 1 ? (moreWidth + gap) : 0;
            if (usedWidth + chipWidth + reserveMore <= availableWidth) {
                usedWidth += chipWidth + gap;
            } else if (hiddenStart === chips.length) {
                hiddenStart = index;
            }
        });

        if (hiddenStart >= chips.length) {
            return;
        }

        chips.forEach(function (chip, index) {
            if (index >= hiddenStart) {
                chip.classList.add('d-none');
                moreMenu.appendChild(buildMoreItem(chip));
            }
        });

        moreWrapper.classList.remove('d-none');
    };

    let resizeTimer = null;
    recalculate();
    window.addEventListener('resize', function () {
        window.clearTimeout(resizeTimer);
        resizeTimer = window.setTimeout(recalculate, 120);
    });
};


function setupAdminRealtimeOrders() {
    const config = document.getElementById('admin-live-orders-config');
    if (!config) return;

    const endpoint = config.getAttribute('data-endpoint') || 'orders_poll.php';
    const scope = config.getAttribute('data-scope') || 'orders';
    const status = config.getAttribute('data-status') || '';
    const from = config.getAttribute('data-from') || '';
    const to = config.getAttribute('data-to') || '';
    const pollInterval = parseInt(config.getAttribute('data-poll-interval') || '4000', 10);
    const badge = document.getElementById('admin-orders-badge');
    const ordersTbody = document.getElementById(scope === 'dashboard' ? 'dashboard-orders-tbody' : 'admin-orders-tbody');
    const revenueNode = document.querySelector('[data-dashboard-revenue]');
    const pendingNode = document.querySelector('[data-dashboard-pending]');
    const totalNode = document.querySelector('[data-dashboard-total]');

    let latestOrderId = parseInt(config.getAttribute('data-latest-order-id') || '0', 10);
    let isFetching = false;
    let hasInitialized = false;

    const escapeHtml = function (value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };

    const updatePendingBadge = function (count) {
        if (!badge) return;
        if (count > 0) {
            badge.textContent = String(count);
            badge.classList.remove('d-none');
        } else {
            badge.textContent = '0';
            badge.classList.add('d-none');
        }
    };

    const createOrdersRowHtml = function (row) {
        return '<tr>'
            + '<td class="text-center fw-bold">#' + row.hd_id + '</td>'
            + '<td>' + row.hd_ngaylap_hienthi + '</td>'
            + '<td>' + escapeHtml(row.nguon_don || '') + '</td>'
            + '<td class="text-center"><span class="badge ' + escapeHtml(row.hd_trangthai_badge || '') + ' px-3 py-2">' + escapeHtml(row.hd_trangthai_hienthi || '') + '</span></td>'
            + '<td class="fw-bold text-success">' + escapeHtml(row.hd_tongtien_hienthi || '0') + ' đ</td>'
            + '<td class="text-center"><span class="badge bg-info text-dark px-3 py-2">' + escapeHtml(row.hd_hinhthuctt || '') + '</span></td>'
            + '<td class="text-center"><a href="' + escapeHtml(row.order_details_url || '#') + '" class="btn btn-sm btn-primary shadow-sm"><i class="fas fa-eye"></i> Xem</a></td>'
            + '</tr>';
    };

    const createDashboardRowHtml = function (row) {
        return '<tr>'
            + '<td class="ps-4 fw-bold text-secondary">#' + row.hd_id + '</td>'
            + '<td>' + row.hd_ngaylap_hienthi + '</td>'
            + '<td>' + escapeHtml(row.nguon_don || '') + '</td>'
            + '<td class="text-center"><span class="badge ' + escapeHtml(row.hd_trangthai_badge || '') + '">' + escapeHtml(row.hd_trangthai_hienthi || '') + '</span></td>'
            + '<td class="text-end fw-bold text-success">' + escapeHtml(row.hd_tongtien_hienthi || '0') + ' đ</td>'
            + '<td class="text-center pe-4"><a href="' + escapeHtml(row.order_details_url || '#') + '" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm">Xem</a></td>'
            + '</tr>';
    };

    const renderOrders = function (rows) {
        if (!ordersTbody) return;
        if (!Array.isArray(rows) || rows.length === 0) {
            if (scope === 'dashboard') {
                ordersTbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-5"><i class="fas fa-box-open fa-2x mb-3 text-light"></i><br>Không có giao dịch nào trong khoảng thời gian này.</td></tr>';
            } else {
                ordersTbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">Chưa có đơn hàng nào.</td></tr>';
            }
            return;
        }

        const builder = scope === 'dashboard' ? createDashboardRowHtml : createOrdersRowHtml;
        ordersTbody.innerHTML = rows.map(builder).join('');
    };

    const updateDashboardSummary = function (summary) {
        if (!summary) return;
        if (revenueNode) revenueNode.textContent = summary.tong_doanh_thu_hienthi || '0';
        if (pendingNode) pendingNode.textContent = String(summary.don_online_dang_xu_ly || 0);
        if (totalNode) totalNode.textContent = String(summary.tong_giao_dich || 0);
    };

    const showNewOrderToast = function (newOrderId) {
        if (!newOrderId || !document.body) return;

        const toast = document.createElement('div');
        toast.className = 'alert alert-success shadow';
        toast.style.position = 'fixed';
        toast.style.top = '18px';
        toast.style.right = '18px';
        toast.style.zIndex = '1080';
        toast.style.minWidth = '280px';
        toast.innerHTML = '<strong>Đơn hàng mới!</strong><br>Khách hàng vừa tạo đơn #' + newOrderId + '.';
        document.body.appendChild(toast);

        setTimeout(function () {
            toast.remove();
        }, 3200);
    };

    const fetchUpdates = function () {
        if (isFetching) return;
        isFetching = true;

        const url = new URL(endpoint, window.location.href);
        if (!url.searchParams.has('scope')) {
            url.searchParams.set('scope', scope);
        }
        if (status !== '') url.searchParams.set('status', status);
        if (from !== '') url.searchParams.set('from', from);
        if (to !== '') url.searchParams.set('to', to);
        url.searchParams.set('_ts', String(Date.now()));

        fetch(url.toString(), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Cache-Control': 'no-cache'
            },
            credentials: 'same-origin'
        })
            .then(function (response) {
                if (!response.ok) throw new Error('Không thể tải dữ liệu đơn hàng realtime.');
                return response.json();
            })
            .then(function (data) {
                if (!data || !data.success) return;

                updatePendingBadge(Number(data.pending_online_count || 0));

                const newLatestOrderId = Number(data.latest_online_order_id || 0);
                if (hasInitialized && newLatestOrderId > latestOrderId) {
                    showNewOrderToast(newLatestOrderId);
                }
                latestOrderId = Math.max(latestOrderId, newLatestOrderId);

                if (scope === 'dashboard') {
                    updateDashboardSummary(data.summary || null);
                    renderOrders(Array.isArray(data.transactions) ? data.transactions : []);
                } else {
                    renderOrders(Array.isArray(data.orders) ? data.orders : []);
                }

                hasInitialized = true;
            })
            .catch(function (error) {
                console.error(error);
            })
            .finally(function () {
                isFetching = false;
            });
    };

    fetchUpdates();
    window.setInterval(function () {
        if (document.visibilityState === 'visible') {
            fetchUpdates();
        }
    }, Math.max(3000, pollInterval));
}
