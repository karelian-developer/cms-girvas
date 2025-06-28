<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

if (!defined('IS_NOT_HACKED')) {
  http_response_code(503);
  die('An attempted hacker attack has been detected.');
}

use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
use \core\PHPLibrary\Template as Template;
use \core\PHPLibrary\Template\Collector as TemplateCollector;

/**
 * Сборка шаблона по запросу
 */
if ($CMSCore->urlp->getPath(2) == 'assembly') {
  if (isset($_GET['templateCategory']) && isset($_GET['templateFilePath'])) {
    $themeCategory = $_GET['templateCategory'];
    
    switch ($themeCategory) {
      case 'base': $themeConfigName = 'base_template'; $localeName = $CMSCore->configurator->existsDatabaseEntryValue('base_locale') ? $CMSCore->configurator->getDatabaseEntryValue('base_locale') : 'en_US'; break;
      case 'admin': $themeConfigName = 'base_admin_template'; $localeName = $CMSCore->configurator->existsDatabaseEntryValue('base_admin_locale') ? $CMSCore->configurator->getDatabaseEntryValue('base_admin_locale') : 'en_US'; break;
      case 'install': $themeConfigName = 'base_install_template'; $localeName = $CMSCore->urlp->getParam('locale') ?? 'en_US'; break;
      default: $themeConfigName = sprintf('%s_template', $themeCategory); break;
    }

    $CMSCore->locale = match ($themeCategory) {
      'base' => new SystemCoreLocale($CMSCore, $localeName, 'base'),
      'admin' => new SystemCoreLocale($CMSCore, $localeName, 'admin'),
      'install' => new SystemCoreLocale($CMSCore, $localeName, 'install'),
      default => $themeCategory . '_template'
    };

    $themeName = ($CMSCore->configurator->existsDatabaseEntryValue($themeConfigName)) ? $CMSCore->configurator->getDatabaseEntryValue($themeConfigName) : 'default';
    $theme = new Template($CMSCore, $themeName, $themeCategory);

    $themesPatterns = [];
    if (isset($_GET['patternNames']) && isset($_GET['patternValues'])) {
      $patternNames = explode(',', $_GET['patternNames']);
      $patternValues = explode(',', $_GET['patternValues']);
      foreach ($patternNames as $index => $name) {
        $themesPatterns[$name] = isset($patternValues[$index]) ? str_replace('{DELIM}', ',', $patternValues[$index]) : '';
      }
    }

    $handlerOutputData['templateAssembled'] = TemplateCollector::assemblyLocale(TemplateCollector::assemblyFileContent($theme, $_GET['templateFilePath'], $themesPatterns), $CMSCore->locale);
    
    $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  }
}

/**
 * Получение данных о текущем шаблоне для конкретной категории
 */
if ($CMSCore->urlp->get_path(2) == null && $CMSCore->urlp->getParam('categoryName') != null) {
  $themeCategoryName = $CMSCore->urlp->getParam('categoryName');
    
  switch ($themeCategoryName) {
    case 'base': $themeConfigName = 'base_template'; $localeName = ($CMSCore->configurator->existsDatabaseEntryValue('base_locale')) ? $CMSCore->configurator->getDatabaseEntryValue('base_locale') : 'en_US'; break;
    case 'admin': $themeConfigName = 'base_admin_template'; $localeName = ($CMSCore->configurator->existsDatabaseEntryValue('base_admin_locale')) ? $CMSCore->configurator->getDatabaseEntryValue('base_admin_locale') : 'en_US'; break;
    case 'install': $themeConfigName = 'base_install_template'; $localeName = $CMSCore->urlp->getParam('locale') ?? 'en_US'; break;
    default: $themeConfigName = sprintf('%s_template', $themeCategoryName); break;
  }

  $CMSCore->locale = match ($themeCategoryName) {
    'base' => new SystemCoreLocale($CMSCore, $localeName, 'base'),
    'admin' => new SystemCoreLocale($CMSCore, $localeName, 'admin'),
    'install' => new SystemCoreLocale($CMSCore, $localeName, 'install'),
    default => $themeCategoryName . '_template'
  };

  $themeName = $CMSCore->configurator->existsDatabaseEntryValue($themeConfigName) ? $CMSCore->configurator->getDatabaseEntryValue($themeConfigName) : 'default';

  $handlerOutputData['template'] = [
    'name' => $themeName,
    'categoryName' => $themeCategoryName
  ];

  $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
  $handlerStatusCode = $handlerStatusCode ?? 1;
}