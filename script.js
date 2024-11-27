// Logout functionality
document.getElementById('logout-btn').addEventListener('click', function(e) {
    e.preventDefault();
    localStorage.removeItem('loggedIn');
    window.location.href = 'login.html';
});

// Navigation active state
const navLinks = document.querySelectorAll('.nav-link');
navLinks.forEach(link => {
    link.addEventListener('click', function() {
        navLinks.forEach(l => l.classList.remove('active'));
        this.classList.add('active');
    });
});

// Charts initialization
const soilTrendsChart = new Chart(document.getElementById('soilTrendsChart'), {
    type: 'line',
    data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [{
            label: 'Soil Moisture (%)',
            data: [45, 42, 47, 43, 45, 41, 45],
            borderColor: '#2ecc71',
            tension: 0.4
        }, {
            label: 'Temperature (°C)',
            data: [24, 25, 23, 24, 26, 25, 24],
            borderColor: '#e74c3c',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

const waterUsageChart = new Chart(document.getElementById('waterUsageChart'), {
    type: 'bar',
    data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [{
            label: 'Water Usage (L)',
            data: [250, 230, 245, 260, 240, 235, 250],
            backgroundColor: '#3498db'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Control buttons functionality
document.querySelectorAll('.control-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const action = this.textContent.toLowerCase();
        if (action.includes('start')) {
            alert('Starting irrigation system...');
        } else if (action.includes('stop')) {
            alert('Stopping irrigation system...');
        } else if (action.includes('schedule')) {
            alert('Opening scheduling interface...');
        }
    });
});

// Search functionality
const searchInput = document.querySelector('.search-bar input');
searchInput.addEventListener('input', function() {
    // Add search functionality here
    console.log('Searching for:', this.value);
});

// User menu toggle
const userProfile = document.querySelector('.user-profile');
const userMenu = document.querySelector('.user-menu');

userProfile.addEventListener('click', function() {
    userMenu.style.display = userMenu.style.display === 'block' ? 'none' : 'block';
});

// Close user menu when clicking outside
document.addEventListener('click', function(e) {
    if (!userProfile.contains(e.target)) {
        userMenu.style.display = 'none';
    }
});

// Alert banner close functionality
const alertBanner = document.querySelector('.alert-banner');
if (alertBanner) {
    const closeBtn = document.createElement('button');
    closeBtn.className = 'alert-close';
    closeBtn.innerHTML = '×';
    alertBanner.appendChild(closeBtn);

    closeBtn.addEventListener('click', function() {
        alertBanner.style.display = 'none';
    });
}
