<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */


namespace core\PHPLibrary\Page\Admin\Settings;

use \core\PHPLibrary\SystemCore as CMSCore;
use \core\PHPLibrary\Template\Collector as ThemeCollector;

class SettingsEmail
{
  const FORM_PATH = 'templates/page/settings';

  public string $title = '';
  public string $description = '';
  public string $assembled = '';

  /**
   * __construct
   * 
   * @param CMSCore $CMSCore
   * @param string $name
   */
  public function __construct(
    public CMSCore $CMSCore,
    public string $name
  ) {}

  /**
   * Установить заголовок
   * 
   * @param string $value
   */
  public function setTitle(string $value) : void
  {
    $this->title = $value;
  }

  /**
   * Установить описание
   * 
   * @param string $value
   */
  public function setDescription(string $value) : void
  {
    $this->description = $value;
  }

  /**
   * Получить заголовок
   * 
   * @return string
   */
  public function getTitle() : string
  {
    return $this->title;
  }

  /**
   * Получить описание
   * 
   * @return string
   */
  public function getDescription() : string
  {
    return $this->description;
  }

  /**
   * Сборка
   * 
   * @param array $templateValues
   */
  public function assembly(array $templateValues = []) : void
  {
    $formTemplatePath = self::FORM_PATH . '/' . $this->name . '.tpl';
    $SMTPConfiguration = $this->CMSCore->configurator->getOtherCollection('smtp');
    
    $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, $formTemplatePath, [
      'SETTINGS_NAME' => $this->name,
      'SETTING_SMTP_PORT_VALUE' => $SMTPConfiguration['port'],
      'SETTING_SMTP_HOST_VALUE' => $SMTPConfiguration['host'],
      'SETTING_SMTP_USERNAME_VALUE' => $SMTPConfiguration['username'],
      'SETTING_SMTP_PASSWORD_VALUE' => $SMTPConfiguration['password'],
      'SETTING_SMTP_DOMAIN_VALUE' => $SMTPConfiguration['domain']
    ]);
  }
}