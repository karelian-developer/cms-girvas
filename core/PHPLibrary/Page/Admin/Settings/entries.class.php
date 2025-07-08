<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */


namespace core\PHPLibrary\Page\Admin\Settings;

use \core\PHPLibrary\SystemCore as SystemCore;
use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
use \core\PHPLibrary\Template as Template;
use \core\PHPLibrary\Template\Collector as ThemeCollector;

class SettingsEntries
{
  const FORM_PATH = 'templates/page/settings';

  public SystemCore $CMSCore;
  public string $title;
  public string $name;
  public string $description;
  public string $assembled = '';

  public function __construct(SystemCore $CMSCore, string $name)
  {
    $this->CMSCore = $CMSCore;
    $this->name = $name;
  }

  public function setTitle(string $value) : void
  {
    $this->title = $value;
  }

  public function setDescription(string $value) : void
  {
    $this->description = $value;
  }

  public function getTitle() : string
  {
    return $this->title;
  }

  public function getDescription() : string
  {
    return $this->description;
  }

  public function assembly(array $templateValues = []) : void
  {
    $formTemplatePath = self::FORM_PATH . '/' . $this->name . '.tpl';
    
    $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, $formTemplatePath, [
      'SETTINGS_NAME' => $this->name
    ]);
  }
}