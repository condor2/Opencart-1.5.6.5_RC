<?php
// Error Reporting
error_reporting(E_ALL);

// HTTP
define('HTTP_SERVER', 'http://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/.\\') . '/');
define('HTTP_OPENCART', 'http://' . $_SERVER['HTTP_HOST'] . rtrim(rtrim(dirname($_SERVER['SCRIPT_NAME']), 'install'), '/.\\') . '/');

// DIR
define('DIR_APPLICATION', str_replace('\'', '/', realpath(__DIR__)) . '/');
define('DIR_SYSTEM', str_replace('\'', '/', realpath(__DIR__ . '/../')) . '/system/');
define('DIR_OPENCART', str_replace('\'', '/', realpath(DIR_APPLICATION . '../')) . '/');
define('DIR_DATABASE', DIR_SYSTEM . 'database/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/template/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');

// Startup
require_once(DIR_SYSTEM . 'startup.php');

// Registry
$registry = new Registry();

// Loader
$loader = new Loader($registry);
$registry->set('load', $loader);

// Url
$url = new Url(HTTP_SERVER);
$registry->set('url', $url);

// Request
$request = new Request();
$registry->set('request', $request);

// Response
$response = new Response();
$response->addHeader('Content-Type: text/html; charset=UTF-8');
$registry->set('response', $response);

// Document
$document = new Document();
$registry->set('document', $document);

// Upgrade
$upgrade = false;

if (file_exists('../config.php')) {
	if (filesize('../config.php') > 0) {
		$upgrade = true;

		$lines = file(DIR_OPENCART . 'config.php');

		foreach ($lines as $line) {
			if (strpos(strtoupper($line), 'DB_') !== false) {
				eval($line);
			}
		}
	}
}

// Front Controller
$controller = new Front($registry);

// Router
if (isset($request->get['route'])) {
	$action = new Action($request->get['route']);
} elseif ($upgrade) {
	$action = new Action('upgrade');
} else {
	$action = new Action('step_1');
}

// Dispatch
$controller->dispatch($action, new Action('not_found'));

// Output
$response->output();
