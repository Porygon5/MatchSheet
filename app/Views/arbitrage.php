<div class="feuille-main">
	<h1 class="title">Arbitrage — Feuille de match</h1>

	<form class="match-sheet-form" action="/matchs/arbitrage/save?id=<?= (int)$match->id ?>" method="POST">
		<input type="hidden" name="id_match" value="<?= (int)$match->id ?>">

		<!--  RESULTATS -->
		<div class="team-section" id="results">
			<h2>Résultat et durée</h2>
			<div class="player-group">
				<div class="player-row">
					<div class="player-checkbox">
						<label for="score_dom">
							<strong><?= htmlspecialchars($match->equipe_dom_nom) ?></strong> — Score
						</label>
					</div>
					<div class="player-selects">
						<input type="number" min="0" id="score_dom" name="score_dom" placeholder="0" class="arb-input arb-input--sm"
							   value="<?= $arbitrage->scoreDom !== null ? (int)$arbitrage->scoreDom : '' ?>">
					</div>
				</div>

				<div class="player-row">
					<div class="player-checkbox">
						<label for="score_ext">
							<strong><?= htmlspecialchars($match->equipe_ext_nom) ?></strong> — Score
						</label>
					</div>
					<div class="player-selects">
						<input type="number" min="0" id="score_ext" name="score_ext" placeholder="0" class="arb-input arb-input--sm"
							   value="<?= $arbitrage->scoreExt !== null ? (int)$arbitrage->scoreExt : '' ?>">
					</div>
				</div>

				<div class="player-row">
					<div class="player-checkbox">
						<label for="temps_jeu">Temps de jeu (minutes)</label>
					</div>
					<div class="player-selects">
						<input type="number" min="1" id="temps_jeu" name="temps_jeu" placeholder="90" class="arb-input"
							   value="<?= $arbitrage->tempsJeu !== null ? (int)$arbitrage->tempsJeu : '' ?>">
					</div>
				</div>
			</div>
		</div>

		<!-- BUTS -->
		<div class="team-section" id="goals">
			<h2>Buts</h2>

			<!-- DOMICILE -->
			<h3><?= htmlspecialchars($match->equipe_dom_nom) ?> — Buts</h3>
			<div class="player-group" id="buts_dom">

				<?php foreach ($arbitrage->butsDom as $i => $but): ?>
					<div class="player-row buts-row">
						<div class="player-checkbox">
							<label>Minute</label>
							<input type="number" min="0" max="130" name="buts_dom[<?= (int)$i ?>][minute]" placeholder="42"
								   class="arb-input arb-input--sm" value="<?= (int)$but['minute'] ?>">
						</div>
						<div class="player-selects">
							<select name="buts_dom[<?= (int)$i ?>][joueur_id]" class="arb-select arb-select--sm">
								<option value="">Buteur…</option>
								<?php foreach ($joueursDom as $j): ?>
									<option value="<?= (int)$j->id ?>" <?= ((int)$j->id === (int)$but['joueur_id']) ? 'selected' : '' ?>>
										<?= htmlspecialchars($j->nom) ?>
									</option>
								<?php endforeach; ?>
							</select>
							<button type="button" class="remove-row arb-btn-ghost">Supprimer</button>
						</div>
					</div>
				<?php endforeach; ?>

				<div class="player-row buts-row template" data-side="dom" style="display:none;">
					<div class="player-checkbox">
						<label>Minute</label>
						<input type="number" min="0" max="130" name="buts_dom[__i__][minute]" placeholder="42" class="arb-input arb-input--sm">
					</div>
					<div class="player-selects">
						<select name="buts_dom[__i__][joueur_id]" class="arb-select arb-select--sm">
							<option value="">Buteur…</option>
							<?php foreach ($joueursDom as $j): ?>
								<option value="<?= (int)$j->id ?>"><?= htmlspecialchars($j->nom) ?></option>
							<?php endforeach; ?>
						</select>
						<button type="button" class="remove-row arb-btn-ghost">Supprimer</button>
					</div>
				</div>
			</div>
			<button type="button" class="submit-btn" data-add="#buts_dom" data-kind="buts" data-side="dom">+ Ajouter un but (dom.)</button>

			<!-- EXTÉRIEUR -->
			<h3><?= htmlspecialchars($match->equipe_ext_nom) ?> — Buts</h3>
			<div class="player-group" id="buts_ext">

				<?php foreach ($arbitrage->butsExt as $i => $but): ?>
					<div class="player-row buts-row">
						<div class="player-checkbox">
							<label>Minute</label>
							<input type="number" min="0" max="130" name="buts_ext[<?= (int)$i ?>][minute]" placeholder="67"
								   class="arb-input arb-input--sm" value="<?= (int)$but['minute'] ?>">
						</div>
						<div class="player-selects">
							<select name="buts_ext[<?= (int)$i ?>][joueur_id]" class="arb-select arb-select--sm">
								<option value="">Buteur…</option>
								<?php foreach ($joueursExt as $j): ?>
									<option value="<?= (int)$j->id ?>" <?= ((int)$j->id === (int)$but['joueur_id']) ? 'selected' : '' ?>>
										<?= htmlspecialchars($j->nom) ?>
									</option>
								<?php endforeach; ?>
							</select>
							<button type="button" class="remove-row arb-btn-ghost">Supprimer</button>
						</div>
					</div>
				<?php endforeach; ?>

				<div class="player-row buts-row template" data-side="ext" style="display:none;">
					<div class="player-checkbox">
						<label>Minute</label>
						<input type="number" min="0" max="130" name="buts_ext[__i__][minute]" placeholder="67" class="arb-input arb-input--sm">
					</div>
					<div class="player-selects">
						<select name="buts_ext[__i__][joueur_id]" class="arb-select arb-select--sm">
							<option value="">Buteur…</option>
							<?php foreach ($joueursExt as $j): ?>
								<option value="<?= (int)$j->id ?>"><?= htmlspecialchars($j->nom) ?></option>
							<?php endforeach; ?>
						</select>
						<button type="button" class="remove-row arb-btn-ghost">Supprimer</button>
					</div>
				</div>
			</div>
			<button type="button" class="submit-btn" data-add="#buts_ext" data-kind="buts" data-side="ext">+ Ajouter un but (ext.)</button>
		</div>

		<!-- CARTONS -->
		<div class="team-section" id="cards">
			<h2>Cartons</h2>

			<!-- DOMICILE -->
			<h3><?= htmlspecialchars($match->equipe_dom_nom) ?> — Cartons</h3>
			<div class="player-group" id="cartons_dom">

				<?php foreach ($arbitrage->cartonsDom as $i => $carton): ?>
					<div class="player-row cartons-row">
						<div class="player-checkbox">
							<label>Type</label>
							<select name="cartons_dom[<?= (int)$i ?>][type]" class="arb-select arb-select--sm">
								<option value="jaune" <?= ($carton['type'] === 'jaune') ? 'selected' : '' ?>>Carton jaune</option>
								<option value="rouge" <?= ($carton['type'] === 'rouge') ? 'selected' : '' ?>>Carton rouge</option>
							</select>
						</div>
						<div class="player-selects">
							<select name="cartons_dom[<?= (int)$i ?>][joueur_id]" class="arb-select arb-select--sm">
								<option value="">Joueur…</option>
								<?php foreach ($joueursDom as $j): ?>
									<option value="<?= (int)$j->id ?>" <?= ((int)$j->id === (int)$carton['joueur_id']) ? 'selected' : '' ?>>
										<?= htmlspecialchars($j->nom) ?>
									</option>
								<?php endforeach; ?>
							</select>
							<input type="number" min="0" max="130" name="cartons_dom[<?= (int)$i ?>][minute]" placeholder="Minute"
								   class="arb-input arb-input--sm" value="<?= (int)$carton['minute'] ?>">
							<button type="button" class="remove-row arb-btn-ghost">Supprimer</button>
						</div>
					</div>
				<?php endforeach; ?>

				<div class="player-row cartons-row template" data-side="dom" style="display:none;">
					<div class="player-checkbox">
						<label>Type</label>
						<select name="cartons_dom[__i__][type]" class="arb-select arb-select--sm">
							<option value="jaune">Carton jaune</option>
							<option value="rouge">Carton rouge</option>
						</select>
					</div>
					<div class="player-selects">
						<select name="cartons_dom[__i__][joueur_id]" class="arb-select arb-select--sm">
							<option value="">Joueur…</option>
							<?php foreach ($joueursDom as $j): ?>
								<option value="<?= (int)$j->id ?>"><?= htmlspecialchars($j->nom) ?></option>
							<?php endforeach; ?>
						</select>
						<input type="number" min="0" max="130" name="cartons_dom[__i__][minute]" placeholder="Minute" class="arb-input arb-input--sm">
						<button type="button" class="remove-row arb-btn-ghost">Supprimer</button>
					</div>
				</div>
			</div>
			<button type="button" class="submit-btn" data-add="#cartons_dom" data-kind="cartons" data-side="dom">+ Ajouter un carton (dom.)</button>

			<!-- CARTONS EXTÉRIEUR -->
			<h3><?= htmlspecialchars($match->equipe_ext_nom) ?> — Cartons</h3>
			<div class="player-group" id="cartons_ext">

				<?php foreach ($arbitrage->cartonsExt as $i => $carton): ?>
					<div class="player-row cartons-row">
						<div class="player-checkbox">
							<label>Type</label>
							<select name="cartons_ext[<?= (int)$i ?>][type]" class="arb-select arb-select--sm">
								<option value="jaune" <?= ($carton['type'] === 'jaune') ? 'selected' : '' ?>>Carton jaune</option>
								<option value="rouge" <?= ($carton['type'] === 'rouge') ? 'selected' : '' ?>>Carton rouge</option>
							</select>
						</div>
						<div class="player-selects">
							<select name="cartons_ext[<?= (int)$i ?>][joueur_id]" class="arb-select arb-select--sm">
								<option value="">Joueur…</option>
								<?php foreach ($joueursExt as $j): ?>
									<option value="<?= (int)$j->id ?>" <?= ((int)$j->id === (int)$carton['joueur_id']) ? 'selected' : '' ?>>
										<?= htmlspecialchars($j->nom) ?>
									</option>
								<?php endforeach; ?>
							</select>
							<input type="number" min="0" max="130" name="cartons_ext[<?= (int)$i ?>][minute]" placeholder="Minute"
								   class="arb-input arb-input--sm" value="<?= (int)$carton['minute'] ?>">
							<button type="button" class="remove-row arb-btn-ghost">Supprimer</button>
						</div>
					</div>
				<?php endforeach; ?>

				<div class="player-row cartons-row template" data-side="ext" style="display:none;">
					<div class="player-checkbox">
						<label>Type</label>
						<select name="cartons_ext[__i__][type]" class="arb-select arb-select--sm">
							<option value="jaune">Carton jaune</option>
							<option value="rouge">Carton rouge</option>
						</select>
					</div>
					<div class="player-selects">
						<select name="cartons_ext[__i__][joueur_id]" class="arb-select arb-select--sm">
							<option value="">Joueur…</option>
							<?php foreach ($joueursExt as $j): ?>
								<option value="<?= (int)$j->id ?>"><?= htmlspecialchars($j->nom) ?></option>
							<?php endforeach; ?>
						</select>
						<input type="number" min="0" max="130" name="cartons_ext[__i__][minute]" placeholder="Minute" class="arb-input arb-input--sm">
						<button type="button" class="remove-row arb-btn-ghost">Supprimer</button>
					</div>
				</div>
			</div>
			<button type="button" class="submit-btn" data-add="#cartons_ext" data-kind="cartons" data-side="ext">+ Ajouter un carton (ext.)</button>
		</div>

		<button type="submit" name="action" class="submit-btn" value="save_officiating" >Enregistrer l’arbitrage</button>
		<button type="submit" name="action" class="submit-btn" value="close_match">Clore le match</button>
	</form>
