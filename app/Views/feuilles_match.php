<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des matchs</title>
</head>
<body>
    <h1>Matchs joués</h1>
    <ul>
        <?php foreach ($matchs as $match): ?>
            <li>
                Match #<?= $match['id_match'] ?> - <?= $match['date_heure'] ?>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
