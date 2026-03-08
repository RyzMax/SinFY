document.addEventListener('DOMContentLoaded', function() {
 
    const uploadsCtx = document.getElementById('uploadsChart').getContext('2d');
    const uploadsData = window.dashboardData.uploadsByDay.reverse(); 
    
    new Chart(uploadsCtx, {
        type: 'line',
        data: {
            labels: uploadsData.map(item => item.day),
            datasets: [{
                label: 'Загрузки',
                data: uploadsData.map(item => item.count),
                borderColor: '#d32f2f',
                backgroundColor: 'rgba(211, 47, 47, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });


    const genresCtx = document.getElementById('genresChart').getContext('2d');
    const genresData = window.dashboardData.genres;
    
    new Chart(genresCtx, {
        type: 'doughnut',
        data: {
            labels: genresData.map(item => item.genre),
            datasets: [{
                data: genresData.map(item => item.count),
                backgroundColor: ['#d32f2f', '#f44336', '#ff5722', '#ff9800', '#ffc107']
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });
});
