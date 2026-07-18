<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — MobileCell Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #060d1f;
            overflow: hidden;
            position: relative;
        }

        /* Background animated gradient orbs */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.15;
            animation: drift 12s ease-in-out infinite;
        }
        .orb-1 {
            width: 500px; height: 500px;
            background: #6366f1;
            top: -150px; left: -150px;
            animation-delay: 0s;
        }
        .orb-2 {
            width: 400px; height: 400px;
            background: #4338ca;
            bottom: -100px; right: -100px;
            animation-delay: -4s;
        }
        .orb-3 {
            width: 300px; height: 300px;
            background: #7c3aed;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: -8s;
        }
        @keyframes drift {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33%       { transform: translate(30px, -30px) scale(1.05); }
            66%       { transform: translate(-20px, 20px) scale(0.95); }
        }

        /* Grid pattern */
        .grid-pattern {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(99,102,241,0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(99,102,241,0.05) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        /* Card */
        .login-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 400px;
            margin: 16px;
        }

        .card-inner {
            background: rgba(15, 20, 40, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(99,102,241,0.2);
            border-radius: 20px;
            padding: 36px;
            box-shadow:
                0 0 0 1px rgba(255,255,255,0.05) inset,
                0 25px 60px rgba(0,0,0,0.5);
        }

        /* Top line glow */
        .card-inner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60%;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(99,102,241,0.6), transparent);
            border-radius: 99px;
        }

        /* Logo */
        .logo-wrap {
            text-align: center;
            margin-bottom: 28px;
        }
        .logo-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: linear-gradient(135deg, #6366f1, #4338ca);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            box-shadow: 0 8px 24px rgba(99,102,241,0.4);
        }

        /* Form elements */
        .form-label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 6px;
        }
        .input-wrap {
            position: relative;
        }
        .input-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #475569;
            font-size: 18px !important;
            pointer-events: none;
        }
        .form-input {
            width: 100%;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 11px 14px 11px 42px;
            font-size: 14px;
            color: #f1f5f9;
            outline: none;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
        }
        .form-input::placeholder {
            color: #475569;
        }
        .form-input:focus {
            border-color: rgba(99,102,241,0.6);
            background: rgba(99,102,241,0.08);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
        }
        .form-input.pr-10 {
            padding-right: 42px;
        }

        /* Show/hide password toggle */
        .toggle-pw {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #475569;
            padding: 2px;
            transition: color 0.15s;
            display: flex;
            align-items: center;
        }
        .toggle-pw:hover { color: #94a3b8; }

        /* Submit btn */
        .btn-primary {
            width: 100%;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
            box-shadow: 0 4px 16px rgba(99,102,241,0.35);
            font-family: 'Inter', sans-serif;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #4f46e5, #4338ca);
            box-shadow: 0 6px 20px rgba(99,102,241,0.45);
            transform: translateY(-1px);
        }
        .btn-primary:active {
            transform: translateY(0);
        }

        /* Checkbox */
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12.5px;
            color: #64748b;
            cursor: pointer;
            user-select: none;
        }
        .checkbox-label:hover { color: #94a3b8; }
        input[type=checkbox] {
            width: 15px;
            height: 15px;
            accent-color: #6366f1;
            cursor: pointer;
        }

        /* Error alert */
        .error-alert {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.2);
            border-radius: 10px;
            padding: 12px 14px;
            display: flex;
            gap: 10px;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        /* Divider */
        .form-divider {
            height: 1px;
            background: rgba(255,255,255,0.06);
            margin: 4px 0 20px;
        }
    </style>
</head>
<body>
    <!-- Background layers -->
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
    <div class="grid-pattern"></div>

    <!-- Login Card -->
    <div class="login-card">
        <div class="card-inner">

            <!-- Logo & Brand -->
            <div class="logo-wrap">
                <div class="logo-icon">
                    <span class="material-symbols-outlined text-white text-2xl">storefront</span>
                </div>
                <h1 style="font-size:20px;font-weight:800;color:#f1f5f9;letter-spacing:-0.3px;">MobileCell</h1>
                <p style="font-size:11px;color:#475569;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;margin-top:3px;">Retail Command Center</p>
            </div>

            <div class="form-divider"></div>

            <!-- Error Alert -->
            @if($errors->any())
                <div class="error-alert">
                    <span class="material-symbols-outlined" style="color:#f87171;font-size:18px;flex-shrink:0;margin-top:1px;font-variation-settings:'FILL' 1">error</span>
                    <div>
                        <p style="font-size:12px;font-weight:700;color:#fca5a5;margin-bottom:4px;">Login gagal</p>
                        @foreach($errors->all() as $error)
                            <p style="font-size:12px;color:#94a3b8;">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Form -->
            <form action="{{ route('login') }}" method="POST" style="display:flex;flex-direction:column;gap:16px;">
                @csrf

                <!-- Email -->
                <div>
                    <label for="email" class="form-label">Alamat Email</label>
                    <div class="input-wrap">
                        <span class="material-symbols-outlined input-icon">mail</span>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email') }}"
                            required
                            placeholder="nama@email.com"
                            class="form-input"
                            autocomplete="email"
                        >
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="form-label">Kata Sandi</label>
                    <div class="input-wrap">
                        <span class="material-symbols-outlined input-icon">lock</span>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            required
                            placeholder="Masukkan kata sandi"
                            class="form-input pr-10"
                            autocomplete="current-password"
                        >
                        <button type="button" class="toggle-pw" onclick="togglePassword()" id="pw-toggle" title="Tampilkan sandi">
                            <span class="material-symbols-outlined" id="pw-icon" style="font-size:18px">visibility</span>
                        </button>
                    </div>
                </div>

                <!-- Remember me -->
                <label class="checkbox-label">
                    <input type="checkbox" name="remember" id="remember">
                    Ingat saya di perangkat ini
                </label>

                <!-- Submit -->
                <div style="margin-top:4px;">
                    <button type="submit" class="btn-primary">
                        <span>Masuk ke Sistem</span>
                        <span class="material-symbols-outlined" style="font-size:16px">arrow_forward</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <p style="text-align:center;font-size:11px;color:#334155;font-weight:500;margin-top:20px;">
            &copy; {{ date('Y') }} MobileCell Systems. All Rights Reserved.
        </p>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon  = document.getElementById('pw-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                input.type = 'password';
                icon.textContent = 'visibility';
            }
        }
    </script>
</body>
</html>
