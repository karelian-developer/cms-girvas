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

class SettingsFiles
{
  const FORM_PATH = 'templates/page/settings';

  public SystemCore $CMSCore;
  public string $title;
  public string $name;
  public string $description;
  public string $assembled = '';

  /**
   * __construct
   * 
   * @param SystemCore $CMSCore
   * @param string $name
   */
  public function __construct(SystemCore $CMSCore, string $name)
  {
    $this->CMSCore = $CMSCore;
    $this->name = $name;
  }

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
    
    $settingAutoConvertFileImageStatusValue = $this->CMSCore->configurator->getAutoConvertFileImageStatus();

    $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, $formTemplatePath, [
      'SETTINGS_NAME' => $this->name,
      // Максимальный вес файла
      'SETTING_UPLOAD_FILE_WEIGHT_MAX_VALUE' => $this->CMSCore->configurator->getUploadFileWeightMax(),
      // Максимальная ширина изображения
      'SETTING_UPLOAD_FILE_IMAGE_WIDTH_MAX_VALUE' => $this->CMSCore->configurator->getUploadFileImageWidthMax(),
      // Максимальная высота изображения
      'SETTING_UPLOAD_FILE_IMAGE_HEIGHT_MAX_VALUE' => $this->CMSCore->configurator->getUploadFileImageHeightMax(),
      // Максимальный вес изображения аватара
      'SETTING_UPLOAD_FILE_IMAGE_AVATAR_WEIGHT_MAX_VALUE' => $this->CMSCore->configurator->getUploadFileImageAvatarWeightMax(),
      // Максимальная ширина изображения аватара
      'SETTING_UPLOAD_FILE_IMAGE_AVATAR_WIDTH_MAX_VALUE' => $this->CMSCore->configurator->getUploadFileImageAvatarWidthMax(),
      // Максимальная высота изображения аватара
      'SETTING_UPLOAD_FILE_IMAGE_AVATAR_HEIGHT_MAX_VALUE' => $this->CMSCore->configurator->getUploadFileImageAvatarHeightMax(),
      // Формат изображения для автоматической конвертации
      'SETTING_AUTO_CONVERT_FILE_IMAGE_FORMAT_VALUE' => $this->CMSCore->configurator->getAutoConvertFileImageExtension(),
      // Статус автоматической конвертации изображения
      'SETTING_AUTO_CONVERT_FILE_IMAGE_STATUS_VALUE' => $settingAutoConvertFileImageStatusValue,
      'SETTING_AUTO_CONVERT_FILE_IMAGE_CHECKED_VALUE' => $settingAutoConvertFileImageStatusValue === 'on' ? 'checked' : '',
    ]);
  }
}