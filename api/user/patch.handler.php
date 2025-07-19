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

if ($CMSCore->client->isLogged(1) || $CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(1);
  $clientUser->initData(['metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  $CMSConfigurator = $CMSCore->configurator;

  if (isset($_PATCH['user_id'])) {
    if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_ADMIN_USERS_MANAGEMENT) || $clientUser->getID() === (int) $_PATCH['user_id']) {
      $userID = is_numeric($_PATCH['user_id']) ? (int) $_PATCH['user_id'] : 0;

      if (User::existsByID($CMSCore, $userID)) {
        $user = new User($CMSCore, $userID);
        $user->initData(['login', 'email', 'securityHash', 'passwordHash']);

        $userData = [];

        $userUpdateIsAllowed = false;

        if (isset($_PATCH['user_is_block'])) {
          if (!isset($userData['metadata'])) $userData['metadata'] = [];
          $userData['metadata']['isBlocked'] = (int) $_PATCH['user_is_block'];
          $userUpdateIsAllowed = true;
        }

        if (isset($_PATCH['user_login'])) {
          $userLogin = trim($_PATCH['user_login']);
          $userLogin = htmlspecialchars(str_replace('\'', '"', $userLogin));

          if ($userLogin !== $user->getLogin() && !empty($userLogin)) {
            if ($CMSConfigurator->getUsersLoginEditStatus(true) && $userID === $clientUser->getID()) {
              $userUpdateIsAllowed = true;
            } else {
              if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_ADMIN_USERS_MANAGEMENT)) {
                $userUpdateIsAllowed = true;
              }
            }

            if ($userUpdateIsAllowed) {
              if ($CMSConfigurator->getUsersLoginsBlacklistStatus(true)) {
                $loginsBlacklist = $CMSConfigurator->getUsersLoginsBlacklist(true);

                foreach ($loginsBlacklist as $loginBlacklist) {
                  if ($CMSConfigurator->getUsersLoginRegisterAccountingStatus(true)) {
                    $loginPattern = '/^' . $loginBlacklist . '$/';

                    if (preg_match($loginPattern, $userLogin)) {
                      $userUpdateIsAllowed = false;

                      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_LOGIN_EXISTS_IN_BLACKLIST');
                      $handlerStatusCode = $handlerStatusCode ?? 0;
                    }
                  } else {
                    $loginPattern = '/^' . $loginBlacklist . '$/i';

                    if (preg_match($loginPattern, $userLogin)) {
                      $userUpdateIsAllowed = false;

                      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_LOGIN_EXISTS_IN_BLACKLIST');
                      $handlerStatusCode = $handlerStatusCode ?? 0;
                    }
                  }
                }
              }
            }

            if ($userUpdateIsAllowed) {
              if ($CMSConfigurator->getUsersLoginSpecialSymbolsStatus(true)) {
                $loginPattern = '[a-zA-Z0-9\_\-\!\@\#\$\%\&]+';
              } else {
                $loginPattern = '[a-zA-Z0-9\_\-]+';
              }

              if ($CMSConfigurator->getUsersLoginRegisterAccountingStatus(true)) {
                $loginPattern = '/^' . $loginPattern . '$/';

                if (!preg_match($loginPattern, $userLogin)) {
                  $userUpdateIsAllowed = false;
      
                  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_INVALID_LOGIN');
                  $handlerStatusCode = $handlerStatusCode ?? 0;
                }
              } else {
                $loginPattern = '/^' . $loginPattern . '$/i';

                if (!preg_match($loginPattern, $userLogin)) {
                  $userUpdateIsAllowed = false;
      
                  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_INVALID_LOGIN');
                  $handlerStatusCode = $handlerStatusCode ?? 0;
                }
              }
            }

            if ($userUpdateIsAllowed) {
              if ($CMSConfigurator->getUsersLoginLengthMax() > 0) {
                if (strlen($userLogin) > $CMSConfigurator->getUsersLoginLengthMax()) {
                  $userUpdateIsAllowed = false;
      
                  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . sprintf($CMSCore->locale->getSingleValueByKey('API_USER_ERROR_INVALID_LOGIN_LENGTH_TOO_LARGE'), $CMSConfigurator->getUsersLoginLengthMax());
                  $handlerStatusCode = $handlerStatusCode ?? 0;
                }
              }
            }
      
            if ($userUpdateIsAllowed) {
              if (strlen($userLogin) < $CMSConfigurator->getUsersLoginLengthMin()) {
                $userUpdateIsAllowed = false;
      
                $handlerMessage = $handlerMessage ?? 'API ERROR: ' . sprintf($CMSCore->locale->getSingleValueByKey('API_USER_ERROR_INVALID_LOGIN_LENGTH_TOO_SMALL'), $CMSConfigurator->getUsersLoginLengthMin());
                $handlerStatusCode = $handlerStatusCode ?? 0;
              }
            }
          }

          if ($userLogin === $user->getLogin()) {
            $userUpdateIsAllowed = true;
          }
        } else {
          $userUpdateIsAllowed = true;
        }

        if (isset($_PATCH['user_email'])) $userEmail = str_replace('\'', '"', trim($_PATCH['user_email']));
        if (isset($_PATCH['user_name'])) $userName = htmlspecialchars(str_replace('\'', '"', trim($_PATCH['user_name'])));
        if (isset($_PATCH['user_surname'])) $userSurname = htmlspecialchars(str_replace('\'', '"', trim($_PATCH['user_surname'])));
        if (isset($_PATCH['user_patronymic'])) $userPatronymic = htmlspecialchars(str_replace('\'', '"', trim($_PATCH['user_patronymic'])));
        if (isset($_PATCH['user_birthdate'])) $userBirthdate = strtotime($_PATCH['user_birthdate']);
        if (isset($_PATCH['user_group_id'])) $userGroupID = (int) $_PATCH['user_group_id'];
        if (isset($_PATCH['user_password'])) $userPassword = str_replace('\'', '"', trim($_PATCH['user_password']));
        if (isset($_PATCH['user_password_repeat'])) $userPasswordRepeat = str_replace('\'', '"', trim($_PATCH['user_password_repeat']));
        if (isset($_PATCH['user_password_old'])) $userPasswordOld = str_replace('\'', '"', trim($_PATCH['user_password_old']));

        // Проверяем, установлены ли переменные $userPassword и $userPasswordRepeat
        if (isset($userPassword) && isset($userPasswordRepeat)) {
          // Проверяем, являются ли переменные $userPassword и $userPasswordRepeat пустыми.
          // Если пустые, то игнорируем проверку пароля
          if (!empty($userPassword) && !empty($userPasswordRepeat)) {
            if ($CMSConfigurator->getUsersPasswordSpecialSymbolsStatus(true)) {
              $passwordRegularPattern = '[a-zA-Z0-9\_\-\!\@\#\$\%\&]+';
            } else {
              $passwordRegularPattern = '[a-zA-Z0-9\_\-]+';
            }
            
            if ($userUpdateIsAllowed) {
              if ($CMSConfigurator->getUsersPasswordLengthMax() > 0) {
                if (strlen($userPassword) > $CMSConfigurator->getUsersPasswordLengthMax()) {
                  $userUpdateIsAllowed = false;
      
                  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . sprintf($CMSCore->locale->getSingleValueByKey('API_USER_ERROR_INVALID_PASSWORD_LENGTH_TOO_LARGE'), $CMSConfigurator->getUsersPasswordLengthMax());
                  $handlerStatusCode = $handlerStatusCode ?? 0;
                }
              }
            }
      
            if ($userUpdateIsAllowed) {
              if (strlen($userPassword) < $CMSConfigurator->getUsersPasswordLengthMin()) {
                $userUpdateIsAllowed = false;
      
                $handlerMessage = $handlerMessage ?? 'API ERROR: ' . sprintf($CMSCore->locale->getSingleValueByKey('API_USER_ERROR_INVALID_PASSWORD_LENGTH_TOO_SMALL'), $CMSConfigurator->getUsersPasswordLengthMin());
                $handlerStatusCode = $handlerStatusCode ?? 0;
              }
            }
      
            if ($userUpdateIsAllowed) {
              if (!preg_match(sprintf('/^%s$/i', $passwordRegularPattern), $userPassword)) {
                $userUpdateIsAllowed = false;
      
                $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_INVALID_PASSWORD');
                $handlerStatusCode = $handlerStatusCode ?? 0;
              }
            }

            if ($userUpdateIsAllowed) {
              if (!empty($userPassword) || !empty($userPasswordRepeat)) {
                if ($userPassword === $userPasswordRepeat) {
                  $userData['passwordHash'] = User::passwordHash($CMSCore, $user->getSecurityHash(), $userPassword);
                } else {
                  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_INVALID_REPEAT_PASSWORD');
                  $handlerStatusCode = $handlerStatusCode ?? 0;
                  $userUpdateIsAllowed = false;
                }

                if (!$clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_ADMIN_USERS_MANAGEMENT) || $userID == $clientUser->getID()) {
                  if (isset($userPasswordOld)) {
                    if (!empty($userPasswordOld)) {
                      if (!$user->passwordVerify($userPasswordOld)) {
                        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_INVALID_OLD_PASSWORD');
                        $handlerStatusCode = $handlerStatusCode ?? 0;
                        $userUpdateIsAllowed = false;
                      }
                    } else {
                      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_EMPTY_OLD_PASSWORD');
                      $handlerStatusCode = $handlerStatusCode ?? 0;
                      $userUpdateIsAllowed = false;
                    }
                  } else {
                    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_INVALID_INPUT_DATA_SET');
                    $handlerStatusCode = $handlerStatusCode ?? 0;
                    $userUpdateIsAllowed = false;
                  }
                }
              }
            }
          }
        }

        if ($userUpdateIsAllowed) {
          if (isset($userLogin) && $clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_ADMIN_USERS_MANAGEMENT)) {
            if ($userLogin !== $user->getLogin()) {
              if (!User::existsByLogin($CMSCore, $userLogin, $CMSConfigurator->getUsersLoginRegisterAccountingStatus(true))) {
                $userData['login'] = $userLogin;
              } else {
                $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_LOGIN_ALREADY_EXISTS');
                $handlerStatusCode = $handlerStatusCode ?? 0;
                $userUpdateIsAllowed = false;
              }
            }
          }
        }

        if ($userUpdateIsAllowed) {
          if (isset($userEmail)) {
            if ($userEmail !== $user->getEmail()) {
              if (User::emailIIsValid($CMSCore, $userEmail)) {
                if (!User::existsByEmail($CMSCore, $userEmail)) {
                  $userData['email'] = $userEmail;
                } else {
                  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_EMAIL_ALREADY_EXISTS');
                  $handlerStatusCode = $handlerStatusCode ?? 0;
                  $userUpdateIsAllowed = false;
                }
              } else {
                $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_INVALID_EMAIL');
                $handlerStatusCode = $handlerStatusCode ?? 0;
                $userUpdateIsAllowed = false;
              }
            }
          }
        }

        if ($userUpdateIsAllowed) {
          if (isset($userBirthdate)) {
            $userBirthdate = is_numeric($userBirthdate) ? $userBirthdate : strtotime($userBirthdate);
            
            if ($userBirthdate <= time()) {
              $userData['metadata']['birthdateUnixTimestamp'] = $userBirthdate;
            } else {
              $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_INVALID_BIRTHDATE_FUTURE');
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
      
            $userData['metadata']['groupID'] = $userGroupID;
          }
        }

        if ($userUpdateIsAllowed) {
          $user->update($userData);

          $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_PATCH_DATA_SUCCESS');
          $handlerStatusCode = $handlerStatusCode ?? 1;
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_NOT_FOUND');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_DONT_HAVE_PERMISSIONS');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_INVALID_INPUT_DATA_SET');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}