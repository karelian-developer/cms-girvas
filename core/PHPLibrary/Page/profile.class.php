<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
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
    public SystemCore $CMSCore;
    public Page $page;
    public string $assembled = '';
    
    /**
     * __construct
     *
     * @param  SystemCore $CMSCore
     * @param  Page $page
     * @return void
     */
    public function __construct(SystemCore $CMSCore, Page $page) {
      $this->CMSCore = $CMSCore;
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
      $this->CMSCore->theme->add_style(['href' => 'styles/page.css', 'rel' => 'stylesheet']);
      $this->CMSCore->theme->add_style(['href' => 'styles/page/profile.css', 'rel' => 'stylesheet']);
      
      $localeData = $this->CMSCore->locale->get_data();
      $localeName = $this->CMSCore->locale->get_name();

      if ($this->CMSCore->client->is_logged(1)) {
        $user = $this->CMSCore->client->get_user(1);
        $user->init_data(['login', 'metadata']);
        
        $profileUserLogin = (!is_null($this->CMSCore->urlp->get_path(1))) ? $this->CMSCore->urlp->get_path(1) : $user->get_login();
        
        /**
         * @var User Объект пользователя
         */
        $profileUser = null;
        if (User::exists_by_login($this->CMSCore, $profileUserLogin)) {
          $profileUser = User::get_by_login($this->CMSCore, $profileUserLogin);
          // Инициализация данных пользователя
          $profileUser->init_data(['login', 'email', 'metadata']);
        }
        
        if (!is_null($profileUser)) {
          $userGroup = $user->get_group();
          $userGroup->init_data(['permissions']);

          $fieldsTypes = $this->CMSCore->configurator->exists_database_entry_value('users_additional_field_type') ? json_decode($this->CMSCore->configurator->get_database_entry_value('users_additional_field_type'), true) : [];
          $fieldsTitles = $this->CMSCore->configurator->exists_database_entry_value('users_additional_field_title') ? json_decode($this->CMSCore->configurator->get_database_entry_value('users_additional_field_title'), true) : [];
          $fieldsDescriptions = $this->CMSCore->configurator->exists_database_entry_value('users_additional_field_description') ? json_decode($this->CMSCore->configurator->get_database_entry_value('users_additional_field_description'), true) : [];
          $fieldsNames = $this->CMSCore->configurator->exists_database_entry_value('users_additional_field_name') ? json_decode($this->CMSCore->configurator->get_database_entry_value('users_additional_field_name'), true) : [];

          $additionalFieldsElements = [];

          if ($this->CMSCore->urlp->get_param('event') == 'edit') {
            if ($userGroup->permission_check($userGroup::PERMISSION_ADMIN_USERS_MANAGEMENT) || $user->get_id() == $profileUser->get_id()) {
              foreach ($fieldsTypes as $fieldIndex => $fieldType) {
                $fieldNameExploded = (isset($fieldsNames[$fieldIndex])) ? explode('_', $fieldsNames[$fieldIndex]) : [];
                $fieldNameTransformed = implode($fieldNameExploded);

                $fieldName = (isset($fieldsNames[$fieldIndex])) ? $fieldsNames[$fieldIndex] : '';
                $fieldTitle = (isset($fieldsTitles[$localeName][$fieldIndex])) ? $fieldsTitles[$localeName][$fieldIndex] : '';
                $fieldDescription = (isset($fieldsDescriptions[$localeName][$fieldIndex])) ? $fieldsDescriptions[$localeName][$fieldIndex] : '';
                $fieldValue = (!is_null($profileUser->get_additional_field_data($fieldNameTransformed))) ? strip_tags($profileUser->get_additional_field_data($fieldNameTransformed)) : '';
                $fieldType = (isset($fieldsTypes[$fieldIndex])) ? $fieldsTypes[$fieldIndex] : 'text';

                if ($fieldTitle != '') {
                  foreach ($fieldNameExploded as $stringIndex => $string) {
                    if ($stringIndex > 0) {
                      $fieldNameExploded[$stringIndex] = ucfirst($string);
                    }
                  }

                  array_push($additionalFieldsElements, TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/profile/editor/fieldInput.tpl', [
                    'FIELD_NAME' => $fieldName,
                    'FIELD_TYPE' => $fieldType === 'textarea' ? '' : $fieldType,
                    'FIELD_TITLE' => $fieldTitle,
                    'FIELD_DESCRIPTION' => $fieldDescription,
                    'FIELD_VALUE' => $fieldValue
                  ]));
                }
              }

              $this->assembled = TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page.tpl', [
                'PAGE_NAME' => 'profile-editor',
                'PAGE_CONTENT' => TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/profile/editor.tpl', [
                  'USER_ID' => $profileUser->get_id(),
                  'USER_LOGIN' => $profileUser->get_login(),
                  'USER_AVATAR_URL' => $profileUser->get_avatar_url(128),
                  'USER_EMAIL' => $profileUser->get_email(),
                  'USER_NAME' => $profileUser->get_name(),
                  'USER_SURNAME' => $profileUser->get_surname(),
                  'USER_PATRONYMIC' => $profileUser->get_patronymic(),
                  'USER_BIRTHDATE' => date('Y-m-d', $profileUser->get_birthdate_unix_timestamp()),
                  'PROFILE_ADDITIONAL_FIELDS' => implode($additionalFieldsElements)
                ])
              ]);
            } else {
              http_response_code(404);

              $pageError = new PageError($this->CMSCore, $this->page, 404);
              $pageError->assembly();
              $this->assembled = $pageError->assembled;
            }
          } else {
            foreach ($fieldsTypes as $fieldIndex => $fieldType) {
              $fieldNameExploded = explode('_', $fieldsNames[$fieldIndex]);
              
              foreach ($fieldNameExploded as $stringIndex => $string) {
                if ($stringIndex > 0) {
                  $fieldNameExploded[$stringIndex] = ucfirst($string);
                }
              }
              $fieldNameTransformed = implode($fieldNameExploded);

              array_push($additionalFieldsElements, TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/profile/additionalField.tpl', [
                'FIELD_TITLE' => $fieldsTitles[$localeName][$fieldIndex],
                'FIELD_VALUE' => (!is_null($profileUser->get_additional_field_data($fieldNameTransformed))) ? strip_tags($profileUser->get_additional_field_data($fieldNameTransformed)) : ''
              ]));
            }

            $this->assembled = TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page.tpl', [
              'PAGE_NAME' => 'profile',
              'PAGE_CONTENT' => TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/profile.tpl', [
                'USER_ID' => $profileUser->get_id(),
                'USER_LOGIN' => $profileUser->get_login(),
                'USER_AVATAR_URL' => $profileUser->get_avatar_url(128),
                'USER_EMAIL' => $profileUser->get_email(),
                'USER_NAME' => $profileUser->get_name(),
                'USER_SURNAME' => $profileUser->get_surname(),
                'USER_PATRONYMIC' => $profileUser->get_patronymic(),
                'USER_BIRTHDATE' => date('d.m.Y', $profileUser->get_birthdate_unix_timestamp()),
                'USER_BIRTHDATE_MINIMUM' => date('Y-m-d', time() - 3155760000),
                'USER_BIRTHDATE_MAXIMUM' => date('Y-m-d', time() - 441763200),
                'PROFILE_ADDITIONAL_FIELDS' => implode($additionalFieldsElements)
              ])
            ]);
          }
        } else {
          http_response_code(404);

          $pageError = new PageError($this->CMSCore, $this->page, 404);
          $pageError->assembly();
          $this->assembled = $pageError->assembled;
        }
      } else {
        http_response_code(503);

        $pageError = new PageError($this->CMSCore, $this->page, 503);
        $pageError->assembly();
        $this->assembled = $pageError->assembled;
      }
    }

  }

}

?>