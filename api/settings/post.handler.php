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

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_ADMIN_SETTINGS_MANAGEMENT)) {
    if (!empty($_POST)) {

      /** @var int Количество пользовательских полей для пользователей */
      $usersAdditionalFieldsCount = 0;
      /** @var int Количество пользовательских полей для записей */
      $entriesAdditionalFieldsCount = 0;
      /** @var int Количество пользовательских полей для статических страниц */
      $staticPagesAdditionalFieldsCount = 0;
      /** @var bool Статус обнаружения ошибок */
      $errorIsDetected = false;

      if (!$errorIsDetected) {
        if (isset($_POST['setting_users_login_length_min']) && isset($_POST['setting_users_login_length_max'])) {
          $settingUsersLoginLengthMin = is_numeric($_POST['setting_users_login_length_min']) ? (int) $_POST['setting_users_login_length_min'] : 0;
          $settingUsersLoginLengthMax = is_numeric($_POST['setting_users_login_length_max']) ? (int) $_POST['setting_users_login_length_max'] : 0;

          if ($settingUsersLoginLengthMax < 0 && !$errorIsDetected) {
            $errorIsDetected = true;

            $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_SETTINGS_USERS_LOGIN_LENGTH_MAX_TOO_SMALL');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }

          if ($settingUsersLoginLengthMax > 0 && !$errorIsDetected) {
            if ($settingUsersLoginLengthMax < $settingUsersLoginLengthMin) {
              $errorIsDetected = true;

              $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_SETTINGS_USERS_LOGIN_LENGTH_MIN_LARGE_MAX');
              $handlerStatusCode = $handlerStatusCode ?? 0;
            }
          }

          if ($settingUsersLoginLengthMin < 4 && !$errorIsDetected) {
            $errorIsDetected = true;

            $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_SETTINGS_USERS_LOGIN_LENGTH_MIN_TOO_SMALL');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }
        }
      }

      if (!$errorIsDetected) {
        if (isset($_POST['setting_users_password_length_min']) && isset($_POST['setting_users_password_length_max'])) {
          $settingUsersPasswordLengthMin = is_numeric($_POST['setting_users_password_length_min']) ? (int) $_POST['setting_users_password_length_min'] : 0;
          $settingUsersPasswordLengthMax = is_numeric($_POST['setting_users_password_length_max']) ? (int) $_POST['setting_users_password_length_max'] : 0;

          if ($settingUsersPasswordLengthMax < 0 && !$errorIsDetected) {
            $errorIsDetected = true;

            $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_SETTINGS_USERS_PASSWORD_LENGTH_MAX_TOO_SMALL');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }

          if ($settingUsersPasswordLengthMax > 0 && !$errorIsDetected) {
            if ($settingUsersPasswordLengthMax < $settingUsersPasswordLengthMin) {
              $errorIsDetected = true;

              $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_SETTINGS_USERS_PASSWORD_LENGTH_MIN_LARGE_MAX');
              $handlerStatusCode = $handlerStatusCode ?? 0;
            }
          }

          if ($settingUsersPasswordLengthMin < 6 && !$errorIsDetected) {
            $errorIsDetected = true;

            $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_SETTINGS_USERS_PASSWORD_LENGTH_MIN_TOO_SMALL');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }
        }
      }
      
      if (!$errorIsDetected) {
        foreach ($_POST as $settingName => $settingValue) {
          if (preg_match('/^setting_([a-z0-9_]+)$/', $settingName, $matches, PREG_OFFSET_CAPTURE)) {
            $settingName = $matches[1][0];

            if ($settingName == 'seo_robots_txt') {
              $fileRobotsTXTPath = CMS_ROOT_DIRECTORY . '/robots.txt';

              try {
                $fileRobotsTXT = @fopen($fileRobotsTXTPath, 'w+');
                if ($fileRobotsTXT === false) {
                  $exceptionMessage = 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_SETTINGS_ROBOTS_TXT_PERMISSION_DENIED');
                  throw new Exception($exceptionMessage);
                }

                fwrite($fileRobotsTXT, $settingValue);
                fclose($fileRobotsTXT);
                chmod($fileRobotsTXTPath, 0664);
              } catch (Exception $exception) {
                $exceptionMessage = 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_SETTINGS_ROBOTS_TXT_PERMISSION_DENIED');
                $handlerMessage = $handlerMessage ?? $exceptionMessage;
                $handlerStatusCode = $handlerStatusCode ?? 0;
              }

              continue;
            }

            if ($settingName === 'users_additional_field_title' && isset($_POST['_users_additional_fields_locale'])) {
              if ($CMSCore->configurator->existsDatabaseEntryValue($settingName)) {
                $fieldsTitles = json_decode($CMSCore->configurator->getDatabaseEntryValue($settingName), true);
                
                foreach ($settingValue as $key => $value) {
                  $fieldsTitles[$_POST['_users_additional_fields_locale']][$key] = htmlspecialchars(str_replace('\'', '"', $value));
                }

                $settingValue = $fieldsTitles;
              } else {
                $fieldsTitles = [];
                foreach ($settingValue as $key => $value) {
                  $fieldsTitles[$_POST['_users_additional_fields_locale']][$key] = htmlspecialchars(str_replace('\'', '"', $value));
                }

                $settingValue = $fieldsTitles;
              }

              $usersAdditionalFieldsCount += 1;
            }

            if ($settingName === 'users_additional_field_description' && isset($_POST['_users_additional_fields_locale'])) {
              if ($CMSCore->configurator->existsDatabaseEntryValue($settingName)) {
                $fieldsDescriptions = json_decode($CMSCore->configurator->getDatabaseEntryValue($settingName), true);
                
                foreach ($settingValue as $key => $value) {
                  $fieldsDescriptions[$_POST['_users_additional_fields_locale']][$key] = htmlspecialchars(str_replace('\'', '"', $value));
                }

                $settingValue = $fieldsDescriptions;
              } else {
                $fieldsDescriptions = [];
                foreach ($settingValue as $key => $value) {
                  $fieldsDescriptions[$_POST['_users_additional_fields_locale']][$key] = htmlspecialchars(str_replace('\'', '"', $value));
                }
                
                $settingValue = $fieldsDescriptions;
              }

              $usersAdditionalFieldsCount += 1;
            }

            if ($settingName === 'users_additional_field_type' || $settingName === 'users_additional_field_name') {
              foreach ($settingValue as $key => $value) {
                $settingValue[$key] = htmlspecialchars(str_replace('\'', '"', $value));
              }
            }

            if ($settingName == 'entries_additional_field_title' && isset($_POST['_entries_additional_fields_locale'])) {
              if ($CMSCore->configurator->existsDatabaseEntryValue($settingName)) {
                $fieldsTitles = json_decode($CMSCore->configurator->getDatabaseEntryValue($settingName), true);
                
                foreach ($settingValue as $key => $value) {
                  $fieldsTitles[$_POST['_entries_additional_fields_locale']][$key] = htmlspecialchars(str_replace('\'', '"', $value));
                }

                $settingValue = $fieldsTitles;
              } else {
                $fieldsTitles = [];
                foreach ($settingValue as $key => $value) {
                  $fieldsTitles[$_POST['_entries_additional_fields_locale']][$key] = htmlspecialchars(str_replace('\'', '"', $value));
                }

                $settingValue = $fieldsTitles;
              }

              $entriesAdditionalFieldsCount += 1;
            }

            if ($settingName == 'entries_additional_field_description' && isset($_POST['_entries_additional_fields_locale'])) {
              if ($CMSCore->configurator->existsDatabaseEntryValue($settingName)) {
                $fieldsDescriptions = json_decode($CMSCore->configurator->getDatabaseEntryValue($settingName), true);
                
                foreach ($settingValue as $key => $value) {
                  $fieldsDescriptions[$_POST['_entries_additional_fields_locale']][$key] = htmlspecialchars(str_replace('\'', '"', $value));
                }

                $settingValue = $fieldsDescriptions;
              } else {
                $fieldsDescriptions = [];
                foreach ($settingValue as $key => $value) {
                  $fieldsDescriptions[$_POST['_entries_additional_fields_locale']][$key] = htmlspecialchars(str_replace('\'', '"', $value));
                }
                
                $settingValue = $fieldsDescriptions;
              }

              $entriesAdditionalFieldsCount += 1;
            }

            if ($settingName === 'entries_additional_field_type' || $settingName === 'entries_additional_field_name') {
              foreach ($settingValue as $key => $value) {
                $settingValue[$key] = htmlspecialchars(str_replace('\'', '"', $value));
              }
            }

            if ($settingName === 'static_pages_additional_field_title' && isset($_POST['_static_pages_additional_fields_locale'])) {
              if ($CMSCore->configurator->existsDatabaseEntryValue($settingName)) {
                $fieldsTitles = json_decode($CMSCore->configurator->getDatabaseEntryValue($settingName), true);
                
                foreach ($settingValue as $key => $value) {
                  $fieldsTitles[$_POST['_static_pages_additional_fields_locale']][$key] = htmlspecialchars(str_replace('\'', '"', $value));
                }

                $settingValue = $fieldsTitles;
              } else {
                $fieldsTitles = [];
                foreach ($settingValue as $key => $value) {
                  $fieldsTitles[$_POST['_static_pages_additional_fields_locale']][$key] = htmlspecialchars(str_replace('\'', '"', $value));
                }

                $settingValue = $fieldsTitles;
              }

              $staticPagesAdditionalFieldsCount += 1;
            }

            if ($settingName === 'static_pages_additional_field_description' && isset($_POST['_static_pages_additional_fields_locale'])) {
              if ($CMSCore->configurator->existsDatabaseEntryValue($settingName)) {
                $fieldsDescriptions = json_decode($CMSCore->configurator->getDatabaseEntryValue($settingName), true);
                
                foreach ($settingValue as $key => $value) {
                  $fieldsDescriptions[$_POST['_static_pages_additional_fields_locale']][$key] = htmlspecialchars(str_replace('\'', '"', $value));
                }

                $settingValue = $fieldsDescriptions;
              } else {
                $fieldsDescriptions = [];
                foreach ($settingValue as $key => $value) {
                  $fieldsDescriptions[$_POST['_static_pages_additional_fields_locale']][$key] = htmlspecialchars(str_replace('\'', '"', $value));
                }
                
                $settingValue = $fieldsDescriptions;
              }

              $staticPagesAdditionalFieldsCount += 1;
            }

            if ($settingName === 'static_pages_additional_field_type' || $settingName === 'static_pages_additional_field_name') {
              foreach ($settingValue as $key => $value) {
                $settingValue[$key] = htmlspecialchars(str_replace('\'', '"', $value));
              }
            }

            if ($settingName === 'setting_static_pages_additional_field_category_id') {
              foreach ($settingValue as $key => $value) {
                if (is_numeric($value)) {
                  $settingValue[$key] = ($value > 0) ? (int)$value : 1;
                }
              }
            }

            if (is_array($settingValue)) $settingValue = json_encode($settingValue);

            $settingValue = match ($settingName) {
              'security_allowed_admin_ip' => !empty($settingValue) ? json_encode(preg_split('/\s*\,\s*/', $settingValue)) : json_encode([]),
              'security_allowed_emails' => !empty($settingValue) ? json_encode(preg_split('/\s*\,\s*/', $settingValue)) : json_encode([]),
              'seo_site_keywords' => !empty($settingValue) ? json_encode(preg_split('/\s*\,\s*/', $settingValue)) : json_encode([]),
              'security_premoderation_words_filter_list' => !empty($settingValue) ? json_encode(preg_split('/\s*\,\s*/', $settingValue)) : json_encode([]),
              'users_logins_blacklist' => !empty($settingValue) ? json_encode(preg_split('/\s*\,\s*/', $settingValue)) : json_encode([]),
              'users_additional_field_title' => $settingValue,
              'users_additional_field_description' => $settingValue,
              'users_additional_field_type' => $settingValue,
              'users_additional_field_name' => $settingValue,
              'entries_additional_field_title' => $settingValue,
              'entries_additional_field_description' => $settingValue,
              'entries_additional_field_type' => $settingValue,
              'entries_additional_field_category_id' => $settingValue,
              'entries_additional_field_name' => $settingValue,
              'static_pages_additional_field_title' => $settingValue,
              'static_pages_additional_field_description' => $settingValue,
              'static_pages_additional_field_type' => $settingValue,
              'static_pages_additional_field_name' => $settingValue,
              default => htmlspecialchars(str_replace('\'', '"', $settingValue))
            };

            if ($CMSCore->configurator->existsDatabaseEntryValue($settingName)) {
              $CMSCore->configurator->updateDatabaseEntryValue($settingName, $settingValue);
            } else {
              $CMSCore->configurator->insertDatabaseEntryValue($settingName, $settingValue);
            }
          }
        }

        if ($usersAdditionalFieldsCount === 0 && isset($_POST['_users_additional_fields_locale'])) {
          foreach (['users_additional_field_title', 'users_additional_field_description', 'users_additional_field_name', 'users_additional_field_type'] as $index => $name) {
            if ($CMSCore->configurator->existsDatabaseEntryValue('users_additional_field_title')) {
              $CMSCore->configurator->updateDatabaseEntryValue($name, json_encode([]));
            }
          }
        }

        if ($entriesAdditionalFieldsCount === 0 && isset($_POST['_entries_additional_fields_locale'])) {
          foreach (['entries_additional_field_title', 'entries_additional_field_description', 'entries_additional_field_name', 'entries_additional_field_type', 'entries_additional_field_category_id'] as $index => $name) {
            if ($CMSCore->configurator->existsDatabaseEntryValue('entries_additional_field_title')) {
              $CMSCore->configurator->updateDatabaseEntryValue($name, json_encode([]));
            }
          }
        }

        if ($staticPagesAdditionalFieldsCount == 0 && isset($_POST['_static_pages_additional_fields_locale'])) {
          foreach (['static_pages_additional_field_title', 'static_pages_additional_field_description', 'static_pages_additional_field_name', 'static_pages_additional_field_type', 'static_pages_additional_field_type'] as $index => $name) {
            if ($CMSCore->configurator->existsDatabaseEntryValue('static_pages_additional_field_title')) {
              $CMSCore->configurator->updateDatabaseEntryValue($name, json_encode([]));
            }
          }
        }

        $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_PATCH_DATA_SUCCESS');
        $handlerStatusCode = $handlerStatusCode ?? 1;
      }
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_INVALID_INPUT_DATA_SET');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_DONT_HAVE_PERMISSIONS');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else {
  http_response_code(401);
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}