// Dashboard Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize dashboard data
    updateMetrics();
    // Update metrics every 5 minutes
    setInterval(updateMetrics, 300000);
});

function updateMetrics() {
    // In a real application, these values would come from your backend API
    // For now, we'll simulate real-time data with random variations
    
    // Update soil moisture
    const soilMoisture = 45 + Math.random() * 10;
    document.querySelector('.metric-card:nth-child(1) .metric-value').textContent = 
        `${Math.round(soilMoisture)}%`;

    // Update temperature
    const temperature = 30 + Math.random() * 5;
    document.querySelector('.metric-card:nth-child(2) .metric-value').textContent = 
        `${Math.round(temperature)}°C`;

    // Update tank level
    const tankLevel = 75 + Math.random() * 10;
    document.querySelector('.metric-card:nth-child(3) .metric-value').textContent = 
        `${Math.round(tankLevel)}%`;

    // Update energy usage
    const energyUsage = 2 + Math.random();
    document.querySelector('.metric-card:nth-child(4) .metric-value').textContent = 
        `${energyUsage.toFixed(1)} kWh`;

    // Update insights
    updateInsights();
    // Update alerts
    checkAlerts(soilMoisture, temperature, tankLevel);
}

function updateInsights() {
    const waterUsed = 450 + Math.round(Math.random() * 100);
    document.querySelector('.insight-card:nth-child(1) .insight-value').textContent = 
        `${waterUsed}L`;

    const cropHealth = Math.random() > 0.7 ? 'Excellent' : 'Good';
    document.querySelector('.insight-card:nth-child(2) .insight-value').textContent = 
        cropHealth;

    const energySaved = 2 + Math.random();
    document.querySelector('.insight-card:nth-child(3) .insight-value').textContent = 
        `${energySaved.toFixed(1)} kWh`;
}

function checkAlerts(soilMoisture, temperature, tankLevel) {
    const alertList = document.querySelector('.alert-list');
    alertList.innerHTML = ''; // Clear existing alerts

    // Check soil moisture
    if (soilMoisture < 30) {
        addAlert('critical', 'Soil Moisture Critical in Zone A', 'Just now');
    }

    // Check tank level
    if (tankLevel < 20) {
        addAlert('warning', 'Tank Water Level Below 20%', '5 minutes ago');
    }

    // Check temperature
    if (temperature > 35) {
        addAlert('warning', 'High Temperature Alert', '15 minutes ago');
    } else if (temperature >= 25 && temperature <= 30) {
        addAlert('success', 'Optimal Temperature Reached', '1 hour ago');
    }

    // Update alert count
    const alertCount = document.querySelector('.alert-count');
    const alerts = alertList.children.length;
    alertCount.textContent = `${alerts} new alert${alerts !== 1 ? 's' : ''}`;
}

function addAlert(type, message, time) {
    const alertList = document.querySelector('.alert-list');
    const alert = document.createElement('div');
    alert.className = `alert-item alert-${type}`;
    
    const icon = type === 'critical' ? 'exclamation-circle' : 
                type === 'warning' ? 'exclamation-triangle' : 'check-circle';
    
    alert.innerHTML = `
        <i class="fas fa-${icon} alert-icon"></i>
        <div class="alert-content">
            <div class="alert-title">${message}</div>
            <div class="alert-time">${time}</div>
        </div>
    `;
    
    alertList.appendChild(alert);
}
