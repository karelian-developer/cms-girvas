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

use \core\PHPLibrary\Parsedown as Parsedown;
use \core\PHPLibrary\User as User;
use \core\PHPLibrary\Client\Session as ClientSession;

if ($system_core->urlp->get_path(2) == 'parsedown') {
  if ($system_core->client->is_logged(1)) {
    $parsedown = new Parsedown();
    $handler_output_data['parsedown'] = $parsedown->text($_POST['markdown_text']);

    $handler_message = (!isset($handler_message)) ? $system_core->locale->get_single_value_by_key('API_UTILS_PARSEDOWN_TRANSFORMED_SUCCESS') : $handler_message;
    $handler_status_code = (!isset($handler_status_code)) ? 1 : $handler_status_code;
  } else {
    $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION')) : $handler_message;
    $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
  }
}

if ($system_core->urlp->get_path(2) == 'registration') {
  if (!$system_core->client->is_logged(1)) {
    if ($system_core->configurator->get_database_entry_value('security_allowed_users_registration_status') == 'on') {
      if (isset($_POST['user_login']) && isset($_POST['user_email']) && isset($_POST['user_password']) && isset($_POST['user_password_repeat'])) {
        $user_login = $_POST['user_login'];
        $user_email = $_POST['user_email'];
        $user_password = $_POST['user_password'];
        $user_password_repeat = $_POST['user_password_repeat'];

        if (preg_match('/^[a-z0-9\_]{4,}$/i', $user_login)) {
          if (preg_match('/^[\w\-\.]{1,30}@([\w\-]{1,63}\.){1,2}[\w\-]{2,4}$/i', $user_email)) {
            if (preg_match('/^[a-z0-9\_\$\%\&\#\@\?]{6,}$/i', $user_password)) {
              if ($user_password == $user_password_repeat) {
                if (!User::exists_by_login($system_core, $user_login)) {
                  if (!User::exists_by_email($system_core, $user_email)) {
                    $allowed_emails = [];
                    if ($system_core->configurator->exists_database_entry_value('security_allowed_emails')) {
                      $allowed_emails = $system_core->configurator->get_database_entry_value('security_allowed_emails');
                      $allowed_emails = json_decode($allowed_emails, true);
                    }

                    if ($system_core->configurator->exists_database_entry_value('security_allowed_emails_status')) {
                      $allowed_emails_status = $system_core->configurator->get_database_entry_value('security_allowed_emails_status');
                    } else {
                      $allowed_emails_status = 'off';
                    }
                    
                    $user_email_exploded = explode('@', $user_email);

                    if (empty($allowed_emails) || in_array($user_email_exploded[1], $allowed_emails) || $allowed_emails_status == 'off') {
                      $user = User::create($system_core, $user_login, $user_email, $user_password);
                      
                      if (!is_null($user)) {
                        $template_base_name = ($system_core->configurator->exists_database_entry_value('base_template')) ? $system_core->configurator->get_database_entry_value('base_template') : 'default';

                        $template = new \core\PHPLibrary\Template($system_core, $template_base_name);
                        $registration_submit = $user->create_registration_submit();

                        if (is_array($registration_submit)) {
                          $site_title = (empty($system_core->configurator->get_meta_title())) ? $system_core->configurator->get_site_title() : $system_core->configurator->get_meta_title();

                          $email_sender = new \core\PHPLibrary\EmailSender($system_core);
                          $email_sender_system_sender_email = \core\PHPLibrary\EmailSender::get_system_sender_email($system_core);
                          $email_sender->set_from_user($site_title, $email_sender_system_sender_email);
                          $email_sender->set_to_user_email($user_email);
                          $email_sender->add_header(sprintf('From: %s <%s>', $site_title, $email_sender_system_sender_email));
                          $email_sender->add_header(sprintf("\r\nX-Mailer: PHP/%s", phpversion()));
                          $email_sender->add_header("\r\nMIME-Version: 1.0");
                          $email_sender->add_header("\r\nContent-type: text/html; charset=UTF-8");

                          $email_sender->set_subject($system_core->locale->get_single_value_by_key('API_UTILS_USER_REGISTRATION_EMAIL_SUBJECT'));
                          $email_sender->set_content(\core\PHPLibrary\Template\Collector::assembly_file_content($template, 'templates/email/default.tpl', [
                            'EMAIL_TITLE' => $system_core->locale->get_single_value_by_key('API_UTILS_USER_REGISTRATION_EMAIL_TITLE'),
                            'EMAIL_CONTENT' => sprintf($system_core->locale->get_single_value_by_key('API_UTILS_USER_REGISTRATION_EMAIL_CONTENT'), $user_login, sprintf('%s/registration?submit=%s', $system_core->get_site_url(), $registration_submit['submit_token']), sprintf('%s/registration?refusal=%s', $system_core->get_site_url(), $registration_submit['refusal_token'])),
                            'EMAIL_COPYRIGHT' => $system_core->locale->get_single_value_by_key('API_UTILS_USER_REGISTRATION_EMAIL_COPYRIGHT')
                          ]));

                          $email_sender->send();
                          
                          $handler_message = (!isset($handler_message)) ? $system_core->locale->get_single_value_by_key('API_UTILS_USER_REGISTRATION_SENDED_SUCCESS') : $handler_message;
                          $handler_status_code = (!isset($handler_status_code)) ? 1 : $handler_status_code;
                        } else {
                          $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_UNKNOWN')) : $handler_message;
                          $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
                        }
                      } else {
                        $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_UNKNOWN')) : $handler_message;
                        $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
                      }
                    } else {
                      $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_UTILS_USER_REGISTRATION_ERROR_EMAIL_IS_NOT_ALLOWED')) : $handler_message;
                      $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
                    }
                  } else {
                    $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_UTILS_USER_REGISTRATION_ERROR_EMAIL_ALREADY_EXISTS')) : $handler_message;
                    $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
                  }
                } else {
                  $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_UTILS_USER_REGISTRATION_ERROR_LOGIN_ALREADY_EXISTS')) : $handler_message;
                  $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
                }
              } else {
                $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_UTILS_USER_REGISTRATION_ERROR_LOGIN_ALREADY_EXISTS')) : $handler_message;
                $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
              }
            } else {
              $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_UTILS_USER_REGISTRATION_ERROR_INVALID_PASSWORD')) : $handler_message;
              $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
            }
          } else {
            $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_UTILS_USER_REGISTRATION_ERROR_INVALID_EMAIL')) : $handler_message;
            $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
          }
        } else {
          $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_UTILS_USER_REGISTRATION_ERROR_INVALID_LOGIN')) : $handler_message;
          $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
        }
      } else {
        $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_INVALID_INPUT_DATA_SET')) : $handler_message;
        $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
      }
    } else {
      $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_UTILS_USER_REGISTRATION_ERROR_DISABLED')) : $handler_message;
      $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
    }
  } else {
    $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_UTILS_USER_REGISTRATION_ERROR_AUTHORIZATION_ALREADY')) : $handler_message;
    $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
  }
}

