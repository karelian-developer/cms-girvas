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

use \core\PHPLibrary\SystemCore\Locale as  CMSLocale;
use \core\PHPLibrary\Template as Theme;
use \core\PHPLibrary\Template\Collector as ThemeCollector;

/**
 * Сборка шаблона по запросу
 */
<<<<<<< HEAD
if ($system_core->urlp->get_path(2) == 'assembly') {
=======
if ($CMSCore->urlp->getPath(2) === 'assembly') {
>>>>>>> develop
  if (isset($_GET['templateCategory']) && isset($_GET['templateFilePath'])) {
    $themeCategory = $_GET['templateCategory'];
    
<<<<<<< HEAD
    switch ($template_category) {
      case 'base': $template_config_name = 'base_template'; $locale_name = ($system_core->configurator->exists_database_entry_value('base_locale')) ? $system_core->configurator->get_database_entry_value('base_locale') : 'en_US'; break;
      case 'admin': $template_config_name = 'base_admin_template'; $locale_name = ($system_core->configurator->exists_database_entry_value('base_admin_locale')) ? $system_core->configurator->get_database_entry_value('base_admin_locale') : 'en_US'; break;
      case 'install': $template_config_name = 'base_install_template'; $locale_name = (!is_null($system_core->urlp->get_param('locale'))) ? $system_core->urlp->get_param('locale') : 'en_US'; break;
      default: $template_config_name = sprintf('%s_template', $template_category); break;
    }

    switch ($template_category) {
      case 'base': $system_core->locale = new SystemCoreLocale($system_core, $locale_name, 'base'); break;
      case 'admin': $system_core->locale = new SystemCoreLocale($system_core, $locale_name, 'admin'); break;
      case 'install': $system_core->locale = new SystemCoreLocale($system_core, $locale_name, 'install'); break;
      default: $system_core->locale = sprintf('%s_template', $template_category); break;
    }
=======
    switch ($themeCategory) {
      case 'base':
        $themeConfigName = 'base_template';
        $localeName = $CMSCore->configurator->existsDatabaseEntryValue('base_locale')
          ? $CMSCore->configurator->getDatabaseEntryValue('base_locale')
          : 'en_US';
        break;
      case 'admin':
        $themeConfigName = 'base_admin_template';
        $localeName = $CMSCore->configurator->existsDatabaseEntryValue('base_admin_locale')
          ? $CMSCore->configurator->getDatabaseEntryValue('base_admin_locale')
          : 'en_US';
        break;
      case 'install':
        $themeConfigName = 'base_install_template';
        $localeName = $CMSCore->urlp->getParam('locale') ?? 'en_US';
        break;
      default:
        $themeConfigName = $themeCategory . '_template';
        break;
    }

    $CMSCore->locale = match ($themeCategory) {
      'base' => new CMSLocale($CMSCore, $localeName, 'base'),
      'admin' => new CMSLocale($CMSCore, $localeName, 'admin'),
      'install' => new CMSLocale($CMSCore, $localeName, 'install'),
      default => $themeCategory . '_template'
    };
>>>>>>> develop

    $CMSCore->locale->setTypeName($themeCategory);
    $CMSCore->locale->initPathes();

    $themeName = $CMSCore->configurator->existsDatabaseEntryValue($themeConfigName) ? $CMSCore->configurator->getDatabaseEntryValue($themeConfigName) : 'default';
    $theme = new Theme($CMSCore, $themeName, $themeCategory);

    $themesPatterns = [];
    if (isset($_GET['patternNames']) && isset($_GET['patternValues'])) {
      $patternNames = explode(',', $_GET['patternNames']);
      $patternValues = explode(',', $_GET['patternValues']);
      foreach ($patternNames as $index => $name) {
        $themesPatterns[$name] = isset($patternValues[$index]) ? str_replace('{DELIM}', ',', $patternValues[$index]) : '';
      }
    }

    $handlerOutputData['templateAssembled'] = ThemeCollector::assemblyLocale(ThemeCollector::assemblyFileContent($theme, $_GET['templateFilePath'], $themesPatterns), $CMSCore->locale);
    
    $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  }
}

/**
 * Получение данных о текущем шаблоне для конкретной категории
 */
<<<<<<< HEAD
if ($system_core->urlp->get_path(2) == null && $system_core->urlp->get_param('categoryName') != null) {
  $template_category_name = $system_core->urlp->get_param('categoryName');
    
  switch ($template_category_name) {
    case 'base': $template_config_name = 'base_template'; $locale_name = ($system_core->configurator->exists_database_entry_value('base_locale')) ? $system_core->configurator->get_database_entry_value('base_locale') : 'en_US'; break;
    case 'admin': $template_config_name = 'base_admin_template'; $locale_name = ($system_core->configurator->exists_database_entry_value('base_admin_locale')) ? $system_core->configurator->get_database_entry_value('base_admin_locale') : 'en_US'; break;
    case 'install': $template_config_name = 'base_install_template'; $locale_name = (!is_null($system_core->urlp->get_param('locale'))) ? $system_core->urlp->get_param('locale') : 'en_US'; break;
    default: $template_config_name = sprintf('%s_template', $template_category_name); break;
  }

  switch ($template_category_name) {
    case 'base': $system_core->locale = new SystemCoreLocale($system_core, $locale_name, 'base'); break;
    case 'admin': $system_core->locale = new SystemCoreLocale($system_core, $locale_name, 'admin'); break;
    case 'install': $system_core->locale = new SystemCoreLocale($system_core, $locale_name, 'install'); break;
    default: $system_core->locale = sprintf('%s_template', $template_category_name); break;
  }

  $template_name = ($system_core->configurator->exists_database_entry_value($template_config_name)) ? $system_core->configurator->get_database_entry_value($template_config_name) : 'default';

  $handler_output_data['template'] = [
    'name' => $template_name,
    'categoryName' => $template_category_name
  ];

  $handler_message = (!isset($handler_message)) ? $system_core->locale->get_single_value_by_key('API_GET_DATA_SUCCESS') : $handler_message;
  $handler_status_code = (!isset($handler_status_code)) ? 1 : $handler_status_code;
}

?>
=======
if ($CMSCore->urlp->getPath(2) === null && $CMSCore->urlp->getParam('categoryName') !== null) {
  $themeCategoryName = $CMSCore->urlp->getParam('categoryName');
    
  switch ($themeCategoryName) {
    case 'base':
      $themeConfigName = 'base_template';
      $localeName = $CMSCore->configurator->existsDatabaseEntryValue('base_locale')
        ? $CMSCore->configurator->getDatabaseEntryValue('base_locale')
        : 'en_US';
      break;
    case 'admin':
      $themeConfigName = 'base_admin_template';
      $localeName = $CMSCore->configurator->existsDatabaseEntryValue('base_admin_locale')
        ? $CMSCore->configurator->getDatabaseEntryValue('base_admin_locale')
        : 'en_US';
      break;
    case 'install':
      $themeConfigName = 'base_install_template';
      $localeName = $CMSCore->urlp->getParam('locale') ?? 'en_US';
      break;
    default:
      $themeConfigName = $themeCategoryName . '_template';
      break;
  }

  $CMSCore->locale = match ($themeCategoryName) {
    'base' => new  CMSLocale($CMSCore, $localeName, 'base'),
    'admin' => new  CMSLocale($CMSCore, $localeName, 'admin'),
    'install' => new  CMSLocale($CMSCore, $localeName, 'install'),
    default => $themeCategoryName . '_template'
  };

  $CMSCore->locale->setTypeName($themeCategoryName);
  $CMSCore->locale->initPathes();

  $themeName = $CMSCore->configurator->existsDatabaseEntryValue($themeConfigName) ? $CMSCore->configurator->getDatabaseEntryValue($themeConfigName) : 'default';

  $handlerOutputData['template'] = [
    'name' => $themeName,
    'categoryName' => $themeCategoryName
  ];

  $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
  $handlerStatusCode = $handlerStatusCode ?? 1;
}
>>>>>>> develop
