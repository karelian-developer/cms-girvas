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

use \core\PHPLibrary\Parsedown as Parsedown;
use \core\PHPLibrary\User as User;
use \core\PHPLibrary\Client\Session as ClientSession;
use \core\PHPLibrary\SystemCore\Report as CMSReport;

if ($CMSCore->urlp->get_path(2) === 'parsedown') {
  if ($CMSCore->client->is_logged(1)) {
    $parsedown = new Parsedown();
    $handlerOutputData['parsedown'] = $parsedown->text($_POST['markdown_text']);

    $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_UTILS_PARSEDOWN_TRANSFORMED_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
}

if ($CMSCore->urlp->get_path(2) === 'registration') {
  $userAgreement = isset($_POST['user_agreement']);

  if (!$CMSCore->client->is_logged(1)) {
    if ($CMSCore->configurator->get_database_entry_value('security_allowed_users_registration_status') == 'on') {
      if ($userAgreement) {
        if (isset($_POST['user_login']) && isset($_POST['user_email']) && isset($_POST['user_password']) && isset($_POST['user_password_repeat'])) {
          $errorIsDetected = false;
          $userLogin = trim($_POST['user_login']);
          $userPassword = trim($_POST['user_password']);

          if ($CMSCore->configurator->get_users_login_special_symbols_status(true)) {
            $loginRegularPattern = '[a-zA-Z0-9\_\-\!\@\#\$\%\&]+';
          } else {
            $loginRegularPattern = '[a-zA-Z0-9\_\-]+';
          }

          if ($CMSCore->configurator->get_users_password_special_symbols_status(true)) {
            $passwordRegularPattern = '[a-zA-Z0-9\_\-\!\@\#\$\%\&]+';
          } else {
            $passwordRegularPattern = '[a-zA-Z0-9\_\-]+';
          }
          
          // Проверка: включен ли черный список логинов
          if ($CMSCore->configurator->get_users_logins_blacklist_status(true)) {
            $loginsBlacklist = $CMSCore->configurator->get_users_logins_blacklist(true);

            foreach ($loginsBlacklist as $login) {
              if ($CMSCore->configurator->get_users_login_register_accounting_status(true)) {
                $loginPattern = '/^' . $userLogin . '$/';

                if (preg_match($loginPattern, $login)) {
                  $errorIsDetected = true;

                  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_USER_ERROR_LOGIN_EXISTS_IN_BLACKLIST');
                  $handlerStatusCode = $handlerStatusCode ?? 0;
                }
              } else {
                $loginPattern = '/^' . $userLogin . '$/i';

                if (preg_match($loginPattern, $login)) {
                  $errorIsDetected = true;

                  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_USER_ERROR_LOGIN_EXISTS_IN_BLACKLIST');
                  $handlerStatusCode = $handlerStatusCode ?? 0;
                }
              }
            }
          } else {
            if ($CMSCore->configurator->get_users_login_register_accounting_status(true)) {
              $loginPattern = '/^' . $userLogin . '$/';

              if (!preg_match($loginPattern, $userLogin)) {
                $errorIsDetected = true;

                $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_USER_ERROR_INVALID_LOGIN');
                $handlerStatusCode = $handlerStatusCode ?? 0;
              }
            } else {
              $loginPattern = '/^' . $userLogin . '$/i';

              if (!preg_match($loginPattern, $userLogin)) {
                $errorIsDetected = true;

                $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_USER_ERROR_INVALID_LOGIN');
                $handlerStatusCode = $handlerStatusCode ?? 0;
              }
            }
          }

          if (!$errorIsDetected) {
            if ($CMSCore->configurator->get_users_login_length_max() > 0) {
              if (strlen($userLogin) > $CMSCore->configurator->get_users_login_length_max()) {
                $errorIsDetected = true;

                $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_USER_ERROR_INVALID_LOGIN_LENGTH_TOO_LARGE');
                $handlerStatusCode = $handlerStatusCode ?? 0;
              }
            }
          }

          if (!$errorIsDetected) {
            if (strlen($userLogin) < $CMSCore->configurator->get_users_login_length_min()) {
              $errorIsDetected = true;

              $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_USER_ERROR_INVALID_LOGIN_LENGTH_TOO_SMALL');
              $handlerStatusCode = $handlerStatusCode ?? 0;
            }
          }

          if (!$errorIsDetected) {
            if ($CMSCore->configurator->get_users_password_length_max() > 0) {
              if (strlen($userPassword) > $CMSCore->configurator->get_users_password_length_max()) {
                $errorIsDetected = true;

                $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_USER_ERROR_INVALID_PASSWORD_LENGTH_TOO_LARGE');
                $handlerStatusCode = $handlerStatusCode ?? 0;
              }
            }
          }

          if (!$errorIsDetected) {
            if (strlen($userPassword) < $CMSCore->configurator->get_users_password_length_min()) {
              $errorIsDetected = true;

              $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_USER_ERROR_INVALID_PASSWORD_LENGTH_TOO_SMALL');
              $handlerStatusCode = $handlerStatusCode ?? 0;
            }
          }

          if (!$errorIsDetected) {
            if (!preg_match(sprintf('/^%s$/i', $passwordRegularPattern), $userPassword)) {
              $errorIsDetected = true;

              $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_USER_ERROR_INVALID_PASSWORD');
              $handlerStatusCode = $handlerStatusCode ?? 0;
            }
          }

          if (!$errorIsDetected) {
            $userEmail = trim($_POST['user_email']);
            $userPasswordRepeat = trim($$_POST['user_password_repeat']);

            $userEmailPattern = '/^[\w\-\.]{1,30}@([\w\-]{1,63}\.){1,2}[\w\-]{2,4}$/i';

            if (preg_match($userEmailPattern, $userEmail)) {
              if ($userPassword === $userPasswordRepeat) {
                if (!User::exists_by_login($CMSCore, $userLogin, $CMSCore->configurator->get_users_login_register_accounting_status(true))) {
                  if (!User::exists_by_email($CMSCore, $userEmail)) {
                    $allowedEmails = [];

                    if ($CMSCore->configurator->exists_database_entry_value('security_allowed_emails')) {
                      $allowedEmails = $CMSCore->configurator->get_database_entry_value('security_allowed_emails');
                      $allowedEmails = json_decode($allowedEmails, true);
                    }

                    if ($CMSCore->configurator->exists_database_entry_value('security_allowed_emails_status')) {
                      $allowedEmailsStatus = $CMSCore->configurator->get_database_entry_value('security_allowed_emails_status');
                    } else {
                      $allowedEmailsStatus = 'off';
                    }
                    
                    $userEmailExploded = explode('@', $userEmail);

                    if (empty($allowedEmails) || in_array($userEmailExploded[1], $allowedEmails) || $allowedEmailsStatus === 'off') {
                      $user = User::create($CMSCore, $userLogin, $userEmail, $userPassword);
                      
                      if (!is_null($user)) {
                        $themeBaseName = ($CMSCore->configurator->exists_database_entry_value('base_template')) ? $CMSCore->configurator->get_database_entry_value('base_template') : 'default';

                        $theme = new \core\PHPLibrary\Template($CMSCore, $themeBaseName);
                        $registrationSubmit = $user->create_registration_submit();

                        if (is_array($registrationSubmit)) {
                          $siteTitle = empty($CMSCore->configurator->get_meta_title()) ? $CMSCore->configurator->get_site_title() : $CMSCore->configurator->get_meta_title();

                          $emailSender = new \core\PHPLibrary\EmailSender($CMSCore);
                          $emailSenderSystemSenderEmail = \core\PHPLibrary\EmailSender::get_system_sender_email($CMSCore);
                          $emailSender->set_from_user($siteTitle, $emailSenderSystemSenderEmail);
                          $emailSender->set_to_user_email($userEmail);
                          $emailSender->add_header(sprintf('From: %s <%s>', $siteTitle, $emailSenderSystemSenderEmail));
                          $emailSender->add_header(sprintf("\r\nX-Mailer: PHP/%s", phpversion()));
                          $emailSender->add_header("\r\nMIME-Version: 1.0");
                          $emailSender->add_header("\r\nContent-type: text/html; charset=UTF-8");

                          $emailSender->set_subject($CMSCore->locale->get_single_value_by_key('API_UTILS_USER_REGISTRATION_EMAIL_SUBJECT'));
                          $emailSender->set_content(\core\PHPLibrary\Template\Collector::assembly_file_content($theme, 'templates/email/default.tpl', [
                            'EMAIL_TITLE' => $CMSCore->locale->get_single_value_by_key('API_UTILS_USER_REGISTRATION_EMAIL_TITLE'),
                            'EMAIL_CONTENT' => sprintf($CMSCore->locale->get_single_value_by_key('API_UTILS_USER_REGISTRATION_EMAIL_CONTENT'), $userLogin, sprintf('%s/registration?submit=%s', $CMSCore->get_site_url(), $registrationSubmit['submit_token']), sprintf('%s/registration?refusal=%s', $CMSCore->get_site_url(), $registrationSubmit['refusal_token'])),
                            'EMAIL_COPYRIGHT' => $CMSCore->locale->get_single_value_by_key('API_UTILS_USER_REGISTRATION_EMAIL_COPYRIGHT')
                          ]));

                          $emailSender->send();
                          
                          $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_UTILS_USER_REGISTRATION_SENDED_SUCCESS');
                          $handlerStatusCode = $handlerStatusCode ?? 1;
                        } else {
                          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_UNKNOWN');
                          $handlerStatusCode = $handlerStatusCode ?? 0;
                        }
                      } else {
                        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_UNKNOWN');
                        $handlerStatusCode = $handlerStatusCode ?? 0;
                      }
                    } else {
                      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_UTILS_USER_REGISTRATION_ERROR_EMAIL_IS_NOT_ALLOWED');
                      $handlerStatusCode = $handlerStatusCode ?? 0;
                    }
                  } else {
                    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_UTILS_USER_REGISTRATION_ERROR_EMAIL_ALREADY_EXISTS');
                    $handlerStatusCode = $handlerStatusCode ?? 0;
                  }
                } else {
                  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_UTILS_USER_REGISTRATION_ERROR_LOGIN_ALREADY_EXISTS');
                  $handlerStatusCode = $handlerStatusCode ?? 0;
                }
              } else {
                $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_UTILS_USER_REGISTRATION_ERROR_LOGIN_ALREADY_EXISTS');
                $handlerStatusCode = $handlerStatusCode ?? 0;
              }
            } else {
              $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_UTILS_USER_REGISTRATION_ERROR_INVALID_EMAIL');
              $handlerStatusCode = $handlerStatusCode ?? 0;
            }
          } else {
            $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_UNKNOWN');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_INVALID_INPUT_DATA_SET');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_UTILS_USER_REGISTRATION_ERROR_AGREEMENT');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_UTILS_USER_REGISTRATION_ERROR_DISABLED');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_UTILS_USER_REGISTRATION_ERRAPI_UTILS_USER_REGISTRATION_ERROR_AUTHORIZATION_ALREADYOR_DISABLED');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
}

if ($CMSCore->urlp->get_path(2) === 'authorization' && $CMSCore->urlp->get_param('method') === 'base') {
  if (!$CMSCore->client->is_logged(1)) {
    $userLogin = trim($_POST['user_login']) ?? null;
    $userPassword = trim($_POST['user_password']) ?? null;
    $userRememberMe = isset($_POST['user_remember_me']);

    if ($userLogin !== null && $userPassword !== null) {
      $user = User::get_by_login($CMSCore, $userLogin);

      if ($user !== null) {
        // Инициализация данных пользователя
        $user->init_data(['passwordHash', 'securityHash']);
        
        // Проверяем правильность пароля
        if ($user->password_verify($userPassword)) {
          /** @var string $userIP */
          $userIP = $_SERVER['REMOTE_ADDR'];
          /** @var string $userToken */
          $userToken = ClientSession::generate_token();

          if (!ClientSession::exists_by_ip_and_user_id($CMSCore, $userIP, $user->get_id(), 1)) {
            /** @var ClientSession|null $userSession */
            $userSession = ClientSession::create($CMSCore, [
              'user_id' => $user->get_id(),
              'token' => $userToken,
              'user_ip' => $userIP,
              'type_id' => 1
            ]);
          } else {
            $userSession = ClientSession::get_by_ip_and_user_id($CMSCore, $userIP, $user->get_id(), 1);
            $userSession->update([]);
          }

          if (!is_null($userSession)) {
            $userSession->init_data(['updatedUnixTimestamp', 'token']);
            $userSessionExpires = $userSession->get_updated_unix_timestamp() + $CMSCore->configurator->get('sessionExpires');

            $userSessionIsSecure = ($CMSCore->configurator->get('ssl_is_enabled')) ? true : false;

            setcookie('_grv_utoken', $userSession->get_token(), [
              'expires' => ($userRememberMe) ? $userSessionExpires : 0,
              'path' => '/',
              'domain' => $CMSCore->configurator->get('domain_cookies'),
              'secure' => $userSessionIsSecure,
              'httponly' => true
            ]);

            $handlerOutputData['reload'] = true;

            /** @var string $handlerMessage Сообщение обработчика */
            $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_UTILS_USER_AUTHORIZATION_SUCCESS');
            $handlerStatusCode = $handlerStatusCode ?? 1;
          } else {
            /** @var string $handlerMessage Сообщение обработчика */
            $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_UNKNOWN');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }

        } else {
          /** @var string $handlerMessage Сообщение обработчика */
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_UTILS_USER_AUTHORIZATION_ERROR_USER_NOT_FOUND');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      } else {
        /** @var string $handlerMessage Сообщение обработчика */
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_UTILS_USER_AUTHORIZATION_ERROR_USER_NOT_FOUND');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_INVALID_INPUT_DATA_SET');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_UTILS_USER_AUTHORIZATION_ERROR_AUTHORIZATION_ALREADY');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
}

if ($CMSCore->urlp->get_path(2) === 'authorization' && $CMSCore->urlp->get_param('method') === 'admin') {
  if (!$CMSCore->client->is_logged(2)) {
    $userLogin = trim($_POST['user_login']) ?? null;
    $userPassword = trim($_POST['user_password']) ?? null;
    $userRememberMe = isset($_POST['user_remember_me']);
    $adminAccessCodes = $_POST['admin_access-code'] ?? [];

    if ($userLogin !== null && !$userPassword !== null && !empty($adminAccessCodes)) {
      $user = User::get_by_login($CMSCore, $userLogin);

      if ($user !== null) {
        // Инициализация данных пользователя
        $user->init_data(['passwordHash', 'securityHash', 'metadata']);
        $userGroup = $user->get_group();
        $userGroup->init_data(['permissions']);
        
        if ($userGroup->permission_check($userGroup::PERMISSION_ADMIN_PANEL_AUTH)) {
          $adminAccessCodesIsValid = true;
          foreach ($adminAccessCodes as $index => $code) {
            switch ($index) {
              case 0: $codeChar = 'a'; break;
              case 1: $codeChar = 'b'; break;
              case 2: $codeChar = 'c'; break;
              case 3: $codeChar = 'd'; break;
            }

            if (!password_verify($code, $CMSCore->configurator->get_database_entry_value('security_admin_code_' . $codeChar))) {
              $adminAccessCodesIsValid = false; break;
            }
          }

          // Проверяем правильность пароля
          if ($user->password_verify($userPassword) && $adminAccessCodesIsValid) {
            /** @var string $userIP */
            $userIP = $_SERVER['REMOTE_ADDR'];
            /** @var string $userToken */
            $userTokenBase = ClientSession::generate_token();
            $userTokenAdmin = ClientSession::generate_token();

            $userSessionBase = null;
            $userSessionAdmin = null;

            // Если сессия не была найдена, то создаем новую.
            if (!ClientSession::exists_by_ip_and_user_id($CMSCore, $userIP, $user->get_id(), 1)) {
              /** @var ClientSession|null $userSession */
              $userSessionBase = ClientSession::create($CMSCore, [
                'user_id' => $user->get_id(),
                'token' => $userTokenBase,
                'user_ip' => $userIP,
                'type_id' => 1
              ]);
            } else {
              $userSessionBase = ClientSession::get_by_ip_and_user_id($CMSCore, $userIP, $user->get_id(), 1);
              $userSessionBase->update([]);
            }

            // Если сессия не была найдена, то создаем новую.
            if (!ClientSession::exists_by_ip_and_user_id($CMSCore, $userIP, $user->get_id(), 2)) {
              /** @var ClientSession|null $userSession */
              $userSessionAdmin = ClientSession::create($CMSCore, [
                'user_id' => $user->get_id(),
                'token' => $userTokenAdmin,
                'user_ip' => $userIP,
                'type_id' => 2
              ]);
            } else {
              $userSessionAdmin = ClientSession::get_by_ip_and_user_id($CMSCore, $userIP, $user->get_id(), 2);
              $userSessionAdmin->update([]);
            }

            if (!is_null($userSessionBase)) {
              $userSessionBase->init_data(['updatedUnixTimestamp', 'token']);
              $userSessionBaseExpires = $userSessionBase->get_updated_unix_timestamp() + $CMSCore->configurator->get('sessionExpires');

              $CMSCore->client::create_cookie($CMSCore, '_grv_utoken', $userSessionBase, $userRememberMe ? $userSessionBaseExpires : 0);
            }

            if (!is_null($userSessionAdmin)) {
              $userSessionAdmin->init_data(['updatedUnixTimestamp', 'token']);
              $userSessionAdmin_expires = $userSessionAdmin->get_updated_unix_timestamp() + $CMSCore->configurator->get('sessionExpires');

              $CMSCore->client::create_cookie($CMSCore, '_grv_atoken', $userSessionAdmin, $userRememberMe ? $userSessionAdmin_expires : 0);

              $CMSReport = CMSReport::create($CMSCore, CMSReport::REPORT_TYPE_ID_AP_AUTHORIZATION_SUCCESS, [
                'clientIP' => $CMSCore->client->get_ip_address(),
                'date' => date('Y/m/d H:i:s', time())
              ]);

              $handlerOutputData['reload'] = true;

              /** @var string $handlerMessage Сообщение обработчика */
              $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_UTILS_USER_AUTHORIZATION_SUCCESS');
              $handlerStatusCode = $handlerStatusCode ?? 1;
            } else {
              /** @var string $handlerMessage Сообщение обработчика */
              $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_UNKNOWN');
              $handlerStatusCode = $handlerStatusCode ?? 0;
            }

          } else {
            $CMSReport = CMSReport::create($CMSCore, CMSReport::REPORT_TYPE_ID_AP_AUTHORIZATION_FAIL, [
              'clientIP' => $CMSCore->client->get_ip_address(),
              'date' => date('Y/m/d H:i:s', time())
            ]);

            /** @var string $handlerMessage Сообщение обработчика */
            $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_UTILS_USER_AUTHORIZATION_ERROR_USER_NOT_FOUND');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_DONT_HAVE_PERMISSIONS');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      } else {
        $CMSReport = CMSReport::create($CMSCore, CMSReport::REPORT_TYPE_ID_AP_AUTHORIZATION_FAIL, [
          'clientIP' => $CMSCore->client->get_ip_address(),
          'date' => date('Y/m/d H:i:s', time())
        ]);
        
        /** @var string $handlerMessage Сообщение обработчика */
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_UTILS_USER_AUTHORIZATION_ERROR_USER_NOT_FOUND');
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }

    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_INVALID_INPUT_DATA_SET');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_UTILS_USER_AUTHORIZATION_ERROR_AUTHORIZATION_ALREADY');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
}

?>