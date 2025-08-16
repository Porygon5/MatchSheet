<div class="feuille-main">
    <h1 class="title">Feuille de match</h1>
    
    <form class="match-sheet-form" action="/matchs/update_sheet?id=<?= $match->id ?>" method="POST">
        
        <!-- Équipe à domicile -->
        <div class="team-section">
            <h2><?= htmlspecialchars($match->equipe_dom_nom) ?> — Équipe à domicile</h2>
            
            <h3>Titulaires</h3>
            <div class="player-group">
                <?php foreach ($joueursDom as $index => $joueur): ?>
                    <div class="player-row">
                        <div class="player-checkbox">
                            <input type="checkbox" name="titulaire_dom[]" value="<?= $joueur->id ?>" id="titulaire_dom_<?= $index ?>">
                            <label for="titulaire_dom_<?= $index ?>"><?= $joueur->nom ?></label>
                        </div>
                        
                        <div class="player-selects">
                            <select name="poste_dom[<?= $joueur->id ?>]">
                                <?php foreach ($postes as $index => $poste): ?>
                                    <option value="<?= $poste->id_poste ?>"><?= $poste->nom ?></option>
                                <?php endforeach; ?>
                            </select>

                            <select name="placement_dom[<?= $joueur->id ?>]">
                                <?php foreach ($placements as $index => $placement): ?>
                                    <option value="<?= $placement->id_placement ?>"><?= $placement->nom ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <h3>Remplaçants</h3>
            <div class="player-group">
                <?php foreach ($joueursDom as $index => $joueur): ?>
                    <div class="player-row">
                        <div class="player-checkbox">
                            <input type="checkbox" name="remplacants_dom[]" value="<?= $joueur->id ?>" id="remplaçant_dom_<?= $index ?>">
                            <label for="remplaçant_dom_<?= $index ?>"><?= $joueur->nom ?></label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <h3>Capitaines</h3>
            <div class="captains-section">
                <div class="captain-row">
                    <label for="capitaine_dom">Capitaine</label>
                    <select name="capitaine_dom" id="capitaine_dom">
                        <option value="">Sélectionnez...</option>
                        <?php foreach ($joueursDom as $joueur): ?>
                            <option value="<?= $joueur->id ?>"><?= $joueur->nom ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="captain-row">
                    <label for="vice_capitaine_dom">Vice-capitaine</label>
                    <select name="vice_capitaine_dom" id="vice_capitaine_dom">
                        <option value="">Sélectionnez...</option>
                        <?php foreach ($joueursDom as $joueur): ?>
                            <option value="<?= $joueur->id ?>"><?= $joueur->nom ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Équipe extérieure en fonction de l'entraîneur -->
        <div class="team-section">
            <h2><?= htmlspecialchars($match->equipe_ext_nom) ?> — Équipe extérieure</h2>
            
            <h3>Titulaires</h3>
            <div class="player-group">
                <?php foreach ($joueursExt as $index => $joueur): ?>
                    <div class="player-row">
                        <div class="player-checkbox">
                            <input type="checkbox" name="titulaire_ext[]" value="<?= $joueur->id ?>" id="titulaire_ext_<?= $index ?>">
                            <label for="titulaire_ext_<?= $index ?>"><?= $joueur->nom ?></label>
                        </div>
                        
                        <div class="player-selects">
                            <select name="poste_dom[<?= $joueur->id ?>]">
                                <?php foreach ($postes as $index => $poste): ?>
                                    <option value="<?= $poste->id_poste ?>"><?= $poste->nom ?></option>
                                <?php endforeach; ?>
                            </select>

                            <select name="placement_dom[<?= $joueur->id ?>]">
                                <?php foreach ($placements as $index => $placement): ?>
                                    <option value="<?= $placement->id_placement ?>"><?= $placement->nom ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <h3>Remplaçants</h3>
            <div class="player-group">
                <?php foreach ($joueursExt as $index => $joueur): ?>
                    <div class="player-row">
                        <div class="player-checkbox">
                            <input type="checkbox" name="remplacants_ext[]" value="<?= $joueur->id ?>" id="remplaçant_ext_<?= $index ?>">
                            <label for="remplaçant_ext_<?= $index ?>"><?= $joueur->nom ?></label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <h3>Capitaines</h3>
            <div class="captains-section">
                <div class="captain-row">
                    <label for="capitaine_ext">Capitaine</label>
                    <select name="capitaine_ext" id="capitaine_ext">
                        <option value="">Sélectionnez...</option>
                        <?php foreach ($joueursExt as $joueur): ?>
                            <option value="<?= $joueur->id ?>"><?= $joueur->nom ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="captain-row">
                    <label for="vice_capitaine_ext">Vice-capitaine</label>
                    <select name="vice_capitaine_ext" id="vice_capitaine_ext">
                        <option value="">Sélectionnez...</option>
                        <?php foreach ($joueursExt as $joueur): ?>
                            <option value="<?= $joueur->id ?>"><?= $joueur->nom ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <button type="submit" class="submit-btn">Enregistrer la feuille de match</button>
    </form>
</div>