<?php

namespace ride\web\cms;

use ride\library\event\Event;
use ride\library\event\EventManager;
use ride\library\i18n\I18n;
use ride\library\orm\entry\LocalizedEntry;
use ride\library\orm\model\GenericModel;

use ride\web\cms\orm\varnish\OrmVarnishInfo;
use ride\web\WebApplication;

use ride\service\CmsVarnishService;

/**
 * Application listener to handle varnish for CMS ORM integration
 */
class CmsOrmVarnishApplicationListener {

    /**
     * Constructs a new instance
     * @param \ride\service\CmsVarnishService $cmsVarnishService
     * @param \ride\web\cms\orm\varnish\OrmVarnishInfo $ormVarnishInfo
     * @param \ride\library\event\EventManager $eventManager
     * @param \ride\library\i18n\I18n
     * @param string $baseUrl
     * @return null
     */
    public function __construct(CmsVarnishService $cmsVarnishService, OrmVarnishInfo $ormVarnishInfo, EventManager $eventManager, I18n $i18n, $baseUrl) {
        $this->varnishService = $cmsVarnishService;
        $this->varnishInfo = $ormVarnishInfo;
        $this->eventManager = $eventManager;
        $this->i18n = $i18n;
        $this->baseUrl = $baseUrl;
        $this->banUrls = array();
    }

    /**
     * Handles a ORM action to detect needed varnish bans
     * @param \ride\library\event\Event $event
     * @return null
     */
    public function handleOrmAction(Event $event) {
        $model = $event->getArgument('model');
        $entry = $event->getArgument('entry');

        switch ($event->getName()) {
            case GenericModel::EVENT_INSERT_PRE:
            case GenericModel::EVENT_UPDATE_PRE:
            case GenericModel::EVENT_DELETE_PRE:
                // gather detail urls
                $this->gatherDetailUrls($model, $entry);

                $this->addBanListener();

                break;
            case GenericModel::EVENT_INSERT_POST:
            case GenericModel::EVENT_UPDATE_POST:
            case GenericModel::EVENT_DELETE_POST:
                // gather overview urls
                $this->gatherOverviewUrls($model, $entry);

                $this->addBanListener();

                break;
        }
    }

    /**
     * Adds the event listener to perform the bans
     * @return null
     */
    private function addBanListener() {
        if (!$this->banUrls || isset($this->isBanEventRegistered)) {
            return;
        }

        $this->eventManager->addEventListener(WebApplication::EVENT_PRE_RESPONSE, array($this, 'handleBan'));

        $this->isBanEventRegistered = true;
    }

    /**
     * Handles the actual ban event
     * @param \ride\library\event\Event $event
     * @return null
     */
    public function handleBan(Event $event) {
        $this->performBan();
    }

    /**
     * Performs a ban command with all gathered URL's
     * @return null
     */
    private function performBan() {
        foreach ($this->banUrls as $url => $recursive) {
            $this->varnishService->banUrl($url, $recursive);
        }
    }

    /**
     * Gathers the detail URL's for the provided entry
     * @param \ride\library\orm\model\GenericModel $model
     * @param \ride\library\orm\entry\Entry $entry
     * @return null
     */
    private function gatherDetailUrls(GenericModel $model, $entry) {
        $locales = $model->getOrmManager()->getLocales();

        if (!$model->getMeta()->isLocalized()) {
            foreach ($locales as $locale) {
                $this->banUrls += $this->varnishInfo->getDetailUrls($model->getName(), $entry, $locale, $this->baseUrl);
            }

            return;
        }

        $this->banUrls += $this->varnishInfo->getDetailUrls($model->getName(), $entry, $entry->getLocale(), $this->baseUrl);
        unset($locales[$entry->getLocale()]);

        foreach ($locales as $locale) {
            $localizedEntry = $model->getById($entry->getId(), $locale);
            if ($localizedEntry) {
                $this->banUrls += $this->varnishInfo->getDetailUrls($model->getName(), $localizedEntry, $locale, $this->baseUrl);
            }
        }
    }

    /**
     * Gathers the overview URL's for the provided entry
     * @param \ride\library\orm\model\GenericModel $model
     * @param \ride\library\orm\entry\Entry $entry
     * @return null
     */
    private function gatherOverviewUrls(GenericModel $model, $entry) {
        $locales = $model->getOrmManager()->getLocales();
        foreach ($locales as $locale) {
            $this->banUrls += $this->varnishInfo->getOverviewUrls($model->getName(), $locale, $this->baseUrl);
        }
    }

}
