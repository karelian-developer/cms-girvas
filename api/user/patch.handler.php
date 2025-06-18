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

use \core\PHPLibrary\User as User;

if ($CMSCore->client->is_logged(1) || $CMSCore->client->is_logged(2)) {
  $clientUser = $CMSCore->client->get_user(1);
  $clientUser->init_data(['metadata']);
  $clientUser_group = $clientUser->get_group();
  $clientUser_group->init_data(['permissions']);

  if (isset($_PATCH['user_id'])) {
    if ($clientUser_group->permission_check($clientUser_group::PERMISSION_ADMIN_USERS_MANAGEMENT) || $clientUser->get_id() == (int)$_PATCH['user_id']) {
      $userID = (is_numeric($_PATCH['user_id'])) ? (int)$_PATCH['user_id'] : 0;

      if (User::exists_by_id($CMSCore, $userID)) {
        $user = new User($CMSCore, $userID);
        $user->init_data(['login', 'email', 'securityHash', 'passwordHash']);

        $userData = [];

        $userUpdateIsAllowed = false;

        if (isset($_PATCH['user_is_block'])) {
          if (!isset($userData['metadata'])) $userData['metadata'] = [];
          $userData['metadata']['isBlocked'] = (int)$_PATCH['user_is_block'];
          $userUpdateIsAllowed = true;
        }

        if (isset($_PATCH['user_login'])) {
          $userLogin = htmlspecialchars(str_replace('\'', '"', $_PATCH['user_login']));

          if ($CMSCore->configurator->get_users_login_edit_status(true) && $userID === $clientUser->get_id()) {
            $userUpdateIsAllowed = true;
          } else {
            if ($clientUser_group->permission_check($clientUser_group::PERMISSION_ADMIN_USERS_MANAGEMENT)) {
              $userUpdateIsAllowed = true;
            }
          }

          if ($userUpdateIsAllowed) {
            if ($CMSCore->configurator->get_users_logins_blacklist_status(true)) {
              $logins_blacklist_array = $CMSCore->configurator->get_users_logins_blacklist(true);

              foreach ($logins_blacklist_array as $login) {
                if ($CMSCore->configurator->get_users_login_register_accounting_status(true)) {
                  $loginPattern = '/^' . $userLogin . '$/';

                  if (preg_match($loginPattern, $login)) {
                    $userUpdateIsAllowed = false;

                    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_USER_ERROR_LOGIN_EXISTS_IN_BLACKLIST');
                    $handlerStatusCode = $handlerStatusCode ?? 0;
                  }
                } else {
                  $loginPattern = '/^' . $userLogin . '$/i';

                  if (preg_match($loginPattern, $login)) {
                    $userUpdateIsAllowed = false;

                    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_USER_ERROR_LOGIN_EXISTS_IN_BLACKLIST');
                    $handlerStatusCode = $handlerStatusCode ?? 0;
                  }
                }
              }
            }
          }

          if ($userUpdateIsAllowed) {
            if ($CMSCore->configurator->get_users_login_special_symbols_status(true)) {
              $loginPattern = '[a-zA-Z0-9\_\-\!\@\#\$\%\&]+';
            } else {
              $loginPattern = '[a-zA-Z0-9\_\-]+';
            }

            if ($CMSCore->configurator->get_users_login_register_accounting_status(true)) {
              $loginPattern = '/^' . $userLogin . '$/i';

              if (!preg_match($loginPattern, $userLogin)) {
                $userUpdateIsAllowed = false;
    
                $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_USER_ERROR_INVALID_LOGIN');
                $handlerStatusCode = $handlerStatusCode ?? 0;
              }
            } else {
              $loginPattern = '/^' . $userLogin . '$/';

              if (!preg_match($loginPattern, $userLogin)) {
                $userUpdateIsAllowed = false;
    
                $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_USER_ERROR_INVALID_LOGIN');
                $handlerStatusCode = $handlerStatusCode ?? 0;
              }
            }
          }

          if ($userUpdateIsAllowed) {
            if ($CMSCore->configurator->get_users_login_length_max() > 0) {
              if (strlen($userLogin) > $CMSCore->configurator->get_users_login_length_max()) {
                $userUpdateIsAllowed = false;
    
                $handlerMessage = $handlerMessage ?? 'API ERROR: ' . sprintf($CMSCore->locale->get_single_value_by_key('API_USER_ERROR_INVALID_LOGIN_LENGTH_TOO_LARGE'), $CMSCore->configurator->get_users_login_length_max());
                $handlerStatusCode = $handlerStatusCode ?? 0;
              }
            }
          }
    
          if ($userUpdateIsAllowed) {
            if (strlen($userLogin) < $CMSCore->configurator->get_users_login_length_min()) {
              $userUpdateIsAllowed = false;
    
              $handlerMessage = $handlerMessage ?? 'API ERROR: ' . sprintf($CMSCore->locale->get_single_value_by_key('API_USER_ERROR_INVALID_LOGIN_LENGTH_TOO_SMALL'), $CMSCore->configurator->get_users_login_length_min());
              $handlerStatusCode = $handlerStatusCode ?? 0;
            }
          }
        }

        if (isset($_PATCH['user_email'])) $userEmail = str_replace('\'', '"', trim($_PATCH['user_email']));
        if (isset($_PATCH['user_name'])) $userName = htmlspecialchars(str_replace('\'', '"', trim($_PATCH['user_name'])));
        if (isset($_PATCH['user_surname'])) $userSurname = htmlspecialchars(str_replace('\'', '"', trim($_PATCH['user_surname'])));
        if (isset($_PATCH['user_patronymic'])) $userPatronymic = htmlspecialchars(str_replace('\'', '"', trim($_PATCH['user_patronymic'])));
        if (isset($_PATCH['user_birthdate'])) $userBirthdate = strtotime($_PATCH['user_birthdate']);
        if (isset($_PATCH['user_group_id'])) $userGroupID = (int)$_PATCH['user_group_id'];
        if (isset($_PATCH['user_password'])) $userPassword = str_replace('\'', '"', trim($_PATCH['user_password']));
        if (isset($_PATCH['user_password_repeat'])) $userPasswordRepeat = str_replace('\'', '"', trim($_PATCH['user_password_repeat']));
        if (isset($_PATCH['user_password_old'])) $userPasswordOld = str_replace('\'', '"', trim($_PATCH['user_password_old']));

        // Проверяем, установлены ли переменные $userPassword и $userPasswordRepeat
        if (isset($userPassword) && isset($userPasswordRepeat)) {
          // Проверяем, являются ли переменные $userPassword и $userPasswordRepeat пустыми.
          // Если пустые, то игнорируем проверку пароля
          if (!empty($userPassword) && !empty($userPasswordRepeat)) {
            if ($CMSCore->configurator->get_users_password_special_symbols_status(true)) {
              $passwordRegularPattern = '[a-zA-Z0-9\_\-\!\@\#\$\%\&]+';
            } else {
              $passwordRegularPattern = '[a-zA-Z0-9\_\-]+';
            }
            
            if ($userUpdateIsAllowed) {
              if ($CMSCore->configurator->get_users_password_length_max() > 0) {
                if (strlen($userPassword) > $CMSCore->configurator->get_users_password_length_max()) {
                  $userUpdateIsAllowed = false;
      
                  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . sprintf($CMSCore->locale->get_single_value_by_key('API_USER_ERROR_INVALID_PASSWORD_LENGTH_TOO_LARGE'), $CMSCore->configurator->get_users_password_length_max());
                  $handlerStatusCode = $handlerStatusCode ?? 0;
                }
              }
            }
      
            if ($userUpdateIsAllowed) {
              if (strlen($userPassword) < $CMSCore->configurator->get_users_password_length_min()) {
                $userUpdateIsAllowed = false;
      
                $handlerMessage = $handlerMessage ?? 'API ERROR: ' . sprintf($CMSCore->locale->get_single_value_by_key('API_USER_ERROR_INVALID_PASSWORD_LENGTH_TOO_SMALL'), $CMSCore->configurator->get_users_password_length_min());
                $handlerStatusCode = $handlerStatusCode ?? 0;
              }
            }
      
            if ($userUpdateIsAllowed) {
              if (!preg_match(sprintf('/^%s$/i', $passwordRegularPattern), $userPassword)) {
                $userUpdateIsAllowed = false;
      
                $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_USER_ERROR_INVALID_PASSWORD');
                $handlerStatusCode = $handlerStatusCode ?? 0;
              }
            }

            if ($userUpdateIsAllowed) {
              if (!empty($userPassword) || !empty($userPasswordRepeat)) {
                if ($userPassword === $userPasswordRepeat) {
                  $userData['password_hash'] = User::password_hash($CMSCore, $user->get_security_hash(), $userPassword);
                } else {
                  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_USER_ERROR_INVALID_REPEAT_PASSWORD');
                  $handlerStatusCode = $handlerStatusCode ?? 0;
                  $userUpdateIsAllowed = false;
                }

                if (!$clientUser_group->permission_check($clientUser_group::PERMISSION_ADMIN_USERS_MANAGEMENT) || $userID == $clientUser->get_id()) {
                  if (isset($userPasswordOld)) {
                    if (!empty($userPasswordOld)) {
                      if (!$user->password_verify($userPasswordOld)) {
                        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_USER_ERROR_INVALID_OLD_PASSWORD');
                        $handlerStatusCode = $handlerStatusCode ?? 0;
                        $userUpdateIsAllowed = false;
                      }
                    } else {
                      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_USER_ERROR_EMPTY_OLD_PASSWORD');
                      $handlerStatusCode = $handlerStatusCode ?? 0;
                      $userUpdateIsAllowed = false;
                    }
                  } else {
                    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_INVALID_INPUT_DATA_SET');
                    $handlerStatusCode = $handlerStatusCode ?? 0;
                    $userUpdateIsAllowed = false;
                  }
                }
              }
            }
          }
        }

        if ($userUpdateIsAllowed) {
          if (isset($userLogin) && $clientUser_group->permission_check($clientUser_group::PERMISSION_ADMIN_USERS_MANAGEMENT)) {
            if ($userLogin != $user->get_login()) {
              if (!User::exists_by_login($CMSCore, $userLogin, $CMSCore->configurator->get_users_login_register_accounting_status(true))) {
                $userData['login'] = $userLogin;
              } else {
                $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_USER_ERROR_LOGIN_ALREADY_EXISTS');
                $handlerStatusCode = $handlerStatusCode ?? 0;
                $userUpdateIsAllowed = false;
              }
            }
          }
        }

        if ($userUpdateIsAllowed) {
          if (isset($userEmail)) {
            if ($userEmail !== $user->get_email()) {
              if (User::email_is_valid($CMSCore, $userEmail)) {
                if (!User::exists_by_email($CMSCore, $userEmail)) {
                  $userData['email'] = $userEmail;
                } else {
                  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_USER_ERROR_EMAIL_ALREADY_EXISTS');
                  $handlerStatusCode = $handlerStatusCode ?? 0;
                  $userUpdateIsAllowed = false;
                }
              } else {
                $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_USER_ERROR_INVALID_EMAIL');
                $handlerStatusCode = $handlerStatusCode ?? 0;
                $userUpdateIsAllowed = false;
              }
            }
          }
        }

        if ($userUpdateIsAllowed) {
          if (isset($userBirthdate)) {
            $userBirthdate = (is_numeric($userBirthdate)) ? $userBirthdate : strtotime($userBirthdate);
            
            if ($userBirthdate <= time()) {
              $userData['metadata']['birthdateUnixTimestamp'] = $userBirthdate;
            } else {
              $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_USER_ERROR_INVALID_BIRTHDATE_FUTURE');
              $handlerStatusCode = $handlerStatusCode ?? 0;
              $userUpdateIsAllowed = false;
            }
          }
        }

        if ($userUpdateIsAllowed) {
          if (isset($userName)) {
            $userData['metadata']['name'] = $userName;
          }
        }

        if ($userUpdateIsAllowed) {
          if (isset($userSurname)) {
            $userData['metadata']['surname'] = $userSurname;
          }
        }

        if ($userUpdateIsAllowed) {
          if (isset($userPatronymic)) {
            $userData['metadata']['patronymic'] = $userPatronymic;
          }
        }

        if ($userUpdateIsAllowed) {
          /**
           * Обновление данных в дополнительных полях
           * Обратите внимание, что наименование поля будет преобразовано - система будет
           * отбрасывать символ "_", а последующий регистр последующего символа будет изменять.
           * Например, если наименование поля "user_home_address",
           * то оно примет следующий вид: userHomeAddress.
           */
          foreach ($_PATCH as $name => $value) {
            if (preg_match('/^user_additional_field_([a-z0-9_]+)$/', $name, $matches, PREG_OFFSET_CAPTURE)) {
              if (!isset($userData['metadata']['additionalFields'])) $userData['metadata']['additionalFields'] = [];
              
              $fieldName = $matches[1][0];
              $fieldNameTransformed = '';

              $fieldNameParts = explode('_', $fieldName);
              for ($i = 0; $i < count($fieldNameParts); $i++) {
                $fieldNameTransformed .= ($i > 0) ? ucfirst($fieldNameParts[$i]) : $fieldNameParts[$i];
              }

              $userData['metadata']['additionalFields'][$fieldNameTransformed] = htmlspecialchars(str_replace('\'', '"', $value));
            }
          }
        }

        if ($userUpdateIsAllowed) {
          if (isset($userGroupID)) {
            if (!isset($userData)) $userData = [];
            if (!isset($userData['metadata'])) $userData['metadata'] = [];
      
            $userData['metadata']['group_id'] = $userGroupID;
          }
        }

        if ($userUpdateIsAllowed) {
          $user->update($userData);

          $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_PATCH_DATA_SUCCESS');
          $handlerStatusCode = $handlerStatusCode ?? 1;
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_UNKNOWN');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_INVALID_INPUT_DATA_SET');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_USER_ERROR_NOT_FOUND');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  }
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}

?>