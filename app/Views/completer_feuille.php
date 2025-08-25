<div class="feuille-main">
    <h1 class="title">Feuille de match</h1>
    
    <form class="match-sheet-form" action="/matchs/selection/save?id=<?= $match->id ?>" method="POST">
        
        <!-- Équipe à domicile -->
        <?php if ($canEditDom): ?>
            <div class="team-section">
                <h2><?= htmlspecialchars($match->equipe_dom_nom) ?> — Équipe à domicile</h2>
                
                <h3>Titulaires</h3>
                <div class="player-group">
                    <?php foreach ($joueursDom as $index => $joueur): ?>
                        <div class="player-row">
                            <div class="player-checkbox">
                                <input type="checkbox" name="titulaire_dom[]" value="<?= $joueur->id ?>" id="titulaire_dom_<?= $index ?>"
                                <?= in_array($joueur->id, $titDom ?? [], true) ? 'checked' : '' ?>>
                                <label for="titulaire_dom_<?= $index ?>"><?= htmlspecialchars($joueur->nom) ?></label>
                            </div>
                            
                            <div class="player-selects">
                                <select name="poste_dom[<?= $joueur->id ?>]">
                                    <?php foreach ($postes as $index => $poste): ?>
                                        <option value="<?= $poste->id_poste ?>"
                                        <?= (isset($posteDomMap[$joueur->id]) && (int)$posteDomMap[$joueur->id] === (int)$poste->id_poste) ? 'selected' : '' ?>
                                        ><?= htmlspecialchars($poste->nom) ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <select name="placement_dom[<?= $joueur->id ?>]">
                                    <?php foreach ($placements as $index => $placement): ?>
                                        <option value="<?= $placement->id_placement ?>"
                                        <?= (isset($placeDomMap[$joueur->id]) && (int)$placeDomMap[$joueur->id] === (int)$placement->id_placement) ? 'selected' : '' ?>
                                        ><?= htmlspecialchars($placement->nom) ?></option>
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
                                <input type="checkbox" name="remplacants_dom[]" value="<?= $joueur->id ?>" id="remplacant_dom<?= $index ?>"
                                <?= in_array($joueur->id, $remDom ?? [], true) ? 'checked' : '' ?>
                                >
                                <label for="remplacant_dom<?= $index ?>"><?= htmlspecialchars($joueur->nom) ?></label>
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
                                <option value="<?= $joueur->id ?>"
                                <?= ((int)($capitaineDom ?? 0) === (int)$joueur->id) ? 'selected' : '' ?>
                                ><?= htmlspecialchars($joueur->nom) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="captain-row">
                        <label for="vice_capitaine_dom">Vice-capitaine</label>
                        <select name="vice_capitaine_dom" id="vice_capitaine_dom">
                            <option value="">Sélectionnez...</option>
                            <?php foreach ($joueursDom as $joueur): ?>
                                <option value="<?= $joueur->id ?>"
                                <?= ((int)($viceDom ?? 0) === (int)$joueur->id) ? 'selected' : '' ?>
                                ><?= htmlspecialchars($joueur->nom) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Équipe extérieure en fonction de l'entraîneur -->
        <?php if ($canEditExt): ?>
            <div class="team-section">
                <h2><?= htmlspecialchars($match->equipe_ext_nom) ?> — Équipe extérieure</h2>
                
                <h3>Titulaires</h3>
                <div class="player-group">
                    <?php foreach ($joueursExt as $index => $joueur): ?>
                        <div class="player-row">
                            <div class="player-checkbox">
                                <input type="checkbox" name="titulaire_ext[]" value="<?= $joueur->id ?>" id="titulaire_ext_<?= $index ?>"
                                <?= in_array($joueur->id, $titExt ?? [], true) ? 'checked' : '' ?>
                                >
                                <label for="titulaire_ext_<?= $index ?>"><?= $joueur->nom ?></label>
                            </div>
                            
                            <div class="player-selects">
                                <select name="poste_ext[<?= $joueur->id ?>]">
                                    <?php foreach ($postes as $index => $poste): ?>
                                        <option value="<?= $poste->id_poste ?>"
                                        <?= (isset($posteExtMap[$joueur->id]) && (int)$posteExtMap[$joueur->id] === (int)$poste->id_poste) ? 'selected' : '' ?>
                                        ><?= $poste->nom ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <select name="placement_ext[<?= $joueur->id ?>]">
                                    <?php foreach ($placements as $index => $placement): ?>
                                        <option value="<?= $placement->id_placement ?>"
                                        <?= (isset($placeExtMap[$joueur->id]) && (int)$placeExtMap[$joueur->id] === (int)$placement->id_placement) ? 'selected' : '' ?>
                                        ><?= $placement->nom ?></option>
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
                                <input type="checkbox" name="remplacants_ext[]" value="<?= $joueur->id ?>" id="remplacant_ext_<?= $index ?>"
                                <?= in_array($joueur->id, $remExt ?? [], true) ? 'checked' : '' ?>
                                >
                                <label for="remplacant_ext_<?= $index ?>"><?= $joueur->nom ?></label>
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
                                <option value="<?= $joueur->id ?>"
                                <?= ((int)($capitaineExt ?? 0) === (int)$joueur->id) ? 'selected' : '' ?>
                                ><?= $joueur->nom ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="captain-row">
                        <label for="vice_capitaine_ext">Vice-capitaine</label>
                        <select name="vice_capitaine_ext" id="vice_capitaine_ext">
                            <option value="">Sélectionnez...</option>
                            <?php foreach ($joueursExt as $joueur): ?>
                                <option value="<?= $joueur->id ?>"
                                <?= ((int)($viceExt ?? 0) === (int)$joueur->id) ? 'selected' : '' ?>
                                ><?= $joueur->nom ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <button type="submit" name="action" class="submit-btn" value="save">Enregistrer la feuille de match</button>
        <button type="submit" name="action" class="submit-btn" value="submit_sheet">Soumettre la feuille de match</button>
    </form>
</div>