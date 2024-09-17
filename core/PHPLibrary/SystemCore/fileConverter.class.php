<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\SystemCore {
  use \core\PHPLibrary\SystemCore\FileConverter\EnumFileFormat as EnumFileFormat;

  final class FileConverter {
    private mixed $system_core = null;
    private array|string $file = '';
    private string $convert_from = '';
    private string $convert_to = '';

    /**
     * __construct
     *
     * @param  mixed $system_core Объект SystemCore
     * @param  EnumFileFormat $format_from
     * @param  EnumFileFormat $format_to
     * @return void
     */
    public function __construct(\core\PHPLibrary\SystemCore $system_core) {
      $this->system_core = $system_core;

      // $this->convert_from = match ($format_from) {
      //   EnumFileFormat::JPG => $this->convert_from = 'jpeg',
      //   EnumFileFormat::PNG => $this->convert_from = 'png',
      //   EnumFileFormat::WEBP => $this->convert_from = 'webp',
      // };

      // $this->convert_to = match ($format_to) {
      //   EnumFileFormat::JPG => $this->convert_to = 'jpeg',
      //   EnumFileFormat::PNG => $this->convert_to = 'png',
      //   EnumFileFormat::WEBP => $this->convert_to = 'webp',
      // };
    }
    
    /**
     * Назначить файл для последующей конвертации
     *
     * @param  array|string $file
     * @return void
     */
    public function set_file(array|string $file) : void {
      $this->file = $file;
    }
    
    /**
     * Конвертация файла
     *
     * @param  array|string $file
     * @param  string $file_output_folder_path
     * @param  EnumFileFormat $convert_to
     * @return bool
     */
    public function convert(array|string $file, string $file_output_folder_path, EnumFileFormat $convert_to, bool $delete_old_file = false) : bool|array {
      $system_salt = $this->system_core->configurator->get('system_salt');
      $file_output_name = md5(sprintf('{GIRVAS:CONVERTER:%s:%d}', $system_salt, time()));
      
      if (file_exists($file_output_folder_path)) {
        $convert_to_extension = match ($convert_to) {
          EnumFileFormat::JPG => $convert_to_extension = 'jpeg',
          EnumFileFormat::PNG => $convert_to_extension = 'png',
          EnumFileFormat::WEBP => $convert_to_extension = 'webp',
          EnumFileFormat::AVIF => $convert_to_extension = 'avif',
          default => ''
        };

        if ($convert_to_extension == '') return false;

        $file_output_name = sprintf('%s.%s', $file_output_name, $convert_to_extension);
        $file_output_path = sprintf('%s/%s', $file_output_folder_path, $file_output_name);
        $file_source_path = ''; $file_extension = '';

        // Проверяем, является ли файл закодированным в Base64
        if (is_string($file)) {
          if (preg_match('/data:(\w+)\/([\w.]+);base64,/', $file, $matches)) {
            $file_extension = $matches[2];
            $file_source_name = sprintf('%s.%s', $file_output_name, $file_extension);
            $file_source_path = sprintf('%s/%s', $file_output_folder_path, $file_source_name);

            $file_open = fopen($file_source_path, 'w+');
            $file_data = explode(',', $file);
            fwrite($file_open, base64_decode($file_data[1]));
            fclose($file_open);
          }
        } else if (is_array($file)) {
          if (file_exists($file['tmp_name'])) {
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $file_source_name = sprintf('%s.%s', $file_output_name, $file_extension);
            $file_source_path = sprintf('%s/%s', $file_output_folder_path, $file_source_name);
            @move_uploaded_file($file['tmp_name'], $file_source_path);
          }
        }

        $converted_result = false;
        if ($file_source_path != '' && file_exists($file_source_path)) {
          if (($file_extension == 'jpeg' || $file_extension == 'jpg') && $convert_to_extension == 'webp') {
            $converted_result = $this->convert_jpeg_to_webp($file_source_path, $file_output_path, $delete_old_file);
          }
          
          if ($file_extension == 'png' && $convert_to_extension == 'webp') {
            $converted_result = $this->convert_png_to_webp($file_source_path, $file_output_path, $delete_old_file);
          }

          if (($file_extension == $file_extension)) {
            if (file_exists($file_source_path)) {
              $file_renamed = rename($file_source_path, $file_output_path);

              if ($file_renamed) {
                $converted_result = true;
              }
            };
          }
        }

        if ($converted_result == true) {
          return [
            'extension_old' => $file_extension,
            'extension_new' => $convert_to_extension,
            'file_name' => $file_output_name,
            'file_path' => $file_source_path
          ];
        }
      }

      return false;
    }
    
    /**
     * convert_jpg_to_webp
     *
     * @param  string $file_source_path
     * @param  string $file_output_path
     * @param  bool $delete_old_file
     * @return bool
     */
    private function convert_jpeg_to_webp(string $file_source_path, string $file_output_path, bool $delete_old_file = false) : bool {
      $image_source = imagecreatefromjpeg($file_source_path);
      $image_source_width = imagesx($image_source);
      $image_source_height = imagesy($image_source);

      $image_converted = imagecreatetruecolor($image_source_width, $image_source_height);
      imagecopy($image_converted, $image_source, 0, 0, 0, 0, $image_source_width, $image_source_height);
      imagewebp($image_converted, $file_output_path, 100);

      imagedestroy($image_source);
      imagedestroy($image_converted);

      if ($delete_old_file) unlink($file_source_path);

      return file_exists($file_output_path);
    }
    
    /**
     * convert_jpg_to_avif
     *
     * @param  string $file_source_path
     * @param  string $file_output_path
     * @param  bool $delete_old_file
     * @return bool
     */
    private function convert_jpeg_to_avif(string $file_source_path, string $file_output_path, bool $delete_old_file = false) : bool {
      $image_source = imagecreatefromjpeg($file_source_path);
      $image_source_width = imagesx($image_source);
      $image_source_height = imagesy($image_source);

      $image_converted = imagecreatetruecolor($image_source_width, $image_source_height);
      imagecopy($image_converted, $image_source, 0, 0, 0, 0, $image_source_width, $image_source_height);
      imageavif($image_converted, $file_output_path);

      imagedestroy($image_source);
      imagedestroy($image_converted);

      if ($delete_old_file) unlink($file_source_path);

      return file_exists($file_output_path);
    }
    
    /**
     * convert_png_to_webp
     *
     * @param  string $file_source_path
     * @param  string $file_output_path
     * @param  bool $delete_old_file
     * @return bool
     */
    private function convert_png_to_webp(string $file_source_path, string $file_output_path, bool $delete_old_file = false) : bool {
      $image_source = imagecreatefrompng($file_source_path);
      $image_source_width = imagesx($image_source);
      $image_source_height = imagesy($image_source);

      $image_converted = imagecreatetruecolor($image_source_width, $image_source_height);
      imageAlphaBlending($image_converted, false);
      imageSaveAlpha($image_converted, true);

      $image_transparent = imagecolorallocatealpha($image_converted, 0, 0, 0, 127);
      imagefilledrectangle($image_converted, 0, 0, $image_source_width - 1, $image_source_height - 1, $image_transparent);

      imagecopy($image_converted, $image_source, 0, 0, 0, 0, $image_source_width, $image_source_height);
      imagewebp($image_converted, $file_output_path, 100);

      imagedestroy($image_source);
      imagedestroy($image_converted);

      if ($delete_old_file) unlink($file_source_path);

      return file_exists($file_output_path);
    }
    
    /**
     * convert_png_to_avif
     *
     * @param  string $file_source_path
     * @param  string $file_output_path
     * @param  bool $delete_old_file
     * @return bool
     */
    private function convert_png_to_avif(string $file_source_path, string $file_output_path, bool $delete_old_file = false) : bool {
      $image_source = imagecreatefrompng($file_source_path);
      $image_source_width = imagesx($image_source);
      $image_source_height = imagesy($image_source);

      $image_converted = imagecreatetruecolor($image_source_width, $image_source_height);
      imageAlphaBlending($image_converted, false);
      imageSaveAlpha($image_converted, true);

      $image_transparent = imagecolorallocatealpha($image_converted, 0, 0, 0, 127);
      imagefilledrectangle($image_converted, 0, 0, $image_source_width - 1, $image_source_height - 1, $image_transparent);

      imagecopy($image_converted, $image_source, 0, 0, 0, 0, $image_source_width, $image_source_height);
      imageavif($image_converted, $file_output_path);

      imagedestroy($image_source);
      imagedestroy($image_converted);

      if ($delete_old_file) unlink($file_source_path);

      return file_exists($file_output_path);
    }
  }
}

?>