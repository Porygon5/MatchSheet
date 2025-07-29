<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'MatchSheet' ?></title>

    <!-- Style global -->
    <link rel="stylesheet" href="/assets/css/style.css">

    <!-- Header -->
    <link rel="stylesheet" href="/assets/components/header.css">

    <!-- CSS spécifique à la page si défini -->
    <?php if (isset($pageCss)) : ?>
        <link rel="stylesheet" href="<?= $pageCss ?>">
    <?php endif; ?>
</head>
<body>
    <?php require_once __DIR__ . '/../../public/assets/components/header.php'; ?>

    <main>
        <?php include($view); ?>
    </main>
</body>
</html>
