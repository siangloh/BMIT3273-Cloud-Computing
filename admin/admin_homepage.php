<?php
require_once '../_base.php';
$_title = "Homepage";
$_pageTitle = "Admin Dashboard";
include 'admin_header.php';

// $hotSales = $_db->query("SELECT p.*, IFNULL((SELECT SUM(qty) FROM `order` o JOIN order_record r ON o.orderID = r.orderID WHERE productID = p.productID AND DATE(o.orderDate) = CURRENT_DATE()), 0) totalSales FROM product p WHERE status = '1' HAVING totalSales > 0 ORDER BY totalSales DESC LIMIT 5");

// $monlySales = $_db->query("SELECT p.*, IFNULL((SELECT SUM(qty) FROM `order` o JOIN order_record r ON o.orderID = r.orderID WHERE productID = p.productID AND Month(o.orderDate) = Month(CURRENT_DATE())), 0) totalSales FROM product p WHERE status = '1' HAVING totalSales > 0 ORDER BY totalSales DESC LIMIT 5");

// $recentOrder = $_db->query("SELECT * FROM `order` WHERE DATE(orderDate) = CURRENT_DATE;");
?>

<head>
    <link href="../css/admin_homepage.css" rel="stylesheet" type="text/css" />
    <title>Student Records Management System with Analytics</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>

