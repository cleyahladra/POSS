<div class="auth-container">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="auth-icon"><i class="fas fa-cash-register"></i></div>
            <h1><?= APP_NAME ?></h1>
            <p>Sign in to your account</p>
        </div>

        <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="" class="auth-form">
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-icon">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" placeholder="admin@pos.com" required autocomplete="email">
                </div>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-full">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>

        <div class="auth-hint">
            <small>Default: admin@pos.com / password</small>
        </div>
    </div>
</div>
