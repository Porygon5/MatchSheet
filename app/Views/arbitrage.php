<div class="feuille-main">
	<h1 class="title">Arbitrage — Feuille de match</h1>

	<form class="match-sheet-form" action="/matchs/arbitrage/save?id=<?= (int)$match->id ?>" method="POST">
		<input type="hidden" name="id_match" value="<?= (int)$match->id ?>">

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

		<div class="team-section" id="goals">
			<h2>Buts</h2>

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

				<div class="player-row buts-row template" style="display:none;">
					<div class="player-checkbox">
						<label>Minute</label>
						<input type="number" min="0" max="130" name="buts_dom[__i__][minute]" placeholder="42" class="arb-input arb-input--sm" disabled>
					</div>
					<div class="player-selects">
						<select name="buts_dom[__i__][joueur_id]" class="arb-select arb-select--sm" disabled>
							<option value="">Buteur…</option>
							<?php foreach ($joueursDom as $j): ?>
								<option value="<?= (int)$j->id ?>"><?= htmlspecialchars($j->nom) ?></option>
							<?php endforeach; ?>
						</select>
						<button type="button" class="remove-row arb-btn-ghost">Supprimer</button>
					</div>
				</div>
			</div>
			<button type="button" class="submit-btn" data-add="#buts_dom" data-kind="buts">+ Ajouter un but (dom.)</button>

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

				<div class="player-row buts-row template" style="display:none;">
					<div class="player-checkbox">
						<label>Minute</label>
						<input type="number" min="0" max="130" name="buts_ext[__i__][minute]" placeholder="67" class="arb-input arb-input--sm" disabled>
					</div>
					<div class="player-selects">
						<select name="buts_ext[__i__][joueur_id]" class="arb-select arb-select--sm" disabled>
							<option value="">Buteur…</option>
							<?php foreach ($joueursExt as $j): ?>
								<option value="<?= (int)$j->id ?>"><?= htmlspecialchars($j->nom) ?></option>
							<?php endforeach; ?>
						</select>
						<button type="button" class="remove-row arb-btn-ghost">Supprimer</button>
					</div>
				</div>
			</div>
			<button type="button" class="submit-btn" data-add="#buts_ext" data-kind="buts">+ Ajouter un but (ext.)</button>
		</div>

		<div class="team-section" id="cards">
			<h2>Cartons</h2>

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

				<div class="player-row cartons-row template" style="display:none;">
					<div class="player-checkbox">
						<label>Type</label>
						<select name="cartons_dom[__i__][type]" class="arb-select arb-select--sm" disabled>
							<option value="jaune">Carton jaune</option>
							<option value="rouge">Carton rouge</option>
						</select>
					</div>
					<div class="player-selects">
						<select name="cartons_dom[__i__][joueur_id]" class="arb-select arb-select--sm" disabled>
							<option value="">Joueur…</option>
							<?php foreach ($joueursDom as $j): ?>
								<option value="<?= (int)$j->id ?>"><?= htmlspecialchars($j->nom) ?></option>
							<?php endforeach; ?>
						</select>
						<input type="number" min="0" max="130" name="cartons_dom[__i__][minute]" placeholder="Minute" class="arb-input arb-input--sm" disabled>
						<button type="button" class="remove-row arb-btn-ghost">Supprimer</button>
					</div>
				</div>
			</div>
			<button type="button" class="submit-btn" data-add="#cartons_dom" data-kind="cartons">+ Ajouter un carton (dom.)</button>

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

				<div class="player-row cartons-row template" style="display:none;">
					<div class="player-checkbox">
						<label>Type</label>
						<select name="cartons_ext[__i__][type]" class="arb-select arb-select--sm" disabled>
							<option value="jaune">Carton jaune</option>
							<option value="rouge">Carton rouge</option>
						</select>
					</div>
					<div class="player-selects">
						<select name="cartons_ext[__i__][joueur_id]" class="arb-select arb-select--sm" disabled>
							<option value="">Joueur…</option>
							<?php foreach ($joueursExt as $j): ?>
								<option value="<?= (int)$j->id ?>"><?= htmlspecialchars($j->nom) ?></option>
							<?php endforeach; ?>
						</select>
						<input type="number" min="0" max="130" name="cartons_ext[__i__][minute]" placeholder="Minute" class="arb-input arb-input--sm" disabled>
						<button type="button" class="remove-row arb-btn-ghost">Supprimer</button>
					</div>
				</div>
			</div>
			<button type="button" class="submit-btn" data-add="#cartons_ext" data-kind="cartons">+ Ajouter un carton (ext.)</button>
		</div>

		<div class="team-section" id="substitutions">
			<h2>Changements</h2>

			<h3><?= htmlspecialchars($match->equipe_dom_nom) ?> — Remplacements</h3>
			<div class="player-group" id="subs_dom">
				<?php foreach ($arbitrage->subsDom as $i => $sub): ?>
					<div class="player-row subs-row">
						<div class="player-checkbox">
							<label>Minute</label>
							<input type="number" min="0" max="130" name="subs_dom[<?= (int)$i ?>][minute]" placeholder="60" class="arb-input arb-input--sm" value="<?= (int)$sub['minute'] ?>">
						</div>
						<div class="player-selects">
							<select name="subs_dom[<?= (int)$i ?>][out]" class="arb-select arb-select--sm">
								<option value="">Joueur sortant…</option>
								<?php foreach ($joueursDom as $j): ?>
									<option value="<?= (int)$j->id ?>" <?= ((int)$j->id === (int)$sub['out']) ? 'selected' : '' ?>>
										<?= htmlspecialchars($j->nom) ?>
									</option>
								<?php endforeach; ?>
							</select>
							<select name="subs_dom[<?= (int)$i ?>][in]" class="arb-select arb-select--sm">
								<option value="">Joueur entrant…</option>
								<?php foreach ($joueursDom as $j): ?>
									<option value="<?= (int)$j->id ?>" <?= ((int)$j->id === (int)$sub['in']) ? 'selected' : '' ?>>
										<?= htmlspecialchars($j->nom) ?>
									</option>
								<?php endforeach; ?>
							</select>
							<button type="button" class="remove-row arb-btn-ghost">Supprimer</button>
						</div>
					</div>
				<?php endforeach; ?>

				<div class="player-row subs-row template" style="display:none;">
					<div class="player-checkbox">
						<label>Minute</label>
						<input type="number" min="0" max="130" name="subs_dom[__i__][minute]" placeholder="60" class="arb-input arb-input--sm" disabled>
					</div>
					<div class="player-selects">
						<select name="subs_dom[__i__][out]" class="arb-select arb-select--sm" disabled>
							<option value="">Joueur sortant…</option>
							<?php foreach ($joueursDom as $j): ?>
								<option value="<?= (int)$j->id ?>"><?= htmlspecialchars($j->nom) ?></option>
							<?php endforeach; ?>
						</select>
						<select name="subs_dom[__i__][in]" class="arb-select arb-select--sm" disabled>
							<option value="">Joueur entrant…</option>
							<?php foreach ($joueursDom as $j): ?>
								<option value="<?= (int)$j->id ?>"><?= htmlspecialchars($j->nom) ?></option>
							<?php endforeach; ?>
						</select>
						<button type="button" class="remove-row arb-btn-ghost">Supprimer</button>
					</div>
				</div>
			</div>
			<button type="button" class="submit-btn" data-add="#subs_dom" data-kind="subs">+ Ajouter un remplacement (dom.)</button>

			<h3><?= htmlspecialchars($match->equipe_ext_nom) ?> — Remplacements</h3>
			<div class="player-group" id="subs_ext">
				<?php foreach ($arbitrage->subsExt as $i => $sub): ?>
					<div class="player-row subs-row">
						<div class="player-checkbox">
							<label>Minute</label>
							<input type="number" min="0" max="130" name="subs_ext[<?= (int)$i ?>][minute]" placeholder="75" class="arb-input arb-input--sm" value="<?= (int)$sub['minute'] ?>">
						</div>
						<div class="player-selects">
							<select name="subs_ext[<?= (int)$i ?>][out]" class="arb-select arb-select--sm">
								<option value="">Joueur sortant…</option>
								<?php foreach ($joueursExt as $j): ?>
									<option value="<?= (int)$j->id ?>" <?= ((int)$j->id === (int)$sub['out']) ? 'selected' : '' ?>>
										<?= htmlspecialchars($j->nom) ?>
									</option>
								<?php endforeach; ?>
							</select>
							<select name="subs_ext[<?= (int)$i ?>][in]" class="arb-select arb-select--sm">
								<option value="">Joueur entrant…</option>
								<?php foreach ($joueursExt as $j): ?>
									<option value="<?= (int)$j->id ?>" <?= ((int)$j->id === (int)$sub['in']) ? 'selected' : '' ?>>
										<?= htmlspecialchars($j->nom) ?>
									</option>
								<?php endforeach; ?>
							</select>
							<button type="button" class="remove-row arb-btn-ghost">Supprimer</button>
						</div>
					</div>
				<?php endforeach; ?>

				<div class="player-row subs-row template" style="display:none;">
					<div class="player-checkbox">
						<label>Minute</label>
						<input type="number" min="0" max="130" name="subs_ext[__i__][minute]" placeholder="75" class="arb-input arb-input--sm" disabled>
					</div>
					<div class="player-selects">
						<select name="subs_ext[__i__][out]" class="arb-select arb-select--sm" disabled>
							<option value="">Joueur sortant…</option>
							<?php foreach ($joueursExt as $j): ?>
								<option value="<?= (int)$j->id ?>"><?= htmlspecialchars($j->nom) ?></option>
							<?php endforeach; ?>
						</select>
						<select name="subs_ext[__i__][in]" class="arb-select arb-select--sm" disabled>
							<option value="">Joueur entrant…</option>
							<?php foreach ($joueursExt as $j): ?>
								<option value="<?= (int)$j->id ?>"><?= htmlspecialchars($j->nom) ?></option>
							<?php endforeach; ?>
						</select>
						<button type="button" class="remove-row arb-btn-ghost">Supprimer</button>
					</div>
				</div>
			</div>
			<button type="button" class="submit-btn" data-add="#subs_ext" data-kind="subs">+ Ajouter un remplacement (ext.)</button>
		</div>

		<button type="submit" name="action" class="submit-btn" value="save_officiating">Enregistrer l'arbitrage</button>
		<button type="submit" name="action" class="submit-btn" value="close_match">Clore le match</button>
	</form>
</div>

<script>
(function(){
	let counter = Date.now();
	
	function addRow(containerSelector, kind) {
		const container = document.querySelector(containerSelector);
		const template = container?.querySelector(`.${kind}-row.template`);
		if (!container || !template) return;
		
		const clone = template.cloneNode(true);
		const idx = ++counter;
		
		clone.classList.remove('template');
		clone.style.display = '';
		
		clone.querySelectorAll('input, select').forEach(el => {
			el.disabled = false;
			if (el.name) el.name = el.name.replace('[__i__]', `[${idx}]`);
		});
		
		container.appendChild(clone);
	}

	document.querySelectorAll('.template input, .template select').forEach(el => el.disabled = true);
	
	document.addEventListener('click', e => {
		if (e.target.matches('button[data-add]')) {
			const container = e.target.getAttribute('data-add');
			const kind = e.target.getAttribute('data-kind');
			addRow(container, kind);
		}
		
		if (e.target.matches('.remove-row')) {
			e.target.closest('.player-row')?.remove();
		}
	});
})();
</script>