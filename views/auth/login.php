<?php
/**
 * Sagar Advertising CRM - Split-Screen Branded Login
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - <?= e(APP_NAME) ?> Business System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/brand.css') ?>">
    <style>
        body {
            background-color: #0E1013;
            color: #FFFFFF;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            width: 100%;
            max-width: 1060px;
            background: #171A1F;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            display: flex;
            min-height: 620px;
        }
        .login-visual-pane {
            flex: 1.1;
            background: linear-gradient(145deg, #121417 0%, #1A1E24 100%);
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border-right: 1px solid rgba(255, 255, 255, 0.06);
            position: relative;
        }
        .login-visual-pane .illustration-wrap {
            max-width: 380px;
            margin: 20px auto;
            filter: drop-shadow(0 15px 30px rgba(255, 85, 0, 0.2));
        }
        .login-visual-pane .illustration-wrap img {
            width: 100%;
            height: auto;
        }
        .login-form-pane {
            flex: 1;
            background: #FFFFFF;
            color: var(--brand-dark);
            padding: 50px 45px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .form-floating > .form-control {
            border-radius: 10px;
            border: 1px solid #E2E8F0;
        }
        .form-floating > .form-control:focus {
            border-color: var(--brand-orange);
            box-shadow: 0 0 0 4px var(--brand-orange-glow);
        }
        .demo-credentials-box {
            background: #F8FAFC;
            border: 1px dashed #CBD5E1;
            border-radius: 10px;
            padding: 12px;
            margin-top: 24px;
        }
        .demo-pill {
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            background: #FFF;
            border: 1px solid #CBD5E1;
            transition: all 0.15s;
        }
        .demo-pill:hover {
            border-color: var(--brand-orange);
            color: var(--brand-orange);
        }
        @media (max-width: 860px) {
            .login-card {
                flex-direction: column;
                min-height: auto;
            }
            .login-visual-pane {
                display: none;
            }
            .login-form-pane {
                padding: 40px 28px;
            }
        }
        @media (max-width: 576px) {
            body {
                padding: 12px;
            }
            .login-card {
                border-radius: 16px;
            }
            .login-form-pane {
                padding: 26px 16px;
            }
            .demo-credentials-box {
                padding: 10px;
            }
            .demo-pill {
                padding: 3px 6px;
                font-size: 10px;
            }
        }
    </style>
</head>
<body>

<div class="login-card">
    <!-- Left Visual & Branding Pane -->
    <div class="login-visual-pane">
        <div>
            <div class="d-flex align-items-center gap-3 mb-3">
                <img src="<?= asset('images/logo.svg') ?>" alt="Sagar Advertising" style="height: 48px; filter: brightness(0) invert(1);">
            </div>
            <p class="text-white-50 small mb-0">Custom Enterprise CRM & Dynamic Quotation Suite</p>
        </div>

        <div class="illustration-wrap text-center">
            <img src="<?= asset('images/brand-visual.svg') ?>" alt="Advertising & Signage">
        </div>

        <div>
            <h5 class="fw-bold text-white mb-1">"Manage Customers. Create Quotes. Grow Your Business."</h5>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <span class="badge bg-dark border border-secondary text-white-50">3D/2D LED Boards</span>
                <span class="badge bg-dark border border-secondary text-white-50">Glow Signboards</span>
                <span class="badge bg-dark border border-secondary text-white-50">ACP Facades</span>
                <span class="badge bg-dark border border-secondary text-white-50">Mobile Floats</span>
            </div>
        </div>
    </div>

    <!-- Right Form Pane -->
    <div class="login-form-pane">
        <div class="d-md-none text-center mb-4">
            <img src="<?= asset('images/logo.svg') ?>" alt="Sagar Advertising" style="height: 42px;">
        </div>

        <div class="mb-4">
            <h3 class="fw-black mb-1" style="font-family: 'Space Grotesk', sans-serif;">Welcome Back</h3>
            <p class="text-muted small">Sign in to manage your advertising operations and quotations.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 px-3 small rounded-3 mb-4 d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span><?= e($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= url('login') ?>" id="loginForm">
            <?= csrf_field() ?>

            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="usernameInput" name="username" placeholder="Username or Email" required autofocus>
                <label for="usernameInput"><i class="bi bi-person me-1"></i> Username or Email</label>
            </div>

            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="passwordInput" name="password" placeholder="Password" required>
                <label for="passwordInput"><i class="bi bi-shield-lock me-1"></i> Password</label>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="rememberMe" name="remember" value="1">
                    <label class="form-check-label small text-muted" for="rememberMe">Remember this device</label>
                </div>
                <span class="small text-muted">Hubballi, Karnataka</span>
            </div>

            <button type="submit" class="btn btn-brand-primary w-100 py-3 fs-6 justify-content-center shadow">
                <i class="bi bi-arrow-right-circle-fill me-1"></i> Sign In to Portal
            </button>
        </form>

        <!-- Quick Demo Credentials Fill -->
        <div class="demo-credentials-box">
            <div class="small fw-bold text-dark mb-1">Quick Demo Logins (Click to autofill):</div>
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="demo-pill" onclick="fillCreds('admin', 'Admin@123')">
                    👑 Admin (Vageesh)
                </button>
                <button type="button" class="demo-pill" onclick="fillCreds('manager', 'Manager@123')">
                    💼 Manager (Sagar)
                </button>
                <button type="button" class="demo-pill" onclick="fillCreds('sales', 'Sales@123')">
                    🎯 Sales Rep (Ramesh)
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function fillCreds(u, p) {
    document.getElementById('usernameInput').value = u;
    document.getElementById('passwordInput').value = p;
    document.getElementById('usernameInput').focus();
}
</script>

</body>
</html>
