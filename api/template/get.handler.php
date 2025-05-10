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
if ($system_core->urlp->get_path(2) == 'assembly') {
  if (isset($_GET['templateCategory']) && isset($_GET['templateFilePath'])) {
    $template_category = $_GET['templateCategory'];
    
    switch ($template_category) {
      case 'default': $template_config_name = 'base_template'; $locale_name = ($system_core->configurator->exists_database_entry_value('base_locale')) ? $system_core->configurator->get_database_entry_value('base_locale') : 'en_US'; break;
      case 'admin': $template_config_name = 'base_admin_template'; $locale_name = ($system_core->configurator->exists_database_entry_value('base_admin_locale')) ? $system_core->configurator->get_database_entry_value('base_admin_locale') : 'en_US'; break;
      case 'install': $template_config_name = 'base_install_template'; $locale_name = (!is_null($system_core->urlp->get_param('locale'))) ? $system_core->urlp->get_param('locale') : 'en_US'; break;
      default: $template_config_name = sprintf('%s_template', $template_category); break;
    }

    switch ($template_category) {
      case 'default': $system_core->locale = new SystemCoreLocale($system_core, $locale_name, 'base'); break;
      case 'admin': $system_core->locale = new SystemCoreLocale($system_core, $locale_name, 'admin'); break;
      case 'install': $system_core->locale = new SystemCoreLocale($system_core, $locale_name, 'install'); break;
      default: $system_core->locale = sprintf('%s_template', $template_category); break;
    }

    $template_name = ($system_core->configurator->exists_database_entry_value($template_config_name)) ? $system_core->configurator->get_database_entry_value($template_config_name) : 'default';
    $template = new Template($system_core, $template_name, $template_category);

    $templates_patterns = [];
    if (isset($_GET['patternNames']) && isset($_GET['patternValues'])) {
      $pattern_names = explode(',', $_GET['patternNames']);
      $pattern_values = explode(',', $_GET['patternValues']);
      foreach ($pattern_names as $pattern_index => $pattern_name) {
        $templates_patterns[$pattern_name] = isset($pattern_values[$pattern_index]) ? str_replace('{DELIM}', ',', $pattern_values[$pattern_index]) : '';
      }
    }

    $handler_output_data['templateAssembled'] = TemplateCollector::assembly_locale(TemplateCollector::assembly_file_content($template, $_GET['templateFilePath'], $templates_patterns), $system_core->locale);
    
    $handler_message = (!isset($handler_message)) ? $system_core->locale->get_single_value_by_key('API_GET_DATA_SUCCESS') : $handler_message;
    $handler_status_code = (!isset($handler_status_code)) ? 1 : $handler_status_code;
  }
}

/**
 * Получение данных о текущем шаблоне для конкретной категории
 */
if ($system_core->urlp->get_path(2) == null && $system_core->urlp->get_param('categoryName') != null) {
  $template_category_name = $system_core->urlp->get_param('categoryName');
    
  switch ($template_category_name) {
    case 'default': $template_config_name = 'base_template'; $locale_name = ($system_core->configurator->exists_database_entry_value('base_locale')) ? $system_core->configurator->get_database_entry_value('base_locale') : 'en_US'; break;
    case 'admin': $template_config_name = 'base_admin_template'; $locale_name = ($system_core->configurator->exists_database_entry_value('base_admin_locale')) ? $system_core->configurator->get_database_entry_value('base_admin_locale') : 'en_US'; break;
    case 'install': $template_config_name = 'base_install_template'; $locale_name = (!is_null($system_core->urlp->get_param('locale'))) ? $system_core->urlp->get_param('locale') : 'en_US'; break;
    default: $template_config_name = sprintf('%s_template', $template_category_name); break;
  }

  switch ($template_category_name) {
    case 'default': $system_core->locale = new SystemCoreLocale($system_core, $locale_name, 'base'); break;
    case 'admin': $system_core->locale = new SystemCoreLocale($system_core, $locale_name, 'admin'); break;
    case 'install': $system_core->locale = new SystemCoreLocale($system_core, $locale_name, 'install'); break;
    default: $system_core->locale = sprintf('%s_template', $template_category_name); break;
  }

  $template_name = ($system_core->configurator->exists_database_entry_value($template_config_name)) ? $system_core->configurator->get_database_entry_value($template_config_name) : 'default';

  $handler_output_data['template'] = [
    'name' => $template_name
  ];

  $handler_message = (!isset($handler_message)) ? $system_core->locale->get_single_value_by_key('API_GET_DATA_SUCCESS') : $handler_message;
  $handler_status_code = (!isset($handler_status_code)) ? 1 : $handler_status_code;
}

?>