</div>

<script>
	/* Duplication/suppression de lignes (buts/cartons) */
	(function() {
		function addRow(containerSelector, kind) {
			const container = document.querySelector(containerSelector);
			if (!container) return;
			const tpl = container.querySelector(`.${kind}-row.template`);
			if (!tpl) return;
			const clone = tpl.cloneNode(true);
			clone.classList.remove('template');
            clone.querySelectorAll('input, select').forEach(el => {
                el.disabled = false; // on réactive uniquement sur l'instance clonée
            });
			clone.style.display = '';
			clone.querySelectorAll('input[type="number"]').forEach(i => {
				i.classList.add('arb-input', 'arb-input--sm');
			});
			clone.querySelectorAll('select').forEach(s => {
				s.classList.add('arb-select', 'arb-select--sm');
			});
			const delBtn = clone.querySelector('.remove-row');
			if (delBtn) delBtn.classList.add('arb-btn-ghost');
			const idx = Date.now() + Math.floor(Math.random() * 1000);
			clone.querySelectorAll('input, select').forEach(el => {
				if (el.name) el.name = el.name.replace('[__i__]', `[${idx}]`);
			});
			container.appendChild(clone);
		}
        // Désactiver tous les champs présents dans les templates pour éviter l'envoi
        document.querySelectorAll('.template input, .template select').forEach(el => {
            el.disabled = true;
        });
		document.querySelectorAll('button[data-add]').forEach(btn => {
			btn.addEventListener('click', () => {
				const container = btn.getAttribute('data-add');
				const kind = btn.getAttribute('data-kind');
				addRow(container, kind);
			});
		});
		document.addEventListener('click', (e) => {
			if (e.target && e.target.classList.contains('remove-row')) {
				const row = e.target.closest('.player-row');
				if (row) row.remove();
			}
		});
	})();
</script>