<body>
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">🎓 Student Records Management System</h1>
            <p class="page-subtitle">University Admissions Dashboard - Peak Admissions Period</p>
        </div>

        <!-- Dashboard Statistics Overview -->
        <div class="dashboard-overview">
            <div class="stats-card">
                <div class="stats-number">1,247</div>
                <div class="stats-label">Total Students</div>
            </div>
            <div class="stats-card">
                <div class="stats-number">89</div>
                <div class="stats-label">Pending Applications</div>
            </div>
            <div class="stats-card">
                <div class="stats-number">156</div>
                <div class="stats-label">Applications This Month</div>
            </div>
            <div class="stats-card">
                <div class="stats-number">23</div>
                <div class="stats-label">New Admissions This Week</div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-section">
            <div class="chart-container">
                <div class="chart-title">📊 Daily Applications (Last 7 Days)</div>
                <div class="chart-canvas">
                    <canvas id="dailyChart"></canvas>
                </div>
            </div>
            <div class="chart-container">
                <div class="chart-title">🎯 Applications by Program</div>
                <div class="chart-canvas">
                    <canvas id="programChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Applications Section -->
        <div class="items-data">
            <h3 class="section-title">Recent Student Applications (Today)</h3>
            <div class="analysis-data-list">
                <table class="data-table">
                    <tr class="table-header-row">
                        <td>Application ID</td>
                        <td>Student Name</td>
                        <td>Email</td>
                        <td>Application Date</td>
                        <td>Status</td>
                    </tr>
                    <tr class="student-record hp-order-record">
                        <td>APP2025001</td>
                        <td>Sarah Johnson</td>
                        <td>sarah.johnson@email.com</td>
                        <td>Sep 13, 2025</td>
                        <td><span class="status-badge status-pending">Pending</span></td>
                    </tr>
                    <tr class="student-record hp-order-record">
                        <td>APP2025002</td>
                        <td>Michael Chen</td>
                        <td>michael.chen@email.com</td>
                        <td>Sep 13, 2025</td>
                        <td><span class="status-badge status-under-review">Under Review</span></td>
                    </tr>
                    <tr class="student-record hp-order-record">
                        <td>APP2025003</td>
                        <td>Emily Davis</td>
                        <td>emily.davis@email.com</td>
                        <td>Sep 13, 2025</td>
                        <td><span class="status-badge status-pending">Pending</span></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Applications Grid -->
        <div class="trending-item">
            <!-- Pending Applications for Review -->
            <div class="items-data">
                <h3 class="section-title">Applications Pending Review</h3>
                <div class="analysis-data-list">
                    <table class="data-table">
                        <tr class="table-header-row">
                            <td>Application ID</td>
                            <td>Student Name</td>
                            <td>Program</td>
                            <td>Submitted</td>
                        </tr>
                        <tr class="student-record hp-order-record">
                            <td>APP2025010</td>
                            <td>James Wilson</td>
                            <td>Computer Science</td>
                            <td>Sep 10, 2025</td>
                        </tr>
                        <tr class="student-record hp-order-record">
                            <td>APP2025011</td>
                            <td>Maria Rodriguez</td>
                            <td>Business Admin</td>
                            <td>Sep 09, 2025</td>
                        </tr>
                        <tr class="student-record hp-order-record">
                            <td>APP2025012</td>
                            <td>David Kim</td>
                            <td>Engineering</td>
                            <td>Sep 08, 2025</td>
                        </tr>
                        <tr class="student-record hp-order-record">
                            <td>APP2025013</td>
                            <td>Lisa Park</td>
                            <td>Psychology</td>
                            <td>Sep 07, 2025</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Recent Admissions -->
            <div class="items-data">
                <h3 class="section-title">Recent Admissions</h3>
                <div class="analysis-data-list">
                    <table class="data-table">
                        <tr class="table-header-row">
                            <td>Student Name</td>
                            <td>Program</td>
                            <td>Admitted Date</td>
                        </tr>
                        <tr class="student-record hp-order-record">
                            <td>Anna Thompson</td>
                            <td>Psychology</td>
                            <td>Sep 12</td>
                        </tr>
                        <tr class="student-record hp-order-record">
                            <td>Robert Brown</td>
                            <td>Mathematics</td>
                            <td>Sep 11</td>
                        </tr>
                        <tr class="student-record hp-order-record">
                            <td>Lisa Garcia</td>
                            <td>Biology</td>
                            <td>Sep 10</td>
                        </tr>
                        <tr class="student-record hp-order-record">
                            <td>John Miller</td>
                            <td>History</td>
                            <td>Sep 09</td>
                        </tr>
                        <tr class="student-record hp-order-record">
                            <td>Rachel Kim</td>
                            <td>Computer Sci</td>
                            <td>Sep 08</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- System Status Summary -->
        <div class="items-data">
            <h3 class="section-title">Application System Summary</h3>
            <div class="analysis-data-list">
                <table class="data-table">
                    <tr class="table-header-row">
                        <td>Status</td>
                        <td>Count</td>
                        <td>Percentage</td>
                        <td>Action Required</td>
                    </tr>
                    <tr class="hp-order-record">
                        <td><span class="status-badge status-pending">Pending Review</span></td>
                        <td>89</td>
                        <td>15.2%</td>
                        <td>Review applications</td>
                    </tr>
                    <tr class="hp-order-record">
                        <td><span class="status-badge status-under-review">Under Review</span></td>
                        <td>34</td>
                        <td>5.8%</td>
                        <td>Complete review process</td>
                    </tr>
                    <tr class="hp-order-record">
                        <td><span class="status-badge status-approved">Approved</span></td>
                        <td>423</td>
                        <td>72.1%</td>
                        <td>Send admission letters</td>
                    </tr>
                    <tr class="hp-order-record">
                        <td><span class="status-badge status-rejected">Rejected</span></td>
                        <td>41</td>
                        <td>7.0%</td>
                        <td>Send rejection notices</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Chart.js configuration
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        font: {
                            family: 'sans-serif',
                            size: 12
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: {
                            family: 'sans-serif'
                        }
                    }
                },
                x: {
                    ticks: {
                        font: {
                            family: 'sans-serif'
                        }
                    }
                }
            }
        };

        // Daily Applications Chart
        const dailyCtx = document.getElementById('dailyChart').getContext('2d');
        const dailyChart = new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: ['Sep 7', 'Sep 8', 'Sep 9', 'Sep 10', 'Sep 11', 'Sep 12', 'Sep 13'],
                datasets: [{
                    label: 'Applications',
                    data: [12, 19, 15, 25, 22, 18, 24],
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2
                }]
            },
            options: chartOptions
        });

        // Applications by Program Chart
        const programCtx = document.getElementById('programChart').getContext('2d');
        const programChart = new Chart(programCtx, {
            type: 'doughnut',
            data: {
                labels: ['Computer Science', 'Business Admin', 'Engineering', 'Psychology', 'Biology', 'Others'],
                datasets: [{
                    data: [45, 32, 28, 24, 18, 9],
                    backgroundColor: [
                        '#667eea',
                        '#764ba2',
                        '#f093fb',
                        '#f5576c',
                        '#4facfe',
                        '#43e97b'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            padding: 15,
                            font: {
                                family: 'sans-serif',
                                size: 11
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>

<?php include "admin_footer.php" ?>