// Initialize all charts when the document is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeRealTimeChart();
    initializeWaterUsageChart();
    initializeCropHealthChart();
    initializeWeatherChart();
    initializeCostChart();
});

// Real-time sensor data chart
function initializeRealTimeChart() {
    const ctx = document.getElementById('realTimeChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['12:00', '12:05', '12:10', '12:15', '12:20', '12:25'],
            datasets: [{
                label: 'Soil Moisture (%)',
                data: [65, 67, 70, 68, 72, 71],
                borderColor: '#2ecc71',
                tension: 0.4
            }, {
                label: 'Temperature (°C)',
                data: [25, 26, 25, 27, 26, 28],
                borderColor: '#e74c3c',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });
}

// Water usage trends chart
function initializeWaterUsageChart() {
    const ctx = document.getElementById('waterUsageChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Water Usage (Liters)',
                data: [300, 450, 320, 500, 380, 420, 290],
                backgroundColor: '#3498db'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });
}

// Crop health distribution chart
function initializeCropHealthChart() {
    const ctx = document.getElementById('cropHealthChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Excellent', 'Good', 'Fair', 'Poor'],
            datasets: [{
                data: [45, 30, 15, 10],
                backgroundColor: [
                    '#2ecc71',
                    '#3498db',
                    '#f1c40f',
                    '#e74c3c'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right',
                }
            }
        }
    });
}

// Weather impact analysis chart
function initializeWeatherChart() {
    const ctx = document.getElementById('weatherChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            datasets: [{
                label: 'Rainfall (mm)',
                data: [45, 30, 60, 25],
                borderColor: '#3498db',
                yAxisID: 'y'
            }, {
                label: 'Irrigation Needed (L)',
                data: [200, 350, 150, 400],
                borderColor: '#2ecc71',
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
}

// Cost analysis chart
function initializeCostChart() {
    const ctx = document.getElementById('costChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Water Cost',
                data: [1200, 1100, 900, 850, 750, 700],
                backgroundColor: '#3498db'
            }, {
                label: 'Energy Cost',
                data: [800, 750, 700, 650, 600, 550],
                backgroundColor: '#e74c3c'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Monthly Resource Costs'
                }
            },
            scales: {
                x: {
                    stacked: true,
                },
                y: {
                    stacked: true
                }
            }
        }
    });
}

// Update real-time data every 5 seconds
setInterval(() => {
    // Simulate new sensor data
    const newMoisture = 60 + Math.random() * 20;
    const newTemp = 25 + Math.random() * 5;
    
    // Update charts with new data
    // In a real application, this would fetch data from your backend
}, 5000);
