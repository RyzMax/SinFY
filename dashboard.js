
const uploadsCtx = document.getElementById('uploadsChart').getContext('2d');
new Chart(uploadsCtx, {
    type: 'line',
    data: {
        labels: ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
        datasets: [{ label: 'Загрузки', data: [12, 19, 3, 5, 2, 3], borderColor: '#d32f2f', tension: 0.4 }]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
});


const genresCtx = document.getElementById('genresChart').getContext('2d');
new Chart(genresCtx, {
    type: 'doughnut',
    data: {
        labels: ['Rock', 'Pop', 'Hip-Hop', 'Electronic', 'Jazz'],
        datasets: [{ data: [30, 25, 20, 15, 10], backgroundColor: ['#d32f2f', '#f44336', '#ff5722', '#ff9800', '#ffc107'] }]
    },
    options: { responsive: true }
});
