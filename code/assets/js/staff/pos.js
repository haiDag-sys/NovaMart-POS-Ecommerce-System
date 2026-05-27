document.addEventListener('DOMContentLoaded', function () {
    setupProductCardSubmit();
    setupPayErrorAlert();
    setupInvoiceModalOnLoad();
});

function setupProductCardSubmit() {
    document.addEventListener('click', function (event) {
        const card = event.target.closest('.js-product-submit');
        if (!card) {
            return;
        }

        const form = card.closest('form');
        if (form) {
            form.submit();
        }
    });
}

function setupInvoiceModalOnLoad() {
    const config = document.getElementById('pos-page-config');
    const invoiceModalElement = document.getElementById('invoiceModal');

    if (!config || !invoiceModalElement || typeof bootstrap === 'undefined') {
        return;
    }

    if (config.dataset.openInvoiceModal === '1') {
        const modal = new bootstrap.Modal(invoiceModalElement);
        modal.show();
    }
}

function setupPayErrorAlert() {
    const config = document.getElementById('pos-page-config');
    if (!config) {
        return;
    }

    const message = config.dataset.payErrorMessage || '';
    if (!message) {
        return;
    }

    window.alert(message);

    if (document.activeElement && typeof document.activeElement.blur === 'function') {
        document.activeElement.blur();
    }
}
