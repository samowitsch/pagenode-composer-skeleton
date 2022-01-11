<!DOCTYPE html>
<html dir="ltr" lang="de-DE">
<head>
    <title><?= $node->title ?? '' ?> - WWW.YOURDOMAIN.HERE</title>
    <meta name="keywords" content="<?= $node->keywords ?? 'default,keywords' ?>">
    <meta name="description" content="<?= $node->description ?? 'Default description' ?>">

<?php require_once 'templates/partials/resources.header.html.php'; ?>

</head>
<body>

<?= $node->body ?? 'EMPTY?' ?>

<?php require_once 'templates/partials/resources.footer.html.php'; ?>

</body>
</html>