<?php
/**
 * Flash Message Helper Functions
 * Handles flash messages without conflicts between multiple tabs
 */

/**
 * Set a flash message
 * @param string $type Type of message (success, error, info, warning)
 * @param string $message The message content
 * @param string $key Optional unique key for the message
 */
function setFlashMessage($type, $message, $key = null) {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    
    // Generate unique key if not provided
    if ($key === null) {
        $key = uniqid($type . '_', true);
    }
    
    $_SESSION['flash_messages'][$key] = [
        'type' => $type,
        'text' => $message,
        'timestamp' => time()
    ];
}

/**
 * Get flash messages by type
 * @param string $type Type of messages to retrieve
 * @param bool $consume Whether to remove messages after retrieving (default: true)
 * @return array Array of messages
 */
function getFlashMessages($type = null, $consume = true) {
    if (!isset($_SESSION['flash_messages'])) {
        return [];
    }
    
    $messages = [];
    $keysToRemove = [];
    
    foreach ($_SESSION['flash_messages'] as $key => $message) {
        // Remove old messages (older than 5 minutes)
        if (isset($message['timestamp']) && (time() - $message['timestamp']) > 300) {
            $keysToRemove[] = $key;
            continue;
        }
        
        if ($type === null || $message['type'] === $type) {
            $messages[] = $message;
            if ($consume) {
                $keysToRemove[] = $key;
            }
        }
    }
    
    // Remove consumed or old messages
    foreach ($keysToRemove as $key) {
        unset($_SESSION['flash_messages'][$key]);
    }
    
    // Clean up empty flash_messages array
    if (empty($_SESSION['flash_messages'])) {
        unset($_SESSION['flash_messages']);
    }
    
    return $messages;
}

/**
 * Display flash messages as HTML
 * @param string $type Type of messages to display
 */
function displayFlashMessages($type = null) {
    $messages = getFlashMessages($type, true);
    
    foreach ($messages as $message) {
        $icon = '';
        $class = '';
        
        switch ($message['type']) {
            case 'success':
                $icon = 'fas fa-check-circle';
                $class = 'alert-success';
                break;
            case 'error':
                $icon = 'fas fa-exclamation-triangle';
                $class = 'alert-danger';
                break;
            case 'info':
                $icon = 'fas fa-info-circle';
                $class = 'alert-info';
                break;
            case 'warning':
                $icon = 'fas fa-exclamation-triangle';
                $class = 'alert-warning';
                break;
            default:
                $icon = 'fas fa-info-circle';
                $class = 'alert-info';
        }
        
        echo '<div class="alert ' . $class . ' alert-dismissible fade show" role="alert">
                <i class="' . $icon . ' me-2"></i>' . htmlspecialchars($message['text']) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
    }
}

/**
 * Check if there are any flash messages
 * @param string $type Optional type to check for
 * @return bool True if messages exist
 */
function hasFlashMessages($type = null) {
    if (!isset($_SESSION['flash_messages'])) {
        return false;
    }
    
    if ($type === null) {
        return !empty($_SESSION['flash_messages']);
    }
    
    foreach ($_SESSION['flash_messages'] as $message) {
        if ($message['type'] === $type) {
            return true;
        }
    }
    
    return false;
}

/**
 * Clear all flash messages
 * @param string $type Optional type to clear (if not provided, clears all)
 */
function clearFlashMessages($type = null) {
    if (!isset($_SESSION['flash_messages'])) {
        return;
    }
    
    if ($type === null) {
        unset($_SESSION['flash_messages']);
        return;
    }
    
    foreach ($_SESSION['flash_messages'] as $key => $message) {
        if ($message['type'] === $type) {
            unset($_SESSION['flash_messages'][$key]);
        }
    }
    
    // Clean up empty array
    if (empty($_SESSION['flash_messages'])) {
        unset($_SESSION['flash_messages']);
    }
}

/**
 * Migrate old session flash messages to new format
 * This function helps with backward compatibility
 */
function migrateOldFlashMessages() {
    $oldKeys = ['success', 'error', 'info', 'warning'];
    
    foreach ($oldKeys as $key) {
        if (isset($_SESSION[$key])) {
            setFlashMessage($key, $_SESSION[$key]);
            unset($_SESSION[$key]);
        }
    }
}
?>