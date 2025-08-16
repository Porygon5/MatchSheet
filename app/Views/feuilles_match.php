<div class="feuille-main">
	<div class="title-feuilles">
		<h1 class="title">Feuilles de match</h1>
		<a href="/matchs/create" class="create-btn">+ Créer une feuille de match</a>
	</div>
	<div class="feuille-section">
		<h2 class="section-title section-title-acompleter">À compléter</h2>
		<div class="match-container">
			<?php foreach ($matchsACompleter as $match): ?>
            <div class="match-card feuille-card-acompleter">
                <div class="match-header"><?= $match->dateFormatee ?></div>
                <div class="match-teams">
                    <div class="team">
                        <div class="team-icon"><?= substr($match->equipe_dom_nom, 0, 2) ?></div>
                        <span class="team-name"><?= $match->equipe_dom_nom ?></span>
                    </div>
                    <div class="vs-separator">VS</div>
                    <div class="team">
                        <div class="team-name"><?= $match->equipe_ext_nom ?></div>
                        <span class="team-icon"><?= substr($match->equipe_ext_nom, 0, 2) ?></span>
                    </div>
                </div>
                <div class="match-location">
                    <span class="location-icon">📍</span>
                    <span class="location-name"><?= $match->lieu_nom ?></span>
                </div>
                <div class="feuille-status badge-acompleter">À compléter</div>
                <a href="/matchs/selection?id=<?= $match->id ?>" class="details-button">Compléter</a>
            </div>
            <?php endforeach; ?>
		</div>
	</div>
	<div class="feuille-section">
		<h2 class="section-title section-title-aconclure">À conclure</h2>
		<div class="match-container">
			<?php foreach ($matchsAConclure as $match): ?>
            <div class="match-card feuille-card-aconclure">
                <div class="match-header"><?= $match->dateFormatee ?></div>
                <div class="match-teams">
                    <div class="team">
                        <div class="team-icon"><?= strtoupper(substr($match->equipe_dom_nom, 0, 2)) ?></div>
                        <span class="team-name"><?= htmlspecialchars($match->equipe_dom_nom) ?></span>
                    </div>
                    <div class="vs-separator">VS</div>
                    <div class="team">
                        <div class="team-name"><?= htmlspecialchars($match->equipe_ext_nom) ?></div>
                        <span class="team-icon"><?= strtoupper(substr($match->equipe_ext_nom, 0, 2)) ?></span>
                    </div>
                </div>
                <div class="match-location">
                    <span class="location-icon">📍</span>
                    <span class="location-name"><?= htmlspecialchars($match->lieu_nom) ?></span>
                </div>
                <div class="feuille-status badge-aconclure">À conclure</div>
                <a href="conclure_feuille.php?id=<?= $match->id ?>" class="details-button">Conclure</a>
            </div>
            <?php endforeach; ?>
		</div>
	</div>
	<div class="feuille-section">
		<h2 class="section-title section-title-terminee">Terminées</h2>
		<div class="match-container">
			<?php foreach ($matchs as $match) : ?>
                <?php if ($match->scoreEquipeDom !== null && $match->scoreEquipeExt !== null) : ?>
                    <div class="match-card feuille-card-terminee">
                        <div class="match-header"><?= $match->dateFormatee ?></div>
                        <div class="match-teams">
                            <div class="team">
                                <div class="team-icon"><?= htmlspecialchars($match->equipeDom->abreviation ?? '') ?></div>
                                <span class="team-name"><?= htmlspecialchars($match->equipeDom->nom ?? '') ?></span>
                            </div>
                            <div class="vs-separator">VS</div>
                            <div class="team">
                                <div class="team-name"><?= htmlspecialchars($match->equipeExt->nom ?? '') ?></div>
                                <span class="team-icon"><?= htmlspecialchars($match->equipeExt->abreviation ?? '') ?></span>
                            </div>
                        </div>
                        <div class="match-location">
                            <span class="location-icon">📍</span>
                            <span class="location-name"><?= htmlspecialchars($match->lieu->nom ?? '') ?></span>
                        </div>
                        <div class="feuille-status badge-terminee">Terminée</div>
                        <a href="match.php?id=<?= $match->id ?>" class="details-button">Voir la feuille</a>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
		</div>
	</div>
</div>