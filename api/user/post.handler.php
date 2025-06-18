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
if ($CMSCore->urlp->get_path(2) == 'avatar') {
  $userID = trim($_POST['user_id']) ?? : 0;
  $userID = (is_numeric($userID)) ? (int)$userID : 0;

  if (User::exists_by_id($CMSCore, $userID)) {
    if (isset($_FILES['avatarFile'])) {
      $handlerOutputData['debug_files'] = $_FILES;

      $uploadedFileExtention = pathinfo($_FILES['avatarFile']['name'], PATHINFO_EXTENSION);
      $fileExtentionsAllowed = ['png', 'gif', 'jpg', 'jpeg', 'webp'];
      $uploadedDirectoryPath = CMS_ROOT_DIRECTORY . '/uploads/avatars';
      $uploadedDirectoryUserPath = CMS_ROOT_DIRECTORY . '/uploads/avatars/' . (string)$userID;

      if (!file_exists($uploadedDirectoryPath)) {
        mkdir($uploadedDirectoryPath, 0777);
      }

      if (!file_exists($uploadedDirectoryUserPath)) {
        mkdir($uploadedDirectoryUserPath, 0777);
      }

      if (file_exists($uploadedDirectoryUserPath)) {
        if (in_array($uploadedFileExtention, $fileExtentionsAllowed)) {
          $fileConverter = new FileConverter($CMSCore);
          $fileConverted = $fileConverter->convert($_FILES['avatarFile'], $uploadedDirectoryUserPath, EnumFileFormat::WEBP, true);

          if (is_array($fileConverted)) {
            $imageOriginalPath = CMS_ROOT_DIRECTORY . '/uploads/avatars/' . (string)$userID . '/' . $fileConverted['file_name'];
            
            foreach ([16, 32, 64, 96, 128, 254] as $imageResizedWidth) {
              list($imageOriginalWidth, $imageOriginalHeight) = getimagesize($imageOriginalPath);
              $imageOriginal = imagecreatefromwebp($imageOriginalPath);
              $imageResizedHeight = ceil($imageOriginalHeight / ($imageOriginalWidth / $imageResizedWidth));
              $imageResized = imagescale($imageOriginal, $imageResizedWidth, $imageResizedHeight);
              imagewebp($imageResized, $uploadedDirectoryUserPath . '/' . (string)$imageResizedWidth . '.webp');
            }

            unlink($imageOriginalPath);
            
            $handlerOutputData['file'] = [];
            $handlerOutputData['file']['url'] = '/uploads/avatars/' . (string)$userID . '/254.webp';
            $handlerOutputData['file']['fullname'] = '254.webp';

            $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_POST_FILES_SUCCESS');
            $handlerStatusCode = $handlerStatusCode ?? 1;
          } else {
            $handlerMessage = $handlerMessage ?? 'API ERROR: ' .$CMSCore->locale->get_single_value_by_key('API_ERROR_UNKNOWN');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }
        }
      }

    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' .$CMSCore->locale->get_single_value_by_key('API_ERROR_INVALID_INPUT_DATA_SET');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' .$CMSCore->locale->get_single_value_by_key('API_USER_ERROR_NOT_FOUND');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
}

/**
 * Восстановление пароля
 * 
 * Выборка пользователя осуществляется через логин или e-mail. В случае, если
 * ни логин, ни e-mail не были отправлены, то обработчик вернет ошибку о неполноте
 * введенных данных.
 */
if ($CMSCore->urlp->get_path(2) == 'reset') {
  /** @var string Логин или e-mail пользователя */
  $userLoginOrEmail = $_POST['user_login_or_email'] ?? '';

  if (!empty($userLoginOrEmail)) {
    /** @var User|null Объект пользователя */
    $user = null;

    if (User::email_is_valid($CMSCore, $userLoginOrEmail)) {
      if (User::exists_by_email($CMSCore, $userLoginOrEmail)) {
        $user = User::get_by_email($CMSCore, $userLoginOrEmail);
      }
    } else {
      if (User::exists_by_login($CMSCore, $userLoginOrEmail)) {
        $user = User::get_by_login($CMSCore, $userLoginOrEmail);
      }
    }

    if (!is_null($user)) {
      $user->init_data(['login', 'email', 'metadata']);
      /** @var string Заголовок веб-сайта */
      $siteTitle = (empty($CMSCore->configurator->get_meta_title())) ? $CMSCore->configurator->get_site_title() : $CMSCore->configurator->get_meta_title();
      /** @var string E-Mail получателя */
      $userEmail = $user->get_email();
      /** @var string Логин получателя */
      $userLogin = $user->get_login();

      $themeBaseName = ($CMSCore->configurator->exists_database_entry_value('base_template')) ? $CMSCore->configurator->get_database_entry_value('base_template') : 'default';

      /** @var Template Объект шаблона */
      $theme = new Template($CMSCore, $themeBaseName);

      /** @var EmailSender Объект отправителя E-Mail */
      $emailSender = new EmailSender($CMSCore);
      $emailSenderSystemSenderEmail = EmailSender::get_system_sender_email($CMSCore);
      $emailSender->set_from_user($siteTitle, $emailSenderSystemSenderEmail);
      $emailSender->set_to_user_email($userEmail);
      $emailSender->add_header(sprintf("From: %s <%s>", $siteTitle, $emailSenderSystemSenderEmail));
      $emailSender->add_header(sprintf("\r\nX-Mailer: PHP/%s", phpversion()));
      $emailSender->add_header("\r\nMIME-Version: 1.0");
      $emailSender->add_header("\r\nContent-type: text/html; charset=UTF-8");
      $emailSender->add_header("\r\n");

      /** @var int Временная отметка в UNIX-формате создания заявки на сброс пароля */
      $resetPasswordCreatedUnixTimestamp = time();
      /** @var string Токен сброса пароля */
      $resetPasswordToken = md5($resetPasswordCreatedUnixTimestamp . $CMSCore::CMS_VERSION);

      $emailSender->set_subject($CMSCore->locale->get_single_value_by_key('API_USER_REQUEST_PASSWORD_RESET_EMAIL_SUBJECT'));
      $emailSender->set_content(TemplateCollector::assembly_file_content($theme, 'templates/email/default.tpl', [
        'EMAIL_TITLE' => $CMSCore->locale->get_single_value_by_key('API_USER_REQUEST_PASSWORD_RESET_EMAIL_TITLE'),
        'EMAIL_CONTENT' => sprintf($CMSCore->locale->get_single_value_by_key('API_USER_REQUEST_PASSWORD_RESET_EMAIL_CONTENT'), $userLogin, $CMSCore->get_site_url() . '/password-reset?token=' . $resetPasswordToken),
        'EMAIL_COPYRIGHT' => $CMSCore->locale->get_single_value_by_key('API_USER_REQUEST_PASSWORD_RESET_EMAIL_COPYRIGHT')
      ]));

      $emailSender->send();

      /** @var int Временная отметка в UNIX-формате создания заявки на сброс пароля */
      $resetPasswordCreatedUnixTimestamp = time();
      $user->update(['metadata' => ['passwordResetToken' => $resetPasswordToken, 'passwordResetTokenCreatedUnixTimestamp' => $resetPasswordCreatedUnixTimestamp]]);

      $handlerMessage = $CMSCore->locale->get_single_value_by_key('API_USER_REQUEST_PASSWORD_RESET_SENDED_SUCCESS');
      $handlerStatusCode = $handlerStatusCode ?? 1;
    } else {
      $handlerMessage = 'API ERROR: ' .$CMSCore->locale->get_single_value_by_key('API_USER_ERROR_NOT_FOUND');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    $handlerMessage = 'API ERROR: ' .$CMSCore->locale->get_single_value_by_key('API_ERROR_INVALID_INPUT_DATA_SET');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
}

?>