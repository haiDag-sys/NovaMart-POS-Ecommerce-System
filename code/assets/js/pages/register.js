document.addEventListener('DOMContentLoaded', function () {
    const pass = document.getElementById('matkhau');
    const rePass = document.getElementById('re_matkhau');
    const message = document.getElementById('check_match');
    const btnSubmit = document.getElementById('btnSubmit');

    if (!pass || !rePass || !message || !btnSubmit) {
        return;
    }

    function validatePassword() {
        if (rePass.value === '') {
            message.innerHTML = '';
            btnSubmit.disabled = false;
            return;
        }

        if (pass.value === rePass.value) {
            message.innerHTML = '<i class="fas fa-check-circle me-1"></i> Mật khẩu đã khớp';
            message.style.color = '#28a745';
            btnSubmit.disabled = false;
        } else {
            message.innerHTML = '<i class="fas fa-times-circle me-1"></i> Mật khẩu không khớp!';
            message.style.color = '#dc3545';
            btnSubmit.disabled = true;
        }
    }

    rePass.addEventListener('keyup', validatePassword);
    pass.addEventListener('keyup', validatePassword);
});
