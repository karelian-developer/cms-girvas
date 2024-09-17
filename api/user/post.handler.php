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

use \core\PHPLibrary\EmailSender as EmailSender;
use \core\PHPLibrary\Template as Template;
use \core\PHPLibrary\Template\Collector as TemplateCollector;
use \core\PHPLibrary\User as User;
use \core\PHPLibrary\UserGroup as UserGroup;
use \core\PHPLibrary\SystemCore\FileConverter as FileConverter;
use \core\PHPLibrary\SystemCore\FileConverter\EnumFileFormat as EnumFileFormat;

/**
 * Загрузка аватара для пользователя
 */
if ($system_core->urlp->get_path(2) == 'avatar') {
  $user_id = (isset($_POST['user_id'])) ? $_POST['user_id'] : 0;
  $user_id = (is_numeric($user_id)) ? (int)$user_id : 0;

  if (User::exists_by_id($system_core, $user_id)) {
    if (isset($_FILES['avatarFile'])) {
      $handler_output_data['debug_files'] = $_FILES;

      $uploaded_file_extention = pathinfo($_FILES['avatarFile']['name'], PATHINFO_EXTENSION);
      $file_extention_allowed = ['png', 'gif', 'jpg', 'jpeg', 'webp'];
      $uploaded_dir_path = sprintf('%s/uploads/avatars', CMS_ROOT_DIRECTORY);
      $uploaded_dir_user_path = sprintf('%s/uploads/avatars/%d', CMS_ROOT_DIRECTORY, $user_id);

      if (!file_exists($uploaded_dir_path)) {
        mkdir($uploaded_dir_path, 0777);
      }

      if (!file_exists($uploaded_dir_user_path)) {
        mkdir($uploaded_dir_user_path, 0777);
      }

      if (file_exists($uploaded_dir_user_path)) {
        if (in_array($uploaded_file_extention, $file_extention_allowed)) {
          $file_converter = new FileConverter($system_core);
          $file_converted = $file_converter->convert($_FILES['avatarFile'], $uploaded_dir_user_path, EnumFileFormat::WEBP, true);

          if (is_array($file_converted)) {
            $image_original_path = sprintf('%s/uploads/avatars/%d/%s', CMS_ROOT_DIRECTORY, $user_id, $file_converted['file_name']);
            foreach ([16, 32, 64, 96, 128, 254] as $image_resized_width) {
              list($image_original_width, $image_original_height) = getimagesize($image_original_path);
              $image_original = imagecreatefromwebp($image_original_path);
              $image_resized_height = ceil($image_original_height / ($image_original_width / $image_resized_width));
              $image_resized = imagescale($image_original, $image_resized_width, $image_resized_height);
              imagewebp($image_resized, sprintf('%s/%d.webp', $uploaded_dir_user_path, $image_resized_width));
            }

            unlink($image_original_path);
            
            $handler_output_data['file'] = [];
            $handler_output_data['file']['url'] = sprintf('/uploads/avatars/%d/254.webp', $user_id);
            $handler_output_data['file']['fullname'] = '254.webp';

            $handler_message = (!isset($handler_message)) ? $system_core->locale->get_single_value_by_key('API_POST_FILES_SUCCESS') : $handler_message;
            $handler_status_code = (!isset($handler_status_code)) ? 1 : $handler_status_code;
          } else {
            $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_UNKNOWN')) : $handler_message;
            $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
          }
        }
      }

    } else {
      $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_INVALID_INPUT_DATA_SET')) : $handler_message;
      $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
    }
  } else {
    $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_USER_ERROR_NOT_FOUND')) : $handler_message;
    $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
  }
}

/**
 * Восстановление пароля
 * 
 * Выборка пользователя осуществляется через логин или e-mail. В случае, если
 * ни логин, ни e-mail не были отправлены, то обработчик вернет ошибку о неполноте
 * введенных данных.
 */
