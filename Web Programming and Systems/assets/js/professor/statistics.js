
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart === 'undefined') {
        document.getElementById('statistics-error').style.display = 'block';
        document.getElementById('error-message').textContent = 'Η βιβλιοθήκη Chart.js δεν φορτώθηκε σωστά.';
        document.getElementById('statistics-loader').style.display = 'none';
        return;
    }
    
    loadStatisticsData();
});

function loadStatisticsData() {
    document.getElementById('statistics-loader').style.display = 'block';
    document.getElementById('statistics-content').style.display = 'none';
    document.getElementById('statistics-error').style.display = 'none';
    
    fetch('../../api/professor/statistics_data.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                document.getElementById('statistics-loader').style.display = 'none';
                document.getElementById('statistics-content').style.display = 'block';
                
                createCharts(data.data);
                
                populateStatisticsTable(data.data);
            } else {
                document.getElementById('statistics-loader').style.display = 'none';
                document.getElementById('statistics-error').style.display = 'block';
                document.getElementById('error-message').textContent = data.message || 'Προέκυψε σφάλμα κατά τη φόρτωση των στατιστικών.';
            }
        })
        .catch(error => {
            document.getElementById('statistics-loader').style.display = 'none';
            document.getElementById('statistics-error').style.display = 'block';
            document.getElementById('error-message').textContent = 'Προέκυψε σφάλμα κατά την επικοινωνία με τον διακομιστή: ' + error.message;
            console.error('Error fetching statistics data:', error);
        });
}

function createCharts(data) {
    Chart.defaults.font.family = "'Roboto', 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif";
    Chart.defaults.font.size = 14;
    Chart.defaults.color = '#555';
    
    createThesisCountChart(data);
    
    createThesisDurationChart(data);
    
    createThesisGradeChart(data);
}

function createThesisCountChart(data) {
    const ctx = document.getElementById('thesisCountChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Επιβλέπων', 'Μέλος Τριμελούς'],
            datasets: [{
                label: 'Πλήθος Διπλωματικών',
                data: [data.supervised.count, data.committee_member.count],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(75, 192, 192, 0.7)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Πλήθος: ${context.raw}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
}

function createThesisDurationChart(data) {
    const ctx = document.getElementById('thesisDurationChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Επιβλέπων', 'Μέλος Τριμελούς'],
            datasets: [{
                label: 'Μέσος Χρόνος (μήνες)',
                data: [data.supervised.avg_duration, data.committee_member.avg_duration],
                backgroundColor: [
                    'rgba(255, 159, 64, 0.7)',
                    'rgba(255, 205, 86, 0.7)'
                ],
                borderColor: [
                    'rgba(255, 159, 64, 1)',
                    'rgba(255, 205, 86, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Μέσος χρόνος: ${context.raw} μήνες`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function createThesisGradeChart(data) {
    const ctx = document.getElementById('thesisGradeChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Επιβλέπων', 'Μέλος Τριμελούς'],
            datasets: [{
                label: 'Μέσος Βαθμός',
                data: [data.supervised.avg_grade, data.committee_member.avg_grade],
                backgroundColor: [
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 99, 132, 0.7)'
                ],
                borderColor: [
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 99, 132, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Μέσος βαθμός: ${context.raw}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 10,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}

function populateStatisticsTable(data) {
    const tableBody = document.getElementById('statistics-table-body');
    tableBody.innerHTML = '';
    
    const supervisedRow = document.createElement('tr');
    supervisedRow.innerHTML = `
        <td><strong>Επιβλέπων</strong></td>
        <td>${data.supervised.count}</td>
        <td>${data.supervised.avg_duration}</td>
        <td>${data.supervised.avg_grade}</td>
    `;
    tableBody.appendChild(supervisedRow);
    
    const committeeMemberRow = document.createElement('tr');
    committeeMemberRow.innerHTML = `
        <td><strong>Μέλος Τριμελούς</strong></td>
        <td>${data.committee_member.count}</td>
        <td>${data.committee_member.avg_duration}</td>
        <td>${data.committee_member.avg_grade}</td>
    `;
    tableBody.appendChild(committeeMemberRow);
    
    const totalCount = data.supervised.count + data.committee_member.count;
    const totalRow = document.createElement('tr');
    totalRow.className = 'table-secondary';
    totalRow.innerHTML = `
        <td><strong>Σύνολο</strong></td>
        <td><strong>${totalCount}</strong></td>
        <td>-</td>
        <td>-</td>
    `;
    tableBody.appendChild(totalRow);
}
