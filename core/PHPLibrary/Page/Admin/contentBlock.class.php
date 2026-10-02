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

use \core\PHPLibrary\Factories\Content as CMSContent;
use \core\PHPLibrary\InterfacePage as InterfacePage;
use \core\PHPLibrary\SystemCore as SystemCore;
use \core\PHPLibrary\User as User;
use \core\PHPLibrary\UserGroup as UserGroup;
use \core\PHPLibrary\ContentBlock as ContentBlock;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Page as Page;
use \core\PHPLibrary\TraitPage as TraitPage;
use \DOMDocument as DOMDocument;

class PageContentBlock implements InterfacePage
{
  use TraitPage;

  const LANG_PAGE_NAVIGATION_LABLE_TEMPLATE = 'PAGE_ENTRY_NAVIGATION_%s_LABEL';

  public SystemCore $CMSCore;
  public Page $page;
  public string $assembled = '';
  public array $navigationSubsections = [
    'back' => [
      'name' => 'back',
      'iconName' => 'back',
      'link' => '/contentBlocks',
      'permanent' => true,
      'isActive' => false,
      'permission' => 'hasPermissionEditorContentBlocksEdit',
    ],
  ];

  public function __construct(SystemCore $CMSCore, Page $page)
  {
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
   * Проверить, есть ли у текущего пользователя право редактирования контент-блоков
   * 
   * @return bool
   */
  private function currentUserCanEditContentBlocks() : bool
  {
    $userGroup = $this->getCurrentUserGroup();

    return $userGroup !== null
      && method_exists($userGroup, 'hasPermissionEditorContentBlocksEdit')
      && $userGroup->hasPermissionEditorContentBlocksEdit();
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
  public function initSubnavigation() : void
  {
    // Если нет прав на редактирование контент-блоков — подразделы не собираем
    if (!$this->currentUserCanEditContentBlocks()) {
      return;
    }

    $themeSource =& $this->CMSCore->theme->core->source;
    $this->initAdminPanelSubnavigation($this->CMSCore, $themeSource);
  }

  public function assembly() : void
  {
    // Защита от прямого захода по URL без прав
    if (!$this->currentUserCanEditContentBlocks()) {
      $this->assemblyError(403);
      return;
    }

    $this->CMSCore->theme->addStyle(['href' => 'styles/page/contentBlock.css', 'rel' => 'stylesheet']);
    $this->CMSCore->theme->addStyle(['href' => 'styles/nadvoTE.css', 'rel' => 'stylesheet']);
    
    $localeData = $this->CMSCore->locale->getData();
    $localeName = $this->CMSCore->locale->getName();

    $contentBlock = null;
    if ($this->CMSCore->urlp->getPath(2) !== null) {
      $contentBlockID = is_numeric($this->CMSCore->urlp->getPath(2))
        ? (int) $this->CMSCore->urlp->getPath(2)
        : 0;
        
      $contentBlock = ContentBlock::existsByID($this->CMSCore, $contentBlockID)
        ? CMSContent::create($this->CMSCore, 'contentBlock', ['id' => $contentBlockID])
        : null;
      
      if ($contentBlock !== null) {
        $contentBlock->initData(['id', 'texts', 'name', 'metadata']);
      } else {
        $this->assemblyError(404);
        return;
      }
    }
    
    $templatesAssembled = [];
    $templatesEditorAssembled = [];
    $templateContent = ThemeCollector::getTemplateFileContent(
      $this->CMSCore->theme,
      'templates/page/contentBlock.tpl'
    );
    $templateEditorContent = ThemeCollector::getTemplateFileContent(
      $this->CMSCore->theme,
      'templates/page/contentBlock/editor.tpl'
    );

    if (ThemeCollector::existsTemplateVariable($templateContent, 'CONTENT_BLOCK_ID')) {
      $value = $contentBlock !== null ? $contentBlock->getID() : 0;

      ThemeCollector::addTemplateVariable(
        $templatesAssembled,
        'CONTENT_BLOCK_ID',
        $value
      );
    }

    if (ThemeCollector::existsTemplateVariable($templateContent, 'CONTENT_BLOCK_TITLE')) {
      $value = $contentBlock !== null ? $contentBlock->getTitle($localeName) : '';

      ThemeCollector::addTemplateVariable(
        $templatesAssembled,
        'CONTENT_BLOCK_TITLE',
        str_replace(
          ThemeCollector::DECODED_ENTITIES,
          ThemeCollector::SAFE_SYMBOLS,
          htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
        )
      );
    }

    if (ThemeCollector::existsTemplateVariable($templateContent, 'CONTENT_BLOCK_DESCRIPTION')) {
      $value = $contentBlock !== null ? $contentBlock->getDescription($localeName) : '';
      
      ThemeCollector::addTemplateVariable(
        $templatesAssembled,
        'CONTENT_BLOCK_DESCRIPTION',
        str_replace(
          ThemeCollector::DECODED_ENTITIES,
          ThemeCollector::SAFE_SYMBOLS,
          htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
        )
      );
    }

    if (ThemeCollector::existsTemplateVariable($templateEditorContent, 'CONTENT_BLOCK_CONTENT')) {
      $value = $contentBlock !== null ? $contentBlock->getContent($localeName) : '';

      ThemeCollector::addTemplateVariable(
        $templatesEditorAssembled,
        'CONTENT_BLOCK_CONTENT',
        str_replace(
          ThemeCollector::DECODED_ENTITIES,
          ThemeCollector::SAFE_SYMBOLS,
          htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
        )
      );
    }

    if (ThemeCollector::existsTemplateVariable($templateContent, 'CONTENT_BLOCK_SECTION_INTEGRATION_NAME')) {
      ThemeCollector::addTemplateVariable(
        $templatesAssembled,
        'CONTENT_BLOCK_SECTION_INTEGRATION_NAME',
        $contentBlock !== null ? $contentBlock->getSectionIntegrationName() : ''
      );
    }

    if (ThemeCollector::existsTemplateVariable($templateContent, 'CONTENT_BLOCK_NAME')) {
      ThemeCollector::addTemplateVariable(
        $templatesAssembled,
        'CONTENT_BLOCK_NAME',
        $contentBlock !== null ? $contentBlock->getName() : ''
      );
    }

    if (ThemeCollector::existsTemplateVariable($templateContent, 'CONTENT_BLOCK_URL_RULE')) {
      ThemeCollector::addTemplateVariable(
        $templatesAssembled,
        'CONTENT_BLOCK_URL_RULE',
        $contentBlock !== null ? $contentBlock->getURLRule() : ''
      );
    }

    if (ThemeCollector::existsTemplateVariable($templateContent, 'CONTENT_BLOCK_FORM_METHOD')) {
      ThemeCollector::addTemplateVariable(
        $templatesAssembled,
        'CONTENT_BLOCK_FORM_METHOD',
        $contentBlock !== null ? 'PATCH' : 'PUT'
      );
    }

    $templatesAssembled['ADMIN_PANEL_PAGE_NAME'] = 'content-block';
    $templatesAssembled['CONTENT_BLOCK_EDITOR'] = ThemeCollector::assemblyFileContent(
      $this->CMSCore->theme,
      'templates/page/contentBlock/editor.tpl',
      $templatesEditorAssembled
    );

    /** @var string $site_page Содержимое шаблона страницы */
    $this->assembled = ThemeCollector::assemblyFileContent(
      $this->CMSCore->theme,
      'templates/page/contentBlock.tpl',
      $templatesAssembled
    );
  }
}