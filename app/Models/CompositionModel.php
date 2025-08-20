<?php
// Models/CompositionModel.php

namespace App\Models;

use PDO;
use App\Entities\Composition;

require_once __DIR__ . '/../Entities/Composition.php';

class CompositionModel
{
    private PDO $pdo;

    public function __construct(PDO $db)
    {
        $this->pdo = $db;
    }

    /**
     * Enregistre la composition d'une équipe dans joueurs_selectionnes
     */
    public function enregistrerCompositionEquipe(Composition $c, ?int $idViceCapitaine = null): void
    {
        // Supprimer la compo existante pour cette équipe
        $del = $this->pdo->prepare("
            DELETE FROM joueurs_selectionnes
            WHERE id_equipe = :e
        ");
        $del->execute(['e' => $c->getIdEquipe()]);

        // Préparer l'insert
        $ins = $this->pdo->prepare("
            INSERT INTO joueurs_selectionnes
                (id_poste_definitif, id_placement_definitif, id_role, titulaire, id_joueur, id_equipe)
            VALUES (:poste, :placement, :role, :titulaire, :joueur, :equipe)
        ");

        // Insérer toutes les entrées
        foreach ($c->getEntries() as $row) {
            $idJoueur  = (int)($row['id_joueur'] ?? 0);
            $titulaire = (int)($row['is_titulaire'] ?? 0);

            $idPoste     = isset($row['id_poste']) ? (int)$row['id_poste'] : null;
            $idPlacement = isset($row['id_placement']) ? (int)$row['id_placement'] : null;

            // On calcule le rôle
            $idRole = $this->determineRoleId(
                $idJoueur,
                $titulaire === 1,
                $c->getIdCapitaine(),
                $idViceCapitaine
            );

            $ins->execute([
                'poste' => $idPoste,
                'placement' => $idPlacement,
                'role' => $idRole,
                'titulaire' => $titulaire,
                'joueur' => $idJoueur,
                'equipe' => $c->getIdEquipe(),
            ]);
        }
    }

    private function determineRoleId(
        int $idJoueur,
        bool $estTitulaire,
        ?int $idCapitaine,
        ?int $idViceCapitaine
    ): int {
        if ($idCapitaine !== null && $idJoueur === $idCapitaine) {
            return 1; // Capitaine
        }
        if ($idViceCapitaine !== null && $idJoueur === $idViceCapitaine) {
            return 2; // Suppléant
        }
        return $estTitulaire ? 3 : 4; // Joueur titulaire sinon Remplaçant
    }

    public function getComposition(int $idEquipe): array
    {
        // Valeurs par défaut si aucune donnée en BDD
        $data = [
            'capitaine' => null,   // id du capitaine
            'vice'      => null,   // id du vice-capitaine (suppléant)
            'tit'       => [],     // titulaires
            'rem'       => [],     // remplaçants
            'poste'     => [],     // id_poste_definitif
            'placement' => [],     // id_placement_definitif
        ];

        $sql = "SELECT id_joueur, titulaire, id_poste_definitif, id_placement_definitif, id_role
                FROM joueurs_selectionnes
                WHERE id_equipe = :e";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['e' => $idEquipe]);

        while ($row = $stmt->fetch()) {
            $idJoueur  = (int)$row['id_joueur'];
            $titulaire = ((int)$row['titulaire'] === 1);

            // Maps pour les <select>
            if ($row['id_poste_definitif'] !== null) {
                $data['poste'][$idJoueur] = (int)$row['id_poste_definitif'];
            }
            if ($row['id_placement_definitif'] !== null) {
                $data['placement'][$idJoueur] = (int)$row['id_placement_definitif'];
            }

            // Rôles
            $idRole = (int)$row['id_role'];
            if ($idRole === 1) {
                $data['capitaine'] = $idJoueur;
            } elseif ($idRole === 2) {
                $data['vice'] = $idJoueur;
            }

            // Cases à cocher
            if ($titulaire) {
                $data['tit'][] = $idJoueur;
            } else {
                $data['rem'][] = $idJoueur;
            }
        }

        return $data;
    }
}
