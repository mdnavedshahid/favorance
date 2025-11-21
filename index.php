<?php
include 'config.php';

// Fetch all images from database
$stmt = $pdo->query("SELECT * FROM images ORDER BY uploaded_at DESC");
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Personal Gallery</title>
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
            line-height: 1.6; 
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; 
            padding: 3rem 0; 
            text-align: center; 
            position: relative;
            overflow: hidden;
        }
        
        header::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><circle fill="%23ffffff10" cx="200" cy="200" r="100"/><circle fill="%23ffffff05" cx="800" cy="300" r="150"/><circle fill="%23ffffff08" cx="600" cy="100" r="80"/></svg>');
        }
        
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 0 20px; 
            position: relative;
            z-index: 1;
        }
        
        h1 { 
            font-size: 3rem; 
            margin-bottom: 1rem; 
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        
        .tagline { 
            font-size: 1.3rem; 
            opacity: 0.9; 
            margin-bottom: 1.5rem;
        }
        
        .admin-link { 
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 1rem; 
            color: white; 
            text-decoration: none; 
            background: rgba(255,255,255,0.2); 
            padding: 12px 24px; 
            border-radius: 25px; 
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
            font-weight: 500;
        }
        
        .admin-link:hover { 
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .gallery { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); 
            gap: 25px; 
            padding: 50px 0; 
            flex: 1;
        }
        
        .image-card { 
            background: white; 
            border-radius: 15px; 
            overflow: hidden; 
            box-shadow: 0 8px 25px rgba(0,0,0,0.1); 
            transition: all 0.3s ease;
            position: relative;
        }
        
        .image-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: linear-gradient(135deg, #667eea20, #764ba220);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1;
        }
        
        .image-card:hover::before {
            opacity: 1;
        }
        
        .image-card:hover { 
            transform: translateY(-10px) scale(1.02); 
            box-shadow: 0 20px 40px rgba(0,0,0,0.15); 
        }
        
        .image-card img { 
            width: 100%; 
            height: 250px; 
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .image-card:hover img {
            transform: scale(1.1);
        }
        
        .image-info { 
            padding: 20px; 
            position: relative;
            z-index: 2;
            background: white;
        }
        
        .image-title { 
            font-weight: 600; 
            margin-bottom: 8px; 
            font-size: 1.2rem; 
            color: #333;
        }
        
        .image-description { 
            color: #666; 
            font-size: 0.95rem; 
            line-height: 1.5;
        }
        
        .no-images { 
            grid-column: 1 / -1; 
            text-align: center; 
            padding: 60px 20px; 
            color: #666; 
        }
        
        .no-images i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #ccc;
        }
        
        .no-images h2 {
            margin-bottom: 10px;
            color: #999;
        }
        
        footer { 
            text-align: center; 
            padding: 30px 0; 
            background: linear-gradient(135deg, #2d3748, #4a5568);
            color: white; 
            margin-top: 40px; 
        }
        
        .image-count {
            background: rgba(255,255,255,0.2);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-left: 10px;
        }
        
        /* Loading animation for images */
        .image-card img {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
        }
        
        .image-card img.loaded {
            background: none;
            animation: none;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .image-card {
            animation: fadeInUp 0.6s ease-out;
        }
        
        @media (max-width: 768px) {
            .gallery { 
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); 
                padding: 30px 0;
                gap: 20px;
            }
            
            h1 { 
                font-size: 2.2rem; 
            }
            
            .tagline {
                font-size: 1.1rem;
            }
            
            header {
                padding: 2rem 0;
            }
        }
        
        @media (max-width: 480px) {
            .gallery { 
                grid-template-columns: 1fr; 
            }
            
            h1 { 
                font-size: 1.8rem; 
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1><i class="fas fa-camera"></i> My Personal Gallery</h1>
            <p class="tagline">A collection of my favorite moments and memories</p>
            <a href="admin.php" class="admin-link">
                <i class="fas fa-lock"></i> Admin Panel
                <?php if (count($images) > 0): ?>
                    <span class="image-count"><?php echo count($images); ?> images</span>
                <?php endif; ?>
            </a>
        </div>
    </header>
    
    <div class="container">
        <div class="gallery">
            <?php if (count($images) > 0): ?>
                <?php foreach ($images as $image): ?>
                    <div class="image-card">
                        <img src="uploads/<?php echo htmlspecialchars($image['filename']); ?>" 
                             alt="<?php echo htmlspecialchars($image['title']); ?>"
                             onerror="this.src='data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 300 200\"><rect width=\"300\" height=\"200\" fill=\"%23f0f0f0\"/><text x=\"50%\" y=\"50%\" font-family=\"Arial\" font-size=\"14\" fill=\"%23999\" text-anchor=\"middle\" dy=\".3em\">Image not found</text></svg>'"
                             onload="this.classList.add('loaded')">
                        <div class="image-info">
                            <div class="image-title"><?php echo htmlspecialchars($image['title']); ?></div>
                            <?php if (!empty($image['description'])): ?>
                                <div class="image-description"><?php echo htmlspecialchars($image['description']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-images">
                    <i class="fas fa-images"></i>
                    <h2>No images yet</h2>
                    <p>Check back later for updates or contact the administrator!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> My Personal Website. All rights reserved.</p>
            <p style="margin-top: 10px; opacity: 0.8; font-size: 0.9rem;">
                <i class="fas fa-heart" style="color: #e53e3e;"></i> Made with passion
            </p>
        </div>
    </footer>

    <script>
        // Add scroll animation
        document.addEventListener('DOMContentLoaded', function() {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            // Observe all image cards
            document.querySelectorAll('.image-card').forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                setTimeout(() => {
                    observer.observe(el);
                }, index * 100);
            });

            // Handle image loading states
            const images = document.querySelectorAll('.image-card img');
            images.forEach(img => {
                if (img.complete) {
                    img.classList.add('loaded');
                }
            });
        });
    </script>
</body>
</html>