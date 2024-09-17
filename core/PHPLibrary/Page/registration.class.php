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
  use \core\PHPLibrary\Template\Collector as TemplateCollector;

  class PageRegistration implements InterfacePage {
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
     * Сборка шаблона страницы
     *
     * @return void
     */
    public function assembly() : void {
      $this->system_core->template->add_style(['href' => 'styles/page.css', 'rel' => 'stylesheet']);
      $this->system_core->template->add_style(['href' => 'styles/page/registration.css', 'rel' => 'stylesheet']);
      
      $locale_data = $this->system_core->locale->get_data();

      if (is_null($this->system_core->urlp->get_param('submit')) && is_null($this->system_core->urlp->get_param('refusal'))) {
        if (!$this->system_core->client->is_logged(1)) {
          $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page.tpl', [
            'PAGE_NAME' => 'registration',
            'PAGE_CONTENT' => TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/registration.tpl', [])
          ]);
        } else {
          http_response_code(503);

          $page_error = new PageError($this->system_core, $this->page, 503);
          $page_error->assembly();
          $this->assembled = $page_error->assembled;
        }
      } else {
        if (!is_null($this->system_core->urlp->get_param('submit')) && is_null($this->system_core->urlp->get_param('refusal'))) {
          if (User::exists_by_registration_submit_token($this->system_core, $this->system_core->urlp->get_param('submit'))) {
            $user_id = User::get_user_id_by_registration_submit_token($this->system_core, $this->system_core->urlp->get_param('submit'));
            $user = new User($this->system_core, $user_id);
            $user_data['email_is_submitted'] = true;
            $user_data_updated = $user->update($user_data);
            if ($user_data_updated) {
              User::delete_registration_submit_by_submit_token($this->system_core, $this->system_core->urlp->get_param('submit'));

              $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page.tpl', [
                'PAGE_NAME' => 'registration',
                'PAGE_CONTENT' => TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/registrationSubmit.tpl', [
                  'REGISTRATION_SUBMIT_TITLE' => $locale_data['PAGE_REGISTRATION_CONFIRMATION_TITLE'],
                  'REGISTRATION_SUBMIT_TEXT' => $locale_data['PAGE_REGISTRATION_CONFIRMATION_SUCCESS_DESCRIPTION']
                ])
              ]);
            } else {
              $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page.tpl', [
                'PAGE_NAME' => 'registration',
                'PAGE_CONTENT' => TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/registrationSubmit.tpl', [
                  'REGISTRATION_SUBMIT_TITLE' => $locale_data['PAGE_REGISTRATION_CONFIRMATION_TITLE'],
                  'REGISTRATION_SUBMIT_TEXT' => $locale_data['PAGE_REGISTRATION_CONFIRMATION_FAIL_DESCRIPTION']
                ])
              ]);
            }
          } else {
            http_response_code(500);

            $page_error = new PageError($this->system_core, $this->page, 500);
            $page_error->assembly();
            $this->assembled = $page_error->assembled;
          }
        } else if (is_null($this->system_core->urlp->get_param('submit')) && !is_null($this->system_core->urlp->get_param('refusal'))) {
          if (User::exists_by_registration_refusal_token($this->system_core, $this->system_core->urlp->get_param('refusal'))) {
            $user_id = User::get_user_id_by_registration_refusal_token($this->system_core, $this->system_core->urlp->get_param('refusal'));
            $user = new User($this->system_core, $user_id);
            $user_deleted = $user->delete();

            if ($user_deleted) {
              User::delete_registration_submit_by_refusal_token($this->system_core, $this->system_core->urlp->get_param('refusal'));

              $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page.tpl', [
                'PAGE_NAME' => 'registration',
                'PAGE_CONTENT' => TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/registrationSubmit.tpl', [
                  'REGISTRATION_SUBMIT_TITLE' => $locale_data['PAGE_REGISTRATION_CANCELLATION_TITLE'],
                  'REGISTRATION_SUBMIT_TEXT' => $locale_data['PAGE_REGISTRATION_CANCELLATION_SUCCESS_DESCRIPTION']
                ])
              ]);
            } else {
              $this->assembled = TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page.tpl', [
                'PAGE_NAME' => 'registration',
                'PAGE_CONTENT' => TemplateCollector::assembly_file_content($this->system_core->template, 'templates/page/registrationSubmit.tpl', [
                  'REGISTRATION_SUBMIT_TITLE' => $locale_data['PAGE_REGISTRATION_CANCELLATION_TITLE'],
                  'REGISTRATION_SUBMIT_TEXT' => $locale_data['PAGE_REGISTRATION_CANCELLATION_FAIL_DESCRIPTION']
                ])
              ]);
            }
          } else {
            http_response_code(500);

            $page_error = new PageError($this->system_core, $this->page, 500);
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

}

?>