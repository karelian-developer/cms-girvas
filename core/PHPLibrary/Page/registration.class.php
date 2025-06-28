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
use \core\PHPLibrary\Template\Collector as TemplateCollector;

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
   * Сборка шаблона страницы
   *
   * @return void
   */
  public function assembly() : void
  {
    $this->CMSCore->theme->addStyle(['href' => 'styles/page.css', 'rel' => 'stylesheet']);
    $this->CMSCore->theme->addStyle(['href' => 'styles/page/registration.css', 'rel' => 'stylesheet']);
    
    $localeData = $this->CMSCore->locale->getData();

    if ($this->CMSCore->urlp->getParam('submit') === null && $this->CMSCore->urlp->getParam('refusal') === null) {
      if (!$this->CMSCore->client->isLogged(1)) {
        $this->assembled = TemplateCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page.tpl', [
          'PAGE_NAME' => 'registration',
          'PAGE_CONTENT' => TemplateCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/registration.tpl', [])
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

            $this->assembled = TemplateCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page.tpl', [
              'PAGE_NAME' => 'registration',
              'PAGE_CONTENT' => TemplateCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/registrationSubmit.tpl', [
                'REGISTRATION_SUBMIT_TITLE' => $localeData['PAGE_REGISTRATION_CONFIRMATION_TITLE'],
                'REGISTRATION_SUBMIT_TEXT' => $localeData['PAGE_REGISTRATION_CONFIRMATION_SUCCESS_DESCRIPTION']
              ])
            ]);
          } else {
            $this->assembled = TemplateCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page.tpl', [
              'PAGE_NAME' => 'registration',
              'PAGE_CONTENT' => TemplateCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/registrationSubmit.tpl', [
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

            $this->assembled = TemplateCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page.tpl', [
              'PAGE_NAME' => 'registration',
              'PAGE_CONTENT' => TemplateCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/registrationSubmit.tpl', [
                'REGISTRATION_SUBMIT_TITLE' => $localeData['PAGE_REGISTRATION_CANCELLATION_TITLE'],
                'REGISTRATION_SUBMIT_TEXT' => $localeData['PAGE_REGISTRATION_CANCELLATION_SUCCESS_DESCRIPTION']
              ])
            ]);
          } else {
            $this->assembled = TemplateCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page.tpl', [
              'PAGE_NAME' => 'registration',
              'PAGE_CONTENT' => TemplateCollector::assemblyFileContent($this->CMSCore->theme, 'templates/page/registrationSubmit.tpl', [
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