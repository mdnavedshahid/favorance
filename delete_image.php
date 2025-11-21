<?php
session_start();
include 'config.php';

// Check if user is logged in (optional - remove if you want public deletion)
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Get filename to delete from server
    $stmt = $pdo->prepare("SELECT filename FROM images WHERE id = ?");
    $stmt->execute([$id]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($image) {
        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM images WHERE id = ?");
        $stmt->execute([$id]);
        
        // Delete file from server
        $filePath = "uploads/" . $image['filename'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    
    header("Location: index.php");
    exit;
}
?>