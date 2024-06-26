<!DOCTYPE html>
<html dir="ltr" lang="de-DE">

<head>
    <title><?= $node->title ?? '' ?> - WWW.YOURDOMAIN.HERE</title>
    <meta name="keywords" content="<?= $node->keywords ?? 'default,keywords' ?>">
    <meta name="description" content="<?= $node->description ?? 'Default description' ?>">

    <?php require_once 'partials/resources.header.html.php'; ?>

</head>

<body>
    <ul>
        <?php foreach ($posts as $key => $post) { ?>
            <li>[<?= $post->postId ?>] <a href="/<?= $post->fullPath ?>" id="<?= $post->title ?>" title="<?= $post->title ?>">
            <?= $post->title ?>
            </a>[<?= $post->thumbnail ?>]
            </li>
        <?php } ?>
    </ul>

    <?= $node->body ?? 'EMPTY?' ?>

    <?php require_once 'partials/resources.footer.html.php'; ?>

</body>

</html>