<div class="feuille-main">
    <h1 class="title">Créer une feuille de match</h1>
    <form class="feuille-form" action="/matchs/store" method="POST" autocomplete="on">
        <div class="form-row">
            <label for="date">Date du match</label>
            <input type="date" name="date" id="date" required>
        </div>
        <div class="form-row">
            <label for="heure">Heure du match</label>
            <input type="time" name="heure" id="heure" required>
        </div>

        <div class="form-row">
            <label for="equipe_dom">Équipe à domicile</label>
            <select name="equipe_dom" id="equipe_dom" required>
                <option value="">Sélectionnez...</option>
                <?php foreach ($equipes as $equipe): ?>
                    <option value="<?= $equipe->id ?>"><?= htmlspecialchars($equipe->nom) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <label for="equipe_ext">Équipe extérieure</label>
            <select name="equipe_ext" id="equipe_ext" required>
                <option value="">Sélectionnez...</option>
                <?php foreach ($equipes as $equipe): ?>
                    <option value="<?= $equipe->id ?>"><?= htmlspecialchars($equipe->nom) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-row">
            <label for="lieu_rencontre">Lieu</label>
            <select name="lieu_rencontre" id="lieu_rencontre" required>
                <option value="">Sélectionnez...</option>
                <?php foreach ($lieux as $lieu): ?>
                    <option value="<?= $lieu->id ?>"><?= htmlspecialchars($lieu->nom) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <label for="arbitre_central">Arbitre central</label>
            <select name="arbitre_central" id="arbitre_central" required>
                <option value="">Sélectionnez un arbitre</option>
                <?php foreach ($arbitres as $arbitre): ?>
                    <option value="<?= $arbitre->id ?>"><?= htmlspecialchars($arbitre->nom) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <label for="arbitre_assistant1">Arbitre assistant 1</label>
            <select name="arbitre_assistant1" id="arbitre_assistant1" required>
                <option value="">Sélectionnez l'arbitre assistant n°1</option>
                <?php foreach ($arbitres as $arbitre): ?>
                    <option value="<?= $arbitre->id ?>"><?= htmlspecialchars($arbitre->nom) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <label for="arbitre_assistant2">Arbitre assistant 2</label>
            <select name="arbitre_assistant2" id="arbitre_assistant2" required>
                <option value="">Sélectionnez l'arbitre assistant n°2</option>
                <?php foreach ($arbitres as $arbitre): ?>
                    <option value="<?= $arbitre->id ?>"><?= htmlspecialchars($arbitre->nom) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <button type="submit" class="create-btn" style="width:100%;">Créer la feuille de match</button>
        </div>
    </form>
</div>