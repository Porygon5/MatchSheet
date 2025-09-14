<div class="mobile-tabs">
    <button class="tab-btn active" onclick="showTab('upcoming')">Prochains matchs</button>
    <button class="tab-btn" onclick="showTab('results')">Derniers résultats</button>
</div>

<!-- Section "Prochains matchs" -->
<h1 class="title" id="title-upcoming">Prochains matchs</h1>
<div class="match-container" id="upcoming">
    <?php if (!empty($upcomingMatches)): ?>
        <?php foreach ($upcomingMatches as $match): ?>
            <div class="match-card">
                <div class="match-header">
                    <span class="match-date"><?= htmlspecialchars($match->dateFormatee) ?></span>
                </div>

                <div class="match-teams">
                    <div class="team">
                        <div class="team-icon"><?= htmlspecialchars($match->equipeDom->abreviation) ?></div>
                        <span class="team-name"><?= htmlspecialchars($match->equipeDom->nom) ?></span>
                    </div>
                    <div class="vs-separator">VS</div>
                    <div class="team">
                        <div class="team-name"><?= htmlspecialchars($match->equipeExt->nom) ?></div>
                        <span class="team-icon"><?= htmlspecialchars($match->equipeExt->abreviation) ?></span>
                    </div>
                </div>

                <div class="match-location">
                    <span class="location-icon">📍</span>
                    <span class="location-name"><?= htmlspecialchars($match->lieu->nom) ?></span>
                </div>

                <a href="/matchs/selection?id=<?= $match->id ?>" class="details-button">Voir les détails</a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="no-matches">Aucun match à venir pour le moment.</p>
    <?php endif; ?>
</div>

<!-- Section "Derniers résultats" -->
<h1 class="title" id="title-results">Derniers résultats</h1>
<div class="last-match-container" id="results">
    <?php if (!empty($lastMatches)): ?>
        <?php foreach ($lastMatches as $match): ?>
            <div class="match-card">
                <div class="match-header">
                    <span class="match-date"><?= htmlspecialchars($match->dateFormatee) ?></span>
                </div>

                <div class="match-teams">
                    <div class="team">
                        <div class="team-icon"><?= htmlspecialchars($match->equipeDom->abreviation) ?></div>
                        <span class="team-name"><?= htmlspecialchars($match->equipeDom->nom) ?></span>
                    </div>
                    <div class="vs-separator">
                        <?= $match->scoreEquipeDom ?? '-' ?> - <?= $match->scoreEquipeExt ?? '-' ?>
                    </div>
                    <div class="team">
                        <div class="team-name"><?= htmlspecialchars($match->equipeExt->nom) ?></div>
                        <span class="team-icon"><?= htmlspecialchars($match->equipeExt->abreviation) ?></span>
                    </div>
                </div>

                <a href="/matchs/view?id=<?= $match->id ?>" class="details-button">Feuille de match</a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="no-matches">Aucun résultat disponible.</p>
    <?php endif; ?>
</div>

<script>
function showTab(tab) {
    const upcoming = document.getElementById('upcoming');
    const results = document.getElementById('results');
    const titleUpcoming = document.getElementById('title-upcoming');
    const titleResults = document.getElementById('title-results');
    const buttons = document.querySelectorAll('.tab-btn');

    // Affichage du bon bloc
    upcoming.style.display = tab === 'upcoming' ? 'flex' : 'none';
    results.style.display = tab === 'results' ? 'flex' : 'none';

    // Affichage du bon titre
    titleUpcoming.style.display = tab === 'upcoming' ? 'block' : 'none';
    titleResults.style.display = tab === 'results' ? 'block' : 'none';

    // Gestion du bouton actif
    buttons.forEach(btn => btn.classList.remove('active'));
    document.querySelector(`.tab-btn[onclick*="${tab}"]`).classList.add('active');
}
</script>