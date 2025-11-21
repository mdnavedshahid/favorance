<?php
session_start();
include 'config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Check credentials against database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && $password === $user['password']) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $user['username'];
        header("Location: admin_panel.php");
        exit;
    } else {
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { 
            margin: 0; padding: 0; box-sizing: border-box; 
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        }
        
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh; 
            display: flex; 
            justify-content: center; 
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><polygon fill="%23ffffff10" points="0,1000 1000,0 1000,1000"/></svg>');
            background-size: cover;
        }
        
        .login-container { 
            background: rgba(255, 255, 255, 0.95); 
            backdrop-filter: blur(10px);
            padding: 50px 40px; 
            border-radius: 20px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.1); 
            width: 100%; 
            max-width: 450px;
            position: relative;
            z-index: 1;
            border: 1px solid rgba(255,255,255,0.2);
            transform: translateY(0);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }
        
        h1 { 
            text-align: center; 
            margin-bottom: 40px; 
            color: #333; 
            font-weight: 600;
            font-size: 2.2rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .form-group { 
            margin-bottom: 25px; 
            position: relative;
        }
        
        label { 
            display: block; 
            margin-bottom: 10px; 
            font-weight: 500; 
            color: #555;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        input[type="text"], input[type="password"] { 
            width: 100%; 
            padding: 15px; 
            border: 2px solid #e1e1e1; 
            border-radius: 10px; 
            font-size: 16px; 
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        
        input[type="text"]:focus, input[type="password"]:focus { 
            border-color: #667eea; 
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
            transform: translateY(-2px);
        }
        
        .btn { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; 
            border: none; 
            padding: 15px; 
            border-radius: 10px; 
            font-size: 16px; 
            font-weight: 600;
            cursor: pointer; 
            width: 100%; 
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn:hover { 
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .error { 
            color: #e74c3c; 
            text-align: center; 
            margin-bottom: 20px; 
            padding: 15px; 
            background: linear-gradient(135deg, #ffeaea, #ffcccc);
            border-radius: 10px; 
            border-left: 4px solid #e74c3c;
            font-weight: 500;
        }
        
        .back-link { 
            display: block; 
            text-align: center; 
            margin-top: 25px; 
            color: #667eea; 
            text-decoration: none; 
            font-weight: 500;
            transition: color 0.3s ease;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .back-link::after {
            content: '';
            position: absolute;
            bottom: -2px; left: 50%;
            width: 0; height: 2px;
            background: #667eea;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }
        
        .back-link:hover { 
            color: #764ba2;
        }
        
        .back-link:hover::after {
            width: 100%;
        }
        
        /* Floating animation */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .login-container {
            animation: float 6s ease-in-out infinite;
        }
        
        .icon {
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1><i class="fas fa-lock"></i> Admin Login</h1>
        <?php if ($error): ?>
            <div class="error">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Username</label>
                <input type="text" id="username" name="username" required placeholder="Enter your username">
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fas fa-key"></i> Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter your password">
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>
        
        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Website
        </a>
        
        <div style="text-align: center; margin-top: 20px; font-size: 12px; color: #666;">
            <p>Default credentials: admin / admin123</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add focus effects
            const inputs = document.querySelectorAll('input[type="text"], input[type="password"]');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'translateY(-2px)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>