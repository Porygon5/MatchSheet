<div class="result-container">
    <div class="match-header">
        <div class="teams-display">
            <div class="team">
                <div class="team-icon">
                    <?= strtoupper(substr($match->equipeDom->nom, 0, 2)) ?>
                </div>
                <div class="team-name"><?= htmlspecialchars($match->equipeDom->nom) ?></div>
            </div>
            <div class="match-status">
                <?php if ($match->statut == 3): ?>
                    TERMINÉ <?= $arbitrage->scoreDom ?? 0 ?> - <?= $arbitrage->scoreExt ?? 0 ?>
                <?php endif; ?>
            </div>
            <div class="team">
                <div class="team-name"><?= htmlspecialchars($match->equipeExt->nom) ?></div>
                <div class="team-icon">
                    <?= strtoupper(substr($match->equipeExt->nom, 0, 2)) ?>
                </div>
            </div>
        </div>

        <?php if ($arbitrage->tempsJeu): ?>
            <div class="data-status">
                Durée du match : <?= $arbitrage->tempsJeu ?> minutes
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($timelineEvents)): ?>
        <div class="timeline">
            <div class="timeline-container">
                <?php
                $currentMinute = 0;
                $halfTimeShown = false;

                foreach ($timelineEvents as $event):

                    $currentMinute = $event['minute'];

                    // Déterminer la classe pour domicile/extérieur
                    $sideClass = $event['equipe'] === 'dom' ? 'left' : 'right';
                    $teamName = $event['equipe'] === 'dom' ? $match->equipeDom->nom : $match->equipeExt->nom;
                ?>
                    <div class="timeline-event <?= $sideClass ?> <?= $event['type'] === 'but' ? 'goal' : ($event['type'] === 'carton_jaune' ? 'yellow-card' : 'red-card') ?>">
                        <div class="event-time"><?= $event['minute'] ?>'</div>
                        <div class="event-type">
                            <?php
                            if ($event['type'] === 'but') echo "But - " . htmlspecialchars($teamName);
                            elseif ($event['type'] === 'carton_jaune') echo "Carton jaune - " . htmlspecialchars($teamName);
                            elseif ($event['type'] === 'carton_rouge') echo "Carton rouge - " . htmlspecialchars($teamName);
                            ?>
                        </div>
                        <div class="event-player">
                            <?= $event['joueur'] ? htmlspecialchars($event['joueur']->prenom . ' ' . $event['joueur']->nom) : 'Joueur inconnu' ?>
                            <?php if (str_starts_with($event['type'], 'carton')): ?>
                                reçoit un <?= $event['type'] === 'carton_jaune' ? 'carton jaune' : 'carton rouge' ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="timeline">
            <div class="timeline-container">
                <div class="no-events">
                    Aucun événement enregistré pour ce match
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>