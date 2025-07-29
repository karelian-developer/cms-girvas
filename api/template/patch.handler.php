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

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_ADMIN_TEMPLATES_MANAGEMENT)) {
    $themeName = $_PATCH['template_name'];
    $themeCategory = $_PATCH['template_category'];
    $theme = new Theme($CMSCore, $themeName, $themeCategory);
    $themePath = $theme->getPath();

    if ($theme->existsCoreFile()) {
      if ($theme->existsFileProperties()) {
        $propertiesData = $theme->getFilePropertiesData();

        foreach ($_PATCH as $name => $data) {
          $propertyName = strtr($name, ['theme_property_' => '']);
          $propertyName = strtoupper($propertyName);

          if (preg_match('^theme_property_', $name) && isset($propertiesData[$propertyName])) {
            $propertiesData[$propertyName] = $data;
          }
        }

        file_put_contents($theme->getFilePropertiesPath(), json_encode($propertiesData));

        http_response_code(200);
        $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_PATCH_DATA_SUCCESS');
        $handlerStatusCode = $handlerStatusCode ?? 1;
      }
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_DONT_HAVE_PERMISSIONS');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}