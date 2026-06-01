<?php

/**
 * Input Validation & Sanitization Helper Functions
 */

/**
 * Sanitize text input
 * Removes dangerous characters and escapes for HTML display
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 * Minimum 8 characters, at least 1 uppercase, 1 lowercase, 1 number
 */
function validatePassword($password) {
    if (strlen($password) < 8) {
        return array('valid' => false, 'message' => 'Password must be at least 8 characters');
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return array('valid' => false, 'message' => 'Password must contain at least 1 uppercase letter');
    }
    if (!preg_match('/[a-z]/', $password)) {
        return array('valid' => false, 'message' => 'Password must contain at least 1 lowercase letter');
    }
    if (!preg_match('/[0-9]/', $password)) {
        return array('valid' => false, 'message' => 'Password must contain at least 1 number');
    }
    return array('valid' => true, 'message' => 'Password is strong');
}

/**
 * Validate file upload
 */
function validateFileUpload($file, $maxSize = 5000000, $allowedTypes = array('jpg', 'jpeg', 'png', 'gif')) {
    if (empty($file['name'])) {
        return array('valid' => false, 'message' => 'No file selected');
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        return array('valid' => false, 'message' => 'File size exceeds 5MB limit');
    }
    
    // Check file type
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedTypes)) {
        return array('valid' => false, 'message' => 'Invalid file type. Allowed: ' . implode(', ', $allowedTypes));
    }
    
    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowedMimes = array('image/jpeg', 'image/png', 'image/gif');
    if (!in_array($mime, $allowedMimes)) {
        return array('valid' => false, 'message' => 'Invalid file format');
    }
    
    return array('valid' => true, 'message' => 'File is valid');
}

/**
 * Sanitize filename for safe storage
 */
function sanitizeFilename($filename) {
    $filename = basename($filename);
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    $filename = time() . '_' . $filename;
    return $filename;
}

?>