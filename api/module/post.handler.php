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

use \core\PHPLibrary\Module as Module;
use \ZipArchive as ZipArchive;

if ($CMSCore->client->is_logged(2)) {
  $clientUser = $CMSCore->client->get_user(2);
  $clientUser->init_data(['metadata']);
  $clientUserGroup = $clientUser->get_group();
  $clientUserGroup->init_data(['permissions']);
  
  if ($clientUserGroup->permission_check($clientUserGroup::PERMISSION_ADMIN_MODULES_MANAGEMENT)) {
    if (isset($_POST['module_name']) && $CMSCore->urlp->get_path(2) === 'install') {
      $moduleName = $_POST['module_name'];
      $module = new Module($CMSCore, $moduleName);

      if (!$module->exists_core_file()) {
        $moduleURL = 'https://repository.cms-girvas.ru/modules/' . $moduleName;

        $cURL = curl_init($moduleURL);
        curl_setopt($cURL, CURLOPT_RETURNTRANSFER, 1);
        $cURLExucuteResult = json_decode(curl_exec($cURL), true);
        curl_close($cURL);

        if (!empty($cURLExucuteResult['outputData'])) {
          $moduleDirectoryPath = CMS_ROOT_DIRECTORY . '/modules/' . $moduleName;
          $moduleArchivePath = CMS_ROOT_DIRECTORY . '/modules/' . $moduleName . '.zip';

          $cURL = curl_init($moduleArchivePath);
          curl_setopt($cURL, CURLOPT_URL, $cURLExucuteResult['outputData']['download_url']);
          curl_setopt($cURL, CURLOPT_RETURNTRANSFER, 1);
          $cURLExucuteResult = curl_exec($cURL);
          curl_close($cURL);

          $file = fopen($moduleArchivePath, "w+");
          fputs($file, $cURLExucuteResult);
          fclose($file);

          if (file_exists($moduleArchivePath)) {
            $zipArchive = new ZipArchive();

            if ($zipArchive->open($moduleArchivePath) === true) {
              mkdir($moduleDirectoryPath);

              $zipArchive_extracted = $zipArchive->extractTo($moduleDirectoryPath);
              $zipArchive->close();

              unlink($moduleArchivePath);

              if ($zipArchive_extracted) {
                $moduleCorePath = $moduleDirectoryPath . '/core.class.php';
                $moduleCoreNamespace = '\\modules\\' . $moduleName . '\\Core';
                
                if (file_exists($moduleCorePath)) {
                  require_once($moduleCorePath);

                  $moduleCore = new $moduleCoreNamespace($CMSCore, $module);

                  if (method_exists($moduleCore, 'install')) {
                    $moduleCore->install();
                  }

                  http_response_code(200);
                  $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_MODULE_UPLOADED');
                  $handlerStatusCode = $handlerStatusCode ?? 1;
                } else {
                  http_response_code(500);
                  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_MODULE_ERROR_CORE_NOT_FOUND');
                  $handlerStatusCode = $handlerStatusCode ?? 0;
                }
              } else {
                http_response_code(500);
                $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_UNZIPPING_NOT_POSSIBLE');
                $handlerStatusCode = $handlerStatusCode ?? 0;
              }
            } else {
              http_response_code(500);
              $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_UNZIPPING_NOT_POSSIBLE');
              $handlerStatusCode = $handlerStatusCode ?? 0;
            }
          } else {
            http_response_code(500);
            $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_MODULE_ERROR_ARCHIVE_NOT_FOUND');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }
        } else {
          http_response_code(500);
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_MODULE_ERROR_REPOSITORY_DATA_NOT_GETTED');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      } else {
        http_response_code(500);
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_MODULE_ALREADY_UPLOADED');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    } else {
      http_response_code(400);
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_INVALID_INPUT_DATA_SET');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_DONT_HAVE_PERMISSIONS');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else {
  http_response_code(401);
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}


?>