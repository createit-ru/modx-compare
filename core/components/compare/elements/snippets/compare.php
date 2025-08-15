<?php

/** @var array $scriptProperties */
/** @var Compare $compare */
/** @var modX $modx */
$compare = $modx->getService('compare', 'Compare', $modx->getOption('compare_core_path', null, $modx->getOption('core_path') . 'components/compare/'), $scriptProperties);
if (!($compare instanceof Compare)) return '';

$compare->initialize($modx->context->key);

$list = !empty($_REQUEST['list']) ? (string) $_REQUEST['list'] : 'default';

return $compare->compare($list, $scriptProperties);