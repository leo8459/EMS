<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Roboto', sans-serif;
            background-color: #f5f5f5;
        }
        .login-container {
            display: flex;
            height: 100vh;
            align-items: center;
            justify-content: space-between;
        }
        .form-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }
        .form-container .login-box {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 40px;
            border-radius: 15px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            color: #000000;
            width: 100%;
            max-width: 400px;
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .form-container .login-box h2 {
            margin-bottom: 30px;
            font-weight: 500;
            font-size: 28px;
        }
        .form-container .login-box input {
            width: 100%;
            padding: 15px;
            margin: 15px 0;
            border: 1px solid #B99C46;
            border-radius: 10px;
            outline: none;
            font-size: 18px;
        }
        .form-container .login-box input:focus {
            border-color: #34447C;
            box-shadow: 0 0 10px rgba(76, 175, 80, 0.8);
        }
        .form-container .login-box button {
            width: 100%;
            padding: 15px;
            background-color: #34447C;
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 18px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .form-container .login-box button:hover {
            background-color: #2a3568;
        }
        .image-container {
            flex: 1;
            height: 100%;
            background: url('/images/LaPaz1.jpeg') no-repeat center center;
            background-size: cover;
        }
        .popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(0, 0, 0, 0.8);
            color: #B99C46;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            display: none;
            z-index: 1000;
        }
        .popup button {
            margin-top: 10px;
            padding: 10px 20px;
            background-color: #34447C;
            border: none;
            border-radius: 5px;
            color: #B99C46;
            font-size: 16px;
            cursor: pointer;
        }
        .popup button:hover {
            background-color: #2a3568;
        }
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            z-index: 999;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Formulario -->
        <div class="form-container">
            <div class="login-box">
                <form id="loginForm" method="POST" action="{{ route('login') }}">
                    @csrf
                    <h2>INICIAR SESION</h2>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <div class="g-recaptcha" data-sitekey="6Leg8LEqAAAAAIl35EcAbLmLidB3fDsrzgTQv-Fl"></div>
                    <button type="submit">INGRESAR</button>
                </form>
            </div>
        </div>
        <!-- Imagen -->
        <div class="image-container"></div>
    </div>

    <!-- Popup de Validación -->
    <div class="popup-overlay" id="popupOverlay"></div>
    <div class="popup" id="popup">
        <p>Por favor completa el Captcha.</p>
        <button onclick="closePopup()">OK</button>
    </div>

    <script>
        const loginForm = document.getElementById('loginForm');
        const popup = document.getElementById('popup');
        const popupOverlay = document.getElementById('popupOverlay');

        loginForm.addEventListener('submit', function (event) {
            const recaptchaResponse = document.querySelector('.g-recaptcha-response').value;
            if (!recaptchaResponse) {
                event.preventDefault();
                showPopup();
            }
        });

        function showPopup() {
            popup.style.display = 'block';
            popupOverlay.style.display = 'block';
        }

        function closePopup() {
            popup.style.display = 'none';
            popupOverlay.style.display = 'none';
        }
    </script>
</body>
</html>
