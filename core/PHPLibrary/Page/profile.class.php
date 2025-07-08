<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Page;

use \core\PHPLibrary\InterfacePage as InterfacePage;
use \core\PHPLibrary\SystemCore as SystemCore;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\Parsedown as Parsedown;
use \core\PHPLibrary\User as User;
use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
use \core\PHPLibrary\Template\Collector as ThemeCollector;

class PageProfile implements InterfacePage
{
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
  public function __construct(SystemCore $CMSCore, Page $page)
  {
    $this->CMSCore = $CMSCore;
    $this->page = $page;
  }

  /**
   * Получение массива дополнительных полей
   * 
   * @return array
   */
  private function getAdditionalFields() : array
  {
    // ...
  }
  
  /**
   * Сборка шаблона страницы
   *
   * @return void
   */
  public function assembly() : void
  {
    $this->CMSCore->theme->addStyle(['href' => 'styles/page.css', 'rel' => 'stylesheet']);
    $this->CMSCore->theme->addStyle(['href' => 'styles/page/profile.css', 'rel' => 'stylesheet']);
    
    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    if ($this->CMSCore->client->isLogged(1)) {
      $user = $this->CMSCore->client->getUser(1);
      $user->initData(['login', 'metadata']);
      
      $profileUserLogin = $this->CMSCore->urlp->getPath(1) ?? $user->getLogin();
      
      /**
       * @var User Объект пользователя
       */
      $profileUser = null;
      if (User::existsByLogin($this->CMSCore, $profileUserLogin)) {
        $profileUser = User::getByLogin($this->CMSCore, $profileUserLogin);
        $profileUser->initData(['login', 'email', 'metadata']);
      }
      
      if ($profileUser !== null) {
        $userGroup = $user->getGroup();
        $userGroup->initData(['permissions']);

        $fieldsTypes = $this->CMSCore->configurator->existsDatabaseEntryValue('users_additional_field_type') ? json_decode($this->CMSCore->configurator->getDatabaseEntryValue('users_additional_field_type'), true) : [];
        $fieldsTitles = $this->CMSCore->configurator->existsDatabaseEntryValue('users_additional_field_title') ? json_decode($this->CMSCore->configurator->getDatabaseEntryValue('users_additional_field_title'), true) : [];
        $fieldsDescriptions = $this->CMSCore->configurator->existsDatabaseEntryValue('users_additional_field_description') ? json_decode($this->CMSCore->configurator->getDatabaseEntryValue('users_additional_field_description'), true) : [];
        $fieldsNames = $this->CMSCore->configurator->existsDatabaseEntryValue('users_additional_field_name') ? json_decode($this->CMSCore->configurator->getDatabaseEntryValue('users_additional_field_name'), true) : [];

        $additionalFieldsElements = [];

        if ($this->CMSCore->urlp->getParam('event') === 'edit') {
          if ($userGroup->permissionCheck($userGroup::PERMISSION_ADMIN_USERS_MANAGEMENT) || $user->getID() == $profileUser->getID()) {
            foreach ($fieldsTypes as $fieldIndex => $fieldType) {
              $fieldNameExploded = isset($fieldsNames[$fieldIndex]) ? explode('_', $fieldsNames[$fieldIndex]) : [];
              $fieldNameTransformed = implode($fieldNameExploded);

              $fieldName = $fieldsNames[$fieldIndex] ?? '';
              $fieldTitle = isset($fieldsTitles[$localeName][$fieldIndex]) ? $fieldsTitles[$localeName][$fieldIndex] : '';
              $fieldDescription = isset($fieldsDescriptions[$localeName][$fieldIndex]) ? $fieldsDescriptions[$localeName][$fieldIndex] : '';
              $fieldValue = !is_null($profileUser->getAdditionalFieldData($fieldNameTransformed)) ? strip_tags($profileUser->getAdditionalFieldData($fieldNameTransformed)) : '';
              $fieldType = $fieldsTypes[$fieldIndex] ?? 'text';

              if ($fieldTitle !== '') {
                foreach ($fieldNameExploded as $stringIndex => $string) {
                  if ($stringIndex > 0) {
                    $fieldNameExploded[$stringIndex] = ucfirst($string);
                  }
                }

                array_push($additionalFieldsElements, ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/profile/editor/fieldInput.tpl', [
                  'FIELD_NAME' => $fieldName,
                  'FIELD_TYPE' => $fieldType === 'textarea' ? '' : $fieldType,
                  'FIELD_TITLE' => $fieldTitle,
                  'FIELD_DESCRIPTION' => $fieldDescription,
                  'FIELD_VALUE' => $fieldValue
                ]));
              }
            }

            $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page.tpl', [
              'PAGE_NAME' => 'profile-editor',
              'PAGE_CONTENT' => ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/profile/editor.tpl', [
                'USER_ID' => $profileUser->getID(),
                'USER_LOGIN' => $profileUser->getLogin(),
                'USER_AVATAR_URL' => $profileUser->getAvatarURL(128),
                'USER_EMAIL' => $profileUser->getEmail(),
                'USER_NAME' => $profileUser->getName(),
                'USER_SURNAME' => $profileUser->getSurname(),
                'USER_PATRONYMIC' => $profileUser->getPatronymic(),
                'USER_BIRTHDATE' => date('Y-m-d', $profileUser->getBirthdateUnixTimestamp()),
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

            array_push($additionalFieldsElements, ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/profile/additionalField.tpl', [
              'FIELD_TITLE' => $fieldsTitles[$localeName][$fieldIndex],
              'FIELD_VALUE' => $profileUser->getAdditionalFieldData($fieldNameTransformed) !== null ? strip_tags($profileUser->getAdditionalFieldData($fieldNameTransformed)) : ''
            ]));
          }

          $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page.tpl', [
            'PAGE_NAME' => 'profile',
            'PAGE_CONTENT' => ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/profile.tpl', [
              'USER_ID' => $profileUser->getID(),
              'USER_LOGIN' => $profileUser->getLogin(),
              'USER_AVATAR_URL' => $profileUser->getAvatarURL(128),
              'USER_EMAIL' => $profileUser->getEmail(),
              'USER_NAME' => $profileUser->getName(),
              'USER_SURNAME' => $profileUser->getSurname(),
              'USER_PATRONYMIC' => $profileUser->getPatronymic(),
              'USER_BIRTHDATE' => date('d.m.Y', $profileUser->getBirthdateUnixTimestamp()),
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