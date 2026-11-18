<?php
$url = '../storage/app/public';
$enlace = 'storage';
symlink($url, $enlace);
echo readlink($enlace);
