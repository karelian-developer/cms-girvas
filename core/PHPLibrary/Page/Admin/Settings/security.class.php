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

use \core\PHPLibrary\PageStatic as PageStatic;
use \core\PHPLibrary\SystemCore as CMSCore;
use \core\PHPLibrary\Template as Template;
use \core\PHPLibrary\Template\Collector as ThemeCollector;

class SettingsSecurity implements SettingsPageInterface
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
    /** @var string Текущая локаль админки */
    $adminLocaleName = $this->CMSCore->locale->getName();

    $formTemplatePath = self::FORM_PATH . '/' . $this->name . '.tpl';
    
    $settingAllowedUsersRegistrationStatusValue = $this->CMSCore->configurator->existsDatabaseEntryValue('security_allowed_users_registration_status')
      ? $this->CMSCore->configurator->getDatabaseEntryValue('security_allowed_users_registration_status')
      : '';
    $settingAllowedEmailsStatusValue = $this->CMSCore->configurator->existsDatabaseEntryValue('security_allowed_emails_status')
      ? $this->CMSCore->configurator->getDatabaseEntryValue('security_allowed_emails_status')
      : '';
    $settingAllowedIPAdminStatusValue = $this->CMSCore->configurator->existsDatabaseEntryValue('security_allowed_admin_ip_status')
      ? $this->CMSCore->configurator->getDatabaseEntryValue('security_allowed_admin_ip_status')
      : '';
    $settingPremoderationCreateStatusValue = $this->CMSCore->configurator->existsDatabaseEntryValue('security_premoderation_create_status')
      ? $this->CMSCore->configurator->getDatabaseEntryValue('security_premoderation_create_status')
      : '';
    $settingPremoderationLinksFilterStatusValue = $this->CMSCore->configurator->existsDatabaseEntryValue('security_premoderation_links_filter_status')
      ? $this->CMSCore->configurator->getDatabaseEntryValue('security_premoderation_links_filter_status')
      : '';
    $settingPremoderationWordsFilterStatusValue = $this->CMSCore->configurator->existsDatabaseEntryValue('security_premoderation_words_filter_status')
      ? $this->CMSCore->configurator->getDatabaseEntryValue('security_premoderation_words_filter_status')
      : '';
    // ============================================================
    // COOKIE-БАННЕР (152-ФЗ)
    // ============================================================
    $settingCookieBannerStatusValue = $this->CMSCore->configurator->existsDatabaseEntryValue('security_cookie_banner_status')
      ? $this->CMSCore->configurator->getDatabaseEntryValue('security_cookie_banner_status')
      : 'off';

    $settingCookieBannerDocumentValue = $this->CMSCore->configurator->existsDatabaseEntryValue('security_cookie_banner_document')
      ? $this->CMSCore->configurator->getDatabaseEntryValue('security_cookie_banner_document')
      : '';

    // Список юр. документов
    $legalDocumentsForCookie = PageStatic::getAllLegalDocuments($this->CMSCore, $adminLocaleName);

    $cookieDocumentItems = [];
    foreach ($legalDocumentsForCookie as $document) {
      $cookieDocumentItems[] = [
        'name'  => $document['name'],
        'title' => $document['title'],
      ];
    }

    $cookieDocumentItemsJSON = json_encode($cookieDocumentItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    // ============================================================
    // ЮРИДИЧЕСКИЕ ДОКУМЕНТЫ (152-ФЗ)
    // ============================================================

    /** @var array Все статические страницы с isLegalDocument = true */
    $legalDocuments = PageStatic::getAllLegalDocuments($this->CMSCore, $adminLocaleName);

    /** @var array Текущее значение настройки (JSON → массив) */
    $currentLegalDocuments = [];
    if ($this->CMSCore->configurator->existsDatabaseEntryValue('security_legal_documents')) {
      $rawValue = $this->CMSCore->configurator->getDatabaseEntryValue('security_legal_documents');
      $currentLegalDocuments = json_decode($rawValue, true) ?? [];
    }

    /** @var string HTML-чекбоксы юридических документов */
    $legalDocumentsElements = [];

    foreach ($legalDocuments as $document) {
      $legalDocumentsElements[] = ThemeCollector::assemblyFileContent(
        $this->CMSCore->theme,
        'templates/page/settings/security/legalDocumentItem.tpl',
        [
          'DOCUMENT_ID' => $document['id'],
          'DOCUMENT_KEY' => htmlspecialchars($document['name']),
          'DOCUMENT_TITLE' => htmlspecialchars($document['title']),
          'DOCUMENT_CHECKED' => in_array($document['name'], $currentLegalDocuments, true) ? 'checked' : ''
        ]
      );
    }

    $legalDocumentsHTML = !empty($legalDocumentsElements)
      ? implode("\n", $legalDocumentsElements)
      : '<p class="settings-empty">' . htmlspecialchars($this->CMSCore->locale->getSingleValueByKey('PAGE_SETTINGS_SETTING_SECURITY_LEGAL_DOCUMENTS_EMPTY')) . '</p>';

    // ============================================================
    // ЮРИДИЧЕСКИЕ ДОКУМЕНТЫ (152-ФЗ)
    // ============================================================
    $adminLocaleName = $this->CMSCore->locale->getName();
    $legalDocuments = PageStatic::getAllLegalDocuments($this->CMSCore, $adminLocaleName);

    $legalDocumentsElements = [];
    foreach ($legalDocuments as $document) {
      $settingName = 'security_legal_documents_' . $document['id'] . '_status';
      $settingValue = $this->CMSCore->configurator->existsDatabaseEntryValue($settingName)
        ? $this->CMSCore->configurator->getDatabaseEntryValue($settingName)
        : 'off';

      $legalDocumentsElements[] = ThemeCollector::assemblyFileContent(
        $this->CMSCore->theme,
        'templates/page/settings/security/legalDocumentItem.tpl',
        [
          'DOCUMENT_ID' => $document['id'],
          'DOCUMENT_KEY' => htmlspecialchars($document['name']),
          'DOCUMENT_TITLE' => htmlspecialchars($document['title']),
          'HIDDEN_INPUT_ID' => 'I' . random_int(1000000000, 9999999999),
          'CHECKBOX_INPUT_ID' => 'I' . random_int(1000000000, 9999999999),
          'STATUS_VALUE' => $settingValue === 'on' ? 'on' : 'off',
          'CHECKED' => $settingValue === 'on' ? 'checked' : ''
        ]
      );
    }

    $legalDocumentsHTML = !empty($legalDocumentsElements)
      ? implode("\n", $legalDocumentsElements)
      : '<div class="cell grid-table__cell grid-table__cell_data">' 
        . htmlspecialchars($this->CMSCore->locale->getSingleValueByKey('PAGE_SETTINGS_SETTING_SECURITY_LEGAL_DOCUMENTS_EMPTY') ?? '')
        . '</div>';

    $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, $formTemplatePath, [
      'SETTINGS_NAME' => $this->name,
      'SETTING_NOTIFICATION_TELEGRAM_CHATS_IDS' => $this->CMSCore->configurator->existsDatabaseEntryValue('security_notification_telegram_chats_ids') ? implode(', ', json_decode($this->CMSCore->configurator->getDatabaseEntryValue('security_notification_telegram_chats_ids'), true)) : '',
      'SETTING_NOTIFICATION_MAX_CHATS_IDS' => $this->CMSCore->configurator->existsDatabaseEntryValue('security_notification_max_chats_ids') ? implode(', ', json_decode($this->CMSCore->configurator->getDatabaseEntryValue('security_notification_max_chats_ids'), true)) : '',
      'SETTING_ALLOWED_USERS_REGISTRATION_STATUS_VALUE' => $this->CMSCore->configurator->existsDatabaseEntryValue('security_allowed_users_registration_status') ? $this->CMSCore->configurator->getDatabaseEntryValue('security_allowed_users_registration_status') : 'off',
      'SETTING_ALLOWED_USERS_REGISTRATION_CHECKED_VALUE' => $settingAllowedUsersRegistrationStatusValue === 'on' ? 'checked' : '',
      'SETTING_ALLOWED_EMAILS_VALUE' => $this->CMSCore->configurator->existsDatabaseEntryValue('security_allowed_emails') ? implode(', ', json_decode($this->CMSCore->configurator->getDatabaseEntryValue('security_allowed_emails'), true)) : '',
      'SETTING_ALLOWED_EMAILS_STATUS_VALUE' => $this->CMSCore->configurator->existsDatabaseEntryValue('security_allowed_emails_status') ? $this->CMSCore->configurator->getDatabaseEntryValue('security_allowed_emails_status') : 'off',
      'SETTING_ALLOWED_EMAILS_CHECKED_VALUE' => $settingAllowedEmailsStatusValue === 'on' ? 'checked' : '',
      'SETTING_ALLOWED_IP_ADMIN_VALUE' => $this->CMSCore->configurator->existsDatabaseEntryValue('security_allowed_admin_ip') ? implode(', ', json_decode($this->CMSCore->configurator->getDatabaseEntryValue('security_allowed_admin_ip'), true)) : '',
      'SETTING_ALLOWED_IP_ADMIN_STATUS_VALUE' => $this->CMSCore->configurator->existsDatabaseEntryValue('security_allowed_admin_ip_status') ? $this->CMSCore->configurator->getDatabaseEntryValue('security_allowed_admin_ip_status') : 'off',
      'SETTING_ALLOWED_IP_ADMIN_CHECKED_VALUE' => $settingAllowedIPAdminStatusValue === 'on' ? 'checked' : '',
      'SETTING_PREMODERATION_CREATE_STATUS_VALUE' => $this->CMSCore->configurator->existsDatabaseEntryValue('security_premoderation_create_status') ? $this->CMSCore->configurator->getDatabaseEntryValue('security_premoderation_create_status') : 'off',
      'SETTING_PREMODERATION_CREATE_CHECKED_VALUE' => $settingPremoderationCreateStatusValue === 'on' ? 'checked' : '',
      'SETTING_NEGATIVE_EVALUATION_THRESHOLD_VALUE' => $this->CMSCore->configurator->existsDatabaseEntryValue('security_negative_evaluation_threshold') ? $this->CMSCore->configurator->getDatabaseEntryValue('security_negative_evaluation_threshold') : 0,
      'SETTING_PREMODERATION_LINKS_FILTER_STATUS_VALUE' => $this->CMSCore->configurator->existsDatabaseEntryValue('security_premoderation_links_filter_status') ? $this->CMSCore->configurator->getDatabaseEntryValue('security_premoderation_links_filter_status') : 'off',
      'SETTING_PREMODERATION_LINKS_FILTER_CHECKED_VALUE' => $settingPremoderationLinksFilterStatusValue === 'on' ? 'checked' : '',
      'SETTING_PREMODERATION_WORDS_FILTER_LIST_VALUE' => $this->CMSCore->configurator->existsDatabaseEntryValue('security_premoderation_words_filter_list') ? implode(', ', json_decode($this->CMSCore->configurator->getDatabaseEntryValue('security_premoderation_words_filter_list'), true)) : '',
      'SETTING_PREMODERATION_WORDS_FILTER_STATUS_VALUE' => $this->CMSCore->configurator->existsDatabaseEntryValue('security_premoderation_words_filter_status') ? $this->CMSCore->configurator->getDatabaseEntryValue('security_premoderation_words_filter_status') : 'off',
      'SETTING_PREMODERATION_WORDS_FILTER_CHECKED_VALUE' => $settingPremoderationWordsFilterStatusValue === 'on' ? 'checked' : '',
      'SETTING_LEGAL_DOCUMENTS_ELEMENTS' => $legalDocumentsHTML,
      'SETTING_COOKIE_BANNER_STATUS_VALUE' => $settingCookieBannerStatusValue,
      'SETTING_COOKIE_BANNER_CHECKED_VALUE' => $settingCookieBannerStatusValue === 'on' ? 'checked' : '',
      'SETTING_COOKIE_BANNER_DOCUMENT_VALUE' => htmlspecialchars($settingCookieBannerDocumentValue),
      'SETTING_COOKIE_BANNER_DOCUMENT_ITEMS' => htmlspecialchars($cookieDocumentItemsJSON, ENT_QUOTES),
    ]);
  }
}