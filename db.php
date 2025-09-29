<?php
$mysql = @new Mysqli('localhost', '047582029_diplom', 'Diplom_41', 'j38202257_diplom');
if($mysql->connecterrno) exit('Ошибка подключения к БД');
$mysql-set_charset('utf8');
?>
