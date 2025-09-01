<?php
$requestUri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$currentPage = explode('/', $requestUri)[0];
$isLoggedIn = isset($_SESSION['user']);
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<base href="/" />
		<title>MatchSheet</title>
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
					<li>
            <a href="/accueil" class="nav-link <?= $currentPage === 'accueil' ? 'active' : '' ?>">Accueil</a>
					</li>
					<li>
            <a href="/equipes" class="nav-link <?= $currentPage === 'equipes' ? 'active' : '' ?>">Équipes</a>
					</li>
					<li>
						<a href="/matchs" class="nav-link <?= $currentPage === 'matchs' ? 'active' : '' ?>">Matchs</a>
					</li>
				</ul>
				<?php if ($isLoggedIn): ?>
					<form action="/logout" method="POST" style="display:inline;">
						<button type="submit" class="connect-btn">Se déconnecter</button>
					</form>
				<?php else: ?>
					<a href="/login" class="connect-btn">Se connecter</a>
				<?php endif; ?>
			</nav>
			<!-- Burger toggle -->
			<div class="burger-toggle" onclick="toggleMobileMenu()">
				<span></span>
			</div>
		</header>
		<!-- Menu mobile injecté dans .nav-menu -->
		<div class="nav-menu mobile-nav" id="mobileMenu">
			<ul class="nav-links">
				<li>
					<a href="/accueil" class="nav-link <?= $currentPage === 'accueil' ? 'active' : '' ?>" onclick="closeMobileMenu()">Accueil </a>
				</li>
				<li>
					<a href="/equipes" class="nav-link <?= $currentPage === 'equipes' ? 'active' : '' ?>" onclick="closeMobileMenu()">Équipes </a>
				</li>
				<li>
					<a href="/matchs" class="nav-link <?= $currentPage === 'matchs' ? 'active' : '' ?>" onclick="closeMobileMenu()">Matchs </a>
				</li>
			</ul>
			<?php if ($isLoggedIn): ?>
				<form action="/logout" method="POST">
					<button type="submit" class="connect-btn" onclick="closeMobileMenu()">Se déconnecter</button>
				</form>
			<?php else: ?>
				<a href="/login" class="connect-btn" onclick="closeMobileMenu()">Se connecter</a>
			<?php endif; ?>
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
			window.addEventListener('resize', function() {
				if (window.innerWidth > 480) closeMobileMenu();
			});
			document.addEventListener('click', function(e) {
				const header = document.querySelector('.header');
				const menu = document.getElementById('mobileMenu');
				if (!header.contains(e.target) && !menu.contains(e.target)) {
					closeMobileMenu();
				}
			});
		</script>
	</body>
</html>