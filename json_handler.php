<?php
include_once __DIR__ . '/config.php';

// JSON File Operations

/**
 * Read all dictionary items
 */
function readDictionaryItems() {
    $filePath = DB_FILE;
    if (!file_exists($filePath)) {
        // Create file if it doesn't exist
        file_put_contents($filePath, json_encode([]));
        return [];
    }
    
    $json = file_get_contents($filePath);
    $data = json_decode($json, true);
    return $data ?? [];
}

/**
 * Save dictionary items to JSON file
 */
function saveDictionaryItems($items) {
    $filePath = DB_FILE;
    $json = json_encode($items, JSON_PRETTY_PRINT);
    return file_put_contents($filePath, $json) !== false;
}

/**
 * Add a new dictionary item with auto-increment ID
 */
function addDictionaryItem($item) {
    $items = readDictionaryItems();
    
    // Find the highest existing ID and increment it
    $maxId = 0;
    foreach ($items as $existingItem) {
        if (isset($existingItem['id']) && $existingItem['id'] > $maxId) {
            $maxId = $existingItem['id'];
        }
    }
    
    // Auto-increment ID (like MySQL)
    $newId = $maxId + 1;
    
    // Add metadata
    $item['id'] = $newId;
    $item['created_at'] = date('Y-m-d H:i:s');
    $item['updated_at'] = $item['created_at'];
    
    $items[] = $item;
    
    return saveDictionaryItems($items) ? $newId : false;
}

/**
 * Get a single dictionary item by ID
 */
function getDictionaryItem($id) {
    $items = readDictionaryItems();
    
    foreach ($items as $item) {
        if (isset($item['id']) && $item['id'] == $id) {
            return $item;
        }
    }
    
    return null;
}

/**
 * Update an existing dictionary item
 */
function updateDictionaryItem($id, $updatedData) {
    $items = readDictionaryItems();
    $found = false;
    
    foreach ($items as &$item) {
        if (isset($item['id']) && $item['id'] == $id) {
            // Keep existing created_at and id, update other fields
            $updatedData['id'] = $id;
            $updatedData['created_at'] = $item['created_at'];
            $updatedData['updated_at'] = date('Y-m-d H:i:s');
            
            $item = $updatedData;
            $found = true;
            break;
        }
    }
    
    if ($found) {
        return saveDictionaryItems($items);
    }
    
    return false;
}

/**
 * Delete a dictionary item by ID
 */
function deleteDictionaryItem($id) {
    $items = readDictionaryItems();
    $originalCount = count($items);
    
    // Filter out the item to delete
    $items = array_filter($items, function($item) use ($id) {
        return !(isset($item['id']) && $item['id'] == $id);
    });
    
    // Re-index array to maintain proper JSON structure
    $items = array_values($items);
    
    // Check if item was removed
    if (count($items) < $originalCount) {
        return saveDictionaryItems($items);
    }
    
    return false;
}

/**
 * Search dictionary items by word or definition
 */
function searchDictionaryItems($keyword) {
    $items = readDictionaryItems();
    $results = [];
    
    foreach ($items as $item) {
        if (stripos($item['word'], $keyword) !== false || 
            stripos($item['definition'] ?? '', $keyword) !== false) {
            $results[] = $item;
        }
    }
    
    return $results;
}

/**
 * Get all dictionary items sorted by ID (or any other field)
 */
function getAllDictionaryItems($sortBy = 'id', $order = 'ASC') {
    $items = readDictionaryItems();
    
    usort($items, function($a, $b) use ($sortBy, $order) {
        $valueA = $a[$sortBy] ?? '';
        $valueB = $b[$sortBy] ?? '';
        
        if ($order === 'ASC') {
            return $valueA <=> $valueB;
        } else {
            return $valueB <=> $valueA;
        }
    });
    
    return $items;
}

/**
 * Get total count of dictionary items
 */
function getDictionaryCount() {
    $items = readDictionaryItems();
    return count($items);
}

/**
 * Utility function to sanitize input
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Display success/error messages
 */
function showMessage($type, $text) {
    $class = $type === 'success' ? 'alert-success' : 'alert-error';
    return '<div class="alert ' . $class . '">' . htmlspecialchars($text) . '</div>';
}

/**
 * Validate dictionary item data
 */
function validateDictionaryItem($data) {
    $errors = [];
    
    if (empty($data['word'])) {
        $errors[] = "Word is required";
    }
    
    if (strlen($data['word'] ?? '') > 100) {
        $errors[] = "Word must be less than 100 characters";
    }
    
    return $errors;
}

/**
 * Backup dictionary data
 */
function backupDictionary() {
    $items = readDictionaryItems();
    $backupDir = __DIR__ . '/backups/';
    
    // Create backups directory if it doesn't exist
    if (!file_exists($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    
    $backupFile = $backupDir . 'dictionary_backup_' . date('Y-m-d_H-i-s') . '.json';
    $json = json_encode($items, JSON_PRETTY_PRINT);
    
    return file_put_contents($backupFile, $json) !== false;
}

/**
 * Restore from backup
 */
function restoreDictionary($backupFile) {
    if (!file_exists($backupFile)) {
        return false;
    }
    
    $json = file_get_contents($backupFile);
    $items = json_decode($json, true);
    
    if ($items !== null) {
        return saveDictionaryItems($items);
    }
    
    return false;
}
?>