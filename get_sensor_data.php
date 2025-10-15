<?php
// get_sensor_data.php
require_once 'db.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    if ($action === 'get_current_reading') {
        // Get the latest water level reading from the database
        $stmt = $pdo->query("
            SELECT water_level, reading_time 
            FROM sensor_readings 
            ORDER BY reading_time DESC 
            LIMIT 1
        ");
        
        $reading = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($reading) {
            echo json_encode([
                'success' => true,
                'waterLevel' => $reading['water_level'],
                'timestamp' => $reading['reading_time']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No sensor readings found'
            ]);
        }
    } elseif ($action === 'save_notification') {
        // Save notification to database
        $title = $_POST['title'] ?? '';
        $message = $_POST['message'] ?? '';
        $type = $_POST['type'] ?? 'info';
        
        $stmt = $pdo->prepare("
            INSERT INTO notifications (title, message, type, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$title, $message, $type]);
        
        echo json_encode(['success' => true]);
    } elseif ($action === 'get_notifications') {
        // Get all notifications from database
        $stmt = $pdo->query("
            SELECT title, message, type, created_at 
            FROM notifications 
            ORDER BY created_at DESC 
            LIMIT 50
        ");
        
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'notifications' => $notifications
        ]);
    } elseif ($action === 'clear_notifications') {
        // Clear all notifications from database
        $stmt = $pdo->prepare("DELETE FROM notifications");
        $stmt->execute();
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>