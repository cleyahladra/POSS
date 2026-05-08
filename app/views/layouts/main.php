<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'POS') ?> — <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css">
    <!-- ADD THIS LINE BELOW -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/music-player.css">
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon"><i class="fas fa-cash-register"></i></div>
            <div class="brand-text">
                <span class="brand-name"><?= APP_NAME ?></span>
                <span class="brand-version">v<?= APP_VERSION ?></span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">
                <span class="nav-label">Main</span>
                <a href="<?= BASE_URL ?>/index.php?url=dashboard" class="nav-link <?= (strpos($_GET['url'] ?? '', 'dashboard') !== false || empty($_GET['url'])) ? 'active' : '' ?>">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>
                <a href="<?= BASE_URL ?>/index.php?url=sales" class="nav-link <?= (($_GET['url'] ?? '') === 'sales') ? 'active' : '' ?>">
                    <i class="fas fa-cash-register"></i> POS Terminal
                </a>
                <a href="<?= BASE_URL ?>/index.php?url=sales/history" class="nav-link <?= (strpos($_GET['url'] ?? '', 'sales/history') !== false || strpos($_GET['url'] ?? '', 'sales/view') !== false) ? 'active' : '' ?>">
                    <i class="fas fa-receipt"></i> Sales History
                </a>
            </div>

            <div class="nav-section">
                <span class="nav-label">Inventory</span>
                <a href="<?= BASE_URL ?>/index.php?url=products" class="nav-link <?= (strpos($_GET['url'] ?? '', 'products') !== false && strpos($_GET['url'] ?? '', 'categories') === false) ? 'active' : '' ?>">
                    <i class="fas fa-box"></i> Products
                </a>
                <a href="<?= BASE_URL ?>/index.php?url=products/categories" class="nav-link <?= (strpos($_GET['url'] ?? '', 'products/categories') !== false) ? 'active' : '' ?>">
                    <i class="fas fa-tags"></i> Categories
                </a>
                <a href="<?= BASE_URL ?>/index.php?url=customers" class="nav-link <?= (strpos($_GET['url'] ?? '', 'customers') !== false) ? 'active' : '' ?>">
                    <i class="fas fa-users"></i> Customers
                </a>
            </div>

            <?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>
            <div class="nav-section">
                <span class="nav-label">Analytics</span>
                <a href="<?= BASE_URL ?>/index.php?url=reports" class="nav-link <?= (strpos($_GET['url'] ?? '', 'reports') !== false && strpos($_GET['url'] ?? '', 'monthly') === false && strpos($_GET['url'] ?? '', 'products') === false) ? 'active' : '' ?>">
                    <i class="fas fa-chart-bar"></i> Daily Report
                </a>
                <a href="<?= BASE_URL ?>/index.php?url=reports/monthly" class="nav-link <?= (strpos($_GET['url'] ?? '', 'reports/monthly') !== false) ? 'active' : '' ?>">
                    <i class="fas fa-chart-line"></i> Monthly Report
                </a>
                <a href="<?= BASE_URL ?>/index.php?url=reports/products" class="nav-link <?= (strpos($_GET['url'] ?? '', 'reports/products') !== false) ? 'active' : '' ?>">
                    <i class="fas fa-chart-pie"></i> Product Report
                </a>
                <a href="<?= BASE_URL ?>/index.php?url=users" class="nav-link <?= (strpos($_GET['url'] ?? '', 'users') !== false) ? 'active' : '' ?>">
                    <i class="fas fa-user-cog"></i> Users
                </a>
            </div>
            <?php endif; ?>
        </nav>

        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['user']['name'] ?? 'U', 0, 1)) ?></div>
                <div class="user-details">
                    <span class="user-name"><?= htmlspecialchars($_SESSION['user']['name'] ?? '') ?></span>
                    <span class="user-role"><?= ucfirst($_SESSION['user']['role'] ?? '') ?></span>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/index.php?url=auth/logout" class="logout-btn" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </aside>

    <div class="main-wrapper">
        <header class="topbar">
            <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
            <div class="topbar-title">
                <h1><?= htmlspecialchars($pageTitle ?? '') ?></h1>
            </div>
            <div class="topbar-right">
                <span class="datetime" id="clock"></span>
            </div>
        </header>

        <main class="main-content">
            <?php if (!empty($flash)): ?>
            <div class="alert alert-<?= $flash['type'] ?>" id="flashMsg">
                <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <?= htmlspecialchars($flash['message']) ?>
                <button onclick="this.parentElement.remove()" class="alert-close">&times;</button>
            </div>
            <?php endif; ?>

            <?= $content ?>
        </main>

        <script src="<?= BASE_URL ?>/js/app.js"></script>
        <!-- ADD THIS LINE BELOW -->
        <script src="<?= BASE_URL ?>/js/music-player.js"></script>
    </div>

    <script src="<?= BASE_URL ?>/js/app.js"></script>
</body>
</html>
