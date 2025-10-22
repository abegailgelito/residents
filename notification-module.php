<?php
function renderNotificationModule() {
?>
<?php require_once 'db.php'; ?>

    <!-- Notification Icon Styles -->
    <style>
        .notification-icon {
            position: relative;
            cursor: pointer;
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-right: 25px;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .notification-icon:hover {
            color: var(--secondary-color);
            transform: scale(1.1);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #ff5252;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
        }

        /* Toast Notification Styles */
        .toast-container {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 400px;
        }

        .toast {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-left: 4px solid #4fc3f7;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            transform: translateX(400px);
            transition: all 0.3s ease;
            opacity: 0;
            max-height: 0;
            overflow: hidden;
        }

        .toast.show {
            transform: translateX(0);
            opacity: 1;
            max-height: 200px;
        }

        .toast.hiding {
            transform: translateX(400px);
            opacity: 0;
            max-height: 0;
        }

        .toast.critical {
            border-left-color: #f44336;
            background: linear-gradient(135deg, #fff, #ffeaea);
        }

        .toast.warning {
            border-left-color: #ff9800;
            background: linear-gradient(135deg, #fff, #fff3e0);
        }

        .toast.info {
            border-left-color: #4caf50;
            background: linear-gradient(135deg, #fff, #e8f5e8);
        }

        .toast-icon {
            font-size: 1.2rem;
            margin-top: 2px;
            flex-shrink: 0;
        }

        .toast.critical .toast-icon {
            color: #f44336;
        }

        .toast.warning .toast-icon {
            color: #ff9800;
        }

        .toast.info .toast-icon {
            color: #4caf50;
        }

        .toast-content {
            flex: 1;
            min-width: 0;
        }

        .toast-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
            font-size: 0.95rem;
        }

        .toast-message {
            color: #666;
            font-size: 0.85rem;
            line-height: 1.4;
            margin: 0;
        }

        .toast-close {
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            font-size: 1rem;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border-radius: 50%;
            transition: all 0.2s ease;
        }

        .toast-close:hover {
            background: rgba(0, 0, 0, 0.1);
            color: #666;
        }

        .toast-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: currentColor;
            opacity: 0.6;
            width: 100%;
            transform: scaleX(1);
            transform-origin: left;
            transition: transform linear;
        }

        /* Existing notification module styles */
        .notification-module {
            position: fixed;
            top: 0;
            right: -400px;
            width: 380px;
            height: 100vh;
            background: rgba(30, 30, 46, 0.95);
            backdrop-filter: blur(10px);
            z-index: 2000;
            transition: right 0.4s ease;
            padding: 20px;
            overflow-y: auto;
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.2);
            color: white;
        }

        .notification-module.show {
            right: 0;
        }

        .module-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .module-header h2 {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
            color: white;
            font-size: 1.5rem;
        }

        .close-module {
            background: none;
            border: none;
            color: white;
            font-size: 1.8rem;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-module:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .clear-notifications {
            background: none;
            border: none;
            color: white;
            font-size: 1rem;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
        }

        .clear-notifications:hover {
            background: rgba(255, 0, 0, 0.2);
        }

        .notification-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .notification-item {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 15px;
            border-left: 4px solid #4fc3f7;
            transition: transform 0.2s ease;
        }

        .notification-item:hover {
            transform: translateX(-5px);
            background: rgba(255, 255, 255, 0.15);
        }

        .notification-item.critical {
            border-left-color: #f44336;
        }

        .notification-item.warning {
            border-left-color: #ff9800;
        }

        .notification-item.info {
            border-left-color: #4caf50;
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }

        .notification-title {
            font-weight: bold;
            color: white;
            font-size: 1rem;
        }

        .notification-time {
            font-size: 0.75rem;
            color: #aaa;
            white-space: nowrap;
            margin-left: 10px;
        }

        .notification-message {
            font-size: 0.9rem;
            line-height: 1.4;
            color: #ddd;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1500;
            display: none;
        }

        .overlay.show {
            display: block;
        }

        .no-notifications {
            text-align: center;
            padding: 40px 20px;
            color: #aaa;
            font-style: italic;
        }

        @media (max-width: 768px) {
            .notification-module {
                width: 100%;
                right: -100%;
            }
            
            .toast-container {
                right: 10px;
                left: 10px;
                max-width: none;
            }
            
            .toast {
                max-width: 100%;
            }
        }
    </style>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Notification Module -->
    <div class="notification-module" id="notificationModule">
        <div class="module-header">
            <h2><i class="fas fa-bell"></i> Notifications</h2>
            <div>
                <button class="close-module" id="closeModule">&times;</button>
            </div>
        </div>
        <div class="notification-list" id="notificationList">
            <!-- Notifications will be populated dynamically -->
        </div>
    </div>

    <!-- Overlay -->
    <div class="overlay" id="overlay"></div>

    <script>
    // Single Sensor Water Level Monitoring and Notification System
    class WaterLevelMonitor {
        constructor() {
            this.notificationCount = 0;
            this.toastCounter = 0;
            this.lastWaterLevel = null;
            this.lastChecked = new Date().toISOString();
            this.alertThresholds = {
                critical: 5,  
                warning: 3,
                low: 2,
                normal: 2
            };
            this.initialized = false;
            this.alertCooldown = {};
        }

        init() {
            if (this.initialized) return;
            
            this.setupEventListeners();
            this.loadNotificationsFromDatabase();
            this.startMonitoring();
            this.initialized = true;
            
            // Show system online toast
            setTimeout(() => {
                this.showToast("System Online", "Water level monitoring system is now active", "info", 4000);
            }, 1000);
        }

        setupEventListeners() {
            const notificationIcon = document.querySelector('.notification-icon');
            const closeModule = document.getElementById('closeModule');
            const overlay = document.getElementById('overlay');
            const clearBtn = document.querySelector('.clear-notifications');

            if (notificationIcon) {
                notificationIcon.addEventListener('click', () => this.showNotificationModule());
            }
            if (closeModule) {
                closeModule.addEventListener('click', () => this.hideNotificationModule());
            }
            if (overlay) {
                overlay.addEventListener('click', () => this.hideNotificationModule());
            }
            if (clearBtn) {
                clearBtn.addEventListener('click', () => this.clearAllNotifications());
            }
        }

        startMonitoring() {
            // Check for new sensor readings every 10 seconds
            setInterval(() => {
                this.checkNewAlerts();
            }, 10000);

            // Also check water level directly every 30 seconds
            setInterval(() => {
                this.checkWaterLevel();
            }, 30000);

            // Initial checks
            setTimeout(() => {
                this.checkNewAlerts();
                this.checkWaterLevel();
            }, 2000);
        }

        async checkNewAlerts() {
            try {
                const response = await fetch(`get_sensor_data.php?action=check_new_alerts&last_checked=${encodeURIComponent(this.lastChecked)}`);
                const data = await response.json();
                
                if (data.success && data.alerts && data.alerts.length > 0) {
                    data.alerts.forEach(alert => {
                        this.showToast(alert.title, alert.message, alert.type, 6000);
                    });
                    
                    // Reload notifications to show new ones
                    this.loadNotificationsFromDatabase();
                    
                    // Update last checked time
                    this.lastChecked = data.last_checked;
                }
            } catch (error) {
                console.error('Error checking new alerts:', error);
            }
        }

        async checkWaterLevel() {
            const currentLevel = await this.getCurrentWaterLevel();
            
            if (currentLevel !== null) {
                this.processWaterLevel(currentLevel);
                this.lastWaterLevel = currentLevel;
            }
        }

        async getCurrentWaterLevel() {
            try {
                const response = await fetch('get_sensor_data.php?action=get_latest_reading');
                const data = await response.json();
                
                if (data.success && data.waterLevel !== undefined && data.waterLevel !== null) {
                    return parseFloat(data.waterLevel);
                } else {
                    console.warn('Could not fetch water level from database:', data.message);
                    return null;
                }
            } catch (error) {
                console.error('Error fetching water level:', error);
                return null;
            }
        }

        processWaterLevel(currentLevel) {
            const previousLevel = this.lastWaterLevel;
            
            // Only generate immediate alerts for significant changes
            if (previousLevel !== null) {
                this.checkThresholdCrossing(currentLevel, previousLevel);
            }

            // Check current level status
            this.checkCurrentLevel(currentLevel);
        }

        checkThresholdCrossing(currentLevel, previousLevel) {
            const thresholds = Object.values(this.alertThresholds).sort((a, b) => b - a);
            
            for (const threshold of thresholds) {
                if (previousLevel < threshold && currentLevel >= threshold) {
                    const type = this.getLevelType(threshold);
                    if (this.canAlert(type)) {
                        this.triggerThresholdAlert(currentLevel, threshold, type);
                    }
                    break;
                }
                
                if (previousLevel >= threshold && currentLevel < threshold) {
                    const type = this.getLevelType(currentLevel);
                    if (this.canAlert('normal')) {
                        this.triggerRecoveryAlert(currentLevel, threshold, type);
                    }
                    break;
                }
            }
        }

        checkCurrentLevel(waterLevel) {
            const levelType = this.getLevelType(waterLevel);
            
            // Only alert for significant levels periodically
            if (levelType === 'critical' && this.canAlert('critical')) {
                this.triggerLevelAlert(waterLevel, levelType);
            }
        }

        getLevelType(waterLevel) {
            if (waterLevel >= this.alertThresholds.critical) return 'critical';
            if (waterLevel >= this.alertThresholds.warning) return 'warning';
            if (waterLevel <= this.alertThresholds.low) return 'low';
            return 'normal';
        }

        canAlert(type) {
            const now = Date.now();
            const lastAlert = this.alertCooldown[type] || 0;
            const cooldown = {
                'critical': 30000,
                'warning': 60000,
                'low': 120000,
                'normal': 180000
            };

            return (now - lastAlert) > cooldown[type];
        }

        triggerThresholdAlert(currentLevel, threshold, type) {
            const messages = {
                critical: `🚨 DANGER: Water level reached ${currentLevel}ft (threshold: ${threshold}ft) - Flood risk!`,
                warning: `⚠️ WARNING: Water level reached ${currentLevel}ft (threshold: ${threshold}ft) - Monitor closely`,
                low: `ℹ️ Water level at ${currentLevel}ft - Normal operation`,
                normal: `ℹ️ Water level at ${currentLevel}ft - Normal operation`
            };

            this.alertCooldown[type] = Date.now();
            this.showPersistentAlert(messages[type], type);
        }

        triggerRecoveryAlert(currentLevel, threshold, type) {
            const message = `✅ Water level dropped to ${currentLevel}ft (below ${threshold}ft threshold) - Situation improving`;
            this.alertCooldown['normal'] = Date.now();
            this.showPersistentAlert(message, 'info');
        }

        triggerLevelAlert(waterLevel, type) {
            const messages = {
                critical: `🚨 CRITICAL water level at ${waterLevel}ft - Flood risk! Take immediate action!`,
                warning: `⚠️ WARNING water level at ${waterLevel}ft - Monitor closely`,
                low: `🔻 LOW water level at ${waterLevel}ft - Consider water conservation`,
                normal: `ℹ️ Water level normal at ${waterLevel}ft`
            };

            this.alertCooldown[type] = Date.now();
            this.showPersistentAlert(messages[type], type);
        }

        async showPersistentAlert(message, type = 'info') {
            const titles = {
                critical: 'DANGER: Critical Water Level',
                warning: 'WARNING: High Water Level', 
                low: 'LOW: Water Level Alert',
                info: 'Water Level Update'
            };

            const title = titles[type] || titles['info'];
            
            // Show toast notification
            this.showToast(title, message, type, 6000);
            
            // Save to database (this makes it persistent)
            await this.addNotification(title, message, type);
        }

        showToast(title, message, type = 'info', duration = 5000) {
            const toastId = 'toast-' + this.toastCounter++;
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.id = toastId;
            
            const icons = {
                'critical': 'fa-exclamation-triangle',
                'warning': 'fa-exclamation-circle',
                'info': 'fa-info-circle'
            };
            
            toast.innerHTML = `
                <div class="toast-icon">
                    <i class="fas ${icons[type] || icons['info']}"></i>
                </div>
                <div class="toast-content">
                    <div class="toast-title">${title}</div>
                    <div class="toast-message">${message}</div>
                </div>
                <button class="toast-close" onclick="waterLevelMonitor.removeToast('${toastId}')">
                    <i class="fas fa-times"></i>
                </button>
                <div class="toast-progress" style="transition-duration: ${duration}ms;"></div>
            `;
            
            const toastContainer = document.getElementById('toastContainer');
            if (toastContainer) {
                toastContainer.appendChild(toast);
                
                setTimeout(() => {
                    toast.classList.add('show');
                    setTimeout(() => {
                        const progress = toast.querySelector('.toast-progress');
                        if (progress) {
                            progress.style.transform = 'scaleX(0)';
                        }
                    }, 50);
                }, 100);
                
                if (duration > 0) {
                    setTimeout(() => {
                        this.removeToast(toastId);
                    }, duration);
                }
            }
            
            return toastId;
        }

        removeToast(toastId) {
            const toast = document.getElementById(toastId);
            if (toast) {
                toast.classList.remove('show');
                toast.classList.add('hiding');
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }
        }

        async addNotification(title, message, type = 'info') {
            try {
                const formData = new FormData();
                formData.append('title', title);
                formData.append('message', message);
                formData.append('type', type);

                const response = await fetch('get_sensor_data.php?action=save_notification', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                if (result.success) {
                    // Reload notifications from database to include the new one
                    this.loadNotificationsFromDatabase();
                } else {
                    console.error('Failed to save notification to database:', result.message);
                }
            } catch (error) {
                console.error('Error saving notification to database:', error);
            }
        }

        async loadNotificationsFromDatabase() {
            try {
                const response = await fetch('get_sensor_data.php?action=get_notifications');
                const data = await response.json();
                
                if (data.success && data.notifications) {
                    const notificationList = document.getElementById('notificationList');
                    
                    // Clear existing notifications first
                    notificationList.innerHTML = '';
                    
                    if (data.notifications.length === 0) {
                        notificationList.innerHTML = '<div class="no-notifications">No notifications yet</div>';
                        this.notificationCount = 0;
                    } else {
                        data.notifications.forEach(notification => {
                            const notificationItem = document.createElement('div');
                            notificationItem.className = `notification-item ${notification.type}`;
                            
                            const dbDate = new Date(notification.created_at);
                            const timeString = dbDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                            const dateString = dbDate.toLocaleDateString();
                            
                            notificationItem.innerHTML = `
                                <div class="notification-header">
                                    <div class="notification-title">${notification.title}</div>
                                    <div class="notification-time">${dateString} ${timeString}</div>
                                </div>
                                <div class="notification-message">${notification.message}</div>
                            `;
                            notificationList.appendChild(notificationItem);
                        });
                        
                        this.notificationCount = data.notifications.length;
                    }
                    
                    this.updateBadge();
                }
            } catch (error) {
                console.error('Error loading notifications from database:', error);
                this.showFallbackNotification();
            }
        }

        showFallbackNotification() {
            const notificationList = document.getElementById('notificationList');
            notificationList.innerHTML = `
                <div class="no-notifications">
                    <p>Unable to load notifications</p>
                    <p style="font-size: 0.8rem; margin-top: 10px;">System will automatically detect new sensor readings</p>
                </div>
            `;
        }

        async clearAllNotifications() {
            if (confirm('Are you sure you want to clear all notifications? This action cannot be undone.')) {
                try {
                    const response = await fetch('get_sensor_data.php?action=clear_notifications', {
                        method: 'POST'
                    });
                    
                    const result = await response.json();
                    if (result.success) {
                        // Reload empty notifications
                        this.loadNotificationsFromDatabase();
                        this.showToast("Notifications Cleared", "All notifications have been removed", "info", 3000);
                    } else {
                        console.error('Failed to clear notifications from database:', result.message);
                    }
                } catch (error) {
                    console.error('Error clearing notifications from database:', error);
                }
            }
        }

        updateBadge() {
            const notificationBadge = document.getElementById('notificationBadge');
            if (notificationBadge) {
                notificationBadge.textContent = this.notificationCount;
                if (this.notificationCount === 0) {
                    notificationBadge.style.display = 'none';
                } else {
                    notificationBadge.style.display = 'flex';
                }
            }
        }

        showNotificationModule() {
            const notificationModule = document.getElementById('notificationModule');
            const overlay = document.getElementById('overlay');
            
            if (notificationModule) notificationModule.classList.add('show');
            if (overlay) overlay.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        hideNotificationModule() {
            const notificationModule = document.getElementById('notificationModule');
            const overlay = document.getElementById('overlay');
            
            if (notificationModule) notificationModule.classList.remove('show');
            if (overlay) overlay.classList.remove('show');
            document.body.style.overflow = '';
        }
    }

    // Initialize the water level monitor
    const waterLevelMonitor = new WaterLevelMonitor();

    // Start monitoring when page loads
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            waterLevelMonitor.init();
        }, 1500);
    });

    // Make monitor globally available
    window.waterLevelMonitor = waterLevelMonitor;
  </script>
    <?php
}
?>