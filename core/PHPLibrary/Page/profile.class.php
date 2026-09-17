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

namespace core\PHPLibrary\Page;

use \core\PHPLibrary\InterfacePage as InterfacePage;
use \core\PHPLibrary\SystemCore as SystemCore;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\PageStatic as PageStatic;
use \core\PHPLibrary\Parsedown as Parsedown;
use \core\PHPLibrary\User as User;
use \core\PHPLibrary\User\Consent as UserConsent;
use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\SystemCore\Report as Report;

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
   * Добавление обязательных CSS-файлов
   * 
   * @return void
   */
  private function addRequiredStyles() : void
  {
    foreach (['page.css', 'page/profile.css'] as $stylePath) {
      $this->CMSCore->theme->addStyle(
        [
          'href' => 'styles/' . $stylePath,
          'rel' => 'stylesheet'
        ]
      );
    }
  }

  /**
   * Собрать HTML-блок «Мои согласия» для владельца профиля
   *
   * @param User $profileUser
   * @return string
   */
  private function buildConsentsBlock(User $profileUser) : string
  {
    $localeName = $this->CMSCore->locale->getName();
    $localeData = $this->CMSCore->locale->getData();

    $activeConsents = UserConsent::getActiveByUser($this->CMSCore, $profileUser->getID());

    if (empty($activeConsents)) {
      $itemsHTML = '<tr class="table__row"><td class="table__cell" colspan="2">'
        . htmlspecialchars($localeData['PROFILE_CONSENTS_EMPTY'] ?? 'У вас нет активных согласий.')
        . '</td></tr>';
    } else {
      $items = [];

      foreach ($activeConsents as $consent) {
        $consent->initData();

        // Заголовок документа из PageStatic
        $documentTitle = '';
        if ($consent->getPageStaticID() > 0) {
          $pageStatic = new PageStatic($this->CMSCore, $consent->getPageStaticID());
          if ($pageStatic !== null) {
            $pageStatic->initData(['name', 'texts']);
            $documentTitle = $pageStatic->getTitle($localeName);
            if (empty($documentTitle)) {
              $documentTitle = $pageStatic->getName();
            }
          }
        }

        if (empty($documentTitle)) {
          $documentTitle = '—';
        }

        // Источник
        $source = $consent->getSource();
        $sourceLabel = match ($source) {
          'form' => $localeData['PROFILE_CONSENTS_SOURCE_FORM'] ?? 'Форма',
          'registration' => $localeData['PROFILE_CONSENTS_SOURCE_REGISTRATION'] ?? 'Регистрация',
          default => $source
        };

        $items[] = ThemeCollector::assemblyFileContent(
          $this->CMSCore->theme,
          'templates/page/profile/consentItem.tpl',
          [
            'CONSENT_ID' => $consent->getID(),
            'CONSENT_DOCUMENT_TITLE' => htmlspecialchars($documentTitle),
            'CONSENT_VERSION' => htmlspecialchars($consent->getDocumentVersion()),
            'CONSENT_DATE' => date('d.m.Y H:i', $consent->getConsentedAt()),
            'CONSENT_SOURCE_LABEL' => htmlspecialchars($sourceLabel),
            'CONSENT_LOCALE' => htmlspecialchars($consent->getLocale())
          ]
        );
      }

      $itemsHTML = implode("\n", $items);
    }

    return ThemeCollector::assemblyFileContent(
      $this->CMSCore->theme,
      'templates/page/profile/consentsBlock.tpl',
      [
        'PROFILE_CONSENTS_BLOCK_TITLE' => $localeData['PROFILE_CONSENTS_BLOCK_TITLE'] ?? 'Мои согласия',
        'PROFILE_CONSENTS_BLOCK_DESCRIPTION' => $localeData['PROFILE_CONSENTS_BLOCK_DESCRIPTION'] ?? '',
        'PROFILE_CONSENTS_ITEMS' => $itemsHTML
      ]
    );
  }
  
  /**
   * Сборка шаблона страницы
   *
   * @return void
   */
  public function assembly() : void
  {
    $this->addRequiredStyles();
    
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
        $CMSConfigurator = $this->CMSCore->configurator;

        $userGroup = $user->getGroup();
        $userGroup->initData(['permissions']);

        // ============================================================
        // ЛОГИРОВАНИЕ ПРОСМОТРА ПРОФИЛЯ (если смотрит не сам себя)
        // ============================================================
        if ($this->CMSCore->urlp->getParam('event') !== 'edit' && $user->getID() !== $profileUser->getID()) {
          Report::create(
            $this->CMSCore,
            Report::REPORT_TYPE_ID_BASE_USER_PERSONAL_DATA_VIEWED,
            [
              'targetUserID' => $profileUser->getID(),
              'viewedByID' => $user->getID(),
              'ip' => $this->CMSCore->client->getIPAddress()
            ]
          );
        }

        $fieldsTypes = $CMSConfigurator->existsDatabaseEntryValue('users_additional_field_type')
          ? json_decode($this->CMSCore->configurator->getDatabaseEntryValue('users_additional_field_type'), true)
          : [];
        $fieldsTitles = $CMSConfigurator->existsDatabaseEntryValue('users_additional_field_title')
          ? json_decode($this->CMSCore->configurator->getDatabaseEntryValue('users_additional_field_title'), true)
          : [];
        $fieldsDescriptions = $CMSConfigurator->existsDatabaseEntryValue('users_additional_field_description')
          ? json_decode($this->CMSCore->configurator->getDatabaseEntryValue('users_additional_field_description'), true)
          : [];
        $fieldsNames = $CMSConfigurator->existsDatabaseEntryValue('users_additional_field_name')
          ? json_decode($this->CMSCore->configurator->getDatabaseEntryValue('users_additional_field_name'), true)
          : [];

        $additionalFieldsElements = [];

        if ($this->CMSCore->urlp->getParam('event') === 'edit') {
          
          if ($userGroup->permissionCheck($userGroup::PERMISSION_ADMIN_USERS_MANAGEMENT) || $user->getID() === $profileUser->getID()) {
            
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

                $additionalFieldsElements[] = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/profile/editor/fieldInput.tpl', [
                  'FIELD_NAME' => $fieldName,
                  'FIELD_TYPE' => $fieldType === 'textarea' ? '' : $fieldType,
                  'FIELD_TITLE' => $fieldTitle,
                  'FIELD_DESCRIPTION' => $fieldDescription,
                  'FIELD_VALUE' => $fieldValue
                ]);
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
                'PROFILE_ADDITIONAL_FIELDS' => implode($additionalFieldsElements),
                'USERS_PASSWORD_LENGTH_MAX' => $CMSConfigurator->getUsersPasswordLengthMax(),
                'USERS_PASSWORD_LENGTH_MIN' => $CMSConfigurator->getUsersPasswordLengthMin(),
                'USERS_LOGIM_LENGTH_MAX' => $CMSConfigurator->getUsersPasswordLengthMax(),
                'USERS_LOGIM_LENGTH_MIN' => $CMSConfigurator->getUsersPasswordLengthMIN()
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

            $additionalFieldsElements[] = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/profile/additionalField.tpl', [
              'FIELD_TITLE' => $fieldsTitles[$localeName][$fieldIndex],
              'FIELD_VALUE' => $profileUser->getAdditionalFieldData($fieldNameTransformed) !== null
                ? strip_tags($profileUser->getAdditionalFieldData($fieldNameTransformed))
                : ''
            ]);
          }

          $consentsBlock = '';
          if ($user->getID() === $profileUser->getID()) {
            $consentsBlock = $this->buildConsentsBlock($profileUser);
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
              'PROFILE_ADDITIONAL_FIELDS' => implode($additionalFieldsElements),
              'PROFILE_CONSENTS_BLOCK' => $consentsBlock
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