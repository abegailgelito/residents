

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FEWS Dashboard</title>
    <!-- <link rel="stylesheet" href="css/style.css"> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <style>
            :root {
            --primary-color: #243c74;
            --secondary-color: #1a68d1;
            --accent-color: #1a6fc4;
            --light-bg: #f0f8ff;
            --card-bg: #ffffff;
        }
        
        body {
            background-color: var(--light-bg);
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        /* Header Styles */
        .header {
            background-color: var(--light-bg);
            padding: 15px 20px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-left: 25px;
        }
        
        /* Main Content */
        .main-content {
            padding: 20px;
        }
        
        /* Dashboard Cards */
        .dashboard-card {
            background: var(--card-bg);
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
            /* height: 100%; */
        }
        
        .card-title {
            color: var(--primary-color);
            font-weight: bold;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        /* Water Level Widget */
        .water-level-widget {
            background: var(--card-bg);
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .water-level-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--secondary-color);
            text-align: center;
            margin: 10px 0;
        }
        
        .unit {
            font-size: 1.2rem;
            color: #666;
        }

        .water-level-digits:hover {
            background-color: rgba(0, 102, 204, 0.1);
            transform: scale(1.05);
        }

        .water-level-digits {
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 2px 8px;
            border-radius: 4px;
            display: inline-block;
        }

       .unit-hint {
            font-size: 1.2rem;
            color: #666;
            margin-top: 2px;
            font-style: italic;
        }
        
        .water-level-status {
            text-align: center;
            font-weight: bold;
            padding: 10px 0;
            border-radius: 5px;
            margin-top: 5px;
            width: 100%;
            font-size: 16px;
            display: block;
        }
        
        /* .status-normal {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-danger {
            background-color: #f8d7da;
            color: #721c24;
        } */
        
        .water-level-legend {
            margin-top: 15px;
            font-size: 1rem;
            display: flex;
            justify-content: center;  /* center horizontally */
            align-items: center;       /* align items vertically */
            gap: 10px;                 /* spacing between each label */
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 5px;
        }

        .status-label {
            font-size: 0.8rem;
            color: #555;
            text-align: left;
            
        }
        
        /* Weather Widget */
        .weather-widget {
            background: var(--card-bg);
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .weather-icon-section {
            display: flex;
            align-items: center;
            text-align: center;
            margin-top: 10px;
        }
        
        .weather-metrics {
            display: flex;
            justify-content: space-around;
            margin: 15px 0;
        }
        
        .weather-metric {
            text-align: center;
        }
        
        .weather-metric i {
            font-size: 1.5rem;
            color: var(--secondary-color);
            margin-bottom: 5px;
        }

        .metric-row {
            display: flex;
            gap: 40px;
            flex-wrap: wrap;
            padding-left: 5px;
        }
        
        .weather-metric-value {
            font-size: 1.2rem;
            font-weight: bold;
        }
        
        .weather-metric-label {
            font-size: 0.9rem;
            color: #666;
        }
        
        /* Map Widget */
        .map-container {
            height: 700px;
            border-radius: 8px;
            overflow: hidden;
        }
        
        /* Forecasting Widget */
        .forecast-table .table {
            margin-bottom: 0;
        }

        .forecast-table .table th {
            background-color: var(--accent-color);
            color: white;
            font-size: 0.9rem;
            padding: 12px 8px;
            text-align: center;
            border: none;
        }

        .forecast-table .table td {
            text-align: center;
            padding: 12px 8px;
            font-weight: 500;
            border: 1px solid #dee2e6;
            vertical-align: middle;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-normal {
            background-color: #d4edda;
            color: #155724;
        }

        .status-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .trend-indicator {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 12px;
            padding: 8px 12px;
            border-radius: 4px;
            background-color: #f8f9fa;
            font-size: 0.9rem;
            font-weight: 500;

        }

        .trend-up {
            color: #dc3545;
            background-color: #f8d7da;
        }

        .trend-down {
            color: #28a745;
            background-color: #d4edda;
        }

        .trend-stable {
            color: #6c757d;
        }

        .forecast-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .forecast-title {
            margin: 0;
            color: var(--primary-color);
            font-weight: bold;
        }

        .increase-forecast {
            margin-top: 15px;
            padding: 12px;
            border-radius: 6px;
            background-color: #f8f9fa;
            border-left: 4px solid var(--accent-color);
        }

        .forecast-warning {
            color: #856404;
            background-color: #fff3cd;
            padding: 8px 12px;
            border-radius: 4px;
            margin: 5px 0;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .forecast-danger {
            color: #721c24;
            background-color: #f8d7da;
            padding: 8px 12px;
            border-radius: 4px;
            margin: 5px 0;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: bold;
        }

        .forecast-safe {
            color: #155724;
            background-color: #d4edda;
            padding: 8px 12px;
            border-radius: 4px;
            margin: 5px 0;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .forecast-info {
            color: #0c5460;
            background-color: #d1ecf1;
            padding: 8px 12px;
            border-radius: 4px;
            margin: 5px 0;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .forecasting-widget {
            background: var(--card-bg);
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        /* .table th {
            background-color: var(--accent-color);
            color: white;
        } */

        /* contact widget */
        .contact-widget {
            background: var(--card-bg);
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
            height: 245px;
        }
        
        /* Responsive Media Queries */
        @media (max-width: 992px) {
            .weather-metrics {
                flex-direction: row;
                gap: 15px;
            }
        }
        
        @media (max-width: 576px) {
            .header-title {
                font-size: 1.2rem;
            }
            
            .dashboard-card,
            .weather-widget,
            .water-level-widget,
            .forecasting-widget {
                padding: 15px;
            }
            
            .water-level-value {
                font-size: 2rem;
            }
        }    
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header" id="header">
        <div class="header-title">FEWS: Flood Early Warning System</div>
        <div class="notification-icon">
            <i class="fa-solid fa-bell"></i>
            <span class="notification-badge" id="notificationBadge">0</span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="container-fluid">
            <div class="row">
                <!-- Map Column -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <?php
                        include 'components/map-widget.php';
                        renderMapWidget();
                    ?>
                </div>
                
                <!-- Weather Column -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <?php
                        include 'components/weather-widget.php';
                        renderWeatherWidget();
                    ?>
                    
                    <!-- Water Level Chart -->
                    <?php
                        include 'components/water-level-chart.php';
                        renderWaterLevelChart();
                    ?>
                </div>
                
                <!-- Water Level & Forecast Column -->
                <div class="col-lg-4 col-md-12 mb-4">
                    <!-- Current Water Level -->
                        <?php
                            include 'components/water-level-widget.php';
                            renderWaterLevelWidget();
                        ?>
                    
                    <!-- Forecasting -->
                        <?php
                            include 'regressionForecast.php';
                        ?>

                        <?php
                            include 'contacts.php';
                        ?>

                        
                </div>
            </div>
        </div>
    </div>

    <script>
        // Update current time
        // function updateTime() {
        //     const now = new Date();
        //     const timeString = now.toLocaleTimeString();
        //     document.getElementById('currentTime').textContent = timeString;
        // }
        
        // Initialize everything when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // initMap();
            // initWaterLevelChart();
            // updateTime();
            setInterval(updateTime, 1000);
            
            // Initialize Bootstrap tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>

     <!-- Notification System -->
    <?php
        include 'notification-module.php';
        renderNotificationModule();
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>