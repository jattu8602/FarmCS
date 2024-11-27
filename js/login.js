document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.querySelector('form');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');

    // Clear any existing login state
    if (localStorage.getItem('isLoggedIn')) {
        window.location.href = 'index.html';
    }

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();

        // Clear previous errors
        clearError();

        // Get form data
        const email = emailInput.value.trim();
        const password = passwordInput.value;

        // Validate inputs
        if (!email) {
            showError('Please enter your email');
            return;
        }

        if (!isValidEmail(email)) {
            showError('Please enter a valid email address');
            return;
        }

        if (!password) {
            showError('Please enter your password');
            return;
        }

        // Get stored user data
        const userData = JSON.parse(localStorage.getItem('userData'));

        // Check if credentials match
        if (userData && userData.email === email && userData.password === password) {
            // Set login state
            localStorage.setItem('isLoggedIn', 'true');
            localStorage.setItem('currentUser', JSON.stringify(userData));

            // Show success message
            showSuccess('Login successful! Redirecting...');

            // Redirect to dashboard after a short delay
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 1000);
        } else {
            showError('Invalid email or password');
        }
    });
});

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.style.cssText = `
        color: #e74c3c;
        background-color: #fde8e7;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 15px;
        text-align: center;
        animation: fadeIn 0.3s ease-in-out;
    `;
    errorDiv.textContent = message;

    clearError();

    // Insert error message before the form
    const form = document.querySelector('form');
    form.parentNode.insertBefore(errorDiv, form);
}

function showSuccess(message) {
    const successDiv = document.createElement('div');
    successDiv.className = 'success-message';
    successDiv.style.cssText = `
        color: #27ae60;
        background-color: #e8f5e9;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 15px;
        text-align: center;
        animation: fadeIn 0.3s ease-in-out;
    `;
    successDiv.textContent = message;

    clearError();

    // Insert success message before the form
    const form = document.querySelector('form');
    form.parentNode.insertBefore(successDiv, form);
}

function clearError() {
    const existingError = document.querySelector('.error-message');
    const existingSuccess = document.querySelector('.success-message');
    
    if (existingError) {
        existingError.remove();
    }
    if (existingSuccess) {
        existingSuccess.remove();
    }
}
