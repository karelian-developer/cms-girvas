<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

if (!defined('IS_NOT_HACKED')) {
  http_response_code(503);
  die('An attempted hacker attack has been detected.');
}

use \core\PHPLibrary\Template as Theme;
use \core\PHPLibrary\User as User;
use \core\PHPLibrary\UserGroup as UserGroup;
use \core\PHPLibrary\UsersGroups as UsersGroups;
use \core\PHPLibrary\SystemCore\Report as Report;

if ($CMSCore->urlp->getPath(3) === 'permissions') {
  $user = null;
  if ($CMSCore->urlp->getPath(2) === '@me') {
    if ($CMSCore->client->isLogged(1)) {
      $user = $CMSCore->client->getUser(1);
    }
  } else {
    $user = is_numeric($CMSCore->urlp->getPath(2)) ? new User($CMSCore, $CMSCore->urlp->getPath(2)) : User::getByLogin($CMSCore, $CMSCore->urlp->getPath(2));
  }

  if ($CMSCore->client->isLogged(1)) {
    if (!is_null($user)) {
      $user->initData(['metadata']);
      $userGroup = $user->getGroup();

      if (!is_null($userGroup)) {
        $userGroup->initData(['permissions']);

        $handlerOutputData['user'] = [];
        $handlerOutputData['user']['permissions'] = [];
        $handlerOutputData['user']['permissions']['admin_panel_auth'] = $userGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_PANEL_AUTH);
        $handlerOutputData['user']['permissions']['admin_users_management'] = $userGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_USERS_MANAGEMENT);
        $handlerOutputData['user']['permissions']['admin_users_groups_management'] = $userGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_USERS_GROUPS_MANAGEMENT);
        $handlerOutputData['user']['permissions']['admin_modules_management'] = $userGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_MODULES_MANAGEMENT);
        $handlerOutputData['user']['permissions']['admin_templates_management'] = $userGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_TEMPLATES_MANAGEMENT);
        $handlerOutputData['user']['permissions']['admin_settings_management'] = $userGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_SETTINGS_MANAGEMENT);
        $handlerOutputData['user']['permissions']['admin_viewing_logs'] = $userGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_VIEWING_LOGS);
        $handlerOutputData['user']['permissions']['moder_users_ban'] = $userGroup->permissionCheck(UserGroup::PERMISSION_MODER_USERS_BAN);
        $handlerOutputData['user']['permissions']['moder_entries_comments_management'] = $userGroup->permissionCheck(UserGroup::PERMISSION_MODER_ENTRIES_COMMENTS_MANAGEMENT);
        $handlerOutputData['user']['permissions']['moder_users_warns'] = $userGroup->permissionCheck(UserGroup::PERMISSION_MODER_USERS_WARNS);
        $handlerOutputData['user']['permissions']['editor_media_files_management'] = $userGroup->permissionCheck(UserGroup::PERMISSION_EDITOR_MEDIA_FILES_MANAGEMENT);
        $handlerOutputData['user']['permissions']['editor_entries_edit'] = $userGroup->permissionCheck(UserGroup::PERMISSION_EDITOR_ENTRIES_EDIT);
        $handlerOutputData['user']['permissions']['editor_entries_categories_edit'] = $userGroup->permissionCheck(UserGroup::PERMISSION_EDITOR_ENTRIES_CATEGORIES_EDIT);
        $handlerOutputData['user']['permissions']['editor_pages_static_edit'] = $userGroup->permissionCheck(UserGroup::PERMISSION_EDITOR_PAGES_STATIC_EDIT);
        $handlerOutputData['user']['permissions']['base_entry_comment_create'] = $userGroup->permissionCheck(UserGroup::PERMISSION_BASE_ENTRY_COMMENT_CREATE);
        $handlerOutputData['user']['permissions']['base_entry_comment_change'] = $userGroup->permissionCheck(UserGroup::PERMISSION_BASE_ENTRY_COMMENT_CHANGE);
        $handlerOutputData['user']['permissions']['base_entry_comment_rate'] = $userGroup->permissionCheck(UserGroup::PERMISSION_BASE_ENTRY_COMMENT_RATE);

        $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
        $handlerStatusCode = $handlerStatusCode ?? 1;
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USERS_GROUP_ERROR_NOT_FOUND');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_NOT_FOUND');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else if ($CMSCore->urlp->getPath(3) === null) {
  $user = $CMSCore->urlp->getPath(2) === '@me' ? $CMSCore->client->getUser(1) : (is_numeric($CMSCore->urlp->getPath(2)) ? new User($CMSCore, $CMSCore->urlp->getPath(2)) : User::getByLogin($CMSCore, $CMSCore->urlp->getPath(2)));
  $locale = $CMSCore->urlp->getParam('locale') ?? $CMSCore->configurator->getDatabaseEntryValue('base_locale');

  if ($user !== null) {
    $user->initData(['login', 'metadata']);

    $userGroup = $user->getGroup();
    $userGroup->initData(['texts']);

    $themeName = ($CMSCore->configurator->existsDatabaseEntryValue('base_template')) ? $CMSCore->configurator->getDatabaseEntryValue('base_template') : 'default';
    $theme = new Theme($CMSCore, $themeName);
    $CMSCore->setTheme($theme);

    $clientUser = $CMSCore->client->getUser(1);
    if ($clientUser !== null && $clientUser->getID() !== $user->getID()) {
      Report::create(
        $CMSCore,
        Report::REPORT_TYPE_ID_BASE_USER_PERSONAL_DATA_VIEWED,
        [
          'targetUserID' => $user->getID(),
          'viewedByID' => $clientUser->getID(),
          'ip' => $CMSCore->client->getIPAddress()
        ]
      );
    }

    $handlerOutputData['user'] = [];
    $handlerOutputData['user']['id'] = $user->getID();
    $handlerOutputData['user']['login'] = $user->getLogin();
    $handlerOutputData['user']['avatarURL'] = $user->getAvatarURL(64);
    $handlerOutputData['user']['isBlocked'] = $user->isBlocked();
    $handlerOutputData['user']['groupID'] = $user->getGroupID();
    $handlerOutputData['user']['group'] = [
      'id' => $userGroup->getID(),
      'title' => $userGroup->getTitle($locale)
    ];

    if ($CMSCore->urlp->getPath(2) === '@me') {
      $handlerOutputData['user']['isLogged'] = $CMSCore->client->isLogged(1);
    }

    $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_NOT_FOUND');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
}