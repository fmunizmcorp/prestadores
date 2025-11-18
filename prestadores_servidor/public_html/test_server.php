<?php
echo 'HTTP_HOST: ' . ($_SERVER['HTTP_HOST'] ?? 'NOT SET') . '<br>';
echo 'SERVER_NAME: ' . ($_SERVER['SERVER_NAME'] ?? 'NOT SET') . '<br>';
echo 'REQUEST_URI: ' . ($_SERVER['REQUEST_URI'] ?? 'NOT SET') . '<br>';
