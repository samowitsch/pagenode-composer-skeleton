<?php

require_once('../vendor/autoload.php');

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

if (
    empty($_REQUEST['name']) &&
    empty($_REQUEST['email']) &&
    empty($_REQUEST['message'])
) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request', true, 400);
    echo '400 Bad Request';
    exit;
}

if (isset($_SERVER['IS_DDEV_PROJECT']) && $_SERVER['IS_DDEV_PROJECT'] === 'true') {
    $transport = Transport::fromDsn('smtp://localhost:1025');
} else {
    $transport = Transport::fromDsn('native://default');
}
$mailer = new Mailer($transport);

$body = <<<BODY
<p><strong>Name:</strong> ${_REQUEST['name']}</p>
<p><strong>E-Mail:</strong> ${_REQUEST['email']}</p>
<p><strong>Telefon:</strong> ${_REQUEST['telephone']}</p>
<p><strong>Anfrage:</strong> ${_REQUEST['anfrage']}</p>
<p><strong>Nachricht:</strong> ${_REQUEST['message']}</p>
BODY;

$email = (new Email())
    ->from('noreply@foo.bar')
    ->to('info@foo.bar')
    ->cc('info@anotherfoo.bar')
    ->subject('Foo message!')
    ->html($body);

$mailer->send($email);