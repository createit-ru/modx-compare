<?php

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['action']) || !isset($data['action'])) {
    die('Access denied');
} else {
    $action = $data['action'];
}

define('MODX_API_MODE', true);

// Load MODX
if (file_exists(dirname(__FILE__, 4) . '/index.php')) {
    require_once dirname(__FILE__, 4) . '/index.php';
} else {
    require_once dirname(__FILE__, 6) . '/index.php';
}

/** @var modX $modx */
$modx->getService('error', 'error.modError');
$modx->getRequest();
$modx->setLogLevel(xPDO::LOG_LEVEL_ERROR);
$modx->setLogTarget('FILE');
$modx->error->message = null;


// Get properties
$properties = [];

define('MODX_ACTION_MODE', true);
/* @var Compare $compare */
$compare = $modx->getService('compare', 'compare', $modx->getOption('compare_core_path', null, $modx->getOption('core_path') . 'components/compare/'), $properties);
if ($modx->error->hasError() || !($compare instanceof Compare)) {
    die('Fatal error');
}

$compare->initialize($modx->context->key);

$response = $modx->toJSON(
    $compare->action($action, $data)
);

@session_write_close();
exit($response);
