<?php
function renderWaterLevelWidget() {
    // Database connection
    $db = new PDO('mysql:host=localhost;dbname=fews', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    try {
        // Fetch the latest water level reading - improved query
        $query = $db->query("SELECT water_level, reading_time, reading_date FROM sensor_readings3 ORDER BY reading_date DESC, reading_time DESC LIMIT 1");
        $latestReading = $query->fetch(PDO::FETCH_ASSOC);
        
        // Debug: Check what data you're actually receiving
        error_log("Raw sensor data: " . print_r($latestReading, true));
        
        // Extract numeric value from string if needed
        $rawValue = $latestReading['water_level'] ?? '0';
        $waterLevel = 0;
        
        // Enhanced data extraction
        if (is_numeric($rawValue)) {
            $waterLevel = (float)$rawValue;
        } else {
            // Handle various string formats
            if (preg_match('/(\d+\.?\d*)\s*(ft|m|meter|feet)?/i', $rawValue, $matches)) {
                $waterLevel = (float)$matches[1];
                // If value is in feet, convert to meters for storage
                if (isset($matches[2]) && stripos($matches[2], 'ft') !== false) {
                    $waterLevel = $waterLevel / 3.28084; // Convert feet to meters
                }
            }
        }
        
        // Convert to feet for display
        $waterLevelMeters = $waterLevel;
        $waterLevelFeet = $waterLevelMeters * 3.28084;

    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $waterLevelMeters = 0;
        $waterLevelFeet = 0;
        $status = "Data Error";
        $statusColor = '#999999';
    }

    // Check data freshness
    checkSensorDataFreshness();

    // Get current status for styling
    $currentStatustexts = getStatustexts($waterLevelMeters);
    $currentStatusColor = getStatusColor($waterLevelMeters);
    $currentStatusText = getStatusText($waterLevelMeters);

    // Status legend data - updated to match your requirements
    $statuses = [
        'Normal' => '#d4edda',
        'Low' => '#efefa3ff',
        'Moderate' => '#f5d882ff',
        'High' => '#f8d7da'
    ];
    ?>

<div class="water-level-widget">
    <h5 class="card-title">San Juan River</h5>
        <div class="water-level-value">
        <div class="water-level-digits" id="waterLevelToggle" data-unit="ft" data-meters="<?= number_format($waterLevelMeters, 2) ?>" data-feet="<?= number_format($waterLevelFeet, 2) ?>">
            <span class="digits"><?= number_format($waterLevelFeet, 2) ?></span>
            <span class="unit">ft</span>
        </div>
    </div>
        <div class="water-level-status">
            <span class="water-level-status" id="statusDisplay" style="color:<?= $currentStatustexts ?>; background-color:<?= $currentStatusColor ?>;"><?= $currentStatusText ?></span>
        </div>
        <div class="water-level-legend">
        <div class="legend-item">
            <?php foreach ($statuses as $s => $color): ?>
                <div class="legend-item">
                    <span class="status-label"><?= $s ?></span>
                    <span class="legend-color" style="background-color:<?= $color ?>"></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- <div class="water-level-widget">
    <h5 class="card-title">San Juan River</h5>
    <div class="water-level-value">
        <div class="water-level-digits" id="waterLevelToggle" data-unit="ft" data-meters="<?= number_format($waterLevelMeters, 2) ?>" data-feet="<?= number_format($waterLevelFeet, 2) ?>">
            <span class="digits"><?= number_format($waterLevelFeet, 2) ?></span>
            <span class="unit">ft</span>
        </div>
    </div>
    <div class="water-level-status">
        <span class="status" id="statusDisplay" style="color:<?= $currentStatustexts ?>; background-color:<?= $currentStatusColor ?>; padding: 6px 160px; border-radius: 4px;"><?= $currentStatusText ?></span>
    </div>
    <div class="water-level-legend">
        <div class="legend-item">
            <?php foreach ($statuses as $s => $color): ?>
                <div class="legend-item">
                    <span class="status-label"><?= $s ?></span>
                    <span class="color-box" style="background-color:<?= $color ?>"></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div> -->

<script>
        // Check water level and trigger alerts
        function checkWaterLevelAlert(waterLevel) {
            if (waterLevel >= 90) {
                showAlert("CRITICAL: Water level has reached " + waterLevel + "%. Immediate action required!");
                
                // You can also automatically add to database via AJAX
                fetch('add_alert.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        title: 'Critical Water Level',
                        message: 'Water level has reached ' + waterLevel + '%. Immediate action required!',
                        type: 'critical'
                    })
                });
            } else if (waterLevel >= 75) {
                showAlert("WARNING: Water level is high at " + waterLevel + "%. Monitor closely.");
            }
        }
        // Function to determine status based on water level in meters - UPDATED
        function getStatus(waterLevelMeters) {
            const waterLevelFeet = waterLevelMeters * 3.28084;
            
            if (waterLevelFeet < 1) {
                return { text: "Normal", color: '#d4edda' };
            } else if (waterLevelFeet >= 1 && waterLevelFeet <= 2) {
                return { text: "Low", color: '#efefa3ff' };
            } else if (waterLevelFeet >= 2 && waterLevelFeet <= 3) {
                return { text: "Moderate", color: '#f5d882ff' };
            } else if (waterLevelFeet >= 3 && waterLevelFeet < 5) {
                return { text: "High", color: '#f8d7da' };
            } else {
                return { text: "Undefined", color: '#999999' };
            }
        }

        // Toggle between meters and feet
        document.getElementById('waterLevelToggle').addEventListener('click', function() {
            const currentUnit = this.getAttribute('data-unit');
            const metersValue = parseFloat(this.getAttribute('data-meters'));
            const feetValue = parseFloat(this.getAttribute('data-feet'));
            
            if (currentUnit === 'ft') {
                // Switch to meters
                this.querySelector('.digits').textContent = metersValue.toFixed(2);
                this.querySelector('.unit').textContent = 'm';
                this.setAttribute('data-unit', 'm');
                
                // Update status based on meters
                const status = getStatus(metersValue);
                document.getElementById('statusDisplay').textContent = status.text;
                document.getElementById('statusDisplay').style.backgroundColor = status.color;
            } else {
                // Switch to feet
                this.querySelector('.digits').textContent = feetValue.toFixed(2);
                this.querySelector('.unit').textContent = 'ft';
                this.setAttribute('data-unit', 'ft');
                
                // Update status based on meters (status calculation uses meters internally)
                const status = getStatus(metersValue);
                document.getElementById('statusDisplay').textContent = status.text;
                document.getElementById('statusDisplay').style.backgroundColor = status.color;
            }
        });
    </script>
    <?php
}

function getStatustexts($waterLevelMeters) {
    $waterLevelFeet = $waterLevelMeters * 3.28084;
    
    if ($waterLevelFeet < 1) {
        return '#155724';      // Normal - Green
    } elseif ($waterLevelFeet >= 1 && $waterLevelFeet <= 2) {
        return '#FFFF00';      // Low - Yellow
    } elseif ($waterLevelFeet >= 2 && $waterLevelFeet <= 3) {
        return '#856404';      // Moderate - Orange
    } elseif ($waterLevelFeet >= 3 && $waterLevelFeet < 5) {
        return '#721c24';      // High - Red
    } else {
        return '#999999';      // Undefined - Gray
    }
}

function getStatusColor($waterLevelMeters) {
    $waterLevelFeet = $waterLevelMeters * 3.28084;
    
    if ($waterLevelFeet < 1) {
        return '#d4edda';      // Normal - Green
    } elseif ($waterLevelFeet >= 1 && $waterLevelFeet <= 2) {
        return '#efefa3ff';      // Low - Yellow
    } elseif ($waterLevelFeet >= 2 && $waterLevelFeet <= 3) {
        return '#f5d882ff';      // Moderate - Orange
    } elseif ($waterLevelFeet >= 3 && $waterLevelFeet < 5) {
        return '#f8d7da';      // High - Red
    } else {
        return '#999999';      // Undefined - Gray
    }
}

function getStatusText($waterLevelMeters) {
    $waterLevelFeet = $waterLevelMeters * 3.28084;
    
    if ($waterLevelFeet < 1) {
        return "Normal";
    } elseif ($waterLevelFeet >= 1 && $waterLevelFeet <= 2) {
        return "Low";
    } elseif ($waterLevelFeet >= 2 && $waterLevelFeet <= 3) {
        return "Moderate";
    } elseif ($waterLevelFeet >= 3 && $waterLevelFeet < 5) {
        return "High";
    } else {
        return "Undefined";
    }
}

function checkSensorDataFreshness() {
    try {
        $db = new PDO('mysql:host=localhost;dbname=fews', 'root', '');
        $query = $db->query("SELECT reading_time, reading_date FROM sensor_readings3 ORDER BY reading_date DESC, reading_time DESC LIMIT 1");
        $latest = $query->fetch(PDO::FETCH_ASSOC);
        
        if ($latest) {
            $lastReading = strtotime($latest['reading_date'] . ' ' . $latest['reading_time']);
            $currentTime = time();
            $timeDiff = $currentTime - $lastReading;
            
            if ($timeDiff > 3600) { // More than 1 hour old
                error_log("Sensor data may be stale. Last reading: " . $latest['reading_date'] . ' ' . $latest['reading_time']);
            }
        }
    } catch (PDOException $e) {
        error_log("Data freshness check error: " . $e->getMessage());
    }
}


?>