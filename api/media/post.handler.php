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

if ($system_core->client->is_logged(2)) {
  $client_user = $system_core->client->get_user(2);
  $client_user->init_data(['metadata']);
  $client_user_group = $client_user->get_group();
  $client_user_group->init_data(['permissions']);

  // Проверка прав пользователя на доступ к данному действию
  if ($client_user_group->permission_check($client_user_group::PERMISSION_EDITOR_MEDIA_FILES_MANAGEMENT)) {
    // Проверка передачи файлов (действительно ли они были переданы в массиве)
    if (!empty($_FILES)) {
      /** @var array Массив передаваемых файлов для отладки */
      $handler_output_data['debug_files'] = $_FILES;

      /** @var string Расширение передаваемого файла */
      $uploaded_file_extention = pathinfo($_FILES['mediaFile']['name'], PATHINFO_EXTENSION);
      
      /** @var array Массив разрешенных расширений передаваемых файлов */
      $file_extention_allowed = ['png', 'gif', 'jpg', 'jpeg', 'webp'];

      /** @var string Путь до загружаемых файлов */
      $uploaded_dir_path = sprintf('%s/uploads/media', CMS_ROOT_DIRECTORY);

      /* Проверка наличия директории для загружаемых файлов
       * Если директория отсутствует, то ее необходимо создать. */
      if (!file_exists($uploaded_dir_path)) {
        mkdir($uploaded_dir_path, 0777);
      }

      // Проверка наличия директории для загружаемых файлов
      if (file_exists($uploaded_dir_path)) {
        // Проверка соответствия расширения массиву разрешенных расширений загружаемых файлов
        if (in_array($uploaded_file_extention, $file_extention_allowed)) {
          // Проверка величины файла на соответствие ограничениям
          if ($system_core->configurator->get_upload_file_weight_max() >= filesize($_FILES['mediaFile']['tmp_name']) / 1024 || $system_core->configurator->get_upload_file_weight_max() == 0) {
            /** @var string Путь до загружаемых файлов */
            $file_uploaded_folder_path = sprintf('%s/uploads/media', CMS_ROOT_DIRECTORY);
            /** @var string MIME-тип загружаемого файла */
            $file_mime_type = mime_content_type($_FILES['mediaFile']['tmp_name']);

            if (preg_match('/^image\//', $file_mime_type)) {
              preg_match('/^image\/([a-z]+)/', $file_mime_type, $matches);
              /** @var EnumFileFormat Расширение файла */
              $file_extension_enum = match ($matches[1]) {
                'jpeg' => EnumFileFormat::JPG,
                'png' => EnumFileFormat::PNG,
                'webp' => EnumFileFormat::WEBP,
                'avif' => EnumFileFormat::AVIF
              };
              /** @var GdImage Изображение, созданное из загружаемого файла */
              $file_image = match ($file_extension_enum) {
                EnumFileFormat::JPG => imagecreatefromjpeg($_FILES['mediaFile']['tmp_name']),
                EnumFileFormat::PNG => imagecreatefrompng($_FILES['mediaFile']['tmp_name']),
                EnumFileFormat::WEBP => imagecreatefromwebp($_FILES['mediaFile']['tmp_name']),
                EnumFileFormat::AVIF => imagecreatefromavif($_FILES['mediaFile']['tmp_name'])
              };

              /** @var int Ширина изображения */
              $file_image_width = imagesx($file_image);
              /** @var int Высота изображения */
              $file_image_height = imagesy($file_image);

              // Уничтожаем изображение
              imagedestroy($file_image);

              // Проверка ширины изображения на соответствие ограничениям
              if ($file_image_width <= $system_core->configurator->get_upload_file_image_width_max() || $system_core->configurator->get_upload_file_image_width_max() == 0) {
                // Проверка высоты изображения на соответствие ограничениям
                if ($file_image_height <= $system_core->configurator->get_upload_file_image_height_max() || $system_core->configurator->get_upload_file_image_height_max() == 0) {
                  
                  if ($system_core->configurator->get_auto_convert_file_image_status(true)) {
                    $file_converted_extension_enum = match ($system_core->configurator->get_auto_convert_file_image_extension()) {
                      'webp' => EnumFileFormat::WEBP,
                      'avif' => EnumFileFormat::AVIF
                    };
                  } else {
                    $file_converted_extension_enum = $file_extension_enum;
                  }
                  
                  /** @var FileConverter Объект-конвектор файлов */
                  $file_converter = new FileConverter($system_core);
                  /** @var array Конвертированный файл */
                  $file_converted = $file_converter->convert($_FILES['mediaFile'], $file_uploaded_folder_path, $file_converted_extension_enum, true);
                  
                  /** @var array Данные конвертированного файла */
                  $file_data = [];
                  // URL до конвертированного файла
                  $file_data['url'] = sprintf('/uploads/media/%s', $file_converted['file_name']);
                  // Полное наименование конвертированного файла
                  $file_data['fullname'] = $file_converted['file_name'];

                  // Передача данных о загруженном файле в глобальную переменную обработчика
                  $handler_output_data['file'] = $file_data;

                  if (is_array($file_converted)) {
                    $handler_message = (!isset($handler_message)) ? $system_core->locale->get_single_value_by_key('API_POST_FILES_SUCCESS') : $handler_message;
                    $handler_status_code = (!isset($handler_status_code)) ? 1 : $handler_status_code;
                  } else {
                    $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_UNKNOWN')) : $handler_message;
                    $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
                  }
                } else {
                  $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', sprintf($system_core->locale->get_single_value_by_key('API_FILE_ERROR_TOO_HEIGHT_IMAGE'), $system_core->configurator->get_upload_file_image_height_max())) : $handler_message;
                  $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
                }
              } else {
                $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', sprintf($system_core->locale->get_single_value_by_key('API_FILE_ERROR_TOO_WIDTH_IMAGE'), $system_core->configurator->get_upload_file_image_width_max())) : $handler_message;
                $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
              }
            }
          } else {
            $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', sprintf($system_core->locale->get_single_value_by_key('API_FILE_ERROR_HEAVY_FILE'), $system_core->configurator->get_upload_file_weight_max())) : $handler_message;
            $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
          }
        } else {
          $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_FILE_ERROR_INVALID_EXTENSION')) : $handler_message;
          $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
        }
      } else {
        $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_FILE_ERROR_DIRECTORY_NOT_FOUND')) : $handler_message;
        $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
      }
    } else {
      $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_UNKNOWN')) : $handler_message;
      $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
    }
  } else {
    $handler_message = sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_DONT_HAVE_PERMISSIONS'));
    $handler_status_code = 0;
  }
} else {
  http_response_code(401);
  $handler_message = (!isset($handler_message)) ? sprintf('API ERROR: %s', $system_core->locale->get_single_value_by_key('API_ERROR_AUTHORIZATION')) : $handler_message;
  $handler_status_code = (!isset($handler_status_code)) ? 0 : $handler_status_code;
}

?>