if ($system_core->urlp->get_path(2) == 'authorization' && $system_core->urlp->get_param('method') == 'base') {
  if (!$system_core->client->is_logged(1)) {
    /** @var string|null $user_login */
    $user_login = (isset($_POST['user_login'])) ? $_POST['user_login'] : null;
    /** @var string|null $user_password */
    $user_password = (isset($_POST['user_password'])) ? $_POST['user_password'] : null;

    if (!is_null($user_login) && !is_null($user_password)) {
      /** @var User|null $user */
      $user = User::get_by_login($system_core, $user_login);

      if (!is_null($user)) {
        // Инициализация данных пользователя
        $user->init_data(['password_hash', 'security_hash']);
        
        // Проверяем правильность пароля
        if ($user->password_verify($user_password)) {
          /** @var string $user_ip */
          $user_ip = $_SERVER['REMOTE_ADDR'];
          /** @var string $user_token */
          $user_token = ClientSession::generate_token();

          if (!ClientSession::exists_by_ip_and_user_id($system_core, $user_ip, $user->get_id(), 1)) {
            /** @var ClientSession|null $user_session */
            $user_session = ClientSession::create($system_core, [
              'user_id' => $user->get_id(),
              'token' => $user_token,
              'user_ip' => $user_ip,
              'type_id' => 1
            ]);
          } else {
            $user_session = ClientSession::get_by_ip_and_user_id($system_core, $user_ip, $user->get_id(), 1);
            $user_session->update([]);
          }

          if (!is_null($user_session)) {
            $user_session->init_data(['updated_unix_timestamp', 'token']);
            $user_session_expires = $user_session->get_updated_unix_timestamp() + $system_core->configurator->get('session_expires');

            setcookie('_grv_utoken', $user_session->get_token(), [
              'expires' => $user_session_expires,
              'path' => '/',
              'domain' => $system_core->configurator->get('domain_cookies'),
              'secure' => true,
              'httponly' => true
            ]);

            $handler_output_data['reload'] = true;

            /** @var string $handler_message Сообщение обработчика */
            $handler_message = (!isset($handler_message)) ? $system_core->locale->get_single_value_by_key('API_UTILS_USER_AUTHORIZATION_SUCCESS') : $handler_message;
            $handler_status_code = (!isset($handler_status_code)) ? 1 : $handler_status_code;
          } else {
            /** @var string $handler_message Сообщение обработчика */
            $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_UNKNOWN')) : $handler_message;
            $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
          }

        } else {
          /** @var string $handler_message Сообщение обработчика */
          $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_UTILS_USER_AUTHORIZATION_ERROR_USER_NOT_FOUND')) : $handler_message;
          $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
        }
      } else {
        /** @var string $handler_message Сообщение обработчика */
        $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_UTILS_USER_AUTHORIZATION_ERROR_USER_NOT_FOUND')) : $handler_message;
        $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
      }
    } else {
      $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_INVALID_INPUT_DATA_SET')) : $handler_message;
      $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
    }
  } else {
    $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_UTILS_USER_AUTHORIZATION_ERROR_AUTHORIZATION_ALREADY')) : $handler_message;
    $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
  }
}

