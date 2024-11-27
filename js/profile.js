// Profile Management
document.addEventListener('DOMContentLoaded', function() {
    // Load profile data when the page loads
    loadProfileData();
});

function loadProfileData() {
    // Get user data from localStorage (saved during signup)
    const userData = JSON.parse(localStorage.getItem('userData')) || {};

    // Display user information
    document.getElementById('profileFirstName').textContent = userData.firstName || '-';
    document.getElementById('profileLastName').textContent = userData.lastName || '-';
    document.getElementById('profileEmail').textContent = userData.email || '-';
    document.getElementById('profileState').textContent = userData.state || '-';
    document.getElementById('profileDistrict').textContent = userData.district || '-';
    document.getElementById('profileFarmType').textContent = formatFarmType(userData.farmType) || '-';
    document.getElementById('profileCreated').textContent = userData.created || '-';
}

function formatFarmType(type) {
    if (!type) return '-';
    
    // Convert farmType value to a more readable format
    const farmTypes = {
        'crop': 'Crop Farming',
        'livestock': 'Livestock Farming',
        'mixed': 'Mixed Farming',
        'organic': 'Organic Farming',
        'greenhouse': 'Greenhouse Farming'
    };
    
    return farmTypes[type] || type;
}