<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Login') ?> - <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css">
</head>
<body class="auth-body">

<audio id="bg-music" loop autoplay>
    <source src="<?= BASE_URL ?>/audio/background-music.mp3" type="audio/mpeg">
</audio>

<?= $content ?>

<script src="<?= BASE_URL ?>/js/app.js"></script>
<script>
    window.addEventListener('DOMContentLoaded', (event) => {
        const music = document.getElementById('bg-music');

        // Set volume to 15% (Very low background level)
        music.volume = 0.15;

        // Attempt to play immediately
        const attemptPlay = () => {
            music.play().then(() => {
                // If successful, remove the backup listeners
                document.removeEventListener('click', attemptPlay);
                document.removeEventListener('keydown', attemptPlay);
            }).catch(error => {
                // Autoplay was prevented by browser policy
                console.log("Autoplay waiting for user interaction...");
            });
        };

        // Run attempt on load
        attemptPlay();

        // Backup: Start music on first click or keypress if autoplay was blocked
        document.addEventListener('click', attemptPlay);
        document.addEventListener('keydown', attemptPlay);
    });
</script>
</body>
</html>