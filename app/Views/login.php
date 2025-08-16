<div class="feuille-main">
    <h1 class="title">Connexion</h1>

    <div class="login-wrapper">
        <form class="login-form" action="/login/submit" method="POST" autocomplete="on">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <div>
                <label for="login-username">Nom d'utilisateur</label>
                <input id="login-username" name="nom_utilisateur" required autocomplete="username" />
            </div>

            <div>
                <label for="login-password">Mot de passe</label>
                <input type="password" id="login-password" name="password" required autocomplete="current-password" />
            </div>

            <button type="submit" class="submit-btn">Se connecter</button>
        </form>
    </div>
</div>