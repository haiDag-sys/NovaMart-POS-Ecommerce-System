document.addEventListener('DOMContentLoaded', function () {
    const avatarTrigger = document.getElementById('avatar-trigger');
    const avatarInput = document.getElementById('file-avatar');
    const avatarForm = document.getElementById('form-avatar');

    if (avatarTrigger && avatarInput) {
        avatarTrigger.addEventListener('click', function () {
            avatarInput.click();
        });
    }

    if (avatarInput && avatarForm) {
        avatarInput.addEventListener('change', function () {
            if (avatarInput.files && avatarInput.files.length > 0) {
                avatarForm.submit();
            }
        });
    }

    if (window.location.hash) {
        const trigger = document.querySelector('[href="' + window.location.hash + '"]');
        if (trigger && window.bootstrap && bootstrap.Tab) {
            const tab = new bootstrap.Tab(trigger);
            tab.show();
        }
    }
});
