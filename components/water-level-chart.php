<?php
function renderWaterLevelChart() {
    date_default_timezone_set('Asia/Manila');
    
    $db = new PDO('mysql:host=localhost;dbname=fews', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("SET time_zone = '+08:00'");

    try {
        // Fetch data from the last 6 hours
        $query = $db->query("
            SELECT 
                water_level,
                DATE_FORMAT(reading_time, '%h:%i%p') as time_label,
                UNIX_TIMESTAMP(reading_time) as timestamp
            FROM sensor_readings3 
            WHERE reading_time >= DATE_SUB(NOW(), INTERVAL 6 HOUR)
            ORDER BY reading_time ASC
        ");
        $allReadings = $query->fetchAll(PDO::FETCH_ASSOC);
        
        // Process data to get 1-minute intervals and extract numeric values
        $filteredData = [];
        $lastMinute = null;
        
        foreach ($allReadings as $reading) {
            $currentMinute = date('i', $reading['timestamp']);
            if ($currentMinute != $lastMinute) {
                // Extract numeric value from water_level (e.g., "1.5FT" → 1.5)
                preg_match('/(\d+\.?\d*)/', $reading['water_level'], $matches);
                $numericValue = isset($matches[1]) ? (float)$matches[1] : 0;
                
                $filteredData[] = [
                    'water_level' => $numericValue,
                    'time_label' => $reading['time_label']
                ];
                $lastMinute = $currentMinute;
            }
        }
        
        // Limit to last 60 data points
        $filteredData = array_slice($filteredData, -60);
        
        // Prepare data for Chart.js
        $waterLevels = array_column($filteredData, 'water_level');
        $timeLabels = array_column($filteredData, 'time_label');
        
        // Debug output
        echo "<!-- DEBUG DATA:\n";
        echo "First 5 Water Levels: " . implode(", ", array_slice($waterLevels, 0, 5)) . "\n";
        echo "First 5 Time Labels: " . implode(", ", array_slice($timeLabels, 0, 5)) . "\n";
        echo "-->";
        
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        // Fallback data if database fails
        $waterLevels = [0, 1.2, 1.5, 2.1, 2.4, 3.0];
        $timeLabels = ['8:00am', '8:30am', '9:00am', '9:30am', '10:00am', '10:30am'];
    }
?>
<style>
.dashboard-card2{
    border: 1px solid #ddd;
    padding: 20px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-left: 4%;
    position: relative;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    position: relative;
}

.card-header h5 {
    margin: 0;
    flex-grow: 1;
    text-align: center;
}

.info-icon-container {
    position: absolute;
    right: 0;
    top: 0;
}

.info-icon {
    background: #243c74;
    border: none;
    border-radius: 50%;
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    color: white;
    font-size: 14px;
}

.info-icon:hover {
    background: #1a68d1;
    color: white;
    transform: scale(1.1);
}

.info-tooltip {
    position: absolute;
    top: 35px;
    right: 0;
    background: #333;
    color: white;
    padding: 15px;
    border-radius: 8px;
    font-size: 0.85rem;
    width: 250px;
    z-index: 100;
    overflow: visible;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
}

.info-tooltip.active {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.info-tooltip:after {
    content: '';
    position: absolute;
    top: -6px;
    right: 10px;
    width: 0;
    height: 0;
    border-left: 6px solid transparent;
    border-right: 6px solid transparent;
    border-bottom: 6px solid #333;
}

.clickable-legend {
    cursor: pointer;
    transition: all 0.3s ease;
}
.clickable-legend:hover {
    opacity: 0.8;
    transform: scale(1.05);
}




</style>

    <div class="dashboard-card mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">Water Level Monitoring</h5>
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" 
                title="Displays the predicted water level based on current data and trends">
                <i class="fas fa-info"></i>
            </button>
        </div>
        <div style="height: 350px;">
            <canvas id="waterLevelChart"></canvas>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Store original data in meters
        const originalWaterLevels = <?= json_encode($waterLevels) ?>;
        const timeLabels = <?= json_encode($timeLabels) ?>;
        
        // Conversion factor: 1 meter = 3.28084 feet
        const METER_TO_FEET = 3.28084;
        
        // Current unit state
        let currentUnit = 'm';
        
        // Function to convert meters to feet
        function convertToFeet(meters) {
            return meters.map(level => level * METER_TO_FEET);
        }
        
        // Function to update chart with new unit
        function updateChartUnit(useFeet) {
            const waterLevels = useFeet ? convertToFeet(originalWaterLevels) : originalWaterLevels;
            const unit = useFeet ? 'ft' : 'm';
            const unitLabel = useFeet ? 'feet' : 'meter';
            const displayUnit = useFeet ? 'ft' : 'm';
            currentUnit = unit;
            
            // Update dataset
            waterLevelChart.data.datasets[0].data = waterLevels;
            waterLevelChart.data.datasets[0].label = `Water Level (${displayUnit})`;
            
            // Update Y-axis configuration
            waterLevelChart.options.scales.y.ticks.callback = function(value) {
                return value.toFixed(1) + ' ' + displayUnit;
            };
            
            // Update tooltip
            waterLevelChart.options.plugins.tooltip.callbacks.label = function(context) {
                return context.dataset.label + ': ' + context.parsed.y.toFixed(2) + ' ' + displayUnit;
            };
            
            // Update suggested max based on unit
            waterLevelChart.options.scales.y.suggestedMax = useFeet ? 16 : 5;
            
            // Update Y-axis title
            waterLevelChart.options.scales.y.title.text = `Water Level (${unitLabel})`;
            
            // Update point colors based on thresholds
            waterLevelChart.data.datasets[0].pointBackgroundColor = function(context) {
                var value = context.dataset.data[context.dataIndex];
                if (useFeet) {
                    // Convert thresholds to feet: 1.219m=4ft, 0.914m=3ft, 0.609m=2ft
                    if (value >=3 && value < 5) return '#F44336';    // Red for high
                    if (value >=2 && value <= 3) return '#ff8c00';    // Orange for moderate
                    if (value >=1 && value <= 2) return '#FFFF00';    // Yellow for low
                    return '#4CAF50';                       // Green for normal
                } else {
                    if (value >=0.914 && value < 1.52) return '#F44336';       // Red for high
                    if (value >=0.609 && value <= 0.914) return '#ff8c00';    // Orange for moderate
                    if (value >=0.304 && value <= 0.609) return '#FFFF00';         // Yellow for low
                    return '#4CAF50';                       // Green for normal
                }
            };
            
            waterLevelChart.update();
        }
        
        // Chart configuration
        var ctx = document.getElementById('waterLevelChart').getContext('2d');
        var waterLevelChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: timeLabels,
                datasets: [{
                    label: 'Water Level (m)',
                    data: originalWaterLevels,
                    borderColor: '#0066cc',
                    backgroundColor: 'rgba(0, 102, 204, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 3,
                    pointBackgroundColor: function(context) {
                        var value = context.dataset.data[context.dataIndex];
                        if (value > 5) return '#F44336';    // Red for high
                       if (value >=2 && value <= 3) return '#ff8c00';    // Orange for moderate
                       if (value >=1 && value <= 2) return '#FFFF00';      // Yellow for low
                        return '#4CAF50';                   // Green for normal
                    }
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMin: 0,
                        suggestedMax: 5,
                        title: {
                            display: true,
                            text: 'Water Level (meter)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value.toFixed(1) + ' m';
                            },
                            stepSize: 0.5
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Time'
                        },
                        grid: {
                            display: false
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 5,
                            generateLabels: function(chart) {
                                const datasets = chart.data.datasets;
                                return datasets.map(function(dataset, i) {
                                    return {
                                        text: dataset.label,
                                        fillStyle: dataset.borderColor,
                                        strokeStyle: dataset.borderColor,
                                        pointStyle: 'circle',
                                        hidden: !chart.isDatasetVisible(i),
                                        index: i
                                    };
                                });
                            }
                        },
                        onClick: function(e, legendItem, legend) {
                            // Toggle between meters and feet when legend is clicked
                            const useFeet = currentUnit === 'm';
                            updateChartUnit(useFeet);
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y.toFixed(2) + ' ' + currentUnit;
                            }
                        }
                    }
                }
            }
        });

        //info icon
        document.addEventListener('DOMContentLoaded', function() {
            const infoIcons = document.querySelectorAll('.info-icon');
            
            infoIcons.forEach(icon => {
                icon.addEventListener('click', function() {
                    const tooltipId = this.getAttribute('data-tooltip');
                    const tooltip = document.getElementById(tooltipId);
                    
                    // Close all other tooltips
                    document.querySelectorAll('.info-tooltip.active').forEach(activeTooltip => {
                        if (activeTooltip !== tooltip) {
                            activeTooltip.classList.remove('active');
                        }
                    });
                    
                    // Toggle current tooltip
                    tooltip.classList.toggle('active');
                });
            });
            
            // Close tooltips when clicking outside
            document.addEventListener('click', function(event) {
                if (!event.target.closest('.info-icon')) {
                    document.querySelectorAll('.info-tooltip.active').forEach(tooltip => {
                        tooltip.classList.remove('active');
                    });
                }
            });
            
            // Close tooltips on ESC key
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    document.querySelectorAll('.info-tooltip.active').forEach(tooltip => {
                        tooltip.classList.remove('active');
                    });
                }
            });
        });

        // Add clickable style to legend
        setTimeout(() => {
            const legendItems = document.querySelectorAll('#waterLevelChart .chartjs-legend .chartjs-legend-item');
            legendItems.forEach(item => {
                item.classList.add('clickable-legend');
                item.title = 'Click to toggle between meters and feet';
            });
        }, 100);

        // Auto-refresh every 1 minute
        // setTimeout(function(){
        //     window.location.reload();
        // }, 60000);
    </script>
<?php
}
?>