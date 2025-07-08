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
use \core\PHPLibrary\UserGroup as UserGroup;

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_ADMIN_USERS_MANAGEMENT)) {
    $userCreationAllowed = true;

    if ($CMSCore->configurator->getUsersLoginSpecialSymbolsStatus(true)) {
      $loginRegularPattern = '[a-zA-Z0-9\_\-\!\@\#\$\%\&]+';
    } else {
      $loginRegularPattern = '[a-zA-Z0-9\_\-]+';
    }

    if ($CMSCore->configurator->getUsersPasswordSpecialSymbolsStatus(true)) {
      $passwordRegularPattern = '[a-zA-Z0-9\_\-\!\@\#\$\%\&]+';
    } else {
      $passwordRegularPattern = '[a-zA-Z0-9\_\-]+';
    }

    $userLogin = isset($_PUT['user_login']) ? htmlspecialchars(str_replace('\'', '"', $_PUT['user_login'])) : '';
    $userEmail = isset($_PUT['user_email']) ? str_replace('\'', '"', $_PUT['user_email']) : '';
    $userName = isset($_PUT['user_name']) ? htmlspecialchars(str_replace('\'', '"', $_PUT['user_name'])) : '';
    $userSurname = isset($_PUT['user_surname']) ? htmlspecialchars(str_replace('\'', '"', $_PUT['user_surname'])) : '';
    $userPatronymic = isset($_PUT['user_patronymic']) ? htmlspecialchars(str_replace('\'', '"', $_PUT['user_patronymic'])) : '';
    $userBirthdate = isset($_PUT['user_birthdate']) ? $_PUT['user_birthdate'] : 0;
    $userGroupID = isset($_PUT['user_group_id']) ? (int) $_PUT['user_group_id'] : 4;
    $userPassword = isset($_PUT['user_password']) ? str_replace('\'', '"', $_PUT['user_password']) : '';
    $userPasswordRepeat = isset($_PUT['user_password_repeat']) ? str_replace('\'', '"', $_PUT['user_password_repeat']) : '';
    
    if (isset($_PUT['user_login'])) {
      if ($CMSCore->configurator->getUsersLoginsBlacklistStatus(true)) {
        $loginsBlacklist = $CMSCore->configurator->getUsersLoginsBlacklist(true);

        foreach ($loginsBlacklist as $login) {
          if ($CMSCore->configurator->getUsersLoginRegisterAccountingStatus(true)) {
            $loginPattern = '/^' . $userLogin . '$/';

            if (preg_match($loginPattern, $login)) {
              $userCreationAllowed = false;

              $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_LOGIN_EXISTS_IN_BLACKLIST');
              $handlerStatusCode = $handlerStatusCode ?? 0;
            }
          } else {
            $loginPattern = '/^' . $userLogin . '$/i';

            if (preg_match($loginPattern, $login)) {
              $userCreationAllowed = false;

              $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_LOGIN_EXISTS_IN_BLACKLIST');
              $handlerStatusCode = $handlerStatusCode ?? 0;
            }
          }
        }
      }

      if ($userCreationAllowed) {
        if ($CMSCore->configurator->getUsersLoginRegisterAccountingStatus(true)) {
          if (!preg_match(sprintf('/^%s$/i', $loginRegularPattern), $userLogin)) {
            $userCreationAllowed = false;

            $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_INVALID_LOGIN');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }
        } else {
          if (!preg_match(sprintf('/^%s$/', $loginRegularPattern), $userLogin)) {
            $userCreationAllowed = false;

            $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_INVALID_LOGIN');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }
        }
      }

      if ($userCreationAllowed) {
        if ($CMSCore->configurator->getUsersLoginLengthMax() > 0) {
          if (strlen($userLogin) > $CMSCore->configurator->getUsersLoginLengthMax()) {
            $userCreationAllowed = false;

            $handlerMessage = $handlerMessage ?? 'API ERROR: ' . sprintf($CMSCore->locale->getSingleValueByKey('API_USER_ERROR_INVALID_LOGIN_LENGTH_TOO_LARGE'), $CMSCore->configurator->getUsersLoginLengthMax());
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }
        }
      }

      if ($userCreationAllowed) {
        if (strlen($userLogin) < $CMSCore->configurator->getUsersLoginLengthMin()) {
          $userCreationAllowed = false;

          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . sprintf($CMSCore->locale->getSingleValueByKey('API_USER_ERROR_INVALID_LOGIN_LENGTH_TOO_SMALL'), $CMSCore->configurator->getUsersLoginLengthMin());
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      }
    }

    if (isset($_PUT['user_password'])) {
      if ($userCreationAllowed) {
        if ($CMSCore->configurator->getUsersPasswordLengthMax() > 0) {
          if (strlen($userPassword) > $CMSCore->configurator->getUsersPasswordLengthMax()) {
            $userCreationAllowed = false;

            $handlerMessage = $handlerMessage ?? 'API ERROR: ' . sprintf($CMSCore->locale->getSingleValueByKey('API_USER_ERROR_INVALID_PASSWORD_LENGTH_TOO_LARGE'), $CMSCore->configurator->getUsersPasswordLengthMax());
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }
        }
      }

      if ($userCreationAllowed) {
        if (strlen($userPassword) < $CMSCore->configurator->getUsersPasswordLengthMin()) {
          $userCreationAllowed = false;

          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . sprintf($CMSCore->locale->getSingleValueByKey('API_USER_ERROR_INVALID_PASSWORD_LENGTH_TOO_SMALL'), $CMSCore->configurator->getUsersPasswordLengthMin());
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      }

      if ($userCreationAllowed) {
        if (!preg_match(sprintf('/^%s$/i', $passwordRegularPattern), $userPassword)) {
          $userCreationAllowed = false;

          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_INVALID_PASSWORD');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      }
    }

    if (User::existsByLogin($CMSCore, $userLogin, $CMSCore->configurator->getUsersLoginRegisterAccountingStatus(true))) {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_LOGIN_ALREADY_EXISTS');
      $handlerStatusCode = $handlerStatusCode ?? 0;
      $userCreationAllowed = false;
    }

    if (User::existsByEmail($CMSCore, $userEmail)) {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_EMAIL_ALREADY_EXISTS');
      $handlerStatusCode = $handlerStatusCode ?? 0;
      $userCreationAllowed = false;
    }

    if (isset($userPassword) && isset($userPasswordRepeat)) {
      if (!empty($userPassword) || !empty($userPasswordRepeat)) {
        if ($userPassword != $userPasswordRepeat) {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_INVALID_REPEAT_PASSWORD');
          $handlerStatusCode = $handlerStatusCode ?? 0;
          $userCreationAllowed = false;
        }
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_INVALID_INPUT_DATA_SET');
        $handlerStatusCode = $handlerStatusCode ?? 0;
        $userCreationAllowed = false;
      }
    }

    if (isset($userEmail)) {
      if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_INVALID_EMAIL');
        $handlerStatusCode = $handlerStatusCode ?? 0;
        $userCreationAllowed = false;
      }
    }

    if (isset($userBirthdate)) {
      $userBirthdate = is_numeric($userBirthdate) ? $userBirthdate : strtotime($userBirthdate);
      
      if ($userBirthdate <= time()) {
        $userData['metadata']['birthdateUnixTimestamp'] = $userBirthdate;
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_INVALID_BIRTHDATE_FUTURE');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    }

    if (isset($userName)) {
      $userData['metadata']['name'] = $userName;
    }

    if (isset($userSurname)) {
      $userData['metadata']['surname'] = $userSurname;
    }

    if (isset($userPatronymic)) {
      $userData['metadata']['patronymic'] = $userPatronymic;
    }

    if (isset($userGroupID)) {
      if (!isset($userData)) $userData = [];
      if (!isset($userData['metadata'])) $userData['metadata'] = [];

      $userData['metadata']['groupID'] = $userGroupID;
    }

    foreach ($_PUT as $key => $value) {
      if (preg_match('/^user\_additional\_field\_([a-z0-9\_]+)$/i', $key, $key_matches, PREG_OFFSET_CAPTURE) && !empty($value)) {
        if (!isset($userData)) $userData = [];
        if (!isset($userData['metadata'])) $userData['metadata'] = [];
        if (!isset($userData['metadata']['additionalFields'])) $userData['metadata']['additionalFields'] = [];
        
        $valueNameParts = explode('_', $key_matches[1][0]);
        foreach ($valueNameParts as $index => $part) {
          if ($index > 0) {
            $valueNameParts[$index] = ucfirst($part);
          }
        }

        if (is_bool($value)) $value = (int)$value;

        $userData['metadata']['additionalFields'][implode($valueNameParts)] = htmlspecialchars(str_replace('\'', '"', $value));
      }
    }

    if ($userCreationAllowed) {
      $user = User::create($CMSCore, $userLogin, $userEmail, $userPassword);
      
      if (!is_null($user)) {
        $user->initData(['*']);
        // Подтверждение E-Mail у пользователя
        $userData['emailIsSubmitted'] = true;

        if (isset($userData)) {
          $user->update($userData);
        }

        $handlerOutputData['user'] = [];
        $handlerOutputData['user']['id'] = $user->getID();

        $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_PUT_DATA_SUCCESS');
        $handlerStatusCode = $handlerStatusCode ?? 1;
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    $handlerMessage = 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_DONT_HAVE_PERMISSIONS');
    $handlerStatusCode = 0;
  }
} else {
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}