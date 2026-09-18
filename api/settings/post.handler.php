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

use \core\PHPLibrary\SystemCore\Report as CMSReport;

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['login','metadata']);
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
        $SMTPConfugration = [];

        // ============================================================
        // СОХРАНЕНИЕ СТАРЫХ ЗНАЧЕНИЙ ДЛЯ ЛОГА (152-ФЗ)
        // ============================================================
        $oldSettingsValues = [];
        foreach ($_POST as $key => $value) {
          if (!preg_match('/^setting_([a-z0-9_]+)$/', $key, $matches)) continue;
          $bareKey = $matches[1];
          $oldSettingsValues[$bareKey] = $CMSCore->configurator->existsDatabaseEntryValue($bareKey)
            ? $CMSCore->configurator->getDatabaseEntryValue($bareKey)
            : null;
        }

        error_log('SETTINGS_POST_KEYS: ' . json_encode(array_keys($_POST)));
        error_log('LEGAL_DOCS_VALUE: ' . json_encode($_POST['setting_security_legal_documents'] ?? 'NOT_SET'));

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

            if ($settingName == 'seo_llms_txt') {
              $fileLLMSTXTPath = CMS_ROOT_DIRECTORY . '/llms.txt';

              try {
                $fileLLMSTXT = @fopen($fileLLMSTXTPath, 'w+');
                if ($fileLLMSTXT === false) {
                  $exceptionMessage = 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_SETTINGS_LLMS_TXT_PERMISSION_DENIED');
                  throw new Exception($exceptionMessage);
                }

                fwrite($fileLLMSTXT, $settingValue);
                fclose($fileLLMSTXT);
                chmod($fileLLMSTXTPath, 0664);
              } catch (Exception $exception) {
                $exceptionMessage = 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_SETTINGS_LLMS_TXT_PERMISSION_DENIED');
                $handlerMessage = $handlerMessage ?? $exceptionMessage;
                $handlerStatusCode = $handlerStatusCode ?? 0;
              }

              continue;
            }

            if (in_array($settingName, ['email_smtp_host', 'email_smtp_port', 'email_smtp_username', 'email_smtp_password', 'email_smtp_domain'])) {
              $SMTPConfugration[$settingName] = $settingValue;
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

            if (in_array($settingName, ['security_notification_telegram_chats_ids', 'security_notification_max_chats_ids'])) {

              $formChatsIDs = explode(',', $settingValue);
              
              foreach ($formChatsIDs as $index => $id) {

                if (!is_numeric($id)) {
                  unset($formChatsIDs[$index]);
                  continue;
                }

                $formChatsIDs[$index] = trim($id);
                $formChatsIDs[$index] = (int)$formChatsIDs[$index];
              }

              $settingValue = $formChatsIDs;
            }

            if (is_array($settingValue) && $settingName !== 'security_legal_documents') {
              $settingValue = json_encode($settingValue);
            }

            $settingValue = match ($settingName) {
              'security_notification_telegram_chats_ids' => !empty($settingValue) ? $settingValue : json_encode([]),
              'security_notification_max_chats_ids' => !empty($settingValue) ? $settingValue : json_encode([]),
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

        if (!empty($SMTPConfugration)) {
          $configurationFilePath = CMS_ROOT_DIRECTORY . '/core/configuration.smtp.php';

          $file = fopen($configurationFilePath, 'w+');
          fwrite($file, '<?php' . PHP_EOL);
          fwrite($file, PHP_EOL);
          fwrite($file, '$configuration = [' . PHP_EOL);

          foreach ($SMTPConfugration as $settingName => $settingValue) {
            $paramName = match ($settingName) {
              'email_smtp_host' => 'host',
              'email_smtp_port' => 'port',
              'email_smtp_username' => 'username',
              'email_smtp_password' => 'password',
              'email_smtp_domain' => 'domain'
            };

            fwrite($file, sprintf('  \'' . $paramName .'\' => \'%s\',', $settingValue) . PHP_EOL);
          }

          fwrite($file, '];' . PHP_EOL);
          fwrite($file, PHP_EOL);
          fclose($file);
          chmod($configurationFilePath, 0664);
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

        // ============================================================
        // ЛОГИРОВАНИЕ ИЗМЕНЕНИЯ НАСТРОЕК CMS (152-ФЗ)
        // ============================================================

        /**
         * @var array Паттерны имён настроек, значения которых НЕ логируются.
         * Проверка идёт по подстроке в имени поля (без префикса setting_).
         */
        $sensitivePatterns = [
          'password', 'token', 'secret', 'hash', 'salt',
          'smtp_password', 'smtp_username',
          'allowed_admin_ip', 'allowed_emails',
          'notification_telegram_chats_ids', 'notification_max_chats_ids',
          'logins_blacklist',
          'premoderation_words_filter_list',
          'additional_field',
        ];

        /**
         * @var array Точные имена полей, которые НЕ sensitive (исключения).
         * Используется для переопределения паттернов — если поле подпадает под паттерн,
         * но по смыслу безопасно (длина, флаг, домен SMTP).
         */
        $sensitiveExceptions = [
          'users_password_length_min',
          'users_password_length_max',
          'email_smtp_host',
          'email_smtp_port',
          'email_smtp_domain',
        ];

        /**
         * Рекурсивно отсортировать ключи массива (для стабильного сравнения)
         */
        $recursiveKsort = function(array $array) use (&$recursiveKsort): array {
          ksort($array);
          foreach ($array as $k => $v) {
            if (is_array($v)) {
              $array[$k] = $recursiveKsort($v);
            }
          }
          return $array;
        };

        /**
         * Нормализовать значение настройки для сравнения.
         * Массивы и JSON-строки приводятся к канонической форме.
         */
        $normalizeSettingValue = function(mixed $value) use ($recursiveKsort): string {
          if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && (is_array($decoded) || is_object($decoded))) {
              $value = $decoded;
            } else {
              return $value;
            }
          }

          if (is_array($value)) {
            $value = $recursiveKsort($value);
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
          }

          if (is_bool($value)) return $value ? '1' : '0';
          if (is_null($value)) return '';

          return (string)$value;
        };

        /** @var array Изменения значений безопасных полей */
        $changedValues = [];
        /** @var array Имена изменившихся чувствительных полей (без значений) */
        $sensitiveChanged = [];
        /** @var array Полный список изменившихся полей (с префиксом setting_) */
        $changedFields = [];

        foreach ($_POST as $key => $value) {
          if (!preg_match('/^setting_([a-z0-9_]+)$/', $key, $matches)) continue;

          $bareKey = $matches[1];
          $oldValue = $oldSettingsValues[$bareKey] ?? null;

          $oldNormalized = $normalizeSettingValue($oldValue);
          $newNormalized = $normalizeSettingValue($value);

          if ($oldNormalized === $newNormalized) continue;

          $changedFields[] = $key;

          if (stripos($bareKey, 'additional_field') !== false) {
            continue;
          }

          // Определяем sensitive
          $isSensitive = false;

          // 1. Исключения — точные имена
          if (in_array($bareKey, $sensitiveExceptions, true)) {
            $isSensitive = false;
          // 2. Флаги _status — не sensitive
          } elseif (str_ends_with($bareKey, '_status')) {
            $isSensitive = false;
          // 3. Паттерны
          } else {
            foreach ($sensitivePatterns as $pattern) {
              if (stripos($bareKey, $pattern) !== false) {
                $isSensitive = true;
                break;
              }
            }
          }

          if ($isSensitive) {
            $sensitiveChanged[] = $key;
          } else {
            $changedValues[$key] = [
              'old' => mb_substr((string)$oldValue, 0, 500),
              'new' => mb_substr((string)$value, 0, 500),
            ];
          }
        }

        // Логируем только если что-то реально изменилось
        if (!empty($changedFields)) {
          CMSReport::create(
            $CMSCore,
            CMSReport::REPORT_TYPE_ID_AP_SETTINGS_EDITED,
            [
              'changedFields'    => $changedFields,
              'changedValues'    => $changedValues,
              'sensitiveChanged' => $sensitiveChanged,
              'userID'           => $clientUser->getID(),
              'userLogin'        => $clientUser->getLogin(),
              'ip'               => $CMSCore->client->getIPAddress()
            ]
          );
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