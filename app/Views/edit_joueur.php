<div class="crud-main">
    <h1 class="title">Modifier le joueur</h1>
    
    <div class="crud-section">
        <form action="/joueurs/edit?id=<?= $joueur->id ?>" method="POST" class="crud-form">
            <div class="form-row">
                <label for="nom">Nom</label>
                <input type="text" name="nom" id="nom" value="<?= htmlspecialchars($joueur->nom) ?>" required>
            </div>
            
            <div class="form-row">
                <label for="prenom">Prénom</label>
                <input type="text" name="prenom" id="prenom" value="<?= htmlspecialchars($joueur->prenom) ?>" required>
            </div>
            
            <div class="form-row">
                <label for="nationalite">Nationalité</label>
                <input type="text" name="nationalite" id="nationalite" value="<?= htmlspecialchars($joueur->nationalite) ?>">
            </div>
            
            <div class="form-row">
                <label for="numero">Numéro</label>
                <input type="number" name="numero" id="numero" min="1" max="99" value="<?= $joueur->numero ?>" required>
            </div>
            
            <div class="form-row">
                <label for="id_equipe">Équipe</label>
                <select name="id_equipe" id="id_equipe" required <?= !$isAdmin ? 'disabled' : '' ?>>
                    <?php foreach ($equipes as $equipe): ?>
                        <option value="<?= $equipe->id ?>" <?= $joueur->equipeId === $equipe->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($equipe->nom) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!$isAdmin): ?>
                    <input type="hidden" name="id_equipe" value="<?= $joueur->equipeId ?>">
                <?php endif; ?>
            </div>
            
            <div class="form-row">
                <label for="id_poste">Poste préféré</label>
                <select name="id_poste" id="id_poste" required>
                    <?php foreach ($postes as $poste): ?>
                        <option value="<?= $poste->id_poste ?>" <?= $joueur->posteId === $poste->id_poste ? 'selected' : '' ?>>
                            <?= htmlspecialchars($poste->nom) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-row">
                <label for="id_placement">Placement préféré</label>
                <select name="id_placement" id="id_placement" required>
                    <?php foreach ($placements as $placement): ?>
                        <option value="<?= $placement->id_placement ?>" <?= $joueur->placementId === $placement->id_placement ? 'selected' : '' ?>>
                            <?= htmlspecialchars($placement->nom) ?>
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