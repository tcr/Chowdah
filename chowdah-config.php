<?php

// HTTP Authorization header hack
$_SERVER['HTTP_AUTHORIZATION'] = $_SERVER['REDIRECT_REMOTE_USER'];

?>