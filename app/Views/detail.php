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
            <button id="printBtn" class="print-button">
                Imprimer la feuille
            </button>
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
                <?php foreach ($timelineEvents as $event):
                    $sideClass = $event['equipe'] === 'dom' ? 'left' : 'right';
                    $teamName = $event['equipe'] === 'dom' ? $match->equipeDom->nom : $match->equipeExt->nom;
                ?>
                    <?php if ($event['type'] === 'but'): ?>
                        <div class="timeline-event <?= $sideClass ?> goal">
                            <div class="event-time"><?= $event['minute'] ?>'</div>
                            <div class="event-type">But - <?= htmlspecialchars($teamName) ?></div>
                            <div class="event-player">
                                <?= $event['joueur'] ? htmlspecialchars($event['joueur']->prenom . ' ' . $event['joueur']->nom) : 'Joueur inconnu' ?>
                            </div>
                        </div>

                    <?php elseif (str_starts_with($event['type'], 'carton')): ?>
                        <div class="timeline-event <?= $sideClass ?> <?= $event['type'] === 'carton_jaune' ? 'yellow-card' : 'red-card' ?>">
                            <div class="event-time"><?= $event['minute'] ?>'</div>
                            <div class="event-type">
                                <?= $event['type'] === 'carton_jaune' ? 'Carton jaune' : 'Carton rouge' ?> - <?= htmlspecialchars($teamName) ?>
                            </div>
                            <div class="event-player">
                                <?= $event['joueur'] ? htmlspecialchars($event['joueur']->prenom . ' ' . $event['joueur']->nom) : 'Joueur inconnu' ?>
                                reçoit un <?= $event['type'] === 'carton_jaune' ? 'carton jaune' : 'carton rouge' ?>
                            </div>
                        </div>

                    <?php elseif ($event['type'] === 'sub'): ?>
                        <div class="timeline-event <?= $sideClass ?> substitution">
                            <div class="event-time"><?= $event['minute'] ?>'</div>
                            <div class="event-type">Remplacement - <?= htmlspecialchars($teamName) ?></div>
                            <div class="substitution-details">
                                <div class="substitution-out">
                                    Sortie : <?= $event['joueur_out'] ? htmlspecialchars($event['joueur_out']->prenom . ' ' . $event['joueur_out']->nom) : 'Joueur inconnu' ?>
                                </div>
                                <div class="substitution-in">
                                    Entrée : <?= $event['joueur_in'] ? htmlspecialchars($event['joueur_in']->prenom . ' ' . $event['joueur_in']->nom) : 'Joueur inconnu' ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
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

<script>
    document.getElementById("printBtn").addEventListener("click", function() {
        window.print();
    });
</script>