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

    public SystemCore $system_core;
    public Page $page;
    public string $assembled = '';
    public array $navigation_subsections_array = [
      'back' => [
        'name' => 'back',
        'iconName' => 'back',
        'link' => '/users',
        'permanent' => true,
        'isActive' => false
      ],
    ];

    public function __construct(SystemCore $system_core, Page $page) {
      $this->system_core = $system_core;
      $this->page = $page;
    }

    /**
     * Инициализация подразделов
     * 
     * @return void
     */
    public function init_subnavigation() : void {
      $template_source =& $this->system_core->template->core->source;
      $this->init_admin_panel_subnavigation($this->system_core, $template_source);
    }

    public function assembly() : void {
      $this->system_core->template->add_style(['href' => 'styles/page/user.css', 'rel' => 'stylesheet']);

      $locale_data = $this->system_core->locale->get_data();

      /** @var null Пустая переменная */
      $user = null;
      if (!is_null($this->system_core->urlp->get_path(2))) {
        /** @var int Идентификационный номер пользователя */
        $user_id = (is_numeric($this->system_core->urlp->get_path(2))) ? (int)$this->system_core->urlp->get_path(2) : 0;
        /** @var User|null Объект пользователя */
        $user = (User::exists_by_id($this->system_core, $user_id)) ? new User($this->system_core, $user_id) : null;
        
        if (!is_null($user)) {
          // Инициализация набора данных пользователя
          $user->init_data(['*']);
        }
      }

      /** ===============================
       *  Дополнительные поля для профиля
       *  ===============================
       */

      /** @var array Типы полей */
      $fields_types = ($this->system_core->configurator->exists_database_entry_value('users_additional_field_type')) ? json_decode($this->system_core->configurator->get_database_entry_value('users_additional_field_type'), true) : [];
      /** @var array Заголовки полей */
      $fields_titles = ($this->system_core->configurator->exists_database_entry_value('users_additional_field_title')) ? json_decode($this->system_core->configurator->get_database_entry_value('users_additional_field_title'), true) : [];
      /** @var array Описания полей */
      $fields_descriptions = ($this->system_core->configurator->exists_database_entry_value('users_additional_field_description')) ? json_decode($this->system_core->configurator->get_database_entry_value('users_additional_field_description'), true) : [];
      /** @var array Имена полей */
      $fields_names = ($this->system_core->configurator->exists_database_entry_value('users_additional_field_name')) ? json_decode($this->system_core->configurator->get_database_entry_value('users_additional_field_name'), true) : [];
      /** @var string Имя языкового базового пакета CMS */
      $cms_locale_setted = $this->system_core->configurator->get_database_entry_value('base_locale');

      $additional_fields_elements = [];
      foreach ($fields_types as $field_index => $field_type) {
        $field_name_exploded = explode('_', $fields_names[$field_index]);

        foreach ($field_name_exploded as $string_index => $string) {
          if ($string_index > 0) {
            $field_name_exploded[$string_index] = ucfirst($string);
          }
        }

        $field_name_transformed = implode($field_name_exploded);

        if ($field_type == 'textarea') {
          if (!is_null($user)) {
            $field_value = (!is_null($user->get_additional_field_data($field_name_transformed))) ? $user->get_additional_field_data($field_name_transformed) : '';
          }

          array_push($additional_fields_elements, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/user/form/fieldTextarea.tpl', [
            'FIELD_NAME' => $fields_names[$field_index],
            'FIELD_DESCRIPTION' => $fields_descriptions[$cms_locale_setted][$field_index],
            'FIELD_TITLE' => $fields_titles[$cms_locale_setted][$field_index],
            'FIELD_VALUE' => (isset($field_value)) ? $field_value : ''
          ]));
        } else {
          if (!is_null($user)) {
            $field_value = (!is_null($user->get_additional_field_data($field_name_transformed))) ? $user->get_additional_field_data($field_name_transformed) : '';
          }

          array_push($additional_fields_elements, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/user/form/fieldInput.tpl', [
            'FIELD_NAME' => $fields_names[$field_index],
            'FIELD_DESCRIPTION' => $fields_descriptions[$cms_locale_setted][$field_index],
            'FIELD_TYPE' => $fields_types[$field_index],
            'FIELD_TITLE' => $fields_titles[$cms_locale_setted][$field_index],
            'FIELD_VALUE' => (isset($field_value)) ? $field_value : ''
          ]));
        }
      }

      /** @var string Содержимое шаблона страницы */
      $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/user.tpl', [
        'ADMIN_PANEL_PAGE_NAME' => 'user',
        'USER_ID' => (!is_null($user)) ? $user->get_id() : 0,
        'USER_LOGIN' => (!is_null($user)) ? $user->get_login() : '',
        'USER_EMAIL' => (!is_null($user)) ? $user->get_email() : '',
        'USER_NAME' => (!is_null($user)) ? $user->get_name() : '',
        'USER_SURNAME' => (!is_null($user)) ? $user->get_surname() : '',
        'USER_PATRONYMIC' => (!is_null($user)) ? $user->get_patronymic() : '',
        'USER_BIRTHDATE' => (!is_null($user)) ? date('Y-m-d', $user->get_birthdate_unix_timestamp()) : 0,
        'USER_BIRTHDATE_MINIMUM' => date('Y-m-d', time() - 3155760000),
        'USER_BIRTHDATE_MAXIMUM' => date('Y-m-d', time() - 441763200),
        'USER_ADDITIONAL_FIELDS' => implode($additional_fields_elements),
        'USER_FORM_METHOD' => (!is_null($user)) ? 'PATCH' : 'PUT'
      ]);
    }

  }

}

?>