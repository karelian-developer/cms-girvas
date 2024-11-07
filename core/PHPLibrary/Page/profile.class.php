<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Page {
  use \core\PHPLibrary\InterfacePage as InterfacePage;
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \core\PHPLibrary\Page as Page;
  use \core\PHPLibrary\Parsedown as Parsedown;
  use \core\PHPLibrary\User as User;
  use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;

  class PageProfile implements InterfacePage {
    public SystemCore $system_core;
    public Page $page;
    public string $assembled = '';
    
    /**
     * __construct
     *
     * @param  SystemCore $system_core
     * @param  Page $page
     * @return void
     */
    public function __construct(SystemCore $system_core, Page $page) {
      $this->system_core = $system_core;
      $this->page = $page;
    }

    /**
     * Получение массива дополнительных полей
     * 
     * @return array
     */
    private function get_additional_fields() : array {
      
    }
    
    /**
     * Сборка шаблона страницы
     *
     * @return void
     */
    public function assembly() : void {
      $this->system_core->template->add_style(['href' => 'styles/page.css', 'rel' => 'stylesheet']);
      $this->system_core->template->add_style(['href' => 'styles/page/profile.css', 'rel' => 'stylesheet']);
      
      $cms_base_locale_setted_name = $this->system_core->configurator->get_database_entry_value('base_locale');
      $url_base_locale_setted_name = $this->system_core->urlp->get_param('locale');
      $cookie_base_locale_setted_name = (isset($_COOKIE['locale'])) ? $_COOKIE['locale'] : null;
      
      $cms_base_locale_name = (!is_null($url_base_locale_setted_name)) ? $url_base_locale_setted_name : $cookie_base_locale_setted_name;
      $cms_base_locale_name = (!is_null($cms_base_locale_name)) ? $cms_base_locale_name : $cms_base_locale_setted_name;
      $cms_base_locale = new SystemCoreLocale($this->system_core, $cms_base_locale_name);
      if (!$cms_base_locale->exists_file_data_json()) {
        $cms_base_locale = new SystemCoreLocale($this->system_core, $cms_base_locale_setted_name);
        $cms_base_locale_name = $cms_base_locale_setted_name;
      }

      $this->system_core->locale = $cms_base_locale;
      $locale_data = $this->system_core->locale->get_data();

      if ($this->system_core->client->is_logged(1)) {
        $user = $this->system_core->client->get_user(1);
        $user->init_data(['login', 'metadata']);
        
        $profile_user_login = (!is_null($this->system_core->urlp->get_path(1))) ? $this->system_core->urlp->get_path(1) : $user->get_login();
        
        /**
         * @var User Объект пользователя
         */
        $profile_user = null;
        if (User::exists_by_login($this->system_core, $profile_user_login)) {
          $profile_user = User::get_by_login($this->system_core, $profile_user_login);
          // Инициализация данных пользователя
          $profile_user->init_data(['login', 'email', 'metadata']);
        }
        
        if (!is_null($profile_user)) {
          $user_group = $user->get_group();
          $user_group->init_data(['permissions']);

          $fields_types = ($this->system_core->configurator->exists_database_entry_value('users_additional_field_type')) ? json_decode($this->system_core->configurator->get_database_entry_value('users_additional_field_type'), true) : [];
          $fields_titles = ($this->system_core->configurator->exists_database_entry_value('users_additional_field_title')) ? json_decode($this->system_core->configurator->get_database_entry_value('users_additional_field_title'), true) : [];
          $fields_names = ($this->system_core->configurator->exists_database_entry_value('users_additional_field_name')) ? json_decode($this->system_core->configurator->get_database_entry_value('users_additional_field_name'), true) : [];

          $additional_fields_elements = [];

          if ($this->system_core->urlp->get_param('event') == 'edit') {
            if ($user_group->permission_check($user_group::PERMISSION_ADMIN_USERS_MANAGEMENT) || $user->get_id() == $profile_user->get_id()) {
              foreach ($fields_types as $field_index => $field_type) {
                $field_name_exploded = explode('_', $fields_names[$field_index]);
                foreach ($field_name_exploded as $string_index => $string) {
                  if ($string_index > 0) {
                    $field_name_exploded[$string_index] = ucfirst($string);
                  }
                }
                $field_name_transformed = implode($field_name_exploded);

                if ($field_type == 'textarea') {
                  array_push($additional_fields_elements, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/profile/editor/fieldTextarea.tpl', [
                    'FIELD_NAME' => $fields_names[$field_index],
                    'FIELD_TITLE' => $fields_titles[$cms_base_locale_name][$field_index],
                    'FIELD_VALUE' => (!is_null($profile_user->get_additional_field_data($field_name_transformed))) ? strip_tags($profile_user->get_additional_field_data($field_name_transformed)) : ''
                  ]));
                } else {
                  array_push($additional_fields_elements, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/profile/editor/fieldInput.tpl', [
                    'FIELD_NAME' => $fields_names[$field_index],
                    'FIELD_TYPE' => $fields_types[$field_index],
                    'FIELD_TITLE' => $fields_titles[$cms_base_locale_name][$field_index],
                    'FIELD_VALUE' => (!is_null($profile_user->get_additional_field_data($field_name_transformed))) ? strip_tags($profile_user->get_additional_field_data($field_name_transformed)) : ''
                  ]));
                }
              }

              $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page.tpl', [
                'PAGE_NAME' => 'profile-editor',
                'PAGE_CONTENT' => TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/profile/editor.tpl', [
                  'USER_ID' => $profile_user->get_id(),
                  'USER_LOGIN' => $profile_user->get_login(),
                  'USER_AVATAR_URL' => $profile_user->get_avatar_url(128),
                  'USER_EMAIL' => $profile_user->get_email(),
                  'USER_NAME' => $profile_user->get_name(),
                  'USER_SURNAME' => $profile_user->get_surname(),
                  'USER_PATRONYMIC' => $profile_user->get_patronymic(),
                  'USER_BIRTHDATE' => date('Y-m-d', $profile_user->get_birthdate_unix_timestamp()),
                  'PROFILE_ADDITIONAL_FIELDS' => implode($additional_fields_elements)
                ])
              ]);
            } else {
              http_response_code(404);

              $page_error = new PageError($this->system_core, $this->page, 404);
              $page_error->assembly();
              $this->assembled = $page_error->assembled;
            }
          } else {
            foreach ($fields_types as $field_index => $field_type) {
              $field_name_exploded = explode('_', $fields_names[$field_index]);
              
              foreach ($field_name_exploded as $string_index => $string) {
                if ($string_index > 0) {
                  $field_name_exploded[$string_index] = ucfirst($string);
                }
              }
              $field_name_transformed = implode($field_name_exploded);

              array_push($additional_fields_elements, TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/profile/additionalField.tpl', [
                'FIELD_TITLE' => $fields_titles[$cms_base_locale_name][$field_index],
                'FIELD_VALUE' => (!is_null($profile_user->get_additional_field_data($field_name_transformed))) ? strip_tags($profile_user->get_additional_field_data($field_name_transformed)) : ''
              ]));
            }

            $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page.tpl', [
              'PAGE_NAME' => 'profile',
              'PAGE_CONTENT' => TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/profile.tpl', [
                'USER_ID' => $profile_user->get_id(),
                'USER_LOGIN' => $profile_user->get_login(),
                'USER_AVATAR_URL' => $profile_user->get_avatar_url(128),
                'USER_EMAIL' => $profile_user->get_email(),
                'USER_NAME' => $profile_user->get_name(),
                'USER_SURNAME' => $profile_user->get_surname(),
                'USER_PATRONYMIC' => $profile_user->get_patronymic(),
                'USER_BIRTHDATE' => date('d.m.Y', $profile_user->get_birthdate_unix_timestamp()),
                'USER_BIRTHDATE_MINIMUM' => date('Y-m-d', time() - 3155760000),
                'USER_BIRTHDATE_MAXIMUM' => date('Y-m-d', time() - 441763200),
                'PROFILE_ADDITIONAL_FIELDS' => implode($additional_fields_elements)
              ])
            ]);
          }
        } else {
          http_response_code(404);

          $page_error = new PageError($this->system_core, $this->page, 404);
          $page_error->assembly();
          $this->assembled = $page_error->assembled;
        }
      } else {
        http_response_code(503);

        $page_error = new PageError($this->system_core, $this->page, 503);
        $page_error->assembly();
        $this->assembled = $page_error->assembled;
      }
    }

  }

}

?>