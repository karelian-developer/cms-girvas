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
use \core\PHPLibrary\Parsedown as Parsedown;
use \core\PHPLibrary\User as User;
use \core\PHPLibrary\Template\Collector as ThemeCollector;

class PageRegistration implements InterfacePage
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
    foreach (['page.css', 'page/registration.css'] as $stylePath) {
      $this->CMSCore->theme->addStyle(
        [
          'href' => 'styles/' . $stylePath,
          'rel' => 'stylesheet'
        ]
      );
    }
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

    if ($this->CMSCore->urlp->getParam('submit') === null && $this->CMSCore->urlp->getParam('refusal') === null) {
      if (!$this->CMSCore->client->isLogged(1)) {
        $CMSConfigurator = $this->CMSCore->configurator;

        $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page.tpl', [
          'PAGE_NAME' => 'registration',
          'PAGE_CONTENT' => ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/registration.tpl', [
            'USERS_PASSWORD_LENGTH_MAX' => $CMSConfigurator->getUsersPasswordLengthMax(),
            'USERS_PASSWORD_LENGTH_MIN' => $CMSConfigurator->getUsersPasswordLengthMin(),
            'USERS_LOGIM_LENGTH_MAX' => $CMSConfigurator->getUsersPasswordLengthMax(),
            'USERS_LOGIM_LENGTH_MIN' => $CMSConfigurator->getUsersPasswordLengthMIN()
          ])
        ]);
      } else {
        http_response_code(503);

        $pageError = new PageError($this->CMSCore, $this->page, 503);
        $pageError->assembly();
        $this->assembled = $pageError->assembled;
      }
    } else {
      if ($this->CMSCore->urlp->getParam('submit') !== null && $this->CMSCore->urlp->getParam('refusal') === null) {
        if (User::existsByRegistrationSubmitToken($this->CMSCore, $this->CMSCore->urlp->getParam('submit'))) {
          $userID = User::getUserIDByRegistrationSubmitToken($this->CMSCore, $this->CMSCore->urlp->getParam('submit'));
          $user = new User($this->CMSCore, $userID);
          $userData['emailIsSubmitted'] = true;
          $userDataIsUpdated = $user->update($userData);

          if ($userDataIsUpdated) {
            User::deleteRegistrationSubmitBySubmitToken($this->CMSCore, $this->CMSCore->urlp->getParam('submit'));

            $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page.tpl', [
              'PAGE_NAME' => 'registration',
              'PAGE_CONTENT' => ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/registrationSubmit.tpl', [
                'REGISTRATION_SUBMIT_TITLE' => $localeData['PAGE_REGISTRATION_CONFIRMATION_TITLE'],
                'REGISTRATION_SUBMIT_TEXT' => $localeData['PAGE_REGISTRATION_CONFIRMATION_SUCCESS_DESCRIPTION']
              ])
            ]);
          } else {
            $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page.tpl', [
              'PAGE_NAME' => 'registration',
              'PAGE_CONTENT' => ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/registrationSubmit.tpl', [
                'REGISTRATION_SUBMIT_TITLE' => $localeData['PAGE_REGISTRATION_CONFIRMATION_TITLE'],
                'REGISTRATION_SUBMIT_TEXT' => $localeData['PAGE_REGISTRATION_CONFIRMATION_FAIL_DESCRIPTION']
              ])
            ]);
          }
        } else {
          http_response_code(500);

          $pageError = new PageError($this->CMSCore, $this->page, 500);
          $pageError->assembly();
          $this->assembled = $pageError->assembled;
        }
      } else if ($this->CMSCore->urlp->getParam('submit') === null && $this->CMSCore->urlp->getParam('refusal') !== null) {
        if (User::existsByRegistrationRefusalToken($this->CMSCore, $this->CMSCore->urlp->getParam('refusal'))) {
          $userID = User::getUserIDByRegistrationRefusalToken($this->CMSCore, $this->CMSCore->urlp->getParam('refusal'));
          $user = new User($this->CMSCore, $userID);
          $userIsDeleted = $user->delete();

          if ($userIsDeleted) {
            User::deleteRegistrationSubmitByRefusalToken($this->CMSCore, $this->CMSCore->urlp->getParam('refusal'));

            $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page.tpl', [
              'PAGE_NAME' => 'registration',
              'PAGE_CONTENT' => ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/registrationSubmit.tpl', [
                'REGISTRATION_SUBMIT_TITLE' => $localeData['PAGE_REGISTRATION_CANCELLATION_TITLE'],
                'REGISTRATION_SUBMIT_TEXT' => $localeData['PAGE_REGISTRATION_CANCELLATION_SUCCESS_DESCRIPTION']
              ])
            ]);
          } else {
            $this->assembled = ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page.tpl', [
              'PAGE_NAME' => 'registration',
              'PAGE_CONTENT' => ThemeCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/registrationSubmit.tpl', [
                'REGISTRATION_SUBMIT_TITLE' => $localeData['PAGE_REGISTRATION_CANCELLATION_TITLE'],
                'REGISTRATION_SUBMIT_TEXT' => $localeData['PAGE_REGISTRATION_CANCELLATION_FAIL_DESCRIPTION']
              ])
            ]);
          }
        } else {
          http_response_code(500);

          $pageError = new PageError($this->CMSCore, $this->page, 500);
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