<?php
require_once 'db.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'get_latest_reading') {
    getLatestReading();
} elseif ($action === 'get_notifications') {
    getNotifications();
} elseif ($action === 'save_notification') {
    saveNotification();
} elseif ($action === 'clear_notifications') {
    clearNotifications();
} elseif ($action === 'check_new_alerts') {
    checkNewAlerts();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function getLatestReading() {
    global $pdo;
    
    try {
        // Get only the MOST RECENT reading
        $stmt = $pdo->prepare("SELECT water_level, reading_time, reading_date 
                              FROM tbl_sensor_readings 
                              ORDER BY reading_date DESC, reading_time DESC 
                              LIMIT 1");
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data && $data['water_level'] !== null) {
            echo json_encode([
                'success' => true, 
                'waterLevel' => floatval($data['water_level']),
                'time' => $data['reading_time'],
                'date' => $data['reading_date']
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'No sensor data available',
                'waterLevel' => null
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'Database error: ' . $e->getMessage(),
            'waterLevel' => null
        ]);
    }
}

function getNotifications() {
    global $pdo;
    
    try {
        // Test if table exists
        $testStmt = $pdo->prepare("SHOW TABLES LIKE 'notifications'");
        $testStmt->execute();
        $tableExists = $testStmt->fetch();
        
        if (!$tableExists) {
            error_log("Notifications table does not exist");
            echo json_encode([
                'success' => false,
                'message' => 'Notifications table does not exist'
            ]);
            return;
        }
        
        $stmt = $pdo->prepare("SELECT title, message, type, created_at FROM notifications ORDER BY created_at DESC LIMIT 50");
        $stmt->execute();
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("Found " . count($notifications) . " notifications");
        
        echo json_encode([
            'success' => true,
            'notifications' => $notifications
        ]);
    } catch (PDOException $e) {
        error_log("Error in getNotifications: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching notifications: ' . $e->getMessage()
        ]);
    }
}

function saveNotification() {
    global $pdo;
    
    $title = $_POST['title'] ?? '';
    $message = $_POST['message'] ?? '';
    $type = $_POST['type'] ?? 'info';
    
    try {
        $stmt = $pdo->prepare("INSERT INTO notifications (title, message, type) VALUES (?, ?, ?)");
        $stmt->execute([$title, $message, $type]);
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error saving notification: ' . $e->getMessage()
        ]);
    }
}

function clearNotifications() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM notifications");
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'All notifications cleared']);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error clearing notifications: ' . $e->getMessage()
        ]);
    }
}

function checkNewAlerts() {
    global $pdo;
    
    try {
        // Get the last checked timestamp from request or use current time minus 1 minute
        $lastChecked = $_GET['last_checked'] ?? date('Y-m-d H:i:s', strtotime('-1 minute'));
        
        // Check for new sensor readings since last check
        $stmt = $pdo->prepare("SELECT water_level, reading_time, reading_date 
                              FROM tbl_sensor_readings 
                              WHERE CONCAT(reading_date, ' ', reading_time) > ? 
                              ORDER BY reading_date DESC, reading_time DESC");
        $stmt->execute([$lastChecked]);
        $newReadings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $alerts = [];
        
        foreach ($newReadings as $reading) {
            $waterLevel = floatval($reading['water_level']);
            $alert = generateAlert($waterLevel, $reading);
            if ($alert) {
                $alerts[] = $alert;
                
                // Save alert to notifications table
                $stmt = $pdo->prepare("INSERT INTO notifications (title, message, type) VALUES (?, ?, ?)");
                $stmt->execute([$alert['title'], $alert['message'], $alert['type']]);
            }
        }
        
        echo json_encode([
            'success' => true,
            'alerts' => $alerts,
            'new_readings' => count($newReadings),
            'last_checked' => date('Y-m-d H:i:s')
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error checking alerts: ' . $e->getMessage()
        ]);
    }
}

function generateAlert($waterLevel, $reading) {
    $alertThresholds = [
        'critical' => 5,
        'warning' => 3,
        'low' => 1
    ];
    
    $time = $reading['reading_time'];
    $date = $reading['reading_date'];
    
    if ($waterLevel >= $alertThresholds['critical']) {
        return [
            'title' => 'DANGER: Critical Water Level',
            'message' => "🚨 CRITICAL: Water level reached {$waterLevel}ft at {$time} on {$date} - FLOOD RISK! Take immediate action!",
            'type' => 'critical',
            'waterLevel' => $waterLevel
        ];
    } elseif ($waterLevel >= $alertThresholds['warning']) {
        return [
            'title' => 'WARNING: High Water Level',
            'message' => "⚠️ WARNING: Water level reached {$waterLevel}ft at {$time} on {$date} - Monitor closely",
            'type' => 'warning',
            'waterLevel' => $waterLevel
        ];
    } elseif ($waterLevel <= $alertThresholds['low']) {
        return [
            'title' => 'LOW: Water Level Alert',
            'message' => "🔻 LOW: Water level at {$waterLevel}ft at {$time} on {$date} - Consider water conservation",
            'type' => 'info',
            'waterLevel' => $waterLevel
        ];
    }
    
    return null;
}

?>