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

namespace core\PHPLibrary\Page\Admin;

use \core\PHPLibrary\InterfacePage as InterfacePage;
use \core\PHPLibrary\SystemCore as SystemCore;
use \core\PHPLibrary\User as User;
use \core\PHPLibrary\UserGroup as UserGroup;
use \core\PHPLibrary\Form as Form;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\TraitPage as TraitPage;

class PageForm implements InterfacePage
{
  use TraitPage;

  const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_FORM_NAVIGATION_%s_LABEL';

  public SystemCore $CMSCore;
  public Page $page;
  public string $assembled = '';
  public array $navigationSubsections = [
    'back' => [
      'name' => 'back',
      'iconName' => 'back',
      'link' => '/forms',
      'permanent' => true,
      'isActive' => false,
      'permission' => 'hasPermissionAdminFormsManagement',
    ],
  ];

  public function __construct(SystemCore $CMSCore, Page $page) {
    $this->CMSCore = $CMSCore;
    $this->page = $page;
  }

  /**
   * Получить группу текущего авторизованного пользователя админки
   * 
   * @return UserGroup|null
   */
  private function getCurrentUserGroup() : ?UserGroup
  {
    /** @var User|null $user */
    $user = $this->CMSCore->client->getUser(2);
    if ($user === null) {
      return null;
    }

    $user->initData(['metadata']);

    /** @var UserGroup|null $userGroup */
    $userGroup = $user->getGroup();
    if ($userGroup === null) {
      return null;
    }

    $userGroup->initData(['permissions']);

    return $userGroup;
  }

  /**
   * Проверить, есть ли у текущего пользователя право управления формами
   * 
   * @return bool
   */
  private function currentUserCanManageForms() : bool
  {
    $userGroup = $this->getCurrentUserGroup();

    return $userGroup !== null
      && method_exists($userGroup, 'hasPermissionAdminFormsManagement')
      && $userGroup->hasPermissionAdminFormsManagement();
  }

  /**
   * Собрать страницу ошибки и подменить итоговую сборку
   * 
   * @param int $httpCode
   * 
   * @return void
   */
  private function assemblyError(int $httpCode) : void
  {
    http_response_code($httpCode);

    $pageError = new PageError($this->CMSCore, $this->page, $httpCode);
    $pageError->assembly();
    $this->assembled = $pageError->assembled;
  }

  /**
   * Инициализация подразделов
   * 
   * @return void
   */
  public function initSubnavigation() : void {
    // Если нет прав на управление формами — подразделы не собираем
    if (!$this->currentUserCanManageForms()) {
      return;
    }

    $themeSource =& $this->CMSCore->theme->core->source;
    $this->initAdminPanelSubnavigation($this->CMSCore, $themeSource);
  }

  public function assembly() : void {
    // Защита от прямого захода по URL без прав
    if (!$this->currentUserCanManageForms()) {
      $this->assemblyError(403);
      return;
    }

    $this->CMSCore->theme->addStyle(['href' => 'styles/page/form.css', 'rel' => 'stylesheet']);
    
    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    $form = null;
    if ($this->CMSCore->urlp->getPath(2) !== null) {
      $formID = is_numeric($this->CMSCore->urlp->getPath(2))
        ? (int) $this->CMSCore->urlp->getPath(2)
        : 0;

      $form = Form::existsByID($this->CMSCore, $formID) ? new Form($this->CMSCore, $formID) : null;
      
      if ($form !== null) {
        $form->initData(['id', 'texts', 'name', 'metadata', 'elements']);
        $formID = $form->getID();
        $formName = $form->getName();

        /** @var string Заголовок */
        $formTitle = $form->getTitle($localeName);
        $formTitle = strip_tags($formTitle);

        /** @var string Описание */
        $formDescription = $form->getDescription($localeName);
        $formDescription = strip_tags($formDescription);

        /** @var string Ссылка обработки */
        $formAction = $form->getAction();
        $formAction = strip_tags($formAction);

        $formTelegramChatsIDs = $form->getTelegramChatsIDs();
        $formTelegramChatsIDs = implode(', ', $formTelegramChatsIDs);
        
        $formMaxChatsIDs = $form->getMaxChatsIDs();
        $formMaxChatsIDs = implode(', ', $formMaxChatsIDs);
      }
    }
    
    $this->assembled = ThemeCollector::assemblyFileContent(
      $this->CMSCore->theme, 'templates/page/form.tpl',
      [
        'FORM_ID' => $form !== null ? $formID : 0,
        'FORM_TITLE' => $form !== null ? $formTitle : '',
        'FORM_DESCRIPTION' => $form !== null ? $formDescription : '',
        'FORM_NAME' => $form !== null ? $formName : '',
        'FORM_ACTION' => $form !== null ? $formAction : '',
        'FORM_NOTIFICATION_TELEGRAM_CHATS_IDS' => $form !== null ? $formTelegramChatsIDs : '',
        'FORM_NOTIFICATION_MAX_CHATS_IDS' => $form !== null ? $formMaxChatsIDs : ''
      ]
    );
  }
}