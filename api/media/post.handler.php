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

use \core\PHPLibrary\SystemCore\File\Converter as FileConverter;
use \core\PHPLibrary\SystemCore\File\EnumFormat as EnumFileFormat;
use \GdImage as GdImage;

if ($CMSCore->client->isLogged(2)) {
  $clientUser = $CMSCore->client->getUser(2);
  $clientUser->initData(['metadata']);
  $clientUserGroup = $clientUser->getGroup();
  $clientUserGroup->initData(['permissions']);

  // Проверка прав пользователя на доступ к данному действию
  if ($clientUserGroup->permissionCheck($clientUserGroup::PERMISSION_EDITOR_MEDIA_FILES_MANAGEMENT)) {
    // Проверка передачи файлов (действительно ли они были переданы в массиве)
    if (!empty($_FILES) && isset($_FILES['mediaFile'])) {
      /** @var array Массив передаваемых файлов для отладки */
      $handlerOutputData['debug_files'] = $_FILES;

      /** @var string Расширение передаваемого файла */
      $fileUploadedExtension = pathinfo($_FILES['mediaFile']['name'], PATHINFO_EXTENSION);
      
      /** @var array Массив разрешенных расширений передаваемых файлов */
      $fileExtensionsAllowed = ['png', 'gif', 'jpg', 'jpeg', 'webp', 'gif', 'pdf'];

      /** @var string Путь до загружаемых файлов */
      $filesDirectoryPath = CMS_ROOT_DIRECTORY . '/uploads/media';

      $handlerUploaderErrorCode = $_FILES['mediaFile']['error'];
      $handlerUploaderErrorMessage = match($handlerUploaderErrorCode) {
        UPLOAD_ERR_INI_SIZE => $CMSCore->locale->getSingleValueByKey('API_FILE_ERROR_UPLOAD_INI_SIZE'),
        UPLOAD_ERR_FORM_SIZE => $CMSCore->locale->getSingleValueByKey('API_FILE_ERROR_UPLOAD_FORM_SIZE'),
        UPLOAD_ERR_PARTIAL => $CMSCore->locale->getSingleValueByKey('API_FILE_ERROR_UPLOAD_PARTIAL'),
        UPLOAD_ERR_NO_FILE => $CMSCore->locale->getSingleValueByKey('API_FILE_ERROR_UPLOAD_NO_FILE'),
        UPLOAD_ERR_NO_TMP_DIR => $CMSCore->locale->getSingleValueByKey('API_FILE_ERROR_UPLOAD_NO_TMP_DIR'),
        UPLOAD_ERR_CANT_WRITE => $CMSCore->locale->getSingleValueByKey('API_FILE_ERROR_UPLOAD_CANT_WRITE'),
        UPLOAD_ERR_EXTENSION => $CMSCore->locale->getSingleValueByKey('API_FILE_ERROR_UPLOAD_EXTENSION'),
        default => sprintf($CMSCore->locale->getSingleValueByKey('API_FILE_ERROR_UPLOAD_EXTENSION'), $handlerUploaderErrorCode)
      };
      
      if ($handlerUploaderErrorCode === 0) {
        if (!file_exists($filesDirectoryPath)) {
          mkdir($filesDirectoryPath, 0777);
        }

        if (file_exists($filesDirectoryPath)) {
          // Проверка соответствия расширения массиву разрешенных расширений загружаемых файлов
          if (in_array($fileUploadedExtension, $fileExtensionsAllowed)) {
            // Проверка величины файла на соответствие ограничениям
            if ($CMSCore->configurator->getUploadFileWeightMax() >= filesize($_FILES['mediaFile']['tmp_name']) / 1024 || $CMSCore->configurator->getUploadFileWeightMax() == 0) {
              $fileDirectoryPath = CMS_ROOT_DIRECTORY . '/uploads/media';
              $fileMIMEType = mime_content_type($_FILES['mediaFile']['tmp_name']);

              if (preg_match('/^image\//', $fileMIMEType)) {
                preg_match('/^image\/([a-z]+)/', $fileMIMEType, $matches);
                $fileExtension = $matches[1];
                
                $fileExtensionEnum = match ($fileExtension) {
                  'jpeg' => EnumFileFormat::JPG,
                  'png' => EnumFileFormat::PNG,
                  'webp' => EnumFileFormat::WEBP,
                  'avif' => EnumFileFormat::AVIF,
                  'gif' => EnumFileFormat::GIF
                };

                $image = match ($fileExtensionEnum) {
                  EnumFileFormat::JPG => imagecreatefromjpeg($_FILES['mediaFile']['tmp_name']),
                  EnumFileFormat::PNG => imagecreatefrompng($_FILES['mediaFile']['tmp_name']),
                  EnumFileFormat::WEBP => imagecreatefromwebp($_FILES['mediaFile']['tmp_name']),
                  EnumFileFormat::AVIF => imagecreatefromavif($_FILES['mediaFile']['tmp_name']),
                  EnumFileFormat::GIF => imagecreatefromgif($_FILES['mediaFile']['tmp_name'])
                };

                /** @var int Ширина изображения */
                $imageWidth = imagesx($image);
                /** @var int Высота изображения */
                $imageHeight = imagesy($image);

                // Уничтожаем изображение
                imagedestroy($image);

                // Проверка ширины изображения на соответствие ограничениям
                if ($imageWidth <= $CMSCore->configurator->getUploadFileImageWidthMax() || $CMSCore->configurator->getUploadFileImageWidthMax() === 0) {
                  // Проверка высоты изображения на соответствие ограничениям
                  if ($imageHeight <= $CMSCore->configurator->getUploadFileImageHeightMax() || $CMSCore->configurator->getUploadFileImageHeightMax() === 0) {
                    // Проверка включения автоматической конвертации изображений
                    if ($CMSCore->configurator->getAutoConvertFileImageStatus(true)) {
                      if ($fileExtensionEnum === EnumFileFormat::GIF) {
                        $fileExtensionConvertedEnum = $fileExtensionEnum;
                        error_log('adfsdfasd');
                      } else {
                        $fileExtensionConvertedEnum = match ($CMSCore->configurator->getAutoConvertFileImageExtension()) {
                          'webp' => EnumFileFormat::WEBP,
                          'avif' => EnumFileFormat::AVIF
                        };
                      }
                    } else {
                      $fileExtensionConvertedEnum = $fileExtensionEnum;
                    }

                    $qualityPercent = 100 - $CMSCore->configurator->getUploadImageCompression();
                    if ($qualityPercent <= 0) {
                      $quality = -1;
                    } else {
                      $quality = min(9, max(0, (int) round(($qualityPercent / 100) * 9)));
                    }
                    
                    /** @var FileConverter Объект-конвектор файлов */
                    $fileConverter = new FileConverter($CMSCore);
                    /** @var array Конвертированный файл */
                    $fileConverted = $fileConverter->convert($_FILES['mediaFile'], $fileDirectoryPath, $fileExtensionConvertedEnum, true, 4658, $quality);

                    /** @var array Данные конвертированного файла */
                    $fileData = [];

                    $fileData['URL'] = '/uploads/media/' . $fileConverted['fileName'];
                    $fileData['isDirectory'] = is_dir(CMS_ROOT_DIRECTORY . $fileData['URL']);
                    $fileData['fullname'] = $fileConverted['fileName'];
                    $fileData['extension'] = $fileConverted['extensionNew'];
                    
                    $handlerOutputData['file'] = $fileData;

                    if (is_array($fileConverted)) {
                      $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_POST_FILES_SUCCESS');
                      $handlerStatusCode = $handlerStatusCode ?? 1;
                    } else {
                      $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_UNKNOWN');
                      $handlerStatusCode = $handlerStatusCode ?? 0;
                    }
                  } else {
                    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . sprintf($CMSCore->locale->getSingleValueByKey('API_FILE_ERROR_TOO_HEIGHT_IMAGE'), $CMSCore->configurator->getUploadFileImageHeightMax());
                    $handlerStatusCode = $handlerStatusCode ?? 0;
                  }
                } else {
                  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . sprintf($CMSCore->locale->getSingleValueByKey('API_FILE_ERROR_TOO_WIDTH_IMAGE'), $CMSCore->configurator->getUploadFileImageWidthMax());
                  $handlerStatusCode = $handlerStatusCode ?? 0;
                }
              }
            } else {
              $handlerMessage = $handlerMessage ?? 'API ERROR: ' . sprintf($CMSCore->locale->getSingleValueByKey('API_FILE_ERROR_HEAVY_FILE'), $CMSCore->configurator->getUploadFileWeightMax());
              $handlerStatusCode = $handlerStatusCode ?? 0;
            }
          } else {
            $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_FILE_ERROR_INVALID_EXTENSION');
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }
        } else {
          $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_FILE_ERROR_DIRECTORY_NOT_FOUND');
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      } else {
        $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $handlerUploaderErrorMessage;
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
  http_response_code(401);
  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->getSingleValueByKey('API_ERROR_AUTHORIZATION');
  $handlerStatusCode = $handlerStatusCode ?? 0;
}