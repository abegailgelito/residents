<?php
include('<db.php');
// session_start();
//Select statement
$sql = "SELECT water_level, reading_time, reading_date FROM (SELECT water_level, reading_time, reading_date FROM sensor_readings3 ORDER BY reading_time DESC limit 6) as water_level ORDER BY reading_time ASC ";
$result = $conn->query($sql);
$waterlevelarray = [];
if ($result->num_rows > 0) {
    // Fetch each row and store the column value in the array
    while ($row = $result->fetch_assoc()) {
        $waterlevelarray[] = $row['water_level'];
    }
}

/**
 * Perform linear regression on a time window
 * and forecast water level after given minutes.
 *
 * @param array $times   Array of timestamps (minutes or Unix timestamps)
 * @param array $levels  Array of water levels (meters)
 * @param int   $window  Number of points for regression
 * @param int   $ahead   Forecast horizon in minutes (default: 60)
 * @return array ['slope_hr' => float, 'forecast' => float, 'intercept' => float]
 */
function regressionForecast(array $times, array $levels, int $window = 6, int $ahead = 60): array {
    $n = count($times);
    if ($n < $window) {
        return ['slope_hr' => null, 'forecast' => null, 'intercept' => null];
    }

    // Use last $window points
    $t_slice = array_slice($times, -$window);
    $h_slice = array_slice($levels, -$window);

    $m = $window;
    $mean_t = array_sum($t_slice) / $m;
    $mean_h = array_sum($h_slice) / $m;

    $num = 0.0;
    $den = 0.0;
    for ($i = 0; $i < $m; $i++) {
        $dt = $t_slice[$i] - $mean_t;
        $dh = $h_slice[$i] - $mean_h;
        $num += $dt * $dh;
        $den += $dt * $dt;
    }

    if ($den == 0) {
        return ['slope_hr' => null, 'forecast' => null, 'intercept' => null];
    }

    $slope_per_min = $num / $den;        // slope (m/min)
    $intercept     = $mean_h - $slope_per_min * $mean_t;

    // Predict future level at t_last + $ahead
    $t_future = end($t_slice) + $ahead;
    $forecast = $intercept + $slope_per_min * $t_future;

    return [
        'slope_hr'  => $slope_per_min * 60, // convert to m/hr
        'forecast'  => $forecast,           // forecasted level (m)
        'intercept' => $intercept
    ];
}

// ---------------- Example usage ----------------
$times  = [0, 5, 10, 15, 20, 25]; // minutes
$levels = [1.20, 1.23, 1.28, 1.36, 1.42, 1.50]; // meters

$result = regressionForecast($times, $waterlevelarray, 6, 60);

// Determine forecast status based on ABSOLUTE forecasted water level in feet
function getForecastStatus($forecastMeters) {
    // Use ABSOLUTE value for flood risk assessment
    $forecastFeet = abs($forecastMeters * 3.28084);
    
    // No Risk: 0-1 ft (absolute value)
    if ($forecastFeet < 1) {
        return [
            'text' => 'Normal',
            'color' => '#d4edda',
            'text_color' => '#155724',
            'icon' => 'fa-check-circle',
            'class' => 'status-normal'
        ];
    }
    // Low Risk: 1-2 ft
    elseif ($forecastFeet >= 1 && $forecastFeet < 2) {
        return [
            'text' => 'Low Risk',
            'color' => '#fff3cd',
            'text_color' => '#856404',
            'icon' => 'fa-exclamation-triangle',
            'class' => 'status-warning'
        ];
    }
    // Moderate Risk: 2-3 ft
    elseif ($forecastFeet >= 2 && $forecastFeet < 3) {
        return [
            'text' => 'Moderate Risk',
            'color' => '#ffc107',
            'text_color' => '#856404',
            'icon' => 'fa-exclamation-circle',
            'class' => 'status-warning'
        ];
    }
    // High Risk: 3-5 ft
    elseif ($forecastFeet >= 3 && $forecastFeet < 5) {
        return [
            'text' => 'High Risk',
            'color' => '#f8d7da',
            'text_color' => '#721c24',
            'icon' => 'fa-exclamation-triangle',
            'class' => 'status-danger'
        ];
    }
    // Critical Risk: 5+ ft
    else {
        return [
            'text' => 'Critical Risk',
            'color' => '#dc3545',
            'text_color' => '#ffffff',
            'icon' => 'fa-skull-crossbones',
            'class' => 'status-danger'
        ];
    }
}

$forecastStatus = getForecastStatus($result['forecast']);

// Determine trend and calculate time to reach critical levels
$trendIcon = '';
$trendClass = '';
$trendText = '';
$increaseForecast = '';

if ($result['slope_hr'] > 0.1) {
    $trendIcon = 'fa-arrow-up';
    $trendClass = 'trend-up';
    $trendText = 'Water level is rising';
    
    // Calculate when it will reach critical levels (3 ft = 0.9144 m, 5 ft = 1.524 m)
    $currentLevel = end($waterlevelarray); // Get current water level
    $slope_per_hr = $result['slope_hr'];
    
    if ($slope_per_hr > 0) {
        // Time to reach moderate risk (2 ft = 0.6096 m)
        if ($currentLevel < 0.6096) {
            $hoursToModerate = (0.6096 - $currentLevel) / $slope_per_hr;
            if ($hoursToModerate > 0 && $hoursToModerate < 48) {
                $increaseForecast .= "<div class='forecast-warning'><i class='fas fa-clock'></i> Will reach Moderate Risk in " . round($hoursToModerate, 1) . " hours</div>";
            }
        }
        
        // Time to reach high risk (3 ft = 0.9144 m)
        if ($currentLevel < 0.9144) {
            $hoursToHigh = (0.9144 - $currentLevel) / $slope_per_hr;
            if ($hoursToHigh > 0 && $hoursToHigh < 48) {
                $increaseForecast .= "<div class='forecast-warning'><i class='fas fa-exclamation-triangle'></i> Will reach High Risk in " . round($hoursToHigh, 1) . " hours</div>";
            }
        }
        
        // Time to reach critical risk (5 ft = 1.524 m)
        if ($currentLevel < 1.524) {
            $hoursToCritical = (1.524 - $currentLevel) / $slope_per_hr;
            if ($hoursToCritical > 0 && $hoursToCritical < 48) {
                $increaseForecast .= "<div class='forecast-danger'><i class='fas fa-skull-crossbones'></i> Will reach Critical Risk in " . round($hoursToCritical, 1) . " hours</div>";
            }
        }
        
        // Peak level prediction in 24 hours
        $peakIn24hr = $currentLevel + ($slope_per_hr * 24);
        $peakFeet = $peakIn24hr * 3.28084;
        $increaseForecast .= "<div class='forecast-info'><i class='fas fa-chart-line'></i> Peak in 24h: " . round($peakFeet, 1) . " ft (" . round($peakIn24hr, 2) . " m)</div>";
    }
    
} elseif ($result['slope_hr'] < -0.1) {
    $trendIcon = 'fa-arrow-down';
    $trendClass = 'trend-down';
    $trendText = 'Water level is falling';
    
    // Calculate when it will drop to safe levels
    $currentLevel = end($waterlevelarray);
    $slope_per_hr = $result['slope_hr'];
    
    if ($slope_per_hr < 0) {
        // Time to reach normal level (1 ft = 0.3048 m)
        $currentFeet = abs($currentLevel * 3.28084);
        if ($currentFeet > 1) {
            $hoursToNormal = (abs($currentLevel) - 0.3048) / abs($slope_per_hr);
            if ($hoursToNormal > 0 && $hoursToNormal < 48) {
                $increaseForecast .= "<div class='forecast-safe'><i class='fas fa-check-circle'></i> Will reach Normal level in " . round($hoursToNormal, 1) . " hours</div>";
            }
        }
        
        // Safe level prediction in 12 hours
        $levelIn12hr = $currentLevel + ($slope_per_hr * 12);
        $levelFeet = abs($levelIn12hr * 3.28084);
        $increaseForecast .= "<div class='forecast-info'><i class='fas fa-chart-line'></i> Level in 12h: " . round($levelFeet, 1) . " ft (" . round($levelIn12hr, 2) . " m)</div>";
    }
    
} else {
    $trendIcon = 'fa-minus';
    $trendClass = 'trend-stable';
    $trendText = 'Water level is stable';
    $increaseForecast = "<div class='forecast-info'><i class='fas fa-info-circle'></i> Water level remains stable</div>";
}
?>

<div class="forecasting-widget">
    <div class="forecast-header">
        <h5 class="card-title mb-0">Forecasting</h5>
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" 
            title="Displays the predicted water level based on current data and trends">
            <i class="fas fa-info"></i>
        </button>
    </div>

    <!-- Simple Table -->
    <div class="forecast-table">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Rate of Change</th>
                        <th>Forecast in 1 Hour</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo round($result['slope_hr'], 3) . " m/hr"; ?></td>
                        <td><?php echo round($result['forecast'], 3) . " m"; ?></td>
                        <td rowspan="2" style="vertical-align: middle;">
                            <div class="status-badge <?= $forecastStatus['class']; ?>">
                                <i class="fas <?= $forecastStatus['icon']; ?>"></i>
                                <?= $forecastStatus['text']; ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo round($result['slope_hr'] * 3.28084, 3) . " ft/hr"; ?></td>
                        <td><?php echo round($result['forecast'] * 3.28084, 3) . " ft"; ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Trend Indicator
    <div class="trend-indicator <?= $trendClass; ?>">
        <i class="fas <?= $trendIcon; ?>"></i>
        <?= $trendText; ?>
    </div> -->
</div>