<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary;

use \core\PHPLibrary\Template\Collector as TemplateCollector;
use \core\PHPLibrary\SystemCore as SystemCore;
use \DOMDocument as DOMDocument;

trait TraitPage
{
  /**
   * Получить абсолютный путь SVG-файла иконки подраздела
   * 
   * @param SystemCore $CMSCore
   * @param string $subnavigationItemName
   * 
   * @return string
   */
  private function getSubnavigationItemIconPath(SystemCore $CMSCore, string $subnavigationItemName) : string
  {
    $themePath = $this->CMSCore->theme->getPath();
    return $themePath . '/images/icons/subNavigation/' . $subnavigationItemName . '.svg';
  }

  public function initAdminPanelSubnavigation(SystemCore $CMSCore, DOMDocument|null &$source) : void
  {
    $themeSource =& $source;

    if (!is_null($themeSource)) {
      $elementCMSAdminPanelSubnavigation = $themeSource->getElementById('SYSTEM_AP_SUBNAVIGATION');

      if (!is_null($elementCMSAdminPanelSubnavigation)) {
        $listElement = $elementCMSAdminPanelSubnavigation->ownerDocument->createElement('ul');
        $listElement->setAttribute('class', 'navigation__list list list-reset');

        if (count($this->navigationSubsections) > 0) {
          foreach ($this->navigationSubsections as $index => $data) {
            $subsectionName = $data['name'];
            $subsectionLink = $data['link'];
            $subsectionIconName = $data['iconName'];
            $subsectionPermanentStatus = $data['permanent'];
            $subsectionIsActiveStatus = $data['isActive'];

            $sectionIsAllowed = true;

            if ($sectionIsAllowed) {
              $itemTitle = sprintf('{LANG:%s}', self::LANG_PAGE_NAVIGATION_LABLE_TEMPLATE);
              $itemTitle = sprintf($itemTitle, strtoupper($subsectionName));
              $itemTitle = TemplateCollector::assemblyLocale($itemTitle, $CMSCore->locale);

              $itemElement = $elementCMSAdminPanelSubnavigation->ownerDocument->createElement('li');
              $linkElement = $elementCMSAdminPanelSubnavigation->ownerDocument->createElement('a');
              $labelElement = $elementCMSAdminPanelSubnavigation->ownerDocument->createElement('div', $itemTitle);

              $iconPath = $this->getSubnavigationItemIconPath($CMSCore, $subsectionIconName);
              if (file_exists($iconPath)) {
                $iconContainerElement = $elementCMSAdminPanelSubnavigation->ownerDocument->createElement('div');
                $iconContainerElement->setAttribute('class', sprintf('item__icon-container icon-container', $subsectionIconName));

                $SVGElement = new DOMDocument();
                $SVGElement->load($iconPath);
                $SVGImportedElement = $themeSource->importNode($SVGElement->documentElement, true);
                $SVGImportedElement->setAttribute('class', 'item__icon icon');

                $iconContainerElement->appendChild($SVGImportedElement);
                $linkElement->appendChild($iconContainerElement);
              }

              if ($subsectionIsActiveStatus) {
                $itemElement->setAttribute('class', sprintf('list__item item item_%s item_is-active', $subsectionName));
              } else {
                $itemElement->setAttribute('class', sprintf('list__item item item_%s', $subsectionName));
              }

              $linkElement->setAttribute('href', '/admin' . $subsectionLink);
              $linkElement->setAttribute('class', 'item__link link');
              $linkElement->setAttribute('title', $itemTitle);
              $labelElement->setAttribute('class', 'item__label label');

              $linkElement->appendChild($labelElement);
              $itemElement->appendChild($linkElement);
              $listElement->appendChild($itemElement);
            }
          }

          $elementCMSAdminPanelSubnavigation->appendChild($listElement);
        }
      }
    }
  }
}