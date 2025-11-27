<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HPE System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f3f4f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-container { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h1 { color: #4F46E5; margin-bottom: 1.5rem; text-align: center; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
        .form-group input { width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 4px; }
        .btn { width: 100%; padding: 0.75rem; background: #4F46E5; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; }
        .btn:hover { background: #4338CA; }
        .alert { padding: 1rem; border-radius: 4px; margin-bottom: 1rem; }
        .alert-error { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>HPE System</h1>
        <div id="error-message"></div>
        <form id="login-form">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required placeholder="admin@hpe.local">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Password">
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        <div style="margin-top: 1rem; font-size: 0.875rem; color: #6b7280; text-align: center;">
            <p>Default: admin@hpe.local / Admin#123</p>
        </div>
    </div>

    <script>
        document.getElementById('login-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            fetch('/api/auth/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(data => {
                if (data.token) {
                    localStorage.setItem('api_token', data.token);
                    window.location.href = '/dashboard';
                } else {
                    document.getElementById('error-message').innerHTML = '<div class="alert alert-error">' + (data.message || 'Login gagal') + '</div>';
                }
            })
            .catch(err => {
                console.error('Error:', err);
                document.getElementById('error-message').innerHTML = '<div class="alert alert-error">Gagal login. Silakan coba lagi.</div>';
            });
        });
    </script>
</body>
</html>

