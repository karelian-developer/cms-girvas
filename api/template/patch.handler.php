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

use \core\PHPLibrary\SystemCore\File\EnumFormat as FileFormat;
use \core\PHPLibrary\SystemCore\File\Converter as FileConverter;
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
          if (!preg_match('/^theme_property_(.+)$/', $name, $nameMatches)) {
            continue;
          }

          $rawPropertyName = $nameMatches[1];
          $localeName = null;

          if (preg_match('/^(.+)__(.+)$/', $rawPropertyName, $parts)) {
            $propertyName = strtoupper($parts[1]);
            $localeName = $parts[2];
          } else {
            $propertyName = strtoupper($rawPropertyName);
          }

          if (!isset($propertiesData[$propertyName])) {
            continue;
          }

          $property = &$propertiesData[$propertyName];

          if ($property['type'] === 'file') {
            if ($localeName !== null) {
              continue;
            }

            if (!empty($data)) {
              preg_match('/data:(\w+)\/([\w.]+);base64,/', $data, $matches);
              $fileExtension = $matches[2];

              $enumFileFormat = match ($fileExtension) {
                'jpeg' => FileFormat::JPG,
                'png'  => FileFormat::PNG,
                'webp' => FileFormat::WEBP,
                'avif' => FileFormat::AVIF
              };

              $fileDirectoryPath = CMS_ROOT_DIRECTORY . '/uploads/media';
              $fileConverter = new FileConverter($CMSCore);
              $fileConverted = $fileConverter->convert(
                $data,
                $fileDirectoryPath,
                $enumFileFormat,
                true,
                $name
              );

              if (is_array($fileConverted)) {
                $property['value'] = '/uploads/media/' . $fileConverted['fileName'];
              }
            }
            continue;
          }

          if ($property['type'] === 'colorScheme') {
            if ($localeName !== null) {
              continue;
            }
            $property['value'] = $data;
            continue;
          }

          if ($property['type'] === 'text') {
            if ($localeName !== null) {
              if (!is_array($property['value'])) {
                $property['value'] = [];
              }
              $property['value'][$localeName] = $data;
            } else {
              $property['value'] = $data;
            }
          }
        }
        unset($property);

        $themeFilePropertiesPath = $theme->getFilePropertiesPath();
        $directoryPath = dirname($themeFilePropertiesPath);

        if (!is_writable($directoryPath)) {
          $handlerMessage = $handlerMessage ?? sprintf($CMSCore->locale->getSingleValueByKey('API_ERROR_DONT_HAVE_PERMISSIONS_WRITABLE'), $directoryPath);
          $handlerStatusCode = $handlerStatusCode ?? 0;
        } else if (!is_writable($themeFilePropertiesPath)) {
          $handlerMessage = $handlerMessage ?? sprintf($CMSCore->locale->getSingleValueByKey('API_ERROR_DONT_HAVE_PERMISSIONS_WRITABLE'), $themeFilePropertiesPath);
          $handlerStatusCode = $handlerStatusCode ?? 0;
        } else {
          file_put_contents($themeFilePropertiesPath, json_encode($propertiesData));

          $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_PATCH_DATA_SUCCESS');
          $handlerStatusCode = $handlerStatusCode ?? 1;
        }
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