<?php

$novaSenha = 'TomaziaBar24admin_#WebApp00';
$hashed_password = password_hash($novaSenha, PASSWORD_DEFAULT);
echo $hashed_password;


?>