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
            padding: 0 10%;
            position: relative;
        }

        .logo {
            position: absolute;
            top: 10%;
            left: 15%;
            transform: translateX(-50%);
        }

        .logo img {
            width: 120px;
            height: auto;
        }

        .card-view {
            background: #0056B3; /* Fondo naranja EMS */
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            color: white;
            width: 100%;
            max-width: 450px;
            height: auto;
            min-height: 400px; /* CardView más alto */
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-left: -1%; /* Mueve el CardView a la derecha */
        }

        .card-view h2 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            text-transform: uppercase;
            text-align: center;
        }

        .card-view input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            color: #0056B3; /* Azul EMS */
            background-color: #f9f9f9;
        }

        .card-view input:focus {
            outline: none;
            box-shadow: 0 0 8px rgba(243, 156, 18, 0.8); /* Naranja brillante al enfocar */
        }

        .card-view button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            color: white;
            background: #F39C12; /* Botón azul EMS */
            cursor: pointer;
            margin-top: 20px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .card-view button:hover {
            background-color: #003d80; /* Azul más oscuro al hover */
            transform: translateY(-3px);
        }

        .g-recaptcha {
            margin: 20px 0;
        }

        /* Responsividad */
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                justify-content: center;
                align-items: center;
                padding: 20px;
            }

            .logo {
                top: 5%;
                left: 50%;
            }

            .card-view {
                max-width: 90%;
                padding: 20px;
                margin-left: 0;
            }

            .card-view h2 {
                font-size: 22px;
            }

            .card-view input,
            .card-view button {
                font-size: 14px;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Logo -->
        {{-- <div class="logo">
            <img src="/images/AGBCLOGO.png" alt="Logo">
        </div> --}}

        <!-- Formulario -->
        <div class="card-view">
            <h2>INICIAR SESIÓN</h2>
            <form id="loginForm" method="POST" action="{{ route('login') }}">
                @csrf
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
