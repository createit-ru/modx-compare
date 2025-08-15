<?php

if (file_exists(dirname(__FILE__, 4) . '/config.core.php')) {
    /** @noinspection PhpIncludeInspection */
    require_once dirname(__FILE__, 4) . '/config.core.php';
} else {
    require_once dirname(__FILE__, 5) . '/config.core.php';
}
/** @noinspection PhpIncludeInspection */
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
/** @noinspection PhpIncludeInspection */
require_once MODX_CONNECTORS_PATH . 'index.php';

/** @var Compare $compare */
/** @var modX $modx */
$compare = $modx->getService('compare', 'compare', MODX_CORE_PATH . 'components/compare/');
$modx->lexicon->load('compare:default');

// handle request
$corePath = $modx->getOption('compare_core_path', null, $modx->getOption('core_path') . 'components/compare/');
$path = $modx->getOption('processorsPath', $compare->config, $corePath . 'processors/');
$modx->getRequest();

/** @var modConnectorRequest $request */
$request = $modx->request;
$request->handleRequest([
    'processors_path' => $path,
    'location' => '',
]);