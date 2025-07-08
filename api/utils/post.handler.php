<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

if (!defined('IS_NOT_HACKED')) {
  http_response_code(503);
  die('An attempted hacker attack has been detected.');
}


use \core\PHPLibrary\Client\Session as ClientSession;
use \core\PHPLibrary\EmailSender as EmailSender;
use \core\PHPLibrary\Parsedown as Parsedown;
use \core\PHPLibrary\Template as Theme;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\User as User;
use \core\PHPLibrary\SystemCore\Report as CMSReport;

if ($CMSCore->urlp->getPath(2) === 'parsedown') {
  if ($CMSCore->client->isLogged(1)) {
    $parsedown = new Parsedown();
    $handlerOutputData['parsedown'] = $parsedown->text($_POST['markdown_text']);

    $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_UTILS_PARSEDOWN_TRANSFORMED_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
}

if ($CMSCore->urlp->getPath(2) === 'registration') {
  $userAgreement = isset($_POST['user_agreement']);

  if (!$CMSCore->client->isLogged(1)) {
    if ($CMSCore->configurator->getDatabaseEntryValue('security_allowed_users_registration_status') == 'on') {
      if ($userAgreement) {
        if (isset($_POST['user_login']) && isset($_POST['user_email']) && isset($_POST['user_password']) && isset($_POST['user_password_repeat'])) {
          $errorIsDetected = false;
          $userLogin = trim($_POST['user_login']);
          $userPassword = trim($_POST['user_password']);

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
          
          // Проверка: включен ли черный список логинов
          if ($CMSCore->configurator->getUsersLoginsBlacklistStatus(true)) {
            $loginsBlacklist = $CMSCore->configurator->getUsersLoginsBlacklist(true);

            foreach ($loginsBlacklist as $login) {
              if ($CMSCore->configurator->getUsersLoginRegisterAccountingStatus(true)) {
                $loginPattern = '/^' . $userLogin . '$/';

                if (preg_match($loginPattern, $login)) {
                  $errorIsDetected = true;

                  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_LOGIN_EXISTS_IN_BLACKLIST');
                  $handlerStatusCode = $handlerStatusCode ?? 0;
                }
              } else {
                $loginPattern = '/^' . $userLogin . '$/i';

                if (preg_match($loginPattern, $login)) {
                  $errorIsDetected = true;

                  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_LOGIN_EXISTS_IN_BLACKLIST');
                  $handlerStatusCode = $handlerStatusCode ?? 0;
                }
              }
            }
          } else {
            if ($CMSCore->configurator->getUsersLoginRegisterAccountingStatus(true)) {
              $loginPattern = '/^' . $userLogin . '$/';

              if (!preg_match($loginPattern, $userLogin)) {
                $errorIsDetected = true;

                $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_INVALID_LOGIN');
                $handlerStatusCode = $handlerStatusCode ?? 0;
              }
            } else {
              $loginPattern = '/^' . $userLogin . '$/i';

              if (!preg_match($loginPattern, $userLogin)) {
                $errorIsDetected = true;

                $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_INVALID_LOGIN');
                $handlerStatusCode = $handlerStatusCode ?? 0;
              }
            }
          }

          if (!$errorIsDetected) {
            $usersLoginLengthMax = $CMSCore->configurator->getUsersLoginLengthMax();

            if ($usersLoginLengthMax > 0) {
              if (strlen($userLogin) > $usersLoginLengthMax) {
                $errorIsDetected = true;

                $handlerMessage = $handlerMessage ?? 'API ERROR: ' . sprintf($CMSCore->locale->getSingleValueByKey('API_USER_ERROR_INVALID_LOGIN_LENGTH_TOO_LARGE'), $usersLoginLengthMax);
                $handlerStatusCode = $handlerStatusCode ?? 0;
              }
            }
          }

          if (!$errorIsDetected) {
            $usersLoginLengthMin = $CMSCore->configurator->getUsersLoginLengthMin();

            if (strlen($userLogin) < $usersLoginLengthMin) {
              $errorIsDetected = true;

              $handlerMessage = $handlerMessage ?? 'API ERROR: ' . sprintf($CMSCore->locale->getSingleValueByKey('API_USER_ERROR_INVALID_LOGIN_LENGTH_TOO_SMALL'), $usersLoginLengthMin);
              $handlerStatusCode = $handlerStatusCode ?? 0;
            }
          }

          if (!$errorIsDetected) {
            $usersPasswordLengthMax = $CMSCore->configurator->getUsersPasswordLengthMax();

            if ($usersPasswordLengthMax > 0) {
              if (strlen($userPassword) > $usersPasswordLengthMax) {
                $errorIsDetected = true;

                $handlerMessage = $handlerMessage ?? 'API ERROR: ' . sprintf($CMSCore->locale->getSingleValueByKey('API_USER_ERROR_INVALID_PASSWORD_LENGTH_TOO_LARGE'), $usersPasswordLengthMax);
                $handlerStatusCode = $handlerStatusCode ?? 0;
              }
            }
          }

          if (!$errorIsDetected) {
            $usersPasswordLengthMin = $CMSCore->configurator->getUsersPasswordLengthMin();

            if (strlen($userPassword) < $usersPasswordLengthMin) {
              $errorIsDetected = true;

              $handlerMessage = $handlerMessage ?? 'API ERROR: ' . sprintf($CMSCore->locale->getSingleValueByKey('API_USER_ERROR_INVALID_PASSWORD_LENGTH_TOO_SMALL'), $usersPasswordLengthMin);
              $handlerStatusCode = $handlerStatusCode ?? 0;
            }
          }

          if (!$errorIsDetected) {
            if (!preg_match(sprintf('/^%s$/i', $passwordRegularPattern), $userPassword)) {
              $errorIsDetected = true;

              $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_USER_ERROR_INVALID_PASSWORD');
              $handlerStatusCode = $handlerStatusCode ?? 0;
            }
          }

          if (!$errorIsDetected) {
            $userEmail = trim($_POST['user_email']);
            $userPasswordRepeat = trim($_POST['user_password_repeat']);

            $userEmailPattern = '/^[\w\-\.]{1,30}@([\w\-]{1,63}\.){1,2}[\w\-]{2,4}$/i';

            if (preg_match($userEmailPattern, $userEmail)) {
              if ($userPassword === $userPasswordRepeat) {
                if (!User::existsByLogin($CMSCore, $userLogin, $CMSCore->configurator->getUsersLoginRegisterAccountingStatus(true))) {
                  if (!User::existsByEmail($CMSCore, $userEmail)) {
                    $allowedEmails = [];

                    if ($CMSCore->configurator->existsDatabaseEntryValue('security_allowed_emails')) {
                      $allowedEmails = $CMSCore->configurator->getDatabaseEntryValue('security_allowed_emails');
                      $allowedEmails = json_decode($allowedEmails, true);
                    }

                    if ($CMSCore->configurator->existsDatabaseEntryValue('security_allowed_emails_status')) {
                      $allowedEmailsStatus = $CMSCore->configurator->getDatabaseEntryValue('security_allowed_emails_status');
                    } else {
                      $allowedEmailsStatus = 'off';
                    }
                    
                    $userEmailExploded = explode('@', $userEmail);

                    if (empty($allowedEmails) || in_array($userEmailExploded[1], $allowedEmails) || $allowedEmailsStatus === 'off') {
                      $user = User::create($CMSCore, $userLogin, $userEmail, $userPassword);
                      
                      if ($user !== null) {
                        $themeBaseName = $CMSCore->configurator->existsDatabaseEntryValue('base_template') ? $CMSCore->configurator->getDatabaseEntryValue('base_template') : 'default';

                        $theme = new Theme($CMSCore, $themeBaseName);
                        $registrationSubmit = $user->createRegistrationSubmit();

                        if (is_array($registrationSubmit)) {
                          $siteTitle = empty($CMSCore->configurator->getMetaTitle()) ? $CMSCore->configurator->getSiteTitle() : $CMSCore->configurator->getMetaTitle();

                          $emailSender = new EmailSender($CMSCore);
                          $emailSenderSystemSenderEmail = EmailSender::getSystemSenderEmail($CMSCore);
                          $emailSender->setFromUser($siteTitle, $emailSenderSystemSenderEmail);
                          $emailSender->setToUserEmail($userEmail);
                          $emailSender->addHeader(sprintf('From: %s <%s>', $siteTitle, $emailSenderSystemSenderEmail));
                          $emailSender->addHeader(sprintf("\r\nX-Mailer: PHP/%s", phpversion()));
                          $emailSender->addHeader("\r\nMIME-Version: 1.0");
                          $emailSender->addHeader("\r\nContent-type: text/html; charset=UTF-8");

                          $emailSender->setSubject($CMSCore->locale->getSingleValueByKey('API_UTILS_USER_REGISTRATION_EMAIL_SUBJECT'));
                          $emailSender->setContent(ThemeCollector::assemblyFileContent($theme, 'templates/email/default.tpl', [
                            'EMAIL_TITLE' => $CMSCore->locale->getSingleValueByKey('API_UTILS_USER_REGISTRATION_EMAIL_TITLE'),
                            'EMAIL_CONTENT' => sprintf($CMSCore->locale->getSingleValueByKey('API_UTILS_USER_REGISTRATION_EMAIL_CONTENT'), $userLogin, sprintf('%s/registration?submit=%s', $CMSCore->getSiteURL(), $registrationSubmit['submitToken']), sprintf('%s/registration?refusal=%s', $CMSCore->getSiteURL(), $registrationSubmit['refusalToken'])),
                            'EMAIL_COPYRIGHT' => $CMSCore->locale->getSingleValueByKey('API_UTILS_USER_REGISTRATION_EMAIL_COPYRIGHT')
                          ]));

                          $emailSender->send();
                          
                          $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_UTILS_USER_REGISTRATION_SENDED_SUCCESS');
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
                      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_UTILS_USER_REGISTRATION_ERROR_EMAIL_IS_NOT_ALLOWED');
                      $handlerStatusCode = $handlerStatusCode ?? 0;
                    }
                  } else {
                    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_UTILS_USER_REGISTRATION_ERROR_EMAIL_ALREADY_EXISTS');
                    $handlerStatusCode = $handlerStatusCode ?? 0;
                  }
                } else {
                  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_UTILS_USER_REGISTRATION_ERROR_LOGIN_ALREADY_EXISTS');
                  $handlerStatusCode = $handlerStatusCode ?? 0;
                }
              } else {
                $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_UTILS_USER_REGISTRATION_ERROR_INVALID_REPEAT_PASSWORD');
                $handlerStatusCode = $handlerStatusCode ?? 0;
              }
            } else {
              $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_UTILS_USER_REGISTRATION_ERROR_INVALID_EMAIL');
              $handlerStatusCode = $handlerStatusCode ?? 0;
            }
          } else {
            $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_INVALID_INPUT_DATA_SET');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_UTILS_USER_REGISTRATION_ERROR_AGREEMENT');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_UTILS_USER_REGISTRATION_ERROR_DISABLED');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_UTILS_USER_REGISTRATION_ERRAPI_UTILS_USER_REGISTRATION_ERROR_AUTHORIZATION_ALREADYOR_DISABLED');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
}

if ($CMSCore->urlp->getPath(2) === 'authorization' && $CMSCore->urlp->getParam('method') === 'base') {
  if (!$CMSCore->client->isLogged(1)) {
    $userLogin = trim($_POST['user_login']) ?? null;
    $userPassword = trim($_POST['user_password']) ?? null;
    $userRememberMe = isset($_POST['user_remember_me']);

    if ($userLogin !== null && $userPassword !== null) {
      $user = User::getByLogin($CMSCore, $userLogin);

      if ($user !== null) {
        // Инициализация данных пользователя
        $user->initData(['passwordHash', 'securityHash']);
        
        // Проверяем правильность пароля
        if ($user->passwordVerify($userPassword)) {
          /** @var string $userIP */
          $userIP = $_SERVER['REMOTE_ADDR'];
          /** @var string $userToken */
          $userToken = ClientSession::generateToken();

          if (!ClientSession::existsByIPAndUserID($CMSCore, $userIP, $user->getID(), 1)) {
            /** @var ClientSession|null $userSession */
            $userSession = ClientSession::create($CMSCore, [
              'userID' => $user->getID(),
              'token' => $userToken,
              'userIP' => $userIP,
              'typeID' => 1
            ]);
          } else {
            $userSession = ClientSession::getByIPAndUserID($CMSCore, $userIP, $user->getID(), 1);
            $userSession->update([]);
          }

          if (!is_null($userSession)) {
            $userSession->initData(['updatedUnixTimestamp', 'token']);
            $userSessionExpires = $userSession->getUpdatedUnixTimestamp() + $CMSCore->configurator->get('sessionExpires');

            $userSessionIsSecure = (bool) $CMSCore->configurator->get('SSLIsEnabled');

            setcookie('_grv_utoken', $userSession->getToken(), [
              'expires' => ($userRememberMe) ? $userSessionExpires : 0,
              'path' => '/',
              'domain' => $CMSCore->configurator->get('domainCookies'),
              'secure' => $userSessionIsSecure,
              'httponly' => true
            ]);

            $handlerOutputData['reload'] = true;

            /** @var string $handlerMessage Сообщение обработчика */
            $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_UTILS_USER_AUTHORIZATION_SUCCESS');
            $handlerStatusCode = $handlerStatusCode ?? 1;
          } else {
            /** @var string $handlerMessage Сообщение обработчика */
            $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }

        } else {
          /** @var string $handlerMessage Сообщение обработчика */
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_UTILS_USER_AUTHORIZATION_ERROR_USER_NOT_FOUND');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      } else {
        /** @var string $handlerMessage Сообщение обработчика */
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_UTILS_USER_AUTHORIZATION_ERROR_USER_NOT_FOUND');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_INVALID_INPUT_DATA_SET');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_UTILS_USER_AUTHORIZATION_ERROR_AUTHORIZATION_ALREADY');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
}

if ($CMSCore->urlp->getPath(2) === 'authorization' && $CMSCore->urlp->getParam('method') === 'admin') {
  if (!$CMSCore->client->isLogged(2)) {
    $userLogin = trim($_POST['user_login']) ?? null;
    $userPassword = trim($_POST['user_password']) ?? null;
    $userRememberMe = isset($_POST['user_remember_me']);
    $adminAccessCodes = $_POST['admin_access-code'] ?? [];

    if ($userLogin !== null && !$userPassword !== null && !empty($adminAccessCodes)) {
      $user = User::getByLogin($CMSCore, $userLogin);

      if ($user !== null) {
        // Инициализация данных пользователя
        $user->initData(['passwordHash', 'securityHash', 'metadata']);
        $userGroup = $user->getGroup();
        $userGroup->initData(['permissions']);
        
        if ($userGroup->permissionCheck($userGroup::PERMISSION_ADMIN_PANEL_AUTH)) {
          $adminAccessCodesIsValid = true;
          foreach ($adminAccessCodes as $index => $code) {
            switch ($index) {
              case 0: $codeChar = 'a'; break;
              case 1: $codeChar = 'b'; break;
              case 2: $codeChar = 'c'; break;
              case 3: $codeChar = 'd'; break;
            }

            if (!password_verify($code, $CMSCore->configurator->getDatabaseEntryValue('security_admin_code_' . $codeChar))) {
              $adminAccessCodesIsValid = false; break;
            }
          }

          // Проверяем правильность пароля
          if ($user->passwordVerify($userPassword) && $adminAccessCodesIsValid) {
            /** @var string $userIP */
            $userIP = $_SERVER['REMOTE_ADDR'];
            /** @var string $userToken */
            $userTokenBase = ClientSession::generateToken();
            $userTokenAdmin = ClientSession::generateToken();

            $userSessionBase = null;
            $userSessionAdmin = null;

            // Если сессия не была найдена, то создаем новую.
            if (!ClientSession::existsByIPAndUserID($CMSCore, $userIP, $user->getID(), 1)) {
              /** @var ClientSession|null $userSession */
              $userSessionBase = ClientSession::create($CMSCore, [
                'userID' => $user->getID(),
                'token' => $userTokenBase,
                'userIP' => $userIP,
                'typeID' => 1
              ]);
            } else {
              $userSessionBase = ClientSession::getByIPAndUserID($CMSCore, $userIP, $user->getID(), 1);
              $userSessionBase->update([]);
            }

            // Если сессия не была найдена, то создаем новую.
            if (!ClientSession::existsByIPAndUserID($CMSCore, $userIP, $user->getID(), 2)) {
              /** @var ClientSession|null $userSession */
              $userSessionAdmin = ClientSession::create($CMSCore, [
                'userID' => $user->getID(),
                'token' => $userTokenAdmin,
                'userIP' => $userIP,
                'typeID' => 2
              ]);
            } else {
              $userSessionAdmin = ClientSession::getByIPAndUserID($CMSCore, $userIP, $user->getID(), 2);
              $userSessionAdmin->update([]);
            }

            if (!is_null($userSessionBase)) {
              $userSessionBase->initData(['updatedUnixTimestamp', 'token']);
              $userSessionBaseExpires = $userSessionBase->getUpdatedUnixTimestamp() + $CMSCore->configurator->get('sessionExpires');

              $CMSCore->client::createCookie($CMSCore, '_grv_utoken', $userSessionBase, $userRememberMe ? $userSessionBaseExpires : 0);
            }

            if (!is_null($userSessionAdmin)) {
              $userSessionAdmin->initData(['updatedUnixTimestamp', 'token']);
              $userSessionAdmin_expires = $userSessionAdmin->getUpdatedUnixTimestamp() + $CMSCore->configurator->get('sessionExpires');

              $CMSCore->client::createCookie($CMSCore, '_grv_atoken', $userSessionAdmin, $userRememberMe ? $userSessionAdmin_expires : 0);

              $CMSReport = CMSReport::create($CMSCore, CMSReport::REPORT_TYPE_ID_AP_AUTHORIZATION_SUCCESS, [
                'clientIP' => $CMSCore->client->getIPAddress(),
                'date' => date('Y/m/d H:i:s', time())
              ]);

              $handlerOutputData['reload'] = true;

              /** @var string $handlerMessage Сообщение обработчика */
              $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_UTILS_USER_AUTHORIZATION_SUCCESS');
              $handlerStatusCode = $handlerStatusCode ?? 1;
            } else {
              /** @var string $handlerMessage Сообщение обработчика */
              $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
              $handlerStatusCode = $handlerStatusCode ?? 0;
            }

          } else {
            $CMSReport = CMSReport::create($CMSCore, CMSReport::REPORT_TYPE_ID_AP_AUTHORIZATION_FAIL, [
              'clientIP' => $CMSCore->client->getIPAddress(),
              'date' => date('Y/m/d H:i:s', time())
            ]);

            /** @var string $handlerMessage Сообщение обработчика */
            $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_UTILS_USER_AUTHORIZATION_ERROR_USER_NOT_FOUND');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_DONT_HAVE_PERMISSIONS');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      } else {
        $CMSReport = CMSReport::create($CMSCore, CMSReport::REPORT_TYPE_ID_AP_AUTHORIZATION_FAIL, [
          'clientIP' => $CMSCore->client->getIPAddress(),
          'date' => date('Y/m/d H:i:s', time())
        ]);
        
        /** @var string $handlerMessage Сообщение обработчика */
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_UTILS_USER_AUTHORIZATION_ERROR_USER_NOT_FOUND');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }

    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_INVALID_INPUT_DATA_SET');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_UTILS_USER_AUTHORIZATION_ERROR_AUTHORIZATION_ALREADY');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
}