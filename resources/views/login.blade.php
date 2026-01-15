<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - @yield('title', 'Sistem Anda')</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: #f8f9fa;
        }

        /* Header Styles */
        .navbar-custom {
            background: linear-gradient(90deg, #FF8A1A 0%, #01B3BC 20%, #292A49 100%);
            box-shadow: 0 1px 1px rgba(0, 0, 0, .1);
        }

        .navbar-brand {
            color: white !important;
            font-weight: 600;
            transition: transform 0.3s ease;
        }

        .navbar-brand:hover {
            transform: scale(1.05);
        }

        /* Main Content */
        .main-container {
            flex: 1;
            display: flex;
            min-height: calc(100vh - 112px);
        }

        /* Banner Section */
        .banner-section {
            flex: 1;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
        }

        .banner-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            opacity: 0;
            transition: opacity 1s ease-in-out;
            background-size: cover;
            background-position: center;
        }

        .banner-slide.active {
            opacity: 1;
        }

        .banner-slide-1 {
            background: linear-gradient(135deg, rgba(41, 42, 73, 0.9), rgba(1, 179, 188, 0.8)),
                url('https://picsum.photos/seed/banner1/1200/800.jpg');
        }

        .banner-slide-2 {
            background: linear-gradient(135deg, rgba(255, 138, 26, 0.9), rgba(41, 42, 73, 0.8)),
                url('https://picsum.photos/seed/banner2/1200/800.jpg');
        }

        .banner-slide-3 {
            background: linear-gradient(135deg, rgba(1, 179, 188, 0.9), rgba(255, 138, 26, 0.8)),
                url('https://picsum.photos/seed/banner3/1200/800.jpg');
        }

        .banner-content h1 {
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            animation: fadeInDown 0.8s ease;
        }

        .banner-content p {
            animation: fadeInUp 0.8s ease 0.2s both;
        }

        .banner-content .mt-4 {
            animation: fadeIn 1s ease 0.4s both;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        /* Login Card */
        .login-card {
            width: 100%;
            max-width: 400px;
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .form-control:focus {
            border-color: #01B3BC;
            box-shadow: 0 0 0 0.25rem rgba(1, 179, 188, 0.25);
        }

        .btn-primary-custom {
            background: var(--bs-primary-gradient);
            border: none;
            padding: 0.75rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(1, 179, 188, 0.3);
        }

        .btn-forgot-password {
            background: transparent;
            border: 1px solid #01B3BC;
            color: #01B3BC;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1rem;
        }

        .btn-forgot-password:hover {
            background: #01B3BC;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(1, 179, 188, 0.3);
        }

        /* Footer */
        .footer-custom {
            background: linear-gradient(90deg, #FF8A1A 0%, #01B3BC 20%, #292A49 100%);
            color: white;
            padding: 0.5rem 0;
            margin-top: auto;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .main-container {
                flex-direction: column;
            }

            .banner-section {
                min-height: 300px;
            }

            .login-section {
                padding: 2rem 1rem;
            }
        }

        /* Loading spinner */
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }
    </style>
</head>

<body>
    <!-- Header using Bootstrap Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom" style="padding: 1px 0;">
        <div class="container-fluid">
            <a class="align-items-center" href="{{ url('/') }}">
                <img src="{{ asset('assets/img/icon.webp') }}" alt="Logo" height="60" class="d-inline-block align-text-top">
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Banner Section with Slideshow -->
        <div class="banner-section d-none d-lg-flex">
            <!-- Slide 1 -->
            <div class="banner-slide banner-slide-1 active">
                <div class="banner-content">
                    <h1 class="display-4">Selamat Datang Kembali</h1>
                    <p class="lead">Masuk ke akun Anda untuk mengakses semua fitur dan layanan kami</p>
                    <div class="mt-4">
                        <i class="bi bi-shield-check display-1 opacity-50"></i>
                    </div>
                </div>
            </div>

            <!-- Slide 2 -->
            <div class="banner-slide banner-slide-2">
                <div class="banner-content">
                    <h1 class="display-4">Monitoring Real-Time</h1>
                    <p class="lead">Pantau data sensor dan perangkat Anda secara langsung dan akurat</p>
                    <div class="mt-4">
                        <i class="bi bi-graph-up-arrow display-1 opacity-50"></i>
                    </div>
                </div>
            </div>

            <!-- Slide 3 -->
            <div class="banner-slide banner-slide-3">
                <div class="banner-content">
                    <h1 class="display-4">Keamanan Terjamin</h1>
                    <p class="lead">Data Anda dilindungi dengan sistem keamanan tingkat tinggi</p>
                    <div class="mt-4">
                        <i class="bi bi-lock-fill display-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Login Section -->
        <div class="login-section d-flex align-items-center justify-content-center p-4"
            style="background-color: background: #E9E9E9;">
            <div class="card login-card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Login
                    </h4>
                </div>
                <div class="card-body p-4">
                    <!-- Laravel Error Messages -->
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <div>
                                    <strong>Perhatian!</strong>
                                    <ul class="mb-0 mt-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Session Status Message -->
                    @if (session('status'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                <span>{{ session('status') }}</span>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Login Form -->
                    <form method="POST" action="{{ route('login') }}" id="loginForm">
                        @csrf

                        <input type="hidden" name="timezone" id="timezone">

                        <!-- Username Field -->
                        <div class="mb-3">
                            <label for="username" class="form-label fw-semibold">Username</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input type="text" class="form-control @error('username') is-invalid @enderror"
                                    id="username" name="username" value="{{ old('username') }}"
                                    placeholder="Masukkan username" required autocomplete="username" autofocus>
                                @error('username')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Password Field -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="password" name="password" placeholder="••••••••" required
                                    autocomplete="current-password">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Remember Me -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Ingat saya
                                </label>
                            </div>
                        </div>

                        <!-- Login Button -->
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <span class="button-text">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                Masuk
                            </span>
                            <span class="spinner-border spinner-border-sm d-none" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </span>
                        </button>

                        <!-- Divider -->
                        <div class="text-center mb-3">
                            <span class="text-muted">atau</span>
                        </div>

                        <a href="{{ route('password.request') }}" class="btn btn-forgot-password">
                            <i class="bi bi-key me-2"></i>
                            Lupa Password?
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer-custom">
        <div class="container text-center">
            <p class="mb-0">
                &copy; {{ date('Y') }} {{ config('app.name', 'SecureLogin') }}.
               
            </p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // Timezona
            const timezoneInput = document.getElementById('timezone');
            if (timezoneInput) {
                timezoneInput.value = Intl.DateTimeFormat().resolvedOptions().timeZone;
            }


            // Toggle password visibility
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');

            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                // Toggle icon
                const icon = this.querySelector('i');
                icon.classList.toggle('bi-eye');
                icon.classList.toggle('bi-eye-slash');
            });

            // Handle form submission
            const loginForm = document.getElementById('loginForm');
            loginForm.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                const buttonText = submitBtn.querySelector('.button-text');
                const spinner = submitBtn.querySelector('.spinner-border');

                // Show loading state
                submitBtn.disabled = true;
                buttonText.classList.add('d-none');
                spinner.classList.remove('d-none');

                // Re-enable after 5 seconds (fallback)
                setTimeout(() => {
                    submitBtn.disabled = false;
                    buttonText.classList.remove('d-none');
                    spinner.classList.add('d-none');
                }, 5000);
            });

            // Auto-hide alerts
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });

            // Add animation to card
            const card = document.querySelector('.login-card');
            card.style.animation = 'fadeInUp 0.6s ease';

            // Banner Slideshow
            const slides = document.querySelectorAll('.banner-slide');
            if (slides.length > 0) {
                let currentSlide = 0;

                function showSlide(index) {
                    slides.forEach((slide, i) => {
                        if (i === index) {
                            slide.classList.add('active');
                        } else {
                            slide.classList.remove('active');
                        }
                    });
                }

                function nextSlide() {
                    currentSlide = (currentSlide + 1) % slides.length;
                    showSlide(currentSlide);
                }

                // Auto advance slides every 5 seconds
                setInterval(nextSlide, 5000);
            }
        });

        // Add fade in animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>

</html>
