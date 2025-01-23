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
            background: url('/images/EMSLOGIN.jpeg') no-repeat center center fixed;
            background-size: cover;
        }

        .login-container {
            display: flex;
            height: 100vh;
            align-items: center;
            justify-content: flex-start;
            padding: 0 5%;
        }

        .form-container {
            width: 35%;
            min-width: 350px;
            background: rgba(255, 255, 255, 0.85);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .form-container .logo img {
            max-width: 120px;
            display: block;
            margin: 0 auto 20px;
        }

        .form-container h2 {
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            color: #34447C;
            margin-bottom: 30px;
        }

        .form-container input {
            width: 100%;
            padding: 15px;
            margin: 15px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            background-color: #f9f9f9;
        }

        .form-container input:focus {
            border-color: #34447C;
            box-shadow: 0 0 8px rgba(52, 68, 124, 0.5);
        }

        .form-container button {
            width: 100%;
            padding: 15px;
            background-color: #34447C;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .form-container button:hover {
            background-color: #2a3568;
        }

        .form-container .g-recaptcha {
            margin: 20px 0;
        }

        .form-container a {
            display: block;
            text-align: center;
            margin-top: 10px;
            color: #34447C;
            font-size: 14px;
            text-decoration: none;
        }

        .form-container a:hover {
            text-decoration: underline;
        }

        /* Responsividad para tablets y celulares */
        @media (max-width: 1024px) {
            .login-container {
                flex-direction: column;
                justify-content: center;
                align-items: center;
                padding: 20px;
            }

            .form-container {
                width: 70%;
                max-width: 400px;
                padding: 30px;
            }

            .form-container h2 {
                font-size: 24px;
            }

            .form-container input,
            .form-container button {
                font-size: 16px;
                padding: 12px;
            }
        }

        @media (max-width: 768px) {
            .form-container {
                width: 90%;
                padding: 20px;
                box-shadow: none;
            }

            .form-container h2 {
                font-size: 22px;
            }

            .form-container .logo img {
                max-width: 100px;
            }

            .form-container input,
            .form-container button {
                font-size: 14px;
                padding: 10px;
            }
        }

        @media (max-width: 480px) {
            .form-container {
                padding: 15px;
                width: 100%;
            }

            .form-container h2 {
                font-size: 20px;
            }

            .form-container input,
            .form-container button {
                font-size: 14px;
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Formulario -->
        <div class="form-container">
            <div class="logo">
                <img src="/images/AGBClogo.png" alt="Logo">
            </div>
            <form id="loginForm" method="POST" action="{{ route('login') }}">
                @csrf
                <h2>INICIAR SESIÓN</h2>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <div class="g-recaptcha" data-sitekey="6Leg8LEqAAAAAIl35EcAbLmLidB3fDsrzgTQv-Fl"></div>
                <button type="submit">INGRESAR</button>
            </form>
        </div>
    </div>

    <script>
        const loginForm = document.getElementById('loginForm');
        loginForm.addEventListener('submit', function(event) {
            const recaptchaResponse = document.querySelector('.g-recaptcha-response').value;
            if (!recaptchaResponse) {
                event.preventDefault();
                alert("Por favor completa el Captcha.");
            }
        });
    </script>
</body>
</html>
