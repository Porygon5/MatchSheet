<div class="crud-main">
    <h1 class="title">Modifier l'équipe</h1>
    
    <div class="crud-section">
        <form action="/joueurs/equipe/edit?id=<?= $equipe->id ?>" method="POST" class="crud-form">
            <div class="form-row">
                <label for="nom">Nom de l'équipe</label>
                <input type="text" name="nom" id="nom" value="<?= htmlspecialchars($equipe->nom) ?>" required>
            </div>
            
            <div class="form-row">
                <label for="id_club">Club</label>
                <select name="id_club" id="id_club" required>
                    <option value="">Sélectionner un club...</option>
                    <?php foreach ($clubs as $club): ?>
                        <option value="<?= $club->id ?>" <?= (int)$equipe->id_club === (int)$club->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($club->nom) ?> (<?= htmlspecialchars($club->ville) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-success">Sauvegarder</button>
                <a href="/joueurs" class="btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>