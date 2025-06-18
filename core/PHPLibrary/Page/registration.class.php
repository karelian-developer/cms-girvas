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
  use \core\PHPLibrary\Template\Collector as TemplateCollector;

  class PageRegistration implements InterfacePage {
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
     * Сборка шаблона страницы
     *
     * @return void
     */
    public function assembly() : void {
      $this->CMSCore->theme->add_style(['href' => 'styles/page.css', 'rel' => 'stylesheet']);
      $this->CMSCore->theme->add_style(['href' => 'styles/page/registration.css', 'rel' => 'stylesheet']);
      
      $localeData = $this->CMSCore->locale->get_data();

      if (is_null($this->CMSCore->urlp->get_param('submit')) && is_null($this->CMSCore->urlp->get_param('refusal'))) {
        if (!$this->CMSCore->client->is_logged(1)) {
          $this->assembled = TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page.tpl', [
            'PAGE_NAME' => 'registration',
            'PAGE_CONTENT' => TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/registration.tpl', [])
          ]);
        } else {
          http_response_code(503);

          $pageError = new PageError($this->CMSCore, $this->page, 503);
          $pageError->assembly();
          $this->assembled = $pageError->assembled;
        }
      } else {
        if (!is_null($this->CMSCore->urlp->get_param('submit')) && is_null($this->CMSCore->urlp->get_param('refusal'))) {
          if (User::exists_by_registration_submit_token($this->CMSCore, $this->CMSCore->urlp->get_param('submit'))) {
            $userID = User::get_user_id_by_registration_submit_token($this->CMSCore, $this->CMSCore->urlp->get_param('submit'));
            $user = new User($this->CMSCore, $userID);
            $user_data['email_is_submitted'] = true;
            $userDataIsUpdated = $user->update($user_data);
            if ($userDataIsUpdated) {
              User::delete_registration_submit_by_submit_token($this->CMSCore, $this->CMSCore->urlp->get_param('submit'));

              $this->assembled = TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page.tpl', [
                'PAGE_NAME' => 'registration',
                'PAGE_CONTENT' => TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/registrationSubmit.tpl', [
                  'REGISTRATION_SUBMIT_TITLE' => $localeData['PAGE_REGISTRATION_CONFIRMATION_TITLE'],
                  'REGISTRATION_SUBMIT_TEXT' => $localeData['PAGE_REGISTRATION_CONFIRMATION_SUCCESS_DESCRIPTION']
                ])
              ]);
            } else {
              $this->assembled = TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page.tpl', [
                'PAGE_NAME' => 'registration',
                'PAGE_CONTENT' => TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/registrationSubmit.tpl', [
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
        } else if (is_null($this->CMSCore->urlp->get_param('submit')) && !is_null($this->CMSCore->urlp->get_param('refusal'))) {
          if (User::exists_by_registration_refusal_token($this->CMSCore, $this->CMSCore->urlp->get_param('refusal'))) {
            $userID = User::get_user_id_by_registration_refusal_token($this->CMSCore, $this->CMSCore->urlp->get_param('refusal'));
            $user = new User($this->CMSCore, $userID);
            $userIsDeleted = $user->delete();

            if ($userIsDeleted) {
              User::delete_registration_submit_by_refusal_token($this->CMSCore, $this->CMSCore->urlp->get_param('refusal'));

              $this->assembled = TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page.tpl', [
                'PAGE_NAME' => 'registration',
                'PAGE_CONTENT' => TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/registrationSubmit.tpl', [
                  'REGISTRATION_SUBMIT_TITLE' => $localeData['PAGE_REGISTRATION_CANCELLATION_TITLE'],
                  'REGISTRATION_SUBMIT_TEXT' => $localeData['PAGE_REGISTRATION_CANCELLATION_SUCCESS_DESCRIPTION']
                ])
              ]);
            } else {
              $this->assembled = TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page.tpl', [
                'PAGE_NAME' => 'registration',
                'PAGE_CONTENT' => TemplateCollector::assembly_file_content($this->CMSCore->theme, 'templates/page/registrationSubmit.tpl', [
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

}

?>