<?php
include 'db.php';

function addNotification($title, $message, $type = 'info') {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO notifications (title, message, type) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $message, $type);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Example usage:
// addNotification("Water Level Alert", "Reservoir A has reached critical level", "critical");
?>