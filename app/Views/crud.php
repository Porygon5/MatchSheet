<!-- app/Views/crud.php -->
<div class="crud-main">
    <h1 class="title">Gestion des équipes et joueurs</h1>
    
    <!-- Section Équipes (Admin seulement) -->
    <?php if ($isAdmin): ?>
    <div class="crud-section">
        <div class="section-header">
            <h2>Équipes</h2>
            <button onclick="toggleForm('equipe-form')" class="btn-primary">+ Ajouter une équipe</button>
        </div>
        
        <!-- Formulaire d'ajout équipe -->
        <div id="equipe-form" class="form-container" style="display: none;">
            <h3>Nouvelle équipe</h3>
            <form action="/joueurs/equipe/add" method="POST" class="crud-form">
                <div class="form-row">
                    <label for="nom">Nom de l'équipe</label>
                    <input type="text" name="nom" id="nom" required>
                </div>
                
                <div class="form-row">
                    <label for="id_club">Club</label>
                    <select name="id_club" id="id_club" required>
                        <option value="">Sélectionner un club...</option>
                            <?php foreach ($clubs as $club): ?>
                                <option value="<?= $club->id ?>"><?= htmlspecialchars($club->nom) ?></option>
                            <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <label for="id_entraineur">Entraîneur</label>
                    <select name="id_entraineur" id="id_entraineur" required>
                        <option value="">Sélectionner un entraineur...</option>
                                <?php foreach ($entraineurs as $entraineur): ?>
                                    <option value="<?= $entraineur->id ?>"><?= htmlspecialchars($entraineur->nom) ?></option>
                                <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-success">Créer l'équipe</button>
                    <button type="button" onclick="toggleForm('equipe-form')" class="btn-secondary">Annuler</button>
                </div>
            </form>
        </div>

        <!-- Liste des équipes -->
        <div class="data-table">
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Nb joueurs</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($equipes as $equipe): ?>
                    <tr>
                        <td><?= htmlspecialchars($equipe->nom) ?></td>
                        <td>
                            <?php 
                            $nbJoueurs = count(array_filter($joueurs, fn($j) => $j->equipeId === $equipe->id));
                            echo $nbJoueurs;
                            ?>
                        </td>
                        <td class="actions">
                            <a href="/joueurs/equipe/edit?id=<?= $equipe->id ?>" class="btn-edit">Modifier</a>
                            <a href="/joueurs/equipe/delete?id=<?= $equipe->id ?>" 
                               onclick="return confirm('Supprimer cette équipe et tous ses joueurs ?')" 
                               class="btn-delete">Supprimer</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Section Joueurs -->
    <div class="crud-section">
        <div class="section-header">
            <h2>Joueurs<?= !$isAdmin ? ' de votre équipe' : '' ?></h2>
            <button onclick="toggleForm('joueur-form')" class="btn-primary">+ Ajouter un joueur</button>
        </div>
        
        <!-- Formulaire d'ajout joueur -->
        <div id="joueur-form" class="form-container" style="display: none;">
            <h3>Nouveau joueur</h3>
            <form action="/joueurs/add" method="POST" class="crud-form">
                <div class="form-row">
                    <label for="nom">Nom</label>
                    <input type="text" name="nom" id="nom" required>
                </div>
                
                <div class="form-row">
                    <label for="prenom">Prénom</label>
                    <input type="text" name="prenom" id="prenom" required>
                </div>
                
                <div class="form-row">
                    <label for="nationalite">Nationalité</label>
                    <input type="text" name="nationalite" id="nationalite" value="France">
                </div>
                
                <div class="form-row">
                    <label for="numero">Numéro</label>
                    <input type="number" name="numero" id="numero" min="1" max="99" required>
                </div>
                
                <div class="form-row">
                    <label for="id_equipe">Équipe</label>
                    <select name="id_equipe" id="id_equipe" required>
                        <option value="">Sélectionner une équipe...</option>
                        <?php foreach ($equipes as $equipe): ?>
                            <option value="<?= $equipe->id ?>"><?= htmlspecialchars($equipe->nom) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <label for="id_poste">Poste préféré</label>
                    <select name="id_poste" id="id_poste" required>
                        <option value="">Sélectionner un poste...</option>
                        <?php foreach ($postes as $poste): ?>
                            <option value="<?= $poste->id_poste ?>"><?= htmlspecialchars($poste->nom) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <label for="id_placement">Placement préféré</label>
                    <select name="id_placement" id="id_placement" required>
                        <option value="">Sélectionner un placement...</option>
                        <?php foreach ($placements as $placement): ?>
                            <option value="<?= $placement->id_placement ?>"><?= htmlspecialchars($placement->nom) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-success">Créer le joueur</button>
                    <button type="button" onclick="toggleForm('joueur-form')" class="btn-secondary">Annuler</button>
                </div>
            </form>
        </div>

        <!-- Transfert de joueur (Admin seulement) -->
        <?php if ($isAdmin && !empty($joueurs)): ?>
        <div class="transfer-section">
            <button onclick="toggleForm('transfer-form')" class="btn-warning">Transférer un joueur</button>
            
            <div id="transfer-form" class="form-container" style="display: none;">
                <h3>Transfert de joueur</h3>
                <form action="/joueurs/transfer" method="POST" class="crud-form">
                    <div class="form-row">
                        <label for="id_joueur">Joueur à transférer</label>
                        <select name="id_joueur" id="id_joueur" required>
                            <option value="">Sélectionner un joueur...</option>
                            <?php foreach ($joueurs as $joueur): ?>
                                <option value="<?= $joueur->id ?>">
                                    <?= htmlspecialchars($joueur->prenom . ' ' . $joueur->nom) ?> - N°<?= $joueur->numero ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <label for="id_equipe_cible">Équipe de destination</label>
                        <select name="id_equipe_cible" id="id_equipe_cible" required>
                            <option value="">Sélectionner une équipe...</option>
                            <?php foreach ($equipes as $equipe): ?>
                                <option value="<?= $equipe->id ?>"><?= htmlspecialchars($equipe->nom) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <label for="nouveau_numero">Nouveau numéro</label>
                        <input type="number" name="nouveau_numero" id="nouveau_numero" min="1" max="99" required>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-warning">Effectuer le transfert</button>
                        <button type="button" onclick="toggleForm('transfer-form')" class="btn-secondary">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Liste des joueurs -->
        <div class="data-table">
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>N°</th>
                        <th>Équipe</th>
                        <th>Poste</th>
                        <th>Placement</th>
                        <th>Nationalité</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($joueurs as $joueur): ?>
                    <tr>
                        <td><?= htmlspecialchars($joueur->nom) ?></td>
                        <td><?= htmlspecialchars($joueur->prenom) ?></td>
                        <td><span class="numero-badge"><?= $joueur->numero ?></span></td>
                        <td>
                            <?php 
                            $equipeJoueur = array_filter($equipes, fn($e) => $e->id === $joueur->equipeId);
                            echo $equipeJoueur ? htmlspecialchars(current($equipeJoueur)->nom) : 'Aucune équipe';
                            ?>
                        </td>
                        <td>
                            <?php 
                            $posteJoueur = array_filter($postes, fn($p) => $p->id_poste === $joueur->posteId);
                            echo $posteJoueur ? htmlspecialchars(current($posteJoueur)->nom) : '-';
                            ?>
                        </td>
                        <td>
                            <?php 
                            $placementJoueur = array_filter($placements, fn($pl) => $pl->id_placement === $joueur->placementId);
                            echo $placementJoueur ? htmlspecialchars(current($placementJoueur)->nom) : '-';
                            ?>
                        </td>
                        <td><?= htmlspecialchars($joueur->nationalite) ?></td>
                        <td class="actions">
                            <a href="/joueurs/edit?id=<?= $joueur->id ?>" class="btn-edit">Modifier</a>
                            <a href="/joueurs/delete?id=<?= $joueur->id ?>" 
                               onclick="return confirm('Supprimer ce joueur ?')" 
                               class="btn-delete">Supprimer</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Fonction pour afficher/masquer les formulaires
function toggleForm(formId) {
    const form = document.getElementById(formId);
    const isVisible = form.style.display !== 'none';
    
    // Masquer tous les formulaires
    document.querySelectorAll('.form-container').forEach(f => f.style.display = 'none');
    
    // Afficher le formulaire demandé s'il était masqué
    if (!isVisible) {
        form.style.display = 'block';
    }
}

// Fermer les formulaires en cliquant en dehors
document.addEventListener('click', function(e) {
    if (!e.target.closest('.form-container') && !e.target.closest('button[onclick*="toggleForm"]')) {
        document.querySelectorAll('.form-container').forEach(f => f.style.display = 'none');
    }
});
</script>