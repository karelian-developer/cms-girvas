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

namespace core\PHPLibrary\Page\Admin\Settings;

use \core\PHPLibrary\SystemCore as CMSCore;
use \core\PHPLibrary\Template as Template;
use \core\PHPLibrary\Template\Collector as ThemeCollector;

class SettingsFiles implements SettingsPageInterface
{
  const FORM_PATH = 'templates/page/settings';

  public string $title;
  public string $description;
  public string $assembled = '';

  /**
   * __construct
   * 
   * @param CMSCore $CMSCore
   * @param string $name
   * 
   * @return void
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
    
    $settingAutoConvertFileImageStatusValue = $this->CMSCore->configurator->getAutoConvertFileImageStatus();

    $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, $formTemplatePath, [
      'SETTINGS_NAME' => $this->name,
      'SETTING_UPLOAD_FILE_WEIGHT_MAX_VALUE' => $this->CMSCore->configurator->getUploadFileWeightMax(),
      'SETTING_UPLOAD_FILE_IMAGE_WIDTH_MAX_VALUE' => $this->CMSCore->configurator->getUploadFileImageWidthMax(),
      'SETTING_UPLOAD_FILE_IMAGE_HEIGHT_MAX_VALUE' => $this->CMSCore->configurator->getUploadFileImageHeightMax(),
      'SETTING_UPLOAD_FILE_IMAGE_AVATAR_WEIGHT_MAX_VALUE' => $this->CMSCore->configurator->getUploadFileImageAvatarWeightMax(),
      'SETTING_UPLOAD_FILE_IMAGE_AVATAR_WIDTH_MAX_VALUE' => $this->CMSCore->configurator->getUploadFileImageAvatarWidthMax(),
      'SETTING_UPLOAD_FILE_IMAGE_AVATAR_HEIGHT_MAX_VALUE' => $this->CMSCore->configurator->getUploadFileImageAvatarHeightMax(),
      'SETTING_UPLOAD_IMAGE_COMPRESSION_VALUE' => $this->CMSCore->configurator->getUploadImageCompression(),
      'SETTING_AUTO_CONVERT_FILE_IMAGE_FORMAT_VALUE' => $this->CMSCore->configurator->getAutoConvertFileImageExtension(),
      'SETTING_AUTO_CONVERT_FILE_IMAGE_STATUS_VALUE' => $settingAutoConvertFileImageStatusValue,
      'SETTING_AUTO_CONVERT_FILE_IMAGE_CHECKED_VALUE' => $settingAutoConvertFileImageStatusValue === 'on' ? 'checked' : '',
    ]);
  }
}