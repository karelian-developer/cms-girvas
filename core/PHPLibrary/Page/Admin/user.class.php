<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Page\Admin {
  use \core\PHPLibrary\InterfacePage as InterfacePage;
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\User as User;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\TraitPage as TraitPage;

  class PageUser implements InterfacePage {
    use TraitPage;

    const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_USER_NAVIGATION_%s_LABEL';

    public SystemCore $CMSCore;
    public Page $page;
    public string $assembled = '';
    public array $navigationSubsections = [
      'back' => [
        'name' => 'back',
        'iconName' => 'back',
        'link' => '/users',
        'permanent' => true,
        'isActive' => false
      ],
    ];

    public function __construct(SystemCore $CMSCore, Page $page) {
      $this->CMSCore = $CMSCore;
      $this->page = $page;
    }

    /**
     * Инициализация подразделов
     * 
     * @return void
     */
    public function init_subnavigation() : void {
      $themeSource =& $this->CMSCore->theme->core->source;
      $this->init_admin_panel_subnavigation($this->CMSCore, $themeSource);
    }

    public function assembly() : void {
      $this->CMSCore->theme->add_style(['href' => 'styles/page/user.css', 'rel' => 'stylesheet']);

      $localeData = $this->CMSCore->locale->get_data();
      $localeName = $this->CMSCore->locale->get_name();

      /** @var null Пустая переменная */
      $user = null;
      if (!is_null($this->CMSCore->urlp->get_path(2))) {
        /** @var int Идентификационный номер пользователя */
        $userID = is_numeric($this->CMSCore->urlp->get_path(2)) ? (int)$this->CMSCore->urlp->get_path(2) : 0;
        /** @var User|null Объект пользователя */
        $user = User::exists_by_id($this->CMSCore, $userID) ? new User($this->CMSCore, $userID) : null;
        
        if (!is_null($user)) {
          // Инициализация набора данных пользователя
          $user->init_data(['*']);
        }
      }

      /** ===================
       *  Дополнительные поля
       *  ===================
       */

      /** @var array Типы полей */
      $fieldsTypes = $this->CMSCore->configurator->exists_database_entry_value('users_additional_field_type') ? json_decode($this->CMSCore->configurator->get_database_entry_value('users_additional_field_type'), true) : [];
      /** @var array Заголовки полей */
      $fieldsTitles = $this->CMSCore->configurator->exists_database_entry_value('users_additional_field_title') ? json_decode($this->CMSCore->configurator->get_database_entry_value('users_additional_field_title'), true) : [];
      /** @var array Описания полей */
      $fieldsDescriptions = $this->CMSCore->configurator->exists_database_entry_value('users_additional_field_description') ? json_decode($this->CMSCore->configurator->get_database_entry_value('users_additional_field_description'), true) : [];
      /** @var array Имена полей */
      $fieldsNames = $this->CMSCore->configurator->exists_database_entry_value('users_additional_field_name') ? json_decode($this->CMSCore->configurator->get_database_entry_value('users_additional_field_name'), true) : [];

      $additionalFieldsElements = [];
      foreach ($fieldsTypes as $index => $type) {
        $field_name_exploded = explode('_', $fieldsNames[$index]);

        foreach ($field_name_exploded as $string_index => $string) {
          if ($string_index > 0) {
            $field_name_exploded[$string_index] = ucfirst($string);
          }
        }

        $fieldNameTransformed = implode($field_name_exploded);

        if ($type === 'textarea') {
          if (!is_null($user)) {
            $fieldValue = !is_null($user->get_additional_field_data($fieldNameTransformed)) ? $user->get_additional_field_data($fieldNameTransformed) : '';
          }

          array_push($additionalFieldsElements, TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/user/form/fieldTextarea.tpl', [
            'FIELD_NAME' => $fieldsNames[$index],
            'FIELD_DESCRIPTION' => $fieldsDescriptions[$localeName][$index],
            'FIELD_TITLE' => $fieldsTitles[$localeName][$index],
            'FIELD_VALUE' => isset($fieldValue) ? $fieldValue : ''
          ]));
        } else {
          if (!is_null($user)) {
            $fieldValue = !is_null($user->get_additional_field_data($fieldNameTransformed)) ? $user->get_additional_field_data($fieldNameTransformed) : '';
          }

          array_push($additionalFieldsElements, TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/user/form/fieldInput.tpl', [
            'FIELD_NAME' => $fieldsNames[$index],
            'FIELD_DESCRIPTION' => $fieldsDescriptions[$localeName][$index],
            'FIELD_TYPE' => $fieldsTypes[$index],
            'FIELD_TITLE' => $fieldsTitles[$localeName][$index],
            'FIELD_VALUE' => isset($fieldValue) ? $fieldValue : ''
          ]));
        }
      }

      /** @var string Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/user.tpl', [
        'ADMIN_PANEL_PAGE_NAME' => 'user',
        'USER_ID' => !is_null($user) ? $user->get_id() : 0,
        'USER_LOGIN' => !is_null($user) ? $user->get_login() : '',
        'USER_EMAIL' => !is_null($user) ? $user->get_email() : '',
        'USER_NAME' => !is_null($user) ? $user->get_name() : '',
        'USER_SURNAME' => !is_null($user) ? $user->get_surname() : '',
        'USER_PATRONYMIC' => !is_null($user) ? $user->get_patronymic() : '',
        'USER_BIRTHDATE' => !is_null($user) ? date('Y-m-d', $user->get_birthdate_unix_timestamp()) : 0,
        'USER_BIRTHDATE_MINIMUM' => date('Y-m-d', time() - 3155760000),
        'USER_BIRTHDATE_MAXIMUM' => date('Y-m-d', time() - 441763200),
        'USER_ADDITIONAL_FIELDS' => implode($additionalFieldsElements),
        'USER_FORM_METHOD' => !is_null($user) ? 'PATCH' : 'PUT'
      ]);
    }
  }
}

?>