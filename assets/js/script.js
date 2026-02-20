/* ============================================================
   JobZee - Main JavaScript
   ============================================================ */

document.addEventListener('DOMContentLoaded', function() {

    // ---- Mobile nav toggle ----
    const navToggle = document.getElementById('navToggle');
    const mainNav   = document.querySelector('.main-nav');
    if (navToggle && mainNav) {
        navToggle.addEventListener('click', function() {
            mainNav.classList.toggle('open');
        });
        // Close on outside click
        document.addEventListener('click', function(e) {
            if (!navToggle.contains(e.target) && !mainNav.contains(e.target)) {
                mainNav.classList.remove('open');
            }
        });
    }

    // ---- Auto-dismiss alerts ----
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() { alert.remove(); }, 500);
        }, 5000);
    });

    // ---- Password strength indicator ----
    const passwordInput = document.getElementById('password');
    const strengthBar   = document.querySelector('.password-strength-bar');
    const strengthText  = document.getElementById('strengthText');
    if (passwordInput && strengthBar) {
        passwordInput.addEventListener('input', function() {
            const val = this.value;
            const score = checkPasswordStrength(val);
            const colors = ['', '#ef4444', '#f59e0b', '#3b82f6', '#10b981'];
            const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
            strengthBar.style.width = (score * 25) + '%';
            strengthBar.style.background = colors[score];
            if (strengthText) strengthText.textContent = score > 0 ? labels[score] : '';
        });
    }

    function checkPasswordStrength(pwd) {
        if (!pwd) return 0;
        let score = 0;
        if (pwd.length >= 8)  score++;
        if (/[A-Z]/.test(pwd)) score++;
        if (/[0-9]/.test(pwd)) score++;
        if (/[^A-Za-z0-9]/.test(pwd)) score++;
        return score;
    }

    // ---- Confirm delete dialogs ----
    const deleteLinks = document.querySelectorAll('[data-confirm]');
    deleteLinks.forEach(function(el) {
        el.addEventListener('click', function(e) {
            const msg = this.dataset.confirm || 'Are you sure you want to delete this?';
            if (!confirm(msg)) {
                e.preventDefault();
            }
        });
    });

    // ---- Password visibility toggle ----
    const eyeBtns = document.querySelectorAll('.btn-eye');
    eyeBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const target = document.getElementById(this.dataset.target);
            if (target) {
                target.type = target.type === 'password' ? 'text' : 'password';
                this.textContent = target.type === 'password' ? '👁' : '🙈';
            }
        });
    });

    // ---- Client-side form validation ----
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            let valid = true;
            // Remove existing errors
            form.querySelectorAll('.form-error').forEach(el => el.remove());
            form.querySelectorAll('.form-control').forEach(el => el.classList.remove('error'));

            const required = form.querySelectorAll('[required]');
            required.forEach(function(field) {
                if (!field.value.trim()) {
                    valid = false;
                    field.classList.add('error');
                    const err = document.createElement('div');
                    err.className = 'form-error';
                    err.textContent = (field.dataset.label || field.name || 'This field') + ' is required.';
                    field.parentNode.insertBefore(err, field.nextSibling);
                }
            });

            // Email validation
            const emailField = form.querySelector('input[type="email"]');
            if (emailField && emailField.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailField.value)) {
                valid = false;
                emailField.classList.add('error');
                const err = document.createElement('div');
                err.className = 'form-error';
                err.textContent = 'Please enter a valid email address.';
                emailField.parentNode.insertBefore(err, emailField.nextSibling);
            }

            // Password confirm
            const pw  = form.querySelector('input[name="password"]');
            const pw2 = form.querySelector('input[name="confirm_password"]');
            if (pw && pw2 && pw.value && pw.value !== pw2.value) {
                valid = false;
                pw2.classList.add('error');
                const err = document.createElement('div');
                err.className = 'form-error';
                err.textContent = 'Passwords do not match.';
                pw2.parentNode.insertBefore(err, pw2.nextSibling);
            }

            if (!valid) e.preventDefault();
        });
    });

    // ---- Role tabs on register page ----
    const roleTabs = document.querySelectorAll('.role-tab');
    const roleInput = document.getElementById('roleInput');
    roleTabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            roleTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            if (roleInput) roleInput.value = this.dataset.role;
        });
    });

    // ---- File upload display ----
    const resumeInput = document.getElementById('resume');
    const fileLabel   = document.getElementById('fileLabel');
    if (resumeInput && fileLabel) {
        resumeInput.addEventListener('change', function() {
            if (this.files.length) {
                fileLabel.textContent = '📎 ' + this.files[0].name;
            }
        });
    }

    // ---- AJAX search (live filter on jobs page) ----
    const searchInput = document.getElementById('searchKeyword');
    let searchTimer;
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                document.getElementById('searchForm')?.submit();
            }, 600);
        });
    }

    // ---- Status select auto-submit ----
    const statusSelects = document.querySelectorAll('.status-select[data-autosubmit]');
    statusSelects.forEach(function(sel) {
        sel.addEventListener('change', function() {
            this.closest('form')?.submit();
        });
    });

});
