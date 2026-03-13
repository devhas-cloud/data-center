<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Data Center</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <style>
        :root {
            --primary-gradient: linear-gradient(90deg, #FF8A1A 0%, #01B3BC 20%, #292A49 100%);
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: #f8f9fa;
            overflow-x: hidden;
            /* Mencegah scroll horizontal */
        }

        /* Header Styles */
        .navbar-custom {
            background: var(--primary-gradient);
            box-shadow: 0 1px 1px rgba(0, 0, 0, .1);
            z-index: 1030;
            /* Pastikan navbar di atas */
        }

        .navbar-brand {
            color: white !important;
            font-weight: 600;
            transition: transform 0.3s ease;
        }

        .navbar-brand:hover {
            transform: scale(1.05);
        }

        /* Main Content Wrapper - Grid Bootstrap */
        .main-container {
            flex: 1;
            display: flex;
            width: 100%;
            position: relative;
        }

        /* Banner Section (Left Side) */
        .banner-section {
            position: relative;
            overflow: hidden;
            /* Penting agar gambar tidak keluar container */
            min-height: 500px;
            display: none;
            /* Hidden default on mobile */
        }

        /* Tampilkan banner hanya di layar besar (Desktop) */
        @media (min-width: 992px) {
            .banner-section {
                display: block;
                flex: 1;
                min-height: auto;
            }
        }

        /* Slide Container */
        .banner-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1.5s ease-in-out;
            /* Transisi fade lebih halus */
            z-index: 1;
        }

        .banner-slide.active {
            opacity: 1;
            z-index: 2;
        }

        /* Gambar di dalam slide */
        .banner-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            /* Kunci utama: Gambar memenuhi area tanpa gepeng/distorsi */
            object-position: center;
            display: block;
        }

        /* Overlay gelap transparan agar teks (jika ada) atau gambar terlihat lebih elegan */
        .banner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.3));
            z-index: 3;
            pointer-events: none;
        }

        
        /* Banner Content (Teks di atas banner) */
        .banner-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 4;
            width: 80%;
            color: white;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.6);
        }

        .banner-content h1 {
            font-weight: 700;
            margin-bottom: 1rem;
            animation: fadeInDown 0.8s ease;
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

        /* Login Section (Right Side) */
        .login-section {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            background-color: #E9E9E9;
            z-index: 5;
            /* Di atas banner jika tumpang tindih di mobile */
            width: 100%;
            min-height: 100vh;
        }

        /* Di Desktop, sesuaikan ukuran */
        @media (min-width: 992px) {
            .login-section {
                flex: 0 0 auto;
                width: auto;
                min-height: auto;
                max-width: 500px;
                padding: 2rem;
            }
        }

        /* Login Card */
        .login-card {
            width: 100%;
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 10px;
            overflow: hidden;
            background: white;
            animation: fadeInUp 0.8s ease;
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #eee;
            padding: 1.5rem;
        }

        .card-body {
            padding: 2rem;
        }

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

        /* Form Elements */
        .form-control:focus {
            border-color: #01B3BC;
            box-shadow: 0 0 0 0.25rem rgba(1, 179, 188, 0.25);
        }

        .input-group-text {
            background-color: #f8f9fa;
            border-color: #ced4da;
            color: #6c757d;
        }

        /* Buttons */
        .btn-primary-custom {
            background: linear-gradient(90deg, #01B3BC 0%, #FF8A1A 100%);
            /* Gradient cantik */
            border: none;
            padding: 0.75rem;
            font-weight: 500;
            transition: all 0.3s ease;
            color: white;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(1, 179, 188, 0.4);
            color: white;
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
            background: var(--primary-gradient);
            color: white;
            padding: 0.8rem 0;
            margin-top: auto;
            font-size: 0.9rem;
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
    <nav class="navbar navbar-expand-lg navbar-custom" style="padding: 5px 0;">
        <div class="container-fluid">
            <a class="navbar-brand align-items-center d-flex" href="{{ url('/') }}">
                <!-- Ganti src dengan asset icon Anda -->
                <img src="{{ asset('assets/img/icon.webp') }}" alt="Logo" height="50" class="d-inline-block align-text-top me-2 rounded">
                <span>Data Center</span>
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Banner Section with Improved Slideshow -->
        <div class="banner-section">

            <!-- Slide 1 -->
            <div class="banner-slide active">
                <img src="{{ asset('assets/img/wqms.webp') }}" alt="Slide 1">
                <div class="banner-overlay"></div>
                <!-- Uncomment jika ingin ada teks -->
                <!-- <div class="banner-content text-center">
                    <h1>Selamat Datang</h1>
                    <p class="lead">Kelola sistem dengan efisien dan cepat.</p>
                </div> -->
            </div>

            <!-- Slide 2 -->
            <div class="banner-slide">

                <img src="{{ asset('assets/img/wqms2.webp') }}" alt="Slide 2">
                <div class="banner-overlay"></div>
                <div class="banner-content text-center">
                    <!-- <h1>Monitoring Real-Time</h1>
                    <p class="lead">Data akurat langsung dari sumbernya.</p> -->
                </div>
            </div>

            <!-- Slide 3 -->
            <div class="banner-slide">

                <img src="{{ asset('assets/img/banner3.webp') }}" alt="Slide 3">
                <div class="banner-overlay"></div>
                <div class="banner-content text-center">
                    <!-- <h1>Keamanan Terjamin</h1>
                    <p class="lead">Sistem proteksi data tingkat lanjut.</p> -->
                </div>
            </div>
        </div>

        <!-- Login Section -->
        <div class="login-section">
            <div class="card login-card">
                <div class="card-header text-center">
                    <h4 class="mb-0 text-primary">
                        <i class="bi bi-person-lock me-2"></i>
                        Login
                    </h4>
                </div>
                <div class="card-body">

                    <!-- Placeholder untuk Alert Laravel -->
                    <div id="alert-placeholder"></div>

                    <!-- Login Form -->
                    <form method="POST" action="{{ route('login') }}" id="loginForm">
                        @csrf
                        <input type="hidden" name="timezone" id="timezone">

                        <!-- Username Field -->
                        <div class="mb-3">
                            <label for="username" class="form-label fw-semibold text-secondary">Username</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-person text-muted"></i>
                                </span>
                                <input type="text"
                                    class="form-control border-start-0 ps-0 @error('username') is-invalid @enderror"
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
                            <label for="password" class="form-label fw-semibold text-secondary">Password</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-lock text-muted"></i>
                                </span>
                                <input type="password"
                                    class="form-control border-start-0 ps-0 @error('password') is-invalid @enderror"
                                    id="password" name="password" placeholder="Password" required
                                    autocomplete="current-password">
                                <button class="btn btn-outline-secondary border-start-0" type="button"
                                    id="togglePassword">
                                    <i class="bi bi-eye-slash" id="toggleIcon"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <!-- Remember Me -->
                        <div class="mb-4 d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label text-muted small" for="remember">
                                    Ingat saya
                                </label>
                            </div>
                        </div>

                        <!-- Login Button -->
                        <button type="submit" class="btn btn-primary-custom w-100 mb-3 shadow-sm">
                            <span class="button-text">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                Masuk Sekarang
                            </span>
                            <span class="spinner-border spinner-border-sm d-none" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </span>
                        </button>

                        <!-- Divider -->
                        <div class="text-center mb-3 position-relative">
                            <hr>
                            <span
                                class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted small">atau</span>
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
                &copy; {{ date('Y') }} {{ config('app.name', 'SecureLogin') }}. All rights reserved.
            </p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // Timezone Detection
            const timezoneInput = document.getElementById('timezone');
            if (timezoneInput) {
                timezoneInput.value = Intl.DateTimeFormat().resolvedOptions().timeZone;
            }

            // Toggle password visibility logic
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                // Toggle Icon
                if (type === 'text') {
                    toggleIcon.classList.remove('bi-eye-slash');
                    toggleIcon.classList.add('bi-eye');
                } else {
                    toggleIcon.classList.remove('bi-eye');
                    toggleIcon.classList.add('bi-eye-slash');
                }
            });

            // Handle form submission with loading state
            const loginForm = document.getElementById('loginForm');
            loginForm.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                const buttonText = submitBtn.querySelector('.button-text');
                const spinner = submitBtn.querySelector('.spinner-border');

                // Show loading state
                submitBtn.disabled = true;
                buttonText.classList.add('d-none');
                spinner.classList.remove('d-none');

                // Fallback re-enable (hanya untuk demo jika tidak ada response server)
                setTimeout(() => {
                    submitBtn.disabled = false;
                    buttonText.classList.remove('d-none');
                    spinner.classList.add('d-none');
                }, 8000);
            });

            // Auto-hide alerts (Simulation for frontend demo)
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });

            // Improved Banner Slideshow Logic
            const slides = document.querySelectorAll('.banner-slide');
            if (slides.length > 0) {
                let currentSlide = 0;
                const slideInterval = 5000; // Ganti slide setiap 5 detik

                function showSlide(index) {
                    slides.forEach((slide, i) => {
                        // Hapus class active
                        slide.classList.remove('active');
                    });

                    // Tambahkan class active ke slide baru
                    slides[index].classList.add('active');
                }

                function nextSlide() {
                    currentSlide = (currentSlide + 1) % slides.length;
                    showSlide(currentSlide);
                }

                // Mulai slideshow otomatis
                setInterval(nextSlide, slideInterval);
            }
        });
    </script>
</body>

</html>
