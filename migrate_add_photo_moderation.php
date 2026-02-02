<?php
/**
 * Migration: Add Photo Moderation Fields
 * 
 * Adds status, uploaded_by, and is_admin_upload columns to fotos table
 * for implementing user photo uploads with admin moderation.
 */

require_once 'config.php';

try {
    $db = getDbConnection();
    
    echo "Starting migration: Add Photo Moderation Fields\n";
    
    // Check if columns already exist
    $result = $db->query("PRAGMA table_info(fotos)");
    $columns = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $columns[] = $row['name'];
    }
    
    // Add status column
    if (!in_array('status', $columns)) {
        echo "Adding 'status' column...\n";
        $db->exec("ALTER TABLE fotos ADD COLUMN status TEXT DEFAULT 'pendente'");
        echo "✓ 'status' column added\n";
    } else {
        echo "⚠ 'status' column already exists\n";
    }
    
    // Add uploaded_by column
    if (!in_array('uploaded_by', $columns)) {
        echo "Adding 'uploaded_by' column...\n";
        $db->exec("ALTER TABLE fotos ADD COLUMN uploaded_by TEXT");
        echo "✓ 'uploaded_by' column added\n";
    } else {
        echo "⚠ 'uploaded_by' column already exists\n";
    }
    
    // Add is_admin_upload column
    if (!in_array('is_admin_upload', $columns)) {
        echo "Adding 'is_admin_upload' column...\n";
        $db->exec("ALTER TABLE fotos ADD COLUMN is_admin_upload INTEGER DEFAULT 0");
        echo "✓ 'is_admin_upload' column added\n";
    } else {
        echo "⚠ 'is_admin_upload' column already exists\n";
    }
    
    // Update existing photos to be approved and marked as admin uploads
    echo "\nUpdating existing photos...\n";
    $stmt = $db->prepare("UPDATE fotos SET status = 'aprovado', is_admin_upload = 1 WHERE status IS NULL OR status = 'pendente'");
    $stmt->execute();
    $changes = $db->changes();
    echo "✓ Updated {$changes} existing photo(s) to 'aprovado' status\n";
    
    // Verify migration
    echo "\nVerifying migration...\n";
    $result = $db->query("PRAGMA table_info(fotos)");
    $finalColumns = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $finalColumns[] = $row['name'];
    }
    
    $requiredColumns = ['status', 'uploaded_by', 'is_admin_upload'];
    $allPresent = true;
    foreach ($requiredColumns as $col) {
        if (!in_array($col, $finalColumns)) {
            echo "✗ Column '{$col}' is missing!\n";
            $allPresent = false;
        } else {
            echo "✓ Column '{$col}' present\n";
        }
    }
    
    if ($allPresent) {
        echo "\n✓ Migration completed successfully!\n";
    } else {
        echo "\n✗ Migration completed with errors\n";
        exit(1);
    }
    
    $db->close();
    
} catch (Exception $e) {
    echo "✗ Error during migration: " . $e->getMessage() . "\n";
    exit(1);
}
?>
