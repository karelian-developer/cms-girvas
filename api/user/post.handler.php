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

use \core\PHPLibrary\Template as Theme;
use \core\PHPLibrary\Template\Collector as ThemeCollector;
use \core\PHPLibrary\Mail\SMTPClient as SMTPClient;
use \core\PHPLibrary\User as User;
use \core\PHPLibrary\User\Consent as UserConsent;
use \core\PHPLibrary\UserGroup as UserGroup;
use \core\PHPLibrary\SystemCore\Report as Report;
use \core\PHPLibrary\SystemCore\Reports as Reports;
use \core\PHPLibrary\SystemCore\File\Converter as FileConverter;
use \core\PHPLibrary\SystemCore\File\EnumFormat as EnumFileFormat;
use \ZipArchive;

/**
 * Загрузка аватара для пользователя
 */
if ($CMSCore->urlp->getPath(2) === 'avatar') {
  $userID = trim($_POST['user_id']) ?? 0;
  $userID = is_numeric($userID) ? (int) $userID : 0;

  if (User::existsByID($CMSCore, $userID)) {
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
            $imageOriginalPath = CMS_ROOT_DIRECTORY . '/uploads/avatars/' . (string) $userID . '/' . $fileConverted['fileName'];
            
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

            $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_POST_FILES_SUCCESS');
            $handlerStatusCode = $handlerStatusCode ?? 1;
          } else {
            $handlerMessage = $handlerMessage ?? 'API ERROR: ' .$CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }
        }
      }

    } else {
      $handlerMessage = $handlerMessage ?? 'API ERROR: ' .$CMSCore->locale->getSingleValueByKey('API_ERROR_INVALID_INPUT_DATA_SET');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    $handlerMessage = $handlerMessage ?? 'API ERROR: ' .$CMSCore->locale->getSingleValueByKey('API_USER_ERROR_NOT_FOUND');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
}

/**
 * Экспорт данных субъекта (право на доступ, ст. 14 152-ФЗ)
 */
if ($CMSCore->urlp->getPath(2) === 'export') {
  if ($CMSCore->client->isLogged(2)) {
    $clientUser = $CMSCore->client->getUser(2);
    $clientUser->initData(['login', 'metadata']);
    $clientUserGroup = $clientUser->getGroup();
    $clientUserGroup->initData(['permissions']);

    $hasAccess = $clientUserGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_USERS_DATA_EXPORT)
      || $clientUserGroup->permissionCheck(UserGroup::PERMISSION_ADMIN_SUPERUSER);

    if (!$hasAccess) {
      http_response_code(403);
      $handlerMessage = 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_DONT_HAVE_PERMISSIONS');
      $handlerStatusCode = $handlerStatusCode ?? 0;
      return;
    }

    $subjectUserID = ($CMSCore->urlp->getPath(3) !== null && is_numeric($CMSCore->urlp->getPath(3)))
      ? (int) $CMSCore->urlp->getPath(3)
      : 0;

    if ($subjectUserID <= 0 || !User::existsByID($CMSCore, $subjectUserID)) {
      http_response_code(404);
      $handlerMessage = 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_SUBJECT_DATA_EXPORT_ERROR_NOT_FOUND');
      $handlerStatusCode = $handlerStatusCode ?? 0;
      return;
    }

    $subject = new User($CMSCore, $subjectUserID);
    $subject->initData();

    $localeName = $CMSCore->locale->getName();

    // ============================================================
    // 1. profile.json
    // ============================================================
    $profileData = [
      'id' => $subject->getID(),
      'login' => $subject->getLogin(),
      'email' => $subject->getEmail(),
      'emailIsSubmitted' => $subject->emailIsSubmitted(),
      'name' => $subject->getName(),
      'surname' => $subject->getSurname(),
      'patronymic' => $subject->getPatronymic(),
      'birthdate' => $subject->getBirthdateUnixTimestamp() > 0
        ? date('Y-m-d', $subject->getBirthdateUnixTimestamp())
        : null,
      'registrationIP' => $subject->getRegistrationIP(),
      'groupID' => $subject->getGroupID(),
      'isBlocked' => $subject->isBlocked(),
      'createdAt' => date('c', $subject->getCreatedUnixTimestamp()),
      'updatedAt' => date('c', $subject->getUpdatedUnixTimestamp()),
    ];

    // ============================================================
    // 2. consents.csv
    // ============================================================
    $consentsCsv = "\xEF\xBB\xBF";
    $consentsCsv .= "ID;Документ;Версия;Локаль;Источник;Статус;Дата согласия;IP;User-Agent;Дата отзыва;Причина отзыва;Кто отозвал\n";

    $consents = UserConsent::getAllByUser($CMSCore, $subject->getID(), false);
    foreach ($consents as $consent) {
      $consent->initData();

      $documentTitle = '';
      if ($consent->getPageStaticID() > 0) {
        $pageStatic = new PageStatic($CMSCore, $consent->getPageStaticID());
        if ($pageStatic !== null) {
          $pageStatic->initData(['texts', 'name']);
          $documentTitle = $pageStatic->getTitle($localeName) ?: $pageStatic->getName();
        }
      }

      $revokedByLogin = '';
      if ($consent->getRevokedByID() > 0 && User::existsByID($CMSCore, $consent->getRevokedByID())) {
        $revoker = new User($CMSCore, $consent->getRevokedByID());
        $revoker->initData(['login']);
        $revokedByLogin = $revoker->getLogin();
      }

      $consentsCsv .= implode(';', [
        $consent->getID(),
        '"' . str_replace('"', '""', $documentTitle) . '"',
        $consent->getDocumentVersion(),
        $consent->getLocale(),
        $consent->getSource(),
        $consent->isRevoked() ? 'Отозвано' : 'Активно',
        date('d.m.Y H:i:s', $consent->getConsentedAt()),
        $consent->getIP(),
        '"' . str_replace('"', '""', $consent->getUserAgent()) . '"',
        $consent->isRevoked() ? date('d.m.Y H:i:s', $consent->getRevokedAt()) : '',
        '"' . str_replace('"', '""', $consent->getRevokeReason()) . '"',
        $revokedByLogin,
      ]) . "\n";
    }

    // ============================================================
    // 3. reports.csv
    // ============================================================
    $reportsCsv = "\xEF\xBB\xBF";
    $reportsCsv .= "ID;Тип;Дата;Переменные\n";

    $reports = Reports::getAllByUser($CMSCore, $subject->getID(), 10000, 0);
    foreach ($reports as $report) {
      $report->initData(['metadata', 'variables', 'createdUnixTimestamp']);
      $reportsCsv .= implode(';', [
        $report->getID(),
        $report->getTypeID(),
        date('d.m.Y H:i:s', $report->getCreatedUnixTimestamp()),
        '"' . str_replace('"', '""', json_encode($report->getMetadata(), JSON_UNESCAPED_UNICODE)) . '"',
        '"' . str_replace('"', '""', json_encode($report->getVariables(), JSON_UNESCAPED_UNICODE)) . '"',
      ]) . "\n";
    }

    // ============================================================
    // 4. manifest.json
    // ============================================================
    $manifest = [
      'generatedAt' => date('c'),
      'generatedBy' => [
        'id' => $clientUser->getID(),
        'login' => $clientUser->getLogin(),
      ],
      'subject' => [
        'id' => $subject->getID(),
        'login' => $subject->getLogin(),
      ],
      'components' => ['profile', 'consents', 'reports'],
      'cmsVersion' => $CMSCore::CMS_VERSION,
      'ip' => $CMSCore->client->getIPAddress(),
    ];

    // ============================================================
    // 5. Сборка ZIP
    // ============================================================
    $tmpPath = tempnam(sys_get_temp_dir(), 'girvas_subject_');
    $zip = new ZipArchive();
    if ($zip->open($tmpPath, ZipArchive::OVERWRITE) !== true) {
      $handlerMessage = 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
      $handlerStatusCode = 0;
      return;
    }

    $zip->addFromString('profile.json', json_encode($profileData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    $zip->addFromString('consents.csv', $consentsCsv);
    $zip->addFromString('reports.csv', $reportsCsv);
    $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    $zip->close();

    // ============================================================
    // 6. Логирование
    // ============================================================
    Report::create(
      $CMSCore,
      Report::REPORT_TYPE_ID_BASE_SUBJECT_DATA_EXPORTED,
      [
        'subjectUserID' => $subject->getID(),
        'subjectUserLogin' => $subject->getLogin(),
        'exportedByID' => $clientUser->getID(),
        'exportedByLogin' => $clientUser->getLogin(),
        'format' => 'zip',
        'components' => ['profile', 'consents', 'reports'],
        'ip' => $CMSCore->client->getIPAddress(),
      ]
    );

    // ============================================================
    // 7. Отдача файла
    // ============================================================
    $fileName = sprintf(
      'subject_%s_%s.zip',
      preg_replace('/[^a-zA-Z0-9_\-]/', '_', $subject->getLogin()),
      date('Y-m-d_H-i-s')
    );

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Length: ' . filesize($tmpPath));
    readfile($tmpPath);
    unlink($tmpPath);
    exit;
  } else {
    http_response_code(401);
    $handlerMessage = 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
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
if ($CMSCore->urlp->getPath(2) == 'reset') {
  /** @var string Логин или e-mail пользователя */
  $userLoginOrEmail = $_POST['user_login_or_email'] ?? '';

  if (!empty($userLoginOrEmail)) {
    /** @var User|null Объект пользователя */
    $user = null;

    if (User::emailIsValid($CMSCore, $userLoginOrEmail)) {
      if (User::existsByEmail($CMSCore, $userLoginOrEmail)) {
        $user = User::getByEmail($CMSCore, $userLoginOrEmail);
      }
    } else {
      if (User::existsByLogin($CMSCore, $userLoginOrEmail)) {
        $user = User::getByLogin($CMSCore, $userLoginOrEmail);
      }
    }

    if (!is_null($user)) {
      $user->initData(['login', 'email', 'metadata']);
      /** @var string Заголовок веб-сайта */
      $siteTitle = $CMSCore->configurator->getMetaTitle() === ''
        ? $CMSCore->configurator->getSiteTitle()
        : $CMSCore->configurator->getMetaTitle();
      
      $userEmail = $user->getEmail();
      $userLogin = $user->getLogin();

      $themeBaseName = $CMSCore->configurator->existsDatabaseEntryValue('base_template')
        ? $CMSCore->configurator->getDatabaseEntryValue('base_template')
        : 'default';
      $theme = new Theme($CMSCore, $themeBaseName);

      $SMTPConfiguration = $CMSCore->configurator->getOtherCollection('smtp');
      $CMSEmail = 'no-reply@' . $SMTPConfiguration['domain'];
      /** @var int Временная отметка в UNIX-формате создания заявки на сброс пароля */
      $resetPasswordCreatedUnixTimestamp = time();
      /** @var string Токен сброса пароля */
      $resetPasswordToken = md5($resetPasswordCreatedUnixTimestamp . $CMSCore::CMS_VERSION);

      try {
        $SMTPClient = new SMTPClient(
          $SMTPConfiguration['host'],
          $SMTPConfiguration['port'],
          $SMTPConfiguration['username'],
          $SMTPConfiguration['password']
        );

        $SMTPClient->connect();
        $SMTPClient->login();

        $mailTitle = $CMSCore->locale->getSingleValueByKey('API_USER_REQUEST_PASSWORD_RESET_EMAIL_TITLE');
        $mailContentText = $CMSCore->locale->getSingleValueByKey('API_USER_REQUEST_PASSWORD_RESET_EMAIL_CONTENT');
        $mailContent = ThemeCollector::assemblyFileContent($theme, 'templates/email/default.tpl', [
          'EMAIL_TITLE' => $mailTitle,
          'EMAIL_CONTENT' => sprintf(
            $mailContentText,
            $userLogin,
            $CMSCore->getSiteURL() . '/password-reset?token=' . $resetPasswordToken
          ),
          'EMAIL_COPYRIGHT' => $CMSCore->locale->getSingleValueByKey('API_USER_REQUEST_PASSWORD_RESET_EMAIL_COPYRIGHT')
        ]);

        $SMTPClient->sendEmail($CMSEmail, $userEmail, $mailTitle, $mailContent, true);
        $SMTPClient->disconnect();

        /** @var int Временная отметка в UNIX-формате создания заявки на сброс пароля */
        $resetPasswordCreatedUnixTimestamp = time();
        $user->update(
          [
            'metadata' => [
              'passwordResetToken' => $resetPasswordToken,
              'passwordResetTokenCreatedUnixTimestamp' => $resetPasswordCreatedUnixTimestamp
            ]
          ]
        );

        $handlerMessage = $CMSCore->locale->getSingleValueByKey('API_USER_REQUEST_PASSWORD_RESET_SENDED_SUCCESS');
        $handlerStatusCode = $handlerStatusCode ?? 1;
      } catch (Exception $exception) {
        $handlerMessage = 'API ERROR: ' . $exception;
        $handlerStatusCode = 0;
      }
    } else {
      $handlerMessage = 'API ERROR: ' .$CMSCore->locale->getSingleValueByKey('API_USER_ERROR_NOT_FOUND');
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    $handlerMessage = 'API ERROR: ' .$CMSCore->locale->getSingleValueByKey('API_ERROR_INVALID_INPUT_DATA_SET');
    $handlerStatusCode = $handlerStatusCode ?? 0;
  }
}