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

use \core\PHPLibrary\SystemCore\FileConverter as FileConverter;
use \core\PHPLibrary\SystemCore\FileConverter\EnumFileFormat as EnumFileFormat;
use \GdImage as GdImage;

if ($CMSCore->client->is_logged(2)) {
  $clientUser = $CMSCore->client->get_user(2);
  $clientUser->init_data(['metadata']);
  $clientUserGroup = $clientUser->get_group();
  $clientUserGroup->init_data(['permissions']);

  // Проверка прав пользователя на доступ к данному действию
  if ($clientUserGroup->permission_check($clientUserGroup::PERMISSION_EDITOR_MEDIA_FILES_MANAGEMENT)) {
    // Проверка передачи файлов (действительно ли они были переданы в массиве)
    if (!empty($_FILES)) {
      /** @var array Массив передаваемых файлов для отладки */
      $handlerOutputData['debug_files'] = $_FILES;

      /** @var string Расширение передаваемого файла */
      $fileUploadedExtension = pathinfo($_FILES['mediaFile']['name'], PATHINFO_EXTENSION);
      
      /** @var array Массив разрешенных расширений передаваемых файлов */
      $fileExtensionsAllowed = ['png', 'gif', 'jpg', 'jpeg', 'webp'];

      /** @var string Путь до загружаемых файлов */
      $filesDirectoryPath = CMS_ROOT_DIRECTORY . '/uploads/media';

      /* Проверка наличия директории для загружаемых файлов
       * Если директория отсутствует, то ее необходимо создать. */
      if (!file_exists($filesDirectoryPath)) {
        mkdir($filesDirectoryPath, 0777);
      }

      // Проверка наличия директории для загружаемых файлов
      if (file_exists($filesDirectoryPath)) {
        // Проверка соответствия расширения массиву разрешенных расширений загружаемых файлов
        if (in_array($fileUploadedExtension, $fileExtensionsAllowed)) {
          // Проверка величины файла на соответствие ограничениям
          if ($CMSCore->configurator->get_upload_file_weight_max() >= filesize($_FILES['mediaFile']['tmp_name']) / 1024 || $CMSCore->configurator->get_upload_file_weight_max() == 0) {
            /** @var string Путь до загружаемых файлов */
            $fileDirectoryPath = CMS_ROOT_DIRECTORY . '/uploads/media';
            /** @var string MIME-тип загружаемого файла */
            $fileMIMEType = mime_content_type($_FILES['mediaFile']['tmp_name']);

            if (preg_match('/^image\//', $fileMIMEType)) {
              preg_match('/^image\/([a-z]+)/', $fileMIMEType, $matches);
              /** @var EnumFileFormat Расширение файла */
              $fileExtensionEnum = match ($matches[1]) {
                'jpeg' => EnumFileFormat::JPG,
                'png' => EnumFileFormat::PNG,
                'webp' => EnumFileFormat::WEBP,
                'avif' => EnumFileFormat::AVIF
              };
              /** @var GdImage Изображение, созданное из загружаемого файла */
              $image = match ($fileExtensionEnum) {
                EnumFileFormat::JPG => imagecreatefromjpeg($_FILES['mediaFile']['tmp_name']),
                EnumFileFormat::PNG => imagecreatefrompng($_FILES['mediaFile']['tmp_name']),
                EnumFileFormat::WEBP => imagecreatefromwebp($_FILES['mediaFile']['tmp_name']),
                EnumFileFormat::AVIF => imagecreatefromavif($_FILES['mediaFile']['tmp_name'])
              };

              /** @var int Ширина изображения */
              $imageWidth = imagesx($image);
              /** @var int Высота изображения */
              $imageHeight = imagesy($image);

              // Уничтожаем изображение
              imagedestroy($image);

              // Проверка ширины изображения на соответствие ограничениям
              if ($imageWidth <= $CMSCore->configurator->get_upload_file_image_width_max() || $CMSCore->configurator->get_upload_file_image_width_max() === 0) {
                // Проверка высоты изображения на соответствие ограничениям
                if ($imageHeight <= $CMSCore->configurator->get_upload_file_image_height_max() || $CMSCore->configurator->get_upload_file_image_height_max() === 0) {
                  
                  if ($CMSCore->configurator->get_auto_convert_file_image_status(true)) {
                    $fileExtensionConvertedEnum = match ($CMSCore->configurator->get_auto_convert_file_image_extension()) {
                      'webp' => EnumFileFormat::WEBP,
                      'avif' => EnumFileFormat::AVIF
                    };
                  } else {
                    $fileExtensionConvertedEnum = $fileExtensionEnum;
                  }
                  
                  /** @var FileConverter Объект-конвектор файлов */
                  $fileConverter = new FileConverter($CMSCore);
                  /** @var array Конвертированный файл */
                  $fileConverted = $fileConverter->convert($_FILES['mediaFile'], $fileDirectoryPath, $fileExtensionConvertedEnum, true);
                  
                  /** @var array Данные конвертированного файла */
                  $fileData = [];
                  // URL до конвертированного файла
                  $fileData['url'] = '/uploads/media/' . $fileConverted['file_name'];
                  // Полное наименование конвертированного файла
                  $fileData['fullname'] = $fileConverted['file_name'];

                  // Передача данных о загруженном файле в глобальную переменную обработчика
                  $handlerOutputData['file'] = $fileData;

                  if (is_array($fileConverted)) {
                    $handlerMessage = $handlerMessage ?? $CMSCore->locale->get_single_value_by_key('API_POST_FILES_SUCCESS');
                    $handlerStatusCode = $handlerStatusCode ?? 1;
                  } else {
                    $handlerMessage = $handlerMessage ?? 'API ERROR: ' . $CMSCore->locale->get_single_value_by_key('API_ERROR_UNKNOWN');
                    $handlerStatusCode = $handlerStatusCode ?? 0;
                  }
                } else {
                  $handlerMessage = $handlerMessage ?? 'API ERROR: ' . sprintf($CMSCore->locale->get_single_value_by_key('API_FILE_ERROR_TOO_HEIGHT_IMAGE'), $CMSCore->configurator->get_upload_file_image_height_max());
                  $handlerStatusCode = $handlerStatusCode ?? 0;
                }
              } else {
                $handlerMessage = $handlerMessage ?? 'API ERROR: ' . sprintf($CMSCore->locale->get_single_value_by_key('API_FILE_ERROR_TOO_WIDTH_IMAGE'), $CMSCore->configurator->get_upload_file_image_width_max());
                $handlerStatusCode = $handlerStatusCode ?? 0;
              }
            }
          } else {
            $handlerMessage = $handlerMessage ?? sprintf('API ERROR: %s', sprintf($CMSCore->locale->get_single_value_by_key('API_FILE_ERROR_HEAVY_FILE'), $CMSCore->configurator->get_upload_file_weight_max()));
            $handlerStatusCode = $handlerStatusCode ?? 0;
          }
        } else {
          $handlerMessage = $handlerMessage ?? sprintf('API ERROR: %s', $CMSCore->locale->get_single_value_by_key('API_FILE_ERROR_INVALID_EXTENSION'));
          $handlerStatusCode = $handlerStatusCode ?? 0;
        }
      } else {
        $handlerMessage = $handlerMessage ?? sprintf('API ERROR: %s', $CMSCore->locale->get_single_value_by_key('API_FILE_ERROR_DIRECTORY_NOT_FOUND'));
        $handlerStatusCode = $handlerStatusCode ?? 0;
      }
    } else {
      $handlerMessage = $handlerMessage ?? sprintf('API ERROR: %s', $CMSCore->locale->get_single_value_by_key('API_ERROR_UNKNOWN'));
      $handlerStatusCode = $handlerStatusCode ?? 0;
    }
  } else {
    $handlerMessage = sprintf('API ERROR: %s', $CMSCore->locale->get_single_value_by_key('API_ERROR_DONT_HAVE_PERMISSIONS'));
    $handlerStatusCode = 0;
  }
} else {
  http_response_code(401);
  $handlerMessage = $handlerMessage ?? sprintf('API ERROR: %s', $CMSCore->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION'));
  $handlerStatusCode = $handlerStatusCode ?? 0;
}

?>