<div class="crud-main">
    <h1 class="title">Modifier l'utilisateur</h1>
    
    <div class="crud-section">
        <form action="/joueurs/utilisateur/edit?id=<?= $utilisateur->id() ?>" method="POST" class="crud-form">
            <div class="form-row">
                <label for="nom_utilisateur">Nom d'utilisateur</label>
                <input type="text" name="nom_utilisateur" id="nom_utilisateur" value="<?= htmlspecialchars($utilisateur->nomUtilisateur()) ?>" required>
            </div>
            
            <div class="form-row">
                <label for="id_permission">Rôle</label>
                <select name="id_permission" id="id_permission" required>
                    <option value="1" <?= $utilisateur->idPermission() === 1 ? 'selected' : '' ?>>Administrateur</option>
                    <option value="2" <?= $utilisateur->idPermission() === 2 ? 'selected' : '' ?>>Entraîneur</option>
                </select>
            </div>
            
            <div class="form-row">
                <label for="nouveau_mot_de_passe">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                <input type="password" name="nouveau_mot_de_passe" id="nouveau_mot_de_passe">
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-success">Sauvegarder</button>
                <a href="/joueurs" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>