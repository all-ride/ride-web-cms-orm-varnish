<?php

namespace ride\web\cms\orm\varnish;

use ride\library\reflection\ReflectionHelper;
use ride\library\orm\entry\EntryProxy;

use ride\web\cms\orm\ContentProperties;
use ride\web\cms\Cms;

/**
 * Gatherer of information about the orm widget and nodes
 */
class GenericOrmVarnishInfo implements OrmVarnishInfo {

    /**
     * Constructs a new instance
     * @param \ride\web\cms\Cms $cms Instance of the CMS
     * @param \ride\library\reflection\ReflectionHelper $reflectionHelper
     * @return null
     */
    public function __construct(Cms $cms, ReflectionHelper $reflectionHelper) {
        $this->cms = $cms;
        $this->reflectionHelper = $reflectionHelper;
        $this->info = null;
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

        $info = $this->getInfo($modelName);
        if (!isset($info['orm.overview'])) {
            return $result;
        }

        foreach ($info['orm.overview'] as $meta) {
            $node = $this->cms->getNode($meta['site'], 'master', $meta['node']);
            if (!$node) {
                continue;
            }

            $url = $node->getUrl($locale, $baseUrl);

            if ($this->hasDetailForNode($node)) {
                $result[$url . '/*'] = false;
                $result[$url . '/*?'] = true;
            } else {
                $result[$url] = false;
                $result[$url . '?'] = true;
            }
        }

        return $result;
    }

    /**
     * Checks if there is a orm detail widget on the provided node
     * @param \ride\library\cms\node\Node $node
     * @return boolean
     */
    private function hasDetailForNode($node) {
        foreach ($this->info as $modelName => $widgets) {
            if (!isset($widgets['orm.detail'])) {
                continue;
            }

            foreach ($widgets['orm.detail'] as $id => $properties) {
                if (strpos($id, $node->getId() . '#') === 0) {
                    return true;
                }
            }
        }

        return false;
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

        $info = $this->getInfo($modelName);
        if (isset($info['orm.detail']) && $entry instanceof EntryProxy) {
            foreach ($info['orm.detail'] as $meta) {
                if (!$entry->isValueLoaded($meta['id'])) {
                    continue;
                }

                $id = $entry->getLoadedValues($meta['id']);

                $node = $this->cms->getNode($meta['site'], 'master', $meta['node']);
                if (!$node) {
                    continue;
                }

                $url = $node->getUrl($locale, $baseUrl);
                $url .= '/' . $id;

                $result[$url] = false;
                $result[$url . '?'] = true;
            }
        }

        if (isset($info['orm.entry'])) {
            foreach ($info['orm.entry'] as $nodeId => $meta) {
                if ($meta['entry'] != $entry->getId()) {
                    continue;
                }

                $node = $this->cms->getNode($meta['site'], 'master', $meta['node']);
                if (!$node) {
                    continue;
                }

                $url = $node->getUrl($locale, $baseUrl);

                $result[$url] = false;
                $result[$url . '?'] = true;
            }
        }

        if (isset($info['node'])) {
            foreach ($info['node'] as $nodeId => $meta) {
                if ($meta['entry'] != $entry->getId()) {
                    continue;
                }

                $node = $this->cms->getNode($meta['site'], 'master', $meta['node']);
                if (!$node) {
                    continue;
                }

                $url = $node->getUrl($locale, $baseUrl);

                $result[$url] = false;
                $result[$url . '?'] = true;

                $children = $this->cms->getNodeModel()->getNodesByPath($meta['site'], 'master', $node->getPath());
                foreach ($children as $child) {
                    $url = $child->getUrl($locale, $baseUrl);

                    $result[$url] = false;
                    $result[$url . '?'] = true;
                }
            }
        }

        return $result;
    }

    /**
     * Gets the gathered info for the provided model
     * @param string $modelName
     * @return array
     */
    public function getInfo($modelName) {
        if ($this->info === null) {
            $this->gatherInfo();
        }

        if (!isset($this->info[$modelName])) {
            return null;
        }

        return $this->info[$modelName];
    }

    /**
     * Gathers the information about the ORM instances inside the CMS
     * @return array
     */
    public function gatherInfo() {
        $this->gatherInfoForWidget('orm.overview');
        $this->gatherInfoForWidget('orm.detail');
        $this->gatherInfoForWidget('orm.entry');

        $this->gatherInfoForNodes();
    }

    /**
     * Gathers information about entry nodes
     * @return array
     */
    protected function gatherInfoForNodes() {
        $sites = $this->cms->getSites();
        foreach ($sites as $site) {
            $nodes = $this->cms->getNodeModel()->getNodesByType($site->getId(), 'master', 'entry');
            foreach ($nodes as $node) {
                $this->info[$node->getEntryModel()]['node'][$node->getId()] = array(
                    'site' => $site->getId(),
                    'node' => $node->getId(),
                    'entry' => $node->getEntryId(),
                );
            }
        }
    }

    /**
     * Gathers all instances of the provided widget
     * @param string $widgetName
     * @return array
     */
    protected function gatherInfoForWidget($widgetName) {
        $nodes = $this->cms->getNodeModel()->getNodesForWidget($widgetName);
        foreach ($nodes as $node) {
            $widgetId = $node->getWidgetId();
            if (!$widgetId) {
                continue;
            }

            $widgetProperties = $node->getWidgetProperties($widgetId);

            $modelName = $widgetProperties->getWidgetProperty(ContentProperties::PROPERTY_MODEL_NAME);
            if (!$modelName) {
                continue;
            }

            $this->info[$modelName][$widgetName][$node->getId() . '#' . $widgetId] = array(
                'site' => $node->getRootNodeId(),
                'node' => $node->getId(),
                'entry' => $widgetProperties->getWidgetProperty(ContentProperties::PROPERTY_ENTRY),
                'id' => $widgetProperties->getWidgetProperty(ContentProperties::PROPERTY_ID_FIELD),
            );
        }
    }

}