if ($system_core->urlp->get_path(2) == 'authorization' && $system_core->urlp->get_param('method') == 'admin') {
  if (!$system_core->client->is_logged(2)) {
    /** @var string|null $user_login */
    $user_login = (isset($_POST['user_login'])) ? $_POST['user_login'] : null;
    /** @var string|null $user_password */
    $user_password = (isset($_POST['user_password'])) ? $_POST['user_password'] : null;
    $admin_access_codes = (isset($_POST['admin_access-code'])) ? $_POST['admin_access-code'] : [];

    if (!is_null($user_login) && !is_null($user_password) && !empty($admin_access_codes)) {
      /** @var \core\PHPLibrary\User|null $user */
      $user = \core\PHPLibrary\User::get_by_login($system_core, $user_login);

      if (!is_null($user)) {
        // Инициализация данных пользователя
        $user->init_data(['password_hash', 'security_hash', 'metadata']);
        $user_group = $user->get_group();
        $user_group->init_data(['permissions']);
        
        if ($user_group->permission_check($user_group::PERMISSION_ADMIN_PANEL_AUTH)) {
          $admin_access_codes_is_valid = true;
          foreach ($admin_access_codes as $admin_access_code_index => $admin_access_code) {
            switch ($admin_access_code_index) {
              case 0: $code_char = 'a'; break;
              case 1: $code_char = 'b'; break;
              case 2: $code_char = 'c'; break;
              case 3: $code_char = 'd'; break;
            }

            if (!password_verify($admin_access_code, $system_core->configurator->get_database_entry_value(sprintf('security_admin_code_%s', $code_char)))) {
              $admin_access_codes_is_valid = false; break;
            }
          }
          error_log(1);

          // Проверяем правильность пароля
          if ($user->password_verify($user_password) && $admin_access_codes_is_valid) {
            error_log(2);
            /** @var string $user_ip */
            $user_ip = $_SERVER['REMOTE_ADDR'];
            /** @var string $user_token */
            $user_token_base = ClientSession::generate_token();
            $user_token_admin = ClientSession::generate_token();

            $user_session_base = null;
            $user_session_admin = null;

            // Если сессия не была найдена, то создаем новую.
            if (!ClientSession::exists_by_ip_and_user_id($system_core, $user_ip, $user->get_id(), 1)) {
              /** @var ClientSession|null $user_session */
              $user_session_base = ClientSession::create($system_core, [
                'user_id' => $user->get_id(),
                'token' => $user_token_base,
                'user_ip' => $user_ip,
                'type_id' => 1
              ]);
            } else {
              $user_session_base = ClientSession::get_by_ip_and_user_id($system_core, $user_ip, $user->get_id(), 1);
              $user_session_base->update([]);
            }

            // Если сессия не была найдена, то создаем новую.
            if (!ClientSession::exists_by_ip_and_user_id($system_core, $user_ip, $user->get_id(), 2)) {
              /** @var ClientSession|null $user_session */
              $user_session_admin = ClientSession::create($system_core, [
                'user_id' => $user->get_id(),
                'token' => $user_token_admin,
                'user_ip' => $user_ip,
                'type_id' => 2
              ]);
            } else {
              $user_session_admin = ClientSession::get_by_ip_and_user_id($system_core, $user_ip, $user->get_id(), 2);
              $user_session_admin->update([]);
            }

            if (!is_null($user_session_base)) {
              $user_session_base->init_data(['updated_unix_timestamp', 'token']);
              $user_session_base_expires = $user_session_base->get_updated_unix_timestamp() + $system_core->configurator->get('session_expires');

              $system_core->client::create_cookie($system_core, '_grv_utoken', $user_session_base, $user_session_base_expires);
            }

            if (!is_null($user_session_admin)) {
              $user_session_admin->init_data(['updated_unix_timestamp', 'token']);
              $user_session_admin_expires = $user_session_admin->get_updated_unix_timestamp() + $system_core->configurator->get('session_expires');

              $system_core->client::create_cookie($system_core, '_grv_atoken', $user_session_admin, $user_session_admin_expires);

              $sc_report = \core\PHPLibrary\SystemCore\Report::create($system_core, \core\PHPLibrary\SystemCore\Report::REPORT_TYPE_ID_AP_AUTHORIZATION_SUCCESS, [
                'clientIP' => $system_core->client->get_ip_address(),
                'date' => date('Y/m/d H:i:s', time())
              ]);

              $handler_output_data['reload'] = true;

              /** @var string $handler_message Сообщение обработчика */
              $handler_message = (!isset($handler_message)) ? $system_core->locale->get_single_value_by_key('API_UTILS_USER_AUTHORIZATION_SUCCESS') : $handler_message;
              $handler_status_code = (!isset($handler_status_code)) ? 1 : $handler_status_code;
            } else {
              /** @var string $handler_message Сообщение обработчика */
              $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_UNKNOWN')) : $handler_message;
              $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
            }

          } else {
            $sc_report = \core\PHPLibrary\SystemCore\Report::create($system_core, \core\PHPLibrary\SystemCore\Report::REPORT_TYPE_ID_AP_AUTHORIZATION_FAIL, [
              'clientIP' => $system_core->client->get_ip_address(),
              'date' => date('Y/m/d H:i:s', time())
            ]);

            /** @var string $handler_message Сообщение обработчика */
            $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_UTILS_USER_AUTHORIZATION_ERROR_USER_NOT_FOUND')) : $handler_message;
            $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
          }
        } else {
          $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_DONT_HAVE_PERMISSIONS')) : $handler_message;
          $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
        }
      } else {
        $sc_report = \core\PHPLibrary\SystemCore\Report::create($system_core, \core\PHPLibrary\SystemCore\Report::REPORT_TYPE_ID_AP_AUTHORIZATION_FAIL, [
          'clientIP' => $system_core->client->get_ip_address(),
          'date' => date('Y/m/d H:i:s', time())
        ]);
        
        /** @var string $handler_message Сообщение обработчика */
        $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_UTILS_USER_AUTHORIZATION_ERROR_USER_NOT_FOUND')) : $handler_message;
        $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
      }

    } else {
      $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_INVALID_INPUT_DATA_SET')) : $handler_message;
      $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
    }
  } else {
    $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_UTILS_USER_AUTHORIZATION_ERROR_AUTHORIZATION_ALREADY')) : $handler_message;
    $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
  }
}

?>