<?php
if(isset($_ENV['CLEARDB_DATABASE_URL'])){
    $db_url = $_ENV['CLEARDB_DATABASE_URL'];
}
else{
    $db_url = 'mysql://root:root@127.0.0.1/symfony?reconnect=true';
}
$container->setParameter('database_driver', 'pdo_mysql');
$container->setParameter('database_url', $db_url);
if(isset($_ENV['MAIL_USER']) && isset($_ENV['MAIL_PASS'])){
    $container->setParameter('mailUser',$_ENV['MAIL_USER']);
    $container->setParameter('mailPass', $_ENV['MAIL_PASS']);
}