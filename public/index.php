<?php

require __DIR__."/../vendor/autoload.php";


/* Load services into dependency injection container */

$app = new ServicesContainer();

if (isset($_SERVER['HTTP_DEBUG']) and $_SERVER['HTTP_DEBUG'] == $app->config->debug->debug_key) {
	ini_set('display_errors', 1);
	ini_set('html_errors', 1);
	error_reporting(E_ALL | E_STRICT);
	header('Debug: Enabled');
} else {
	$error_handler = new Raven_ErrorHandler($app->sentry);
	set_error_handler(array($error_handler, 'handleError'));
	set_exception_handler(array($error_handler, 'handleException'));
}

/* Define routing and dispatch controllers to build response */

$router = new Routing\Router($app);

// Set URL slug patterns
$router->setPattern('eventslug', '\d{4}\-\w+');
$router->setPattern('id', '\d+');

// Authentication routes
$router->route('/auth/callback', 'AuthCallback');
$router->route('/auth/logout', 'AuthLogout');
$router->route('/auth/email/start-verify', 'PublicSite\AuthEmailSendCode');
$router->route('/auth/email/verify', 'PublicSite\AuthEmailVerify');

// Public content routes
$router->route('/:eventslug', 'PublicSite\Info');
$router->route('/:eventslug/(?<page>schedule|faq|hub)', 'PublicSite\Info');
$router->route('/:eventslug/register', 'PublicSite\Register');
$router->route('/:eventslug/video', 'PublicSite\VideoAPI');
$router->route('/:eventslug/video/(?<video_id>[\w\d\-\_]+)', 'PublicSite\VideoAPI');
$router->route('/:eventslug/pay/charge', 'PublicSite\BillingCharge');
$router->route('/:eventslug/pay/cancel', 'PublicSite\BillingCancel');

// Public tools
$router->route('/sign', 'Signage');

// Admin routes
$router->route('/admin', '/admin/people');
$router->route('/admin/people', 'Admin\People');
$router->route('/admin/people/:id', 'Admin\Person');
$router->route('/admin/people/new', 'Admin\Person');
$router->route('/admin/panels', 'Admin\Panels');
$router->route('/admin/invite', 'Admin\Invite');
$router->route('/admin/rate', 'Admin\Rate');
$router->route('/admin/badges', 'Admin\Badges');
$router->route('/admin/exports/(?<export>panels|attendees)', 'Admin\Export');

$router->route('/hub', '/2015-london/hub');
$router->route('/feedback', 'https://docs.google.com/forms/d/1bVPMF3FJjLPyj9-ECCko4leA4kV-6y0gbOYiEGvB-18/viewform');

$router->route('/', '/2015-london');
$router->route('/faq(?:\.html)', '/2015-london/faq');



$router->route('/errortest', 'ErrorTest');

$router->errorUnsupportedMethod('Errors\Error405');
$router->errorNoRoute('Errors\Error404');

$req = Routing\Request::createFromGlobals();
$resp = new Routing\Response();

$router->dispatch($req, $resp);


/* Serve the response */

$resp->serve();
