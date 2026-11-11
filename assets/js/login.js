// PASSWORD VISIBILITY TOGGLE

const passwordInput = document.getElementById('password');
const togglePassword = document.getElementById('togglePassword');

if (passwordInput && togglePassword) {
    togglePassword.addEventListener('click', function () {

        const isPasswordHidden = passwordInput.type === 'password';

        passwordInput.type = isPasswordHidden ? 'text' : 'password';

        togglePassword.textContent = isPasswordHidden ? 'Hide' : 'Show';

        togglePassword.setAttribute(
            'aria-label',
            isPasswordHidden ? 'Hide password' : 'Show password'
        );

        togglePassword.setAttribute(
            'aria-pressed',
            String(isPasswordHidden)
        );
    });
}

// PREVENT DUPLICATE LOGIN SUBMISSIONS

const loginForm = document.querySelector('.login-form');
const loginButton = document.querySelector('.login-button');

if (loginForm && loginButton) {
    loginForm.addEventListener('submit', function () {

        if (!loginForm.checkValidity()) {
            return;
        }

        loginButton.disabled = true;
        loginButton.textContent = 'Signing In...';
    });
}

// SMART LOGIN FIELD AUTOFOCUS

const emailInput = document.getElementById('email');

if (emailInput && !emailInput.value.trim()) {
    emailInput.focus();
}