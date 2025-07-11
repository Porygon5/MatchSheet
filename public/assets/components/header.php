<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>MatchSheet</title>
  <link rel="stylesheet" href="/MatchSheet/public/assets/css/style.css">
  <link rel="stylesheet" href="/MatchSheet/public/assets/components/header.css" />
</head>
<body>
  <header class="header">
    <div class="logo-section">
      <div class="logo">
        <img src="/MatchSheet/public/assets/images/logo.png" alt="Logo FFF" class="logo-img" />
    </div>
      <h1 class="brand-name">MatchSheet</h1>
    </div>

    <!-- Menu desktop -->
    <nav class="nav-menu">
      <ul class="nav-links">
        <li><a href="index.php" class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>">Accueil</a></li>
        <li><a href="equipes.php" class="nav-link <?= $currentPage === 'equipes.php' ? 'active' : '' ?>">Équipes</a></li>
        <li><a href="feuilles_match.php" class="nav-link <?= $currentPage === 'feuilles_match.php' ? 'active' : '' ?>">Matchs</a></li>
        <li><a href="classement.php" class="nav-link <?= $currentPage === 'classement.php' ? 'active' : '' ?>">Classement</a></li>
      </ul>
      <a href="login.php" class="connect-btn">Se connecter</a>
    </nav>

    <!-- Burger toggle -->
    <div class="burger-toggle" onclick="toggleMobileMenu()">
      <span></span>
    </div>
  </header>

  <!-- Menu mobile injecté dans .nav-menu -->
  <div class="nav-menu mobile-nav" id="mobileMenu">
    <ul class="nav-links">
      <li><a href="index.php" class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>" onclick="closeMobileMenu()">Accueil</a></li>
      <li><a href="equipes.php" class="nav-link <?= $currentPage === 'equipes.php' ? 'active' : '' ?>" onclick="closeMobileMenu()">Équipes</a></li>
      <li><a href="feuilles_match.php" class="nav-link <?= $currentPage === 'feuilles_match.php' ? 'active' : '' ?>" onclick="closeMobileMenu()">Matchs</a></li>
      <li><a href="classement.php" class="nav-link <?= $currentPage === 'classement.php' ? 'active' : '' ?>" onclick="closeMobileMenu()">Classement</a></li>
    </ul>
    <button class="connect-btn" onclick="closeMobileMenu()">Se connecter</button>
  </div>

  <script>
    function toggleMobileMenu() {
      const mobileMenu = document.getElementById('mobileMenu');
      const toggle = document.querySelector('.burger-toggle');
      mobileMenu.classList.toggle('active');
      toggle.classList.toggle('active');
    }

    function closeMobileMenu() {
      document.getElementById('mobileMenu').classList.remove('active');
      document.querySelector('.burger-toggle').classList.remove('active');
    }

    window.addEventListener('resize', function () {
      if (window.innerWidth > 480) closeMobileMenu();
    });

    document.addEventListener('click', function (e) {
      const header = document.querySelector('.header');
      const menu = document.getElementById('mobileMenu');
      if (!header.contains(e.target) && !menu.contains(e.target)) {
        closeMobileMenu();
      }
    });
  </script>
</body>
</html>