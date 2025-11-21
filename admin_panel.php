<?php
session_start();
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin.php");
    exit;
}

// Handle image upload
$uploadMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    $fileName = time() . '_' . basename($_FILES['image']['name']);
    $targetFile = $targetDir . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    
    // Check if image file is actual image
    $check = getimagesize($_FILES['image']['tmp_name']);
    if ($check === false) {
        $uploadMessage = "File is not an image.";
    } 
    // Check file size (5MB max)
    elseif ($_FILES['image']['size'] > 5000000) {
        $uploadMessage = "Sorry, your file is too large. Max 5MB allowed.";
    }
    // Allow certain file formats
    elseif (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        $uploadMessage = "Sorry, only JPG, JPEG, PNG, GIF & WEBP files are allowed.";
    }
    // Upload file
    elseif (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
        // Save to database
        $stmt = $pdo->prepare("INSERT INTO images (filename, title, description) VALUES (?, ?, ?)");
        $stmt->execute([$fileName, $title, $description]);
        $uploadMessage = "Image uploaded successfully!";
    } else {
        $uploadMessage = "Sorry, there was an error uploading your file.";
    }
}

// Handle image deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
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
    
    header("Location: admin_panel.php");
    exit;
}

// Fetch all images
$stmt = $pdo->query("SELECT * FROM images ORDER BY uploaded_at DESC");
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f8f9fa;
            color: #333;
            min-height: 100vh;
        }
        
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        h1 {
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            background: rgba(255, 255, 255, 0.15);
            padding: 8px 20px;
            border-radius: 25px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .btn:hover {
            background-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
        
        .logout-btn {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .main-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
            padding: 30px 0;
        }
        
        .upload-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid #f0f0f0;
            height: fit-content;
            position: sticky;
            top: 100px;
        }
        
        .images-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid #f0f0f0;
        }
        
        h2 {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
            color: #333;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
            font-weight: 500;
            color: #555;
            font-size: 14px;
        }
        
        input[type="text"],
        textarea,
        input[type="file"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        
        input[type="text"]:focus,
        textarea:focus {
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
            transform: translateY(-2px);
        }
        
        input[type="file"] {
            padding: 15px;
            border: 2px dashed #dee2e6;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }
        
        input[type="file"]:hover {
            border-color: #667eea;
            background: #f0f4ff;
        }
        
        textarea {
            min-height: 120px;
            resize: vertical;
            font-family: inherit;
            line-height: 1.5;
        }
        
        .upload-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 20px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .upload-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 4px solid;
        }
        
        .success {
            background-color: #f0fff4;
            color: #2d8c4e;
            border-left-color: #48bb78;
        }
        
        .error {
            background-color: #fff5f5;
            color: #c53030;
            border-left-color: #f56565;
        }
        
        .images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            max-height: 70vh;
            overflow-y: auto;
            padding: 15px;
            border-radius: 10px;
            background: #f8f9fa;
        }
        
        .images-grid::-webkit-scrollbar {
            width: 8px;
        }
        
        .images-grid::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        .images-grid::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 4px;
        }
        
        .images-grid::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #764ba2, #667eea);
        }
        
        .image-item {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            background: white;
        }
        
        .image-item:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .image-item img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            display: block;
            transition: transform 0.3s ease;
        }
        
        .image-item:hover img {
            transform: scale(1.1);
        }
        
        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.9), rgba(118, 75, 162, 0.9));
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .image-item:hover .image-overlay {
            opacity: 1;
        }
        
        .delete-btn {
            background: #e53e3e;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .delete-btn:hover {
            background: #c53030;
            transform: translateY(-2px);
        }
        
        .no-images {
            text-align: center;
            padding: 60px 20px;
            color: #666;
            grid-column: 1 / -1;
        }
        
        .no-images i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #ccc;
        }
        
        .image-count {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .file-info {
            color: #666;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }
        
        @media (max-width: 968px) {
            .main-content {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .upload-section {
                position: static;
            }
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .user-info {
                flex-direction: column;
                gap: 10px;
            }
            
            .images-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
            
            h1 {
                font-size: 1.5rem;
            }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .upload-section, .images-section {
            animation: fadeIn 0.6s ease-out;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <h1><i class="fas fa-cogs"></i> Admin Panel</h1>
                <div class="user-info">
                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="index.php" class="btn"><i class="fas fa-eye"></i> View Website</a>
                    <a href="logout.php" class="btn logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="main-content">
            <div class="upload-section">
                <h2><i class="fas fa-upload"></i> Upload New Image</h2>
                
                <?php if ($uploadMessage): ?>
                    <div class="message <?php echo strpos($uploadMessage, 'successfully') !== false ? 'success' : 'error'; ?>">
                        <i class="fas <?php echo strpos($uploadMessage, 'successfully') !== false ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i>
                        <?php echo $uploadMessage; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" enctype="multipart/form-data" id="uploadForm">
                    <div class="form-group">
                        <label for="title"><i class="fas fa-heading"></i> Image Title *</label>
                        <input type="text" id="title" name="title" placeholder="Enter image title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description"><i class="fas fa-align-left"></i> Description</label>
                        <textarea id="description" name="description" placeholder="Enter image description (optional)"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="image"><i class="fas fa-image"></i> Select Image *</label>
                        <input type="file" id="image" name="image" accept="image/*" required>
                        <span class="file-info">Max file size: 5MB. Supported formats: JPG, JPEG, PNG, GIF, WEBP</span>
                    </div>
                    
                    <button type="submit" class="upload-btn">
                        <i class="fas fa-cloud-upload-alt"></i> Upload Image
                    </button>
                </form>
            </div>
            
            <div class="images-section">
                <h2>
                    <i class="fas fa-images"></i> Manage Images 
                    <span class="image-count"><?php echo count($images); ?></span>
                </h2>
                
                <div class="images-grid" id="imagesGrid">
                    <?php if (count($images) > 0): ?>
                        <?php foreach ($images as $image): ?>
                            <div class="image-item">
                                <img src="uploads/<?php echo htmlspecialchars($image['filename']); ?>" 
                                     alt="<?php echo htmlspecialchars($image['title']); ?>"
                                     title="<?php echo htmlspecialchars($image['title']); ?>"
                                     onerror="this.src='data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 300 200\"><rect width=\"300\" height=\"200\" fill=\"%23f0f0f0\"/><text x=\"50%\" y=\"50%\" font-family=\"Arial\" font-size=\"14\" fill=\"%23999\" text-anchor=\"middle\" dy=\".3em\">Image not found</text></svg>'">
                                <div class="image-overlay">
                                    <a href="admin_panel.php?delete=<?php echo $image['id']; ?>" 
                                       class="delete-btn" 
                                       onclick="return confirm('Are you sure you want to delete this image?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-images">
                            <i class="fas fa-images"></i>
                            <h3>No images uploaded yet</h3>
                            <p>Upload your first image using the form on the left</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // File size validation
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const maxSize = 5 * 1024 * 1024; // 5MB in bytes
            
            if (file && file.size > maxSize) {
                alert('File size must be less than 5MB');
                e.target.value = '';
            }
        });

        // Form validation
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const file = document.getElementById('image').files[0];
            
            if (!title) {
                alert('Please enter an image title');
                e.preventDefault();
                return;
            }
            
            if (!file) {
                alert('Please select an image file');
                e.preventDefault();
                return;
            }
        });

        // Add loading animation to images
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('.image-item img');
            images.forEach(img => {
                img.style.opacity = '0';
                img.style.transition = 'opacity 0.3s ease';
                
                img.onload = function() {
                    this.style.opacity = '1';
                };
                
                // If image is already loaded
                if (img.complete) {
                    img.style.opacity = '1';
                }
            });

            // Add fade-in animation to sections
            const sections = document.querySelectorAll('.upload-section, .images-section');
            sections.forEach((section, index) => {
                section.style.opacity = '0';
                section.style.transform = 'translateY(20px)';
                section.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                
                setTimeout(() => {
                    section.style.opacity = '1';
                    section.style.transform = 'translateY(0)';
                }, index * 200);
            });
        });
    </script>
</body>
</html>