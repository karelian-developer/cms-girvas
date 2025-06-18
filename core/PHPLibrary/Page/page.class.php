<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Page {
  use \core\PHPLibrary\InterfacePage as InterfacePage;
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\PageStatic as PageStatic;
  use \core\PHPLibrary\Parsedown as Parsedown;
  use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;

  class PagePage implements InterfacePage {
    public SystemCore $CMSCore;
    public Page $page;
    public string $assembled = '';

    /**
     * __construct
     *
     * @param  SystemCore $CMSCore
     * @param  Page $page
     * @return void
     */
    public function __construct(SystemCore $CMSCore, Page $page) {
      $this->CMSCore = $CMSCore;
      $this->page = $page;
    }
    
    /**
     * Сборка шаблона страницы
     *
     * @return void
     */
    public function assembly() : void {
      $this->CMSCore->theme->add_style(['href' => 'styles/page.css', 'rel' => 'stylesheet']);
      $this->CMSCore->theme->add_style(['href' => 'styles/page/static.css', 'rel' => 'stylesheet']);

      $localeData = $this->CMSCore->locale->get_data();
      $localeName = $this->CMSCore->locale->get_name();

      if (!is_null($this->CMSCore->urlp->get_path(1))) {
        $pageStaticName = urldecode($this->CMSCore->urlp->get_path(1));

        if (PageStatic::exists_by_name($this->CMSCore, $pageStaticName)) {
          $pageStatic = PageStatic::get_by_name($this->CMSCore, $pageStaticName);
          $pageStatic->init_data(['id', 'texts', 'name', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'metadata']);

          if ($this->CMSCore->urlp->get_param('locale') === $localeName) {
            $this->CMSCore->theme->add_link_canonical('/page/' . $pageStatic->get_name());
          }

          $isVisible = false;

          $clientIsLogged = $this->CMSCore->client->is_logged(1);
          $clientUser = ($clientIsLogged) ? $this->CMSCore->client->get_user(1) : null;

          $isVisible = ($pageStatic->is_published()) ? true : false;
          if (!$isVisible) {
            if ($clientUser != null) {
              $isVisible = ($clientUser->get_id() === 1 || $clientUser->get_group_id() === 1) ? true : false;
            }
          }

          if ($isVisible) {
            http_response_code(200);

            $this->page->breadcrumbs->add($localeData['PAGE_STATIC_PAGE_BREADCRUMPS_INDEX_LABEL'], '/');
            $this->page->breadcrumbs->add($pageStatic->get_title($this->CMSCore->configurator->get_database_entry_value('base_locale')), $pageStatic->get_name());
            $this->page->breadcrumbs->assembly();

            $this->CMSCore->configurator->set_meta_title($pageStatic->get_title($localeName));
            $this->CMSCore->configurator->set_meta_description(str_replace('"', '&quot;', $pageStatic->get_description($localeName)));
            $this->CMSCore->configurator->set_meta_keywrords(str_replace('"', '&quot;', $pageStatic->get_keywords($localeName)));

            /**
             * @var Parsedown Парсер markdown-разметки
             */
            $parsedown = new Parsedown();

            /**
             * @var string Заголовок статической страницы
             */
            $pageStaticTitle = $pageStatic->get_title($localeName);
            $pageStaticTitle = strip_tags($pageStaticTitle);
            /**
             * @var string Содержание статической страницы
             */
            $pageStaticContent = $pageStatic->get_content($localeName);

            $createdDateTimestamp = date('d.m.Y H:i:s', $pageStatic->get_created_unix_timestamp());
            $publishedDateTimestamp = date('d.m.Y H:i:s', $pageStatic->get_published_unix_timestamp());
            $updatedDateTimestamp = date('d.m.Y H:i:s', $pageStatic->get_updated_unix_timestamp());

            $createdDateTimestampWithoutTime = date('d.m.Y', $pageStatic->get_created_unix_timestamp());
            $publishedDateTimestampWithoutTime = date('d.m.Y', $pageStatic->get_published_unix_timestamp());
            $updatedDateTimestampWithoutTime = date('d.m.Y', $pageStatic->get_updated_unix_timestamp());
    
            $createdDateTimestampWithoutData = date('H:i:s', $pageStatic->get_created_unix_timestamp());
            $publishedDateTimestampWithoutData = date('H:i:s', $pageStatic->get_published_unix_timestamp());
            $updatedDateTimestampWithoutData = date('H:i:s', $pageStatic->get_updated_unix_timestamp());

            $createdDateTimestampISO8601 = date('Y-m-dH:i:s', $pageStatic->get_created_unix_timestamp());
            $publishedDateTimestampISO8601 = date('Y-m-dH:i:s', $pageStatic->get_published_unix_timestamp());
            $updatedDateTimestampISO8601 = date('Y-m-dH:i:s', $pageStatic->get_updated_unix_timestamp());

            $createdDateTimestampISO8601WithoutTime = date('Y-m-d', $pageStatic->get_created_unix_timestamp());
            $publishedDateTimestampISO8601WithoutTime = date('Y-m-d', $pageStatic->get_published_unix_timestamp());
            $updatedDateTimestampISO8601WithoutTime = date('Y-m-d', $pageStatic->get_updated_unix_timestamp());
    
            $createdDateTimestampISO8601WithoutData = date('H:i:s', $pageStatic->get_created_unix_timestamp());
            $publishedDateTimestampISO8601WithoutData = date('H:i:s', $pageStatic->get_published_unix_timestamp());
            $updatedDateTimestampISO8601WithoutData = date('H:i:s', $pageStatic->get_updated_unix_timestamp());

            $pageTemplateVariables = [
              'PAGE_ID' => $pageStatic->get_id(),
              'PAGE_BREADCRUMPS' => $this->page->breadcrumbs->assembled,
              'PAGE_TITLE' => $pageStaticTitle,
              'PAGE_CONTENT' => $parsedown->text($pageStaticContent),
              'PAGE_PREVIEW_URL' => ($pageStatic->get_preview_url() != '') ? $pageStatic->get_preview_url() : PageStatic::get_preview_default_url($this->CMSCore, 1024),
              'PAGE_CREATED_DATE_TIMESTAMP' => $createdDateTimestamp,
              'PAGE_PUBLISHED_DATE_TIMESTAMP' => $pageStatic->get_published_unix_timestamp() > 0 ? $publishedDateTimestamp : '-',
              'PAGE_UPDATED_DATE_TIMESTAMP' => $updatedDateTimestamp,
              'PAGE_CREATED_DATE_TIMESTAMP_WITHOUT_TIME' => $createdDateTimestampWithoutTime,
              'PAGE_PUBLISHED_DATE_TIMESTAMP_WITHOUT_TIME' => $pageStatic->get_published_unix_timestamp() > 0 ? $publishedDateTimestampWithoutTime : '-',
              'PAGE_UPDATED_DATE_TIMESTAMP_WITHOUT_TIME' => $updatedDateTimestampWithoutTime,
              'PAGE_CREATED_DATE_TIMESTAMP_WITHOUT_DATE' => $createdDateTimestampWithoutData,
              'PAGE_PUBLISHED_DATE_TIMESTAMP_WITHOUT_DATE' => $pageStatic->get_published_unix_timestamp() > 0 ? $publishedDateTimestampWithoutData : '-',
              'PAGE_UPDATED_DATE_TIMESTAMP_WITHOUT_DATE' => $updatedDateTimestampWithoutData,
              'PAGE_CREATED_DATE_TIMESTAMP_ISO_8601' => $createdDateTimestampISO8601,
              'PAGE_PUBLISHED_DATE_TIMESTAMP_ISO_8601' => $publishedDateTimestampISO8601,
              'PAGE_UPDATED_DATE_TIMESTAMP_ISO_8601' => $updatedDateTimestampISO8601,
              'PAGE_CREATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME' => $createdDateTimestampISO8601WithoutTime,
              'PAGE_PUBLISHED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME' => $publishedDateTimestampISO8601WithoutTime,
              'PAGE_UPDATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME' => $updatedDateTimestampISO8601WithoutTime,
              'PAGE_CREATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE' => $createdDateTimestampISO8601WithoutData,
              'PAGE_PUBLISHED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE' => $publishedDateTimestampISO8601WithoutData,
              'PAGE_UPDATED_DATE_TIMESTAMP_ISO_8601_WITHOUT_DATE' => $updatedDateTimestampISO8601WithoutData
            ];

            $additionalFieldsData = $pageStatic->get_additional_fields_data();
            if (count($additionalFieldsData) > 0) {
              foreach ($additionalFieldsData as $name => $data) {
                $variableName = sprintf('PAGE_ADDITIONAL_DATA_%s', strtoupper($name));
                $pageTemplateVariables[$variableName] = $data;
              }
            }

            /** @var string Путь до персонального шаблона */
            $personalTemplatePath = ($pageStatic->exists_personal_template_file()) ? 'templates/' . $pageStatic->get_personal_template_path() : 'templates/page/static.tpl';

            $this->assembled = TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page.tpl', [
              'PAGE_NAME' => 'static',
              'PAGE_CONTENT' => TemplateCollector::assembly_file_content($this->CMSCore->theme, $personalTemplatePath, $pageTemplateVariables)
            ]);
          } else {
            http_response_code(404);
  
            $pageError = new PageError($this->CMSCore, $this->page, 404);
            $pageError->assembly();
            $this->assembled = $pageError->assembled;
          }
        } else {
          http_response_code(404);

          $pageError = new PageError($this->CMSCore, $this->page, 404);
          $pageError->assembly();
          $this->assembled = $pageError->assembled;
        }
      } else {
        http_response_code(404);

        $pageError = new PageError($this->CMSCore, $this->page, 404);
        $pageError->assembly();
        $this->assembled = $pageError->assembled;
      }
    }

  }

}

?>