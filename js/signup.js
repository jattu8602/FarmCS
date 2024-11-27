document.addEventListener('DOMContentLoaded', function() {
    const signupForm = document.querySelector('form');

    signupForm.addEventListener('submit', function(e) {
        e.preventDefault();

        // Get form data
        const formData = {
            firstName: document.getElementById('firstName').value,
            lastName: document.getElementById('lastName').value,
            email: document.getElementById('email').value,
            state: document.getElementById('state').value,
            district: document.getElementById('district').value,
            farmType: document.getElementById('farmType').value,
            created: new Date().toLocaleString()
        };

        // Save user data to localStorage
        localStorage.setItem('userData', JSON.stringify(formData));

        // Save login state
        localStorage.setItem('isLoggedIn', 'true');

        // Redirect to dashboard
        window.location.href = 'index.html';
    });
});
