<?php
require_once('database/init.php');
session_start();
session_destroy();
redirect_to("index.php");
?>
