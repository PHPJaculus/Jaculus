<?php

/**
 * This is the place for 404, 405, maintenance mode handlers.
 */

$r->not_found('error/not_found.html');
$r->method_not_allowed('error/method_not_allowed.html');
$r->server_error('error/server_error.html');
$r->maintenance('error/maintenance.html');