<?php

namespace ride\web\cms\orm\varnish;

use ride\library\reflection\ReflectionHelper;
use ride\library\orm\entry\EntryProxy;

use ride\web\cms\orm\ContentProperties;
use ride\web\cms\Cms;

/**
 * Chained getherer of information about the orm widget and nodes
 */
class ChainOrmVarnishInfo implements OrmVarnishInfo {

    /**
     * Chain of varnish info instances
     * @var array
     */
    protected $chain = array();

    /**
     * Adds multiple varnish info instances to the chain
     * @param array $ormVarnishInfos
     * @return null
     */
    public function addOrmVarnishInfos(array $ormVarnishInfos) {
        foreach ($ormVarnishInfos as $ormVarnishInfo) {
            $this->addOrmVarnishInfo($ormVarnishInfo);
        }
    }

    /**
     * Adds a varnish info instance to the chain
     * @param OrmVarnishInfo $ormVarnishInfo
     * @return null
     */
    public function addOrmVarnishInfo(OrmVarnishInfo $ormVarnishInfo) {
        $this->removeOrmVarnishInfo($ormVarnishInfo);

        $this->chain[] = $ormVarnishInfo;
    }

    /**
     * Removes a varnish info instance from the chain
     * @param OrmVarnishInfo $ormVarnishInfo
     * @return null
     */
    public function removeOrmVarnishInfo(OrmVarnishInfo $ormVarnishInfo) {
        foreach ($this->chain as $index => $varnishInfo) {
            if ($varnishInfo === $ormVarnishInfo) {
                unset($this->chain[$index]);
            }
        }
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
        $result = array();

        foreach ($this->chain as $varnishInfo) {
            $result += $varnishInfo->getOverviewUrls($modelName, $locale, $baseUrl);
        }

        return $result;
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

        foreach ($this->chain as $varnishInfo) {
            $result += $varnishInfo->getDetailUrls($modelName, $entry, $locale, $baseUrl);
        }

        return $result;
    }

}
