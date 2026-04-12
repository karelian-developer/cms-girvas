<?php

/**
 * CMS «ГИРВАС»
 * 
 * Включена в Реестр российского программного обеспечения Минцифры РФ
 * Реестровый номер: №25012 от 27.11.2024
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Репозиторий продукта
 * @link        https://cms-girvas.ru Сайт продукта
 * 
 * @copyright   Copyright (c) 2021 - 2026, ИП Шестаков А.Р., «Карельский разработчик» (https://карельский-разработчик.рф/)
 * Все права защищены.
 * 
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 * @author      Андрей Шестаков <andrey.shestakov@karelian-developer.ru>
 * 
 * @support     support@karelian-developer.ru
 */

namespace core\PHPLibrary\Page;

use \core\PHPLibrary\InterfacePage as InterfacePage;
use \core\PHPLibrary\SystemCore as SystemCore;
use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\Pagination as Pagination;
use \core\PHPLibrary\Parsedown as Parsedown;
use \core\PHPLibrary\Entries as Entries;
use \core\PHPLibrary\Entry as Entry;
use \core\PHPLibrary\EntryCategory as EntryCategory;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\User as User;

class PageEntries implements InterfacePage
{
  public SystemCore $CMSCore;
  public Page $page;
  public string $assembled = '';

  /**
   * __construct
   *
   * @param  SystemCore $CMSCore
   * @param  Page $page
   * 
   * @return void
   */
  public function __construct(SystemCore $CMSCore, Page $page)
  {
    $this->CMSCore = $CMSCore;
    $this->page = $page;
  }

  /**
   * Добавление обязательных CSS-файлов
   * 
   * @return void
   */
  private function addRequiredStyles() : void
  {
    foreach (['page.css', 'page/entries.css'] as $stylePath) {
      $this->CMSCore->theme->addStyle(
        [
          'href' => 'styles/' . $stylePath,
          'rel' => 'stylesheet'
        ]
      );
    }
  }

  /**
   * Проверка возможности отображения записи для пользователя
   * 
   * Объект User должен передаваться с инициализированными данными:
   * - metadata
   * 
   * @param bool $isPublished
   * @param ?User $user
   * 
   * @return bool
   */
  public function entryIsVisible(bool $isPublished, ?User $user) : bool
  {
    if ($isPublished && $user !== null) {
      $userGroup = $user->getGroup();

      if ($userGroup !== null) {
        return $user->isSuperAdmin()
          || $userGroup->isSuperGroup()
          || $userGroup->hasPermissionEditorEntriesEdit();
      }
    }

    return false;
  }
  
  /**
   * Сборка шаблона страницы
   *
   * @return void
   */
  public function assembly() : void
  {
    $this->addRequiredStyles();

    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    $categoryName = $this->CMSCore->urlp->getPath(1) !== null ? urldecode($this->CMSCore->urlp->getPath(1)) : 'all';
    
    if (EntryCategory::existsByName($this->CMSCore, $categoryName) || $categoryName === 'all') {
      http_response_code(200);

      $entriesCountOnPage = 6;
      $pageIndex = $this->CMSCore->urlp->getParam('pageNumber');
      $paginationItemCurrent = $pageIndex ?? 0;
      $paginationItemCurrent = is_numeric($paginationItemCurrent) ? (int) $paginationItemCurrent : 0;

      $this->page->breadcrumbs->add($localeData['PAGE_ENTRIES_BREADCRUMPS_ALL_ENTRIES_LABEL'], '/entries');

      $clientIsLogged = $this->CMSCore->client->isLogged(1);
      
      $clientUser = $clientIsLogged ? $this->CMSCore->client->getUser(1) : null;
      if ($clientUser !== null) {
        $clientUser->initData(['metadata']);
      }

      if ($clientUser !== null) {
        $isPublished = $clientUser->getID() !== 1 || $clientUser->getGroupID() !== 1;
      } else {
        $isPublished = true;
      }

      if ($categoryName !== 'all') {
        $category = EntryCategory::getByName($this->CMSCore, $categoryName);
        $category->initData(['name', 'texts']);
        $categoryID = $category->getID();

        $categoryTitle = strip_tags($category->getTitle($localeName));
        $categorySEOTitle = strip_tags($category->getSEOTitle($localeName));
        $categorySEOTitle = $categorySEOTitle !== ''
          ? $categorySEOTitle
          : $categoryTitle;
        
        $categoryDescription = strip_tags($category->getDescription($localeName));
        $categorySEODescription = strip_tags($category->getSEODescription($localeName));
        $categorySEODescription = $categorySEODescription !== ''
          ? $categorySEODescription
          : $categoryDescription;
        $categorySEODescription = str_replace('"', '&quot;', $categorySEODescription);
        $categoryKeywords = $category->getKeywords($localeName);
        $categoryKeywords = str_replace('"', '&quot;', $categoryKeywords);

        $this->CMSCore->configurator->setMetaTitle($categorySEOTitle . ' | ' . $localeData['DEFAULT_TEXT_PAGE'] . ' ' . $pageIndex + 1);
        $this->CMSCore->configurator->setMetaDescription($categorySEODescription);
        $this->CMSCore->configurator->setMetaKeywords($categoryKeywords);

        $this->page->breadcrumbs->add($category->getTitle($localeName), '/entries/' . $category->getName());
        $this->page->breadcrumbs->assembly();

        /** @var Entries $entries Объект класса Entries */
        $entries = new Entries($this->CMSCore);
        $entriesObjects = $entries->getByCategoryID($categoryID, [
          'limit' => [$entriesCountOnPage, $paginationItemCurrent * $entriesCountOnPage]
        ], $isPublished);
        
        $entriesCount = $entries->getCountByCategoryID($categoryID, $isPublished);
      } else {
        $this->page->breadcrumbs->assembly();

        $this->CMSCore->configurator->setMetaTitle($localeData['PAGE_ENTRIES_BREADCRUMPS_ALL_ENTRIES_LABEL'] . ' | ' . $this->CMSCore->configurator->getSiteTitle());

        /** @var Entries $entries Объект класса Entries */
        $entries = new Entries($this->CMSCore);
        $entriesObjects = $entries->getAll([
          'limit' => [$entriesCountOnPage, $paginationItemCurrent * $entriesCountOnPage]
        ], $isPublished);

        $entriesCount = $entries->getCountTotal($isPublished);
      }

      unset($entries);

      $entriesArrayTemplates = [];
      $entriesTemplateContent = ThemeCollector::getTemplateFileContent(
        $this->CMSCore->theme,
        'templates/page/entries/entriesList/item.tpl'
      );

      $templatesAssembled = [];
      foreach ($entriesObjects as $entryObject) {
        $entryObject->initData(['id', 'categoryID', 'texts', 'name', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'metadata']);
        
        if ($entryObject->getPublishedUnixTimestamp() > time()) {
          continue;
        }

        $entryCategory = $entryObject->getCategory();
            
        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_CATEGORY_TITLE')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_CATEGORY_TITLE',
            $entryCategory->getTitle($localeName)
          );
        }

        $entryCreatedUnixTimestamp = $entryObject->getCreatedUnixTimestamp();
        $entryPublishedUnixTimestamp = $entryObject->getPublishedUnixTimestamp();
        $entryUpdatedUnixTimestamp = $entryObject->getUpdatedUnixTimestamp();

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_CREATED_DATE_TIMESTAMP')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_CREATED_DATE_TIMESTAMP',
            date('d.m.Y H:i:s', $entryCreatedUnixTimestamp)
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_CREATED_DATE_TIMESTAMP')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP',
            $entryPublishedUnixTimestamp > 0
              ? date('d.m.Y H:i:s', $entryPublishedUnixTimestamp)
              : '-'
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_CREATED_DATE_TIMESTAMP')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_UPDATED_DATE_TIMESTAMP',
            date('d.m.Y H:i:s', $entryUpdatedUnixTimestamp)
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_CREATED_DATE_TIMESTAMP_WITHOUT_TIME')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_CREATED_DATE_TIMESTAMP_WITHOUT_TIME',
            date('d.m.Y', $entryCreatedUnixTimestamp)
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_PUBLISHED_DATE_TIMESTAMP_WITHOUT_TIME')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP_WITHOUT_TIME',
            $entryPublishedUnixTimestamp > 0
              ? date('d.m.Y', $entryPublishedUnixTimestamp)
              : '-'
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_UPDATED_DATE_TIMESTAMP_WITHOUT_TIME')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_UPDATED_DATE_TIMESTAMP_WITHOUT_TIME',
            date('d.m.Y', $entryUpdatedUnixTimestamp)
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_CREATED_DATE_TIMESTAMP_WITHOUT_DATE')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_CREATED_DATE_TIMESTAMP_WITHOUT_DATE',
            date('H:i:s', $entryCreatedUnixTimestamp)
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_PUBLISHED_DATE_TIMESTAMP_WITHOUT_DATE')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP_WITHOUT_DATE',
            $entryPublishedUnixTimestamp > 0
              ? date('H:i:s', $entryPublishedUnixTimestamp)
              : '-'
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_UPDATED_DATE_TIMESTAMP_WITHOUT_DATE')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_UPDATED_DATE_TIMESTAMP_WITHOUT_DATE',
            date('H:i:s', $entryUpdatedUnixTimestamp)
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601',
            date('Y-m-dH:i:s', $entryCreatedUnixTimestamp)
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601',
            $entryPublishedUnixTimestamp > 0
              ? date('Y-m-dH:i:s', $entryPublishedUnixTimestamp)
              : '-'
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601',
            date('Y-m-dH:i:s', $entryUpdatedUnixTimestamp)
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME',
            date('Y-m-d', $entryCreatedUnixTimestamp)
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME',
            $entryPublishedUnixTimestamp > 0
              ? date('Y-m-d', $entryPublishedUnixTimestamp)
              : '-'
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME',
            date('Y-m-d', $entryUpdatedUnixTimestamp)
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_CREATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE',
            date('H:i:s', $entryCreatedUnixTimestamp)
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE',
            $entryPublishedUnixTimestamp > 0
              ? date('H:i:s', $entryPublishedUnixTimestamp)
              : '-'
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_UPDATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE',
            date('H:i:s', $entryUpdatedUnixTimestamp)
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_ID')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_ID',
            $entryObject->getID()
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_NAME')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_NAME',
            $entryObject->getName()
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_TITLE')) {
          $value = $entryObject !== null ? $entryObject->getTitle($localeName) : '';

          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_TITLE',
            str_replace(
              ThemeCollector::DECODED_ENTITIES,
              ThemeCollector::SAFE_SYMBOLS,
              htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
            )
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_DESCRIPTION')) {
          $value = $entryObject !== null ? $entryObject->getDescription($localeName) : '';

          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_DESCRIPTION',
            str_replace(
              ThemeCollector::DECODED_ENTITIES,
              ThemeCollector::SAFE_SYMBOLS,
              htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
            )
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_URL')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_URL',
            $entryObject->getURL()
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_PREVIEW_URL')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_PREVIEW_URL',
            $entryObject->getPreviewURL() !== '' ? $entryObject->getPreviewURL() : Entry::getPreviewDefaultURL($this->CMSCore, 512)
          );
        }

        if (ThemeCollector::existsTemplateVariable($entriesTemplateContent, 'ENTRY_CATEGORY_URL')) {
          ThemeCollector::addTemplateVariable(
            $templatesAssembled,
            'ENTRY_CATEGORY_URL',
            $entryCategory->getURL()
          );
        }

        if (isset($templatesAssembled['ENTRY_TITLE']) && isset($templatesAssembled['ENTRY_DESCRIPTION'])) {
          $entriesArrayTemplates[] = ThemeCollector::assemblyFileContent(
            $this->CMSCore->theme,
            'templates/page/entries/entriesList/item.tpl',
            $templatesAssembled
          );
        }

        $templatesAssembled = [];
      }

      unset($entriesObjects);

      $pagination = new Pagination($this->CMSCore, $entriesCount, $entriesCountOnPage, $paginationItemCurrent);
      $pagination->assembly();

      $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page.tpl', [
        'PAGE_NAME' => 'entries',
        'PAGE_CONTENT' => ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/entries.tpl', [
          'PAGE_BREADCRUMPS' => $this->page->breadcrumbs->assembled,
          'ENTRIES_CATEGORY_TITLE' => ($categoryName == 'all') ? $localeData['PAGE_ENTRIES_BREADCRUMPS_ALL_ENTRIES_LABEL'] : $category->getTitle($localeName),
          'ENTRIES_CATEGORY_DESCRIPTION' => ($categoryName == 'all') ? $localeData['PAGE_ENTRIES_BREADCRUMPS_ALL_ENTRIES_DESCRIPTION'] : $category->getDescription($localeName),
          'ENTRIES_LIST' => (!empty($entriesArrayTemplates)) ? ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/entries/entriesList/list.tpl', [
            'ENTRIES_LIST_ITEMS' => implode($entriesArrayTemplates)
          ]) : sprintf('<div class="page__simple-note">%s</div>', $localeData['PAGE_ENTRIES_NOT_FOUND_LABEL']),
          'ENTRIES_PAGINATION' => (!empty($entriesArrayTemplates)) ? $pagination->assembled : ''
        ])
      ]);

      unset($entriesArrayTemplates);
    } else {
      http_response_code(404);

      $pageError = new PageError($this->CMSCore, $this->page, 404);
      $pageError->assembly();
      $this->assembled = $pageError->assembled;
    }
  }
}