<?php
require __DIR__ . '/adminIzende/init.php';
$admins = WHMCS\Database\Capsule::table('tbladmins')->get(['username','email']);
foreach ($admins as $a) {
    echo $a->username . '|' . $a->email . "\n";
}