if ($system_core->urlp->get_path(2) == 'reset') {
  /** @var string Логин или e-mail пользователя */
  $user_login_or_email = (isset($_POST['user_login_or_email'])) ? $_POST['user_login_or_email'] : '';

  if (!empty($user_login_or_email)) {
    /** @var User|null Объект пользователя */
    $user = null;

    if (User::email_is_valid($system_core, $user_login_or_email)) {
      if (User::exists_by_email($system_core, $user_login_or_email)) {
        $user = User::get_by_email($system_core, $user_login_or_email);
      }
    } else {
      if (User::exists_by_login($system_core, $user_login_or_email)) {
        $user = User::get_by_login($system_core, $user_login_or_email);
      }
    }

    if (!is_null($user)) {
      $user->init_data(['login', 'email', 'metadata']);
      /** @var string Заголовок веб-сайта */
      $site_title = (empty($system_core->configurator->get_meta_title())) ? $system_core->configurator->get_site_title() : $system_core->configurator->get_meta_title();
      /** @var string E-Mail получателя */
      $user_email = $user->get_email();
      /** @var string Логин получателя */
      $user_login = $user->get_login();

      $template_base_name = ($system_core->configurator->exists_database_entry_value('base_template')) ? $system_core->configurator->get_database_entry_value('base_template') : 'default';

      /** @var Template Объект шаблона */
      $template = new Template($system_core, $template_base_name);

      /** @var EmailSender Объект отправителя E-Mail */
      $email_sender = new EmailSender($system_core);
      $email_sender_system_sender_email = EmailSender::get_system_sender_email($system_core);
      $email_sender->set_from_user($site_title, $email_sender_system_sender_email);
      $email_sender->set_to_user_email($user_email);
      $email_sender->add_header(sprintf("From: %s <%s>", $site_title, $email_sender_system_sender_email));
      $email_sender->add_header(sprintf("\r\nX-Mailer: PHP/%s", phpversion()));
      $email_sender->add_header("\r\nMIME-Version: 1.0");
      $email_sender->add_header("\r\nContent-type: text/html; charset=UTF-8");
      $email_sender->add_header("\r\n");

      /** @var int Временная отметка в UNIX-формате создания заявки на сброс пароля */
      $reset_password_created_unix_timestamp = time();
      /** @var string Токен сброса пароля */
      $reset_password_token = md5($reset_password_created_unix_timestamp . $system_core::CMS_VERSION);

      $email_sender->set_subject($system_core->locale->get_single_value_by_key('API_USER_REQUEST_PASSWORD_RESET_EMAIL_SUBJECT'));
      $email_sender->set_content(TemplateCollector::assembly_file_content($template, 'templates/email/default.tpl', [
        'EMAIL_TITLE' => $system_core->locale->get_single_value_by_key('API_USER_REQUEST_PASSWORD_RESET_EMAIL_TITLE'),
        'EMAIL_CONTENT' => sprintf($system_core->locale->get_single_value_by_key('API_USER_REQUEST_PASSWORD_RESET_EMAIL_CONTENT'), $user_login, sprintf('%s/password-reset?token=%s', $system_core->get_site_url(), $reset_password_token)),
        'EMAIL_COPYRIGHT' => $system_core->locale->get_single_value_by_key('API_USER_REQUEST_PASSWORD_RESET_EMAIL_COPYRIGHT')
      ]));

      $email_sender->send();

      /** @var int Временная отметка в UNIX-формате создания заявки на сброс пароля */
      $reset_password_created_unix_timestamp = time();
      $user->update(['metadata' => ['passwordResetToken' => $reset_password_token, 'passwordResetTokenCreatedUnixTimestamp' => $reset_password_created_unix_timestamp]]);

      $handler_message = $system_core->locale->get_single_value_by_key('API_USER_REQUEST_PASSWORD_RESET_SENDED_SUCCESS');
      $handler_status_code = 1;
    } else {
      $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_USER_ERROR_NOT_FOUND'));
      $handler_status_code = 0;
    }
  } else {
    $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_INVALID_INPUT_DATA_SET'));
    $handler_status_code = 0;
  }
}

?>