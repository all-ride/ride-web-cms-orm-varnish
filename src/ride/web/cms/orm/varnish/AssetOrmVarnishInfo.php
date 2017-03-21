<?php

namespace ride\web\cms\orm\varnish;

use ride\service\RouterService;

/**
 * Gatherer of information about Assets
 */
class AssetOrmVarnishInfo implements OrmVarnishInfo {

    /**
     * Constructs a new asset varnish info
     * @param \ride\service\RouterService $routerService
     * @return null
     */
    public function __construct(RouterService $routerService) {
        $this->routerService = $routerService;
    }

    /**
     * Gets the overview URL's which need to be cleared
     * @param string $modelName
     * @param string $locale
     * @param string $baseUrl
     * @return array Array with the URL as key and a boolean flag to see if the
     * URL should be cleared recursive
     */
    public function getOverviewUrls($modelName, $locale, $baseUrl) {
        return array();
    }

    /**
     * Gets the detail URL's which need to be cleared
     * @param string $modelName
     * @param \ride\library\orm\entry\Entry $entry
     * @param string $locale
     * @param string $baseUrl
     * @return array Array with the URL as key and a boolean flag to see if the
     * URL should be cleared recursive
     */
    public function getDetailUrls($modelName, $entry, $locale, $baseUrl) {
        $result = array();

        if ($modelName == 'Asset' && $entry->getId()) {
            $result[(string) $this->routerService->getUrl($baseUrl, "assets.value", array('asset' => $entry->getId()))] = false;
            if ($entry->getSlug()) {
                $result[(string) $this->routerService->getUrl($baseUrl, "assets.value", array('asset' => $entry->getSlug()))] = false;
            }
        }

        return $result;
    }

}
