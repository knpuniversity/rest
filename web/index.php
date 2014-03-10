<?php

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

$app = require_once __DIR__.'/../app/bootstrap.php';

/*
 * Repeated in the server
 *
 * A big hack. In Behat, I was seeing that Guzzle was making requests to
 * localhost:9002 (the host I was using locally). But when it hit the application,
 * the HTTP_HOST header was "localhost" instead of "localhost:9002", which
 * caused the port to be wrong on all generated links. This hacks around
 * whatever bug (on my end or another) that caused that.
 */
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;
$port = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : null;
if (
    !in_array($port, array('80', '443'))
    && (false === $pos = strrpos($host, ':'))
) {
    $_SERVER['HTTP_HOST'] .= ':'.$port;
}

$request = Request::createFromGlobals();
$app->run($request);
