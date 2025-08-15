<?php

use Compare\Services\CompareService;
use Compare\SessionStorage;

require_once dirname(__FILE__) . '/autoload.php';

// remove this in release
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);


class Compare
{
    private modX $modx;

    private pdoFetch $pdoFetch;

    private CompareService $compareService;

    public array $initialized = [];

    public array $config = [];

    /**
     * @param modX $modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = [])
    {
        $this->modx = &$modx;

        $corePath = $this->modx->getOption('compare_core_path', $config, $this->modx->getOption('core_path') . 'components/compare/');
        $assetsUrl = $this->modx->getOption('compare_assets_url', $config, $this->modx->getOption('assets_url') . 'components/compare/');

        $this->config = array_merge(array(
            'assetsUrl' => $assetsUrl,
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',
            'connectorUrl' => $assetsUrl . 'connector.php',

            'corePath' => $corePath,
            'processorsPath' => $corePath . 'processors/',

            //'frontend_css' => $this->modx->getOption('compare_frontend_css', null, '[[+assetsUrl]]css/default.css'),
            //'frontend_js' => $this->modx->getOption('compare_frontend_js', null, '[[+assetsUrl]]js/default.js'),
        ), $config);

        $this->modx->lexicon->load('compare:default');
    }

    /**
     * Initializes component
     *
     * @param string $ctx The context to load. Defaults to web.
     * @param array $scriptProperties array with additional parameters
     *
     * @return boolean
     */
    public function initialize(string $ctx = 'web', array $scriptProperties = []): bool
    {
        $this->config = array_merge($this->config, $scriptProperties);
        $this->config['ctx'] = $ctx;
        if (!empty($this->initialized[$ctx])) {
            return true;
        }
        switch ($ctx) {
            case 'mgr':
                break;
            default:
                if (!defined('MODX_API_MODE') || !MODX_API_MODE) {
                    // if ($css = trim($this->config['frontend_css'])) {
                    //     if (preg_match('/\.css/i', $css)) {
                    //         $this->modx->regClientCSS(str_replace('[[+assetsUrl]]', $this->config['assetsUrl'], $css));
                    //     }
                    // }
                    // if ($js = trim($this->config['frontend_js'])) {
                    //     if (preg_match('/\.js/i', $js)) {
                    //         $this->modx->regClientScript(str_replace('[[+assetsUrl]]', $this->config['assetsUrl'], $js));
                    //     }
                    // }
                }
                $this->pdoFetch = $this->modx->getService('pdoFetch');
                $this->compareService = (new CompareService($this->modx, new SessionStorage()));
                $this->initialized[$ctx] = true;
                break;
        }
        return true;
    }

    public function action(string $action, array $data = []): array
    {
        switch ($action) {
            case 'load':
                return $this->success(
                    $this->compareService->load($data)
                );
            case 'toggle':
                return $this->success(
                    $this->compareService->toggle($data)
                );
            case 'remove':
                return $this->success(
                    $this->compareService->remove($data)
                );
            case 'clean':
                return $this->success(
                    $this->compareService->clean($data)
                );
            case 'mini':
                return $this->success(
                    $this->compareService->mini($data)
                );
            default:
                return $this->error(
                    $this->modx->lexicon('compare_unknown_action')
                );
        }
    }

    public function compare(string $list, $scriptProperties): string
    {
        $this->pdoFetch->setConfig($scriptProperties);

        $tpl = $this->modx->getOption('tpl', $scriptProperties, '');

        $fields = explode(",", $this->modx->getOption('fields', $scriptProperties, ''));
        $fields = array_filter(array_map('trim', $fields));

        $priceFields = explode(",", $this->modx->getOption('priceFields', $scriptProperties, 'price,old_price'));
        $priceFields = array_map('trim', $priceFields);

        $weightFields = explode(",", $this->modx->getOption('weightFields', $scriptProperties, 'weight'));
        $weightFields = array_map('trim', $weightFields);

        $best = explode(",", $this->modx->getOption('best', $scriptProperties, 'price:min'));
        $best = array_map('trim', $best);
        $bestMap = [];
        foreach ($best as $value) {
            if(strpos($value, ':') !== false) {
                [$field, $type] = explode(':', $value);
                $bestMap[$field] = $type;
            }
        }

        $data = $this->compareService->compare(
            $list,
            $fields,
            $priceFields,
            $weightFields,
            $bestMap
        );

        return $this->getChunk($tpl, $data);
    }

    /**
     * Process and return the output from a Chunk by name.
     *
     * @param string $name The name of the chunk.
     * @param array $properties An associative array of properties to process the Chunk with, treated as placeholders within the scope of the Element.
     * @return mixed
     */
    private function getChunk(string $name = '', array $properties = [])
    {
        // Можно включить для режима разработки и отладки, чтобы не собирать пакет каждый раз
        // $properties['tplPath'] = MODX_BASE_PATH . 'Extras/compare/core/components/compare/elements/';
        return $this->pdoFetch->getChunk($name, $properties);
    }

    /**
     * This method returns a success of the action
     *
     * @param array $data .Additional data, for example cart status
     *
     * @return array|string $response
     * */
    private function success(array $data = [])
    {
        $response = array(
            'success' => true,
            'data' => $data
        );
        return $this->config['json_response']
            ? $this->modx->toJSON($response)
            : $response;
    }

    /**
     * This method returns an error of the action
     *
     * @param string $message Message to show
     *
     * @return array|string $response
     * */
    private function error(string $message)
    {
        $response = array(
            'success' => false,
            'message' => $message
        );
        return $this->config['json_response']
            ? $this->modx->toJSON($response)
            : $response;
    }
}
