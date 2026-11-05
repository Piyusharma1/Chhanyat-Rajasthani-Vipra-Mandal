<!DOCTYPE html>
<html lang="hi">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Yatra+One&family=Tiro+Devanagari+Hindi&family=Noto+Sans+Devanagari:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | छःन्यात राजस्थानी विप्र मंडल</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/css/style.css')?>">
    <style>
        :root {
            /* Core palette */
            --saffron: #FF6B00; 
            --saffron-deep: #D94F00; 
            --saffron-light: #FF9A3C; 
            --saffron-pale: #FFF3E6;
            --maroon: #7B1A1A; 
            --maroon-deep: #550F0F; 
            --maroon-soft: #9B2424;
            --gold: #C9941A; 
            --gold-light: #F0C060; 
            --gold-pale: #FDF5E0;
            --cream: #FEF9F0; 
            --white: #FFFFFF;
            --text-dark: #2A1000; 
            --text-mid: #5C3010; 
            --text-soft: #8B5A2B;
            --border: rgba(201,148,26,0.25);
            --shadow-card: 0 4px 20px rgba(123,26,26,0.12);
            --shadow-hover: 0 8px 24px rgba(123,26,26,0.18);
            --radius: 12px; 
            --radius-sm: 8px;
            --transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);

            /* Orange + Red Theme Alignment */
            --orange: #FF6B00;
            --orange-light: #FF8C42;
            --red: #D32F2F;
            --red-dark: #B71C1C;

            /* Fallbacks & Backgrounds */
            --bg-light: #FFF8F3;
            --bg-card: #FFFFFF;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, var(--maroon-deep) 0%, var(--maroon) 50%, var(--saffron-deep) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
            overflow-x: hidden;
        }

        /* Royal Traditional Pattern Overlay */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 20% 30%, rgba(240, 192, 96, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(255, 107, 0, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, rgba(255, 255, 255, 0.05) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        .login-container {
            max-width: 440px;
            width: 100%;
            background: var(--bg-card);
            border-radius: var(--radius);
            box-shadow: var(--shadow-card);
            border: 2px solid var(--gold-light);
            overflow: hidden;
            position: relative;
            z-index: 1;
            animation: slideUp 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-deep) 100%);
            color: var(--white);
            padding: 35px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-bottom: 3px solid var(--gold);
        }

        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(240, 192, 96, 0.2) 0%, transparent 60%);
            animation: shimmer 8s ease-in-out infinite;
        }

        @keyframes shimmer {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(-10%, -10%); }
        }

        .login-header h2 {
            margin: 0 0 8px;
            font-size: 1.8rem;
            font-weight: 700;
            position: relative;
            z-index: 1;
            color: var(--gold-light);
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
        }

        .login-header p {
            margin: 0;
            opacity: 0.95;
            font-size: 1rem;
            position: relative;
            z-index: 1;
            color: var(--cream);
            font-weight: 500;
        }

        .login-header .subtitle {
            color: var(--gold-light);
            font-size: 1.1rem;
            margin-top: 8px;
            letter-spacing: 1px;
            font-weight: bold;
        }

        .login-body {
            padding: 40px 35px;
            background: linear-gradient(to bottom, var(--white) 0%, var(--cream) 100%);
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--maroon);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group label i {
            color: var(--saffron);
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper .prefix-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-soft);
            font-size: 1rem;
            transition: var(--transition);
            pointer-events: none;
        }

        .toggle-password {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-soft);
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            z-index: 10;
        }

        .toggle-password:hover {
            color: var(--saffron);
        }

        .form-group input {
            width: 100%;
            padding: 13px 40px 13px 48px;
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 1rem;
            transition: var(--transition);
            box-sizing: border-box;
            background: var(--white);
            color: var(--text-dark);
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--saffron);
            box-shadow: 0 0 0 4px var(--saffron-pale);
        }

        .form-group input:focus ~ .prefix-icon {
            color: var(--saffron);
            transform: translateY(-50%) scale(1.1);
        }

        .login-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--saffron) 0%, var(--saffron-deep) 100%);
            color: var(--white);
            border: none;
            border-radius: var(--radius-sm);
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(217, 79, 0, 0.3);
            letter-spacing: 0.5px;
        }

        .login-btn:hover {
            background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-deep) 100%);
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .login-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .error-msg {
            color: var(--red-dark);
            text-align: center;
            margin-top: 18px;
            padding: 12px 15px;
            background: #FFEBEE;
            border-radius: var(--radius-sm);
            border-left: 4px solid var(--red);
            display: none;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .success-msg {
            color: #2E7D32;
            text-align: center;
            margin-top: 18px;
            padding: 12px 15px;
            background: #E8F5E9;
            border-radius: var(--radius-sm);
            border-left: 4px solid #2E7D32;
            display: none;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .back-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px dashed var(--gold-light);
        }

        .back-link a {
            color: var(--text-mid);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: var(--transition);
        }

        .back-link a:hover {
            color: var(--maroon);
            gap: 10px;
        }

        /* Decorative Traditional Corner Borders */
        .corner-decoration {
            position: absolute;
            width: 40px;
            height: 40px;
            opacity: 0.4;
            pointer-events: none;
        }

        .corner-decoration.top-left {
            top: 12px;
            left: 12px;
            border-top: 3px solid var(--gold);
            border-left: 3px solid var(--gold);
        }

        .corner-decoration.top-right {
            top: 12px;
            right: 12px;
            border-top: 3px solid var(--gold);
            border-right: 3px solid var(--gold);
        }

        .corner-decoration.bottom-left {
            bottom: 12px;
            left: 12px;
            border-bottom: 3px solid var(--gold);
            border-left: 3px solid var(--gold);
        }

        .corner-decoration.bottom-right {
            bottom: 12px;
            right: 12px;
            border-bottom: 3px solid var(--gold);
            border-right: 3px solid var(--gold);
        }

        @media (max-width: 480px) {
            .login-container {
                border-radius: var(--radius-sm);
            }
            .login-header {
                padding: 25px 20px;
            }
            .login-header h2 {
                font-size: 1.5rem;
            }
            .login-body {
                padding: 30px 25px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="corner-decoration top-left"></div>
        <div class="corner-decoration top-right"></div>
        <div class="corner-decoration bottom-left"></div>
        <div class="corner-decoration bottom-right"></div>

        <div class="login-header">
            <h2>🔐 एडमिन लॉगिन</h2>
            <p>छःन्यात राजस्थानी विप्र मंडल</p>
            <p class="subtitle">लातूर</p>
        </div>
        
        <div class="login-body">
            <form id="login-form">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Username</label>
                    <div class="input-wrapper">
                        <input type="text" id="username" required autocomplete="username" placeholder="अपना username दर्ज करें">
                        <i class="fas fa-user-circle prefix-icon"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" required autocomplete="current-password" placeholder="अपना password दर्ज करें">
                        <i class="fas fa-key prefix-icon"></i>
                        <i class="fas fa-eye toggle-password" id="toggle-password-icon"></i>
                    </div>
                </div>
                
                <button type="submit" class="login-btn" id="login-btn">
                    <i class="fas fa-sign-in-alt"></i> लॉगिन करें
                </button>
                
                <div id="error-msg" class="error-msg"></div>
                <div id="success-msg" class="success-msg"></div>
            </form>
            
            <div class="back-link">
                <a href="index.php"><i class="fas fa-arrow-left"></i> होम पेज पर वापस जाएं</a>
            </div>
        </div>
    </div>

    <script>
        // Toggle Password Visibility
        const passwordInput = document.getElementById('password');
        const togglePasswordIcon = document.getElementById('toggle-password-icon');

        togglePasswordIcon.addEventListener('click', () => {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                togglePasswordIcon.classList.remove('fa-eye');
                togglePasswordIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                togglePasswordIcon.classList.remove('fa-eye-slash');
                togglePasswordIcon.classList.add('fa-eye');
            }
        });

        // Check if already logged in
        async function checkExistingAuth() {
            try {
                const response = await fetch('api/check_auth.php?_t=' + Date.now(), { cache: 'no-store' });
                const result = await response.json();
                
                if (result.isAdmin) {
                    window.location.href = 'admin-dashboard.php';
                }
            } catch (error) {
                console.error('Auth check failed:', error);
            }
        }
        
        checkExistingAuth();
        
        // Handle login form
        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const errorMsg = document.getElementById('error-msg');
            const successMsg = document.getElementById('success-msg');
            const loginBtn = document.getElementById('login-btn');
            
            // Hide messages
            errorMsg.style.display = 'none';
            successMsg.style.display = 'none';
            
            // Disable button
            loginBtn.disabled = true;
            loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> लॉगिन हो रहा है...';
            
            try {
                const response = await fetch('api/check_auth.php?_t=' + Date.now(), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, password })
                });
                
                const result = await response.json();
                
                if (result.isAdmin) {
                    if (result.csrf_token) {
                        try { sessionStorage.setItem('csrf_token', result.csrf_token); } catch(e) {}
                    }
                    successMsg.textContent = '✅ लॉगिन सफल! रिडायरेक्ट हो रहा है...';
                    successMsg.style.display = 'block';
                    
                    setTimeout(() => {
                        window.location.href = 'admin-dashboard.php';
                    }, 200);
                } else {
                    errorMsg.textContent = result.error || '❌ गलत username या password';
                    errorMsg.style.display = 'block';
                    loginBtn.disabled = false;
                    loginBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> लॉगिन करें';
                }
            } catch (error) {
                errorMsg.textContent = '❌ लॉगिन विफल। कृपया पुनः प्रयास करें।';
                errorMsg.style.display = 'block';
                loginBtn.disabled = false;
                loginBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> लॉगिन करें';
            }
        });
    </script>
</body>
</html>