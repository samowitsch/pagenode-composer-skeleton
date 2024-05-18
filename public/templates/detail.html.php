<!DOCTYPE html>
<html dir="ltr" lang="de-DE">

<head>
    <title><?= $node->title ?? '' ?> - WWW.YOURDOMAIN.HERE</title>
    <meta name="keywords" content="<?= $node->keywords ?? 'default,keywords' ?>">
    <meta name="description" content="<?= $node->description ?? 'Default description' ?>">

    <?php require_once 'partials/resources.header.html.php'; ?>

</head>

<body>
    <?php if ($node->thumbnail) { ?>
        <img src="<?= $node->thumbnail ?>" title="thumbnail" />
    <?php } ?>

    <p><?= $node->date ?></p>
    <p>Categories: <?= $node->categories ?></p>
    <p>Tags: <?php echo implode(', ', $node->tags); ?></p>

    <?= $node->body ?? 'EMPTY?' ?>

    <?php require_once 'partials/resources.footer.html.php'; ?>

</body>

</html>