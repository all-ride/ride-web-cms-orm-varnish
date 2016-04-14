<?php

namespace ride\web\cms\orm\varnish;

use ride\library\reflection\ReflectionHelper;
use ride\library\orm\entry\EntryProxy;

use ride\web\cms\orm\ContentProperties;
use ride\web\cms\Cms;

/**
 * Gatherer of information about the orm widget and nodes
 */
interface OrmVarnishInfo {

    /**
     * Gets the overview URL's which need to be cleared
     * @param string $modelName
     * @param string $locale
     * @param string $baseUrl
     * @return array Array with the URL as key and a boolean flag to see if the
     * URL should be cleared recursive
     */
    public function getOverviewUrls($modelName, $locale, $baseUrl);

    /**
     * Gets the detail URL's which need to be cleared
     * @param string $modelName
     * @param \ride\library\orm\entry\Entry $entry
     * @param string $locale
     * @param string $baseUrl
     * @return array Array with the URL as key and a boolean flag to see if the
     * URL should be cleared recursive
     */
    public function getDetailUrls($modelName, $entry, $locale, $baseUrl);

}
