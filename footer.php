<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!-- Link to Google Fonts (same as the navigation) -->
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

<!-- Updated footer with smoother fonts and shadows -->
<footer class="bg-dark text-white text-center" style="padding: 10px 0; font-family: 'Roboto', sans-serif; font-size: 14px; box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);">
    <p>
        <a href="https://github.com/engren/filamentmanager" class="text-white" target="_blank" style="font-weight: 500;">https://github.com/engren/filamentmanager</a>
    </p>
</footer>

<style>
    footer {
        position: fixed;
        bottom: 0;
        width: 100%;
    }
</style>

