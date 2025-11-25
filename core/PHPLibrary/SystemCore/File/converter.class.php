<?php

/**
 * CMS «ГИРВАС»
 * 
 * Включена в Реестр российского программного обеспечения Минцифры РФ
 * Реестровый номер: №25012 от 27.11.2024
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Репозиторий продукта
 * @link        https://cms-girvas.ru Сайт продукта
 * 
 * @copyright   Copyright (c) 2021 - 2026, ИП Шестаков А.Р., «Карельский разработчик» (https://карельский-разработчик.рф/)
 * Все права защищены.
 * 
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 * @author      Андрей Шестаков <andrey.shestakov@karelian-developer.ru>
 * 
 * @support     support@karelian-developer.ru
 */

namespace core\PHPLibrary\SystemCore\File;

use \core\PHPLibrary\SystemCore as CMSCore;
use \core\PHPLibrary\SystemCore\File\EnumFormat as EnumFileFormat;

final class Converter implements InterfaceConverter
{
  private ?CMSCore $CMSCore = null;
  private array|string $file = '';
  private string $convertFrom = '';
  private string $convertTo = '';

  /**
   * __construct
   *
   * @param  mixed $CMSCore Объект SystemCore
   * 
   * @return void
   */
  public function __construct(CMSCore $CMSCore)
  {
    $this->CMSCore = $CMSCore;
  }
  
  /**
   * Назначить файл для последующей конвертации
   *
   * @param  array|string $file
   * 
   * @return void
   */
  public function setFile(array|string $file) : void
  {
    $this->file = $file;
  }
  
  /**
   * Конвертация файла
   *
   * @param array|string $file
   * @param string $fileOutputFolderPath
   * @param EnumFileFormat $convertTo
   * @param bool $deleteOldFile
   * @param mixed $salt
   * @param int $quality
   * 
   * @return bool
   */
  public function convert(array|string $file, string $fileOutputFolderPath, EnumFileFormat $convertTo, bool $deleteOldFile = false, mixed $salt = 0, int $quality = -1) : bool|array
  {
    $CMSSalt = $this->CMSCore->configurator->get('salt');
    $fileOutputName = md5(sprintf('{GIRVAS:CONVERTER:%s:%d+%s}', $CMSSalt, time(), $salt));
    
    if (file_exists($fileOutputFolderPath)) {
      $convertToExtension = match ($convertTo) {
        EnumFileFormat::JPG => $convertToExtension = 'jpeg',
        EnumFileFormat::PNG => $convertToExtension = 'png',
        EnumFileFormat::WEBP => $convertToExtension = 'webp',
        EnumFileFormat::AVIF => $convertToExtension = 'avif',
        default => ''
      };

      if ($convertToExtension === '') return false;

      $fileOutputName = $fileOutputName . '.' . $convertToExtension;
      $fileOutputPath = $fileOutputFolderPath . '/' . $fileOutputName;
      $fileSourcePath = ''; $fileExtension = '';

      // Проверяем, является ли файл закодированным в Base64
      if (is_string($file)) {
        if (preg_match('/data:(\w+)\/([\w.]+);base64,/', $file, $matches)) {
          $fileExtension = $matches[2];

          $fileSourceName = $fileOutputName . '.' . $fileExtension;
          $fileSourcePath = $fileOutputFolderPath . '/' . $fileSourceName;

          $fileOpen = fopen($fileSourcePath, 'w+');
          $fileData = explode(',', $file);
          fwrite($fileOpen, base64_decode($fileData[1]));
          fclose($fileOpen);
        }
      } else if (is_array($file)) {
        if (file_exists($file['tmp_name'])) {
          $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
          $fileSourceName = $fileOutputName . '.' . $fileExtension;
          $fileSourcePath = $fileOutputFolderPath . '/' . $fileSourceName;
          @move_uploaded_file($file['tmp_name'], $fileSourcePath);
        }
      }

      $convertedResult = false;
      if ($fileSourcePath != '' && file_exists($fileSourcePath)) {
        if (($fileExtension === 'jpeg' || $fileExtension === 'jpg') && $convertToExtension === 'png') {
          $convertedResult = $this->convertJPEGToPNG($fileSourcePath, $fileOutputPath, $deleteOldFile, $quality);
        }

        if (($fileExtension === 'jpeg' || $fileExtension === 'jpg') && $convertToExtension === 'webp') {
          $convertedResult = $this->convertJPEGToWEBP($fileSourcePath, $fileOutputPath, $deleteOldFile), $quality;
        }

        if (($fileExtension === 'jpeg' || $fileExtension === 'jpg') && $convertToExtension === 'avif') {
          $convertedResult = $this->convertJPEGToAVIF($fileSourcePath, $fileOutputPath, $deleteOldFile, $quality);
        }
        
        if ($fileExtension === 'png' && ($convertToExtension === 'jpeg' || $convertToExtension === 'jpg')) {
          $convertedResult = $this->convertPNGToJPEG($fileSourcePath, $fileOutputPath, $deleteOldFile, $quality);
        }
        
        if ($fileExtension === 'png' && $convertToExtension === 'webp') {
          $convertedResult = $this->convertPNGToWEBP($fileSourcePath, $fileOutputPath, $deleteOldFile, $quality);
        }
        
        if ($fileExtension === 'png' && $convertToExtension === 'avif') {
          $convertedResult = $this->convertPNGToAVIF($fileSourcePath, $fileOutputPath, $deleteOldFile, $quality);
        }
        
        if ($fileExtension === 'webp' && ($convertToExtension === 'jpeg' || $convertToExtension === 'jpg')) {
          $convertedResult = $this->convertWEBPToJPEG($fileSourcePath, $fileOutputPath, $deleteOldFile, $quality);
        }
        
        if ($fileExtension === 'webp' && $convertToExtension === 'png') {
          $convertedResult = $this->convertWEBPToPNG($fileSourcePath, $fileOutputPath, $deleteOldFile, $quality);
        }
        
        if ($fileExtension === 'webp' && $convertToExtension === 'avif') {
          $convertedResult = $this->convertWEBPToAVIF($fileSourcePath, $fileOutputPath, $deleteOldFile, $quality);
        }
        
        if ($fileExtension === 'avif' && ($convertToExtension === 'jpeg' || $convertToExtension === 'jpg')) {
          $convertedResult = $this->convertAVIFToJPEG($fileSourcePath, $fileOutputPath, $deleteOldFile, $quality);
        }
        
        if ($fileExtension === 'avif' && $convertToExtension === 'png') {
          $convertedResult = $this->convertAVIFToPNG($fileSourcePath, $fileOutputPath, $deleteOldFile, $quality);
        }
        
        if ($fileExtension === 'avif' && $convertToExtension === 'webp') {
          $convertedResult = $this->convertAVIFToWEBP($fileSourcePath, $fileOutputPath, $deleteOldFile, $quality);
        }

        if (($fileExtension === $convertToExtension)) {
          if (file_exists($fileSourcePath)) {
            $fileRenamed = rename($fileSourcePath, $fileOutputPath);

            if ($fileRenamed) {
              $convertedResult = true;
            }
          };
        }
      }

      if ($convertedResult === true) {
        return [
          'extensionOld' => $fileExtension,
          'extensionNew' => $convertToExtension,
          'fileName' => $fileOutputName,
          'filePath' => $fileOutputPath,
          'fileURL' => str_replace(CMS_ROOT_DIRECTORY, '', $fileOutputPath)
        ];
      }
    }

    return false;
  }
  
  /**
   * convertJPEGToPNG
   *
   * @param  string $fileSourcePath
   * @param  string $fileOutputPath
   * @param  bool $deleteOldFile
   * 
   * @return bool
   */
  private function convertJPEGToPNG(string $fileSourcePath, string $fileOutputPath, bool $deleteOldFile = false, int $quality = -1) : bool
  {
    $imageSource = imagecreatefromjpeg($fileSourcePath);
    $imageSourceWidth = imagesx($imageSource);
    $imageSourceHeight = imagesy($imageSource);

    $imageConverted = imagecreatetruecolor($imageSourceWidth, $imageSourceHeight);
    imagecopy($imageConverted, $imageSource, 0, 0, 0, 0, $imageSourceWidth, $imageSourceHeight);
    imagepng($imageConverted, $fileOutputPath, $quality);
    
    imagedestroy($imageSource);
    imagedestroy($imageConverted);

    if ($deleteOldFile) unlink($fileSourcePath);

    return file_exists($fileOutputPath);
  }
  
  /**
   * convertJPEGToWEBP
   *
   * @param string $fileSourcePath
   * @param string $fileOutputPath
   * @param bool $deleteOldFile
   * @param int $quality
   * 
   * @return bool
   */
  private function convertJPEGToWEBP(string $fileSourcePath, string $fileOutputPath, bool $deleteOldFile = false, int $quality = -1) : bool
  {
    $imageSource = imagecreatefromjpeg($fileSourcePath);
    $imageSourceWidth = imagesx($imageSource);
    $imageSourceHeight = imagesy($imageSource);

    $imageConverted = imagecreatetruecolor($imageSourceWidth, $imageSourceHeight);
    imagecopy($imageConverted, $imageSource, 0, 0, 0, 0, $imageSourceWidth, $imageSourceHeight);
    imagewebp($imageConverted, $fileOutputPath, $quality);

    imagedestroy($imageSource);
    imagedestroy($imageConverted);

    if ($deleteOldFile) unlink($fileSourcePath);

    return file_exists($fileOutputPath);
  }
  
  /**
   * convertJPEGToAVIF
   *
   * @param  string $fileSourcePath
   * @param  string $fileOutputPath
   * @param  bool $deleteOldFile
   * 
   * @return bool
   */
  private function convertJPEGToAVIF(string $fileSourcePath, string $fileOutputPath, bool $deleteOldFile = false, int $quality = -1) : bool
  {
    $imageSource = imagecreatefromjpeg($fileSourcePath);
    $imageSourceWidth = imagesx($imageSource);
    $imageSourceHeight = imagesy($imageSource);

    $imageConverted = imagecreatetruecolor($imageSourceWidth, $imageSourceHeight);
    imagecopy($imageConverted, $imageSource, 0, 0, 0, 0, $imageSourceWidth, $imageSourceHeight);
    imageavif($imageConverted, $fileOutputPath, $quality);

    imagedestroy($imageSource);
    imagedestroy($imageConverted);

    if ($deleteOldFile) unlink($fileSourcePath);

    return file_exists($fileOutputPath);
  }
  
  /**
   * convertPNGToJPEG
   *
   * @param  string $fileSourcePath
   * @param  string $fileOutputPath
   * @param  bool $deleteOldFile
   * 
   * @return bool
   */
  private function convertPNGToJPEG(string $fileSourcePath, string $fileOutputPath, bool $deleteOldFile = false, int $quality = -1) : bool
  {
    $imageSource = imagecreatefrompng($fileSourcePath);
    $imageSourceWidth = imagesx($imageSource);
    $imageSourceHeight = imagesy($imageSource);

    $imageConverted = imagecreatetruecolor($imageSourceWidth, $imageSourceHeight);
    imagefill($imageConverted, 0, 0, imagecolorallocate($imageConverted, 255, 255, 255));
    imagealphablending($imageConverted, true);

    imagecopy($imageConverted, $imageSource, 0, 0, 0, 0, $imageSourceWidth, $imageSourceHeight);
    imagejpeg($imageConverted, $fileOutputPath, $quality);
    
    imagedestroy($imageSource);
    imagedestroy($imageConverted);

    if ($deleteOldFile) unlink($fileSourcePath);

    return file_exists($fileOutputPath);
  }
  
  /**
   * convertPNGToWEBP
   *
   * @param  string $fileSourcePath
   * @param  string $fileOutputPath
   * @param  bool $deleteOldFile
   * 
   * @return bool
   */
  private function convertPNGToWEBP(string $fileSourcePath, string $fileOutputPath, bool $deleteOldFile = false, int $quality = -1) : bool
  {
    $imageSource = imagecreatefrompng($fileSourcePath);
    $imageSourceWidth = imagesx($imageSource);
    $imageSourceHeight = imagesy($imageSource);

    $imageConverted = imagecreatetruecolor($imageSourceWidth, $imageSourceHeight);
    imageAlphaBlending($imageConverted, false);
    imageSaveAlpha($imageConverted, true);

    $image_transparent = imagecolorallocatealpha($imageConverted, 0, 0, 0, 127);
    imagefilledrectangle($imageConverted, 0, 0, $imageSourceWidth - 1, $imageSourceHeight - 1, $image_transparent);

    imagecopy($imageConverted, $imageSource, 0, 0, 0, 0, $imageSourceWidth, $imageSourceHeight);
    imagewebp($imageConverted, $fileOutputPath, $quality);

    imagedestroy($imageSource);
    imagedestroy($imageConverted);

    if ($deleteOldFile) unlink($fileSourcePath);

    return file_exists($fileOutputPath);
  }
  
  /**
   * convertPNGToAVIF
   *
   * @param  string $fileSourcePath
   * @param  string $fileOutputPath
   * @param  bool $deleteOldFile
   * 
   * @return bool
   */
  private function convertPNGToAVIF(string $fileSourcePath, string $fileOutputPath, bool $deleteOldFile = false, int $quality = -1) : bool
  {
    $imageSource = imagecreatefrompng($fileSourcePath);
    $imageSourceWidth = imagesx($imageSource);
    $imageSourceHeight = imagesy($imageSource);

    $imageConverted = imagecreatetruecolor($imageSourceWidth, $imageSourceHeight);
    imageAlphaBlending($imageConverted, false);
    imageSaveAlpha($imageConverted, true);

    $image_transparent = imagecolorallocatealpha($imageConverted, 0, 0, 0, 127);
    imagefilledrectangle($imageConverted, 0, 0, $imageSourceWidth - 1, $imageSourceHeight - 1, $image_transparent);

    imagecopy($imageConverted, $imageSource, 0, 0, 0, 0, $imageSourceWidth, $imageSourceHeight);
    imageavif($imageConverted, $fileOutputPath, $quality);

    imagedestroy($imageSource);
    imagedestroy($imageConverted);

    if ($deleteOldFile) unlink($fileSourcePath);

    return file_exists($fileOutputPath);
  }
  
  /**
   * convertWEBPToJPEG
   *
   * @param  string $fileSourcePath
   * @param  string $fileOutputPath
   * @param  bool $deleteOldFile
   * 
   * @return bool
   */
  private function convertWEBPToJPEG(string $fileSourcePath, string $fileOutputPath, bool $deleteOldFile = false, int $quality = -1) : bool
  {
    $imageSource = imagecreatefromwebp($fileSourcePath);
    $imageSourceWidth = imagesx($imageSource);
    $imageSourceHeight = imagesy($imageSource);

    $imageConverted = imagecreatetruecolor($imageSourceWidth, $imageSourceHeight);
    imagefill($imageConverted, 0, 0, imagecolorallocate($imageConverted, 255, 255, 255));
    imagealphablending($imageConverted, true);

    imagecopy($imageConverted, $imageSource, 0, 0, 0, 0, $imageSourceWidth, $imageSourceHeight);
    imagejpeg($imageConverted, $fileOutputPath, $quality);
    
    imagedestroy($imageSource);
    imagedestroy($imageConverted);

    if ($deleteOldFile) unlink($fileSourcePath);

    return file_exists($fileOutputPath);
  }
  
  /**
   * convertWEBPToPNG
   *
   * @param  string $fileSourcePath
   * @param  string $fileOutputPath
   * @param  bool $deleteOldFile
   * 
   * @return bool
   */
  private function convertWEBPToPNG(string $fileSourcePath, string $fileOutputPath, bool $deleteOldFile = false, int $quality = -1) : bool
  {
    $imageSource = imagecreatefromwebp($fileSourcePath);
    $imageSourceWidth = imagesx($imageSource);
    $imageSourceHeight = imagesy($imageSource);

    $imageConverted = imagecreatetruecolor($imageSourceWidth, $imageSourceHeight);
    imageAlphaBlending($imageConverted, false);
    imageSaveAlpha($imageConverted, true);

    $image_transparent = imagecolorallocatealpha($imageConverted, 0, 0, 0, 127);
    imagefilledrectangle($imageConverted, 0, 0, $imageSourceWidth - 1, $imageSourceHeight - 1, $image_transparent);
    imagecopy($imageConverted, $imageSource, 0, 0, 0, 0, $imageSourceWidth, $imageSourceHeight);
    imagepng($imageConverted, $fileOutputPath, $quality);
    
    imagedestroy($imageSource);
    imagedestroy($imageConverted);

    if ($deleteOldFile) unlink($fileSourcePath);

    return file_exists($fileOutputPath);
  }
  
  /**
   * convertWEBPToAVIF
   *
   * @param  string $fileSourcePath
   * @param  string $fileOutputPath
   * @param  bool $deleteOldFile
   * 
   * @return bool
   */
  private function convertWEBPToAVIF(string $fileSourcePath, string $fileOutputPath, bool $deleteOldFile = false, int $quality = -1) : bool
  {
    $imageSource = imagecreatefromwebp($fileSourcePath);
    $imageSourceWidth = imagesx($imageSource);
    $imageSourceHeight = imagesy($imageSource);

    $imageConverted = imagecreatetruecolor($imageSourceWidth, $imageSourceHeight);
    imageAlphaBlending($imageConverted, false);
    imageSaveAlpha($imageConverted, true);

    $image_transparent = imagecolorallocatealpha($imageConverted, 0, 0, 0, 127);
    imagefilledrectangle($imageConverted, 0, 0, $imageSourceWidth - 1, $imageSourceHeight - 1, $image_transparent);

    imagecopy($imageConverted, $imageSource, 0, 0, 0, 0, $imageSourceWidth, $imageSourceHeight);
    imageavif($imageConverted, $fileOutputPath, $quality);

    imagedestroy($imageSource);
    imagedestroy($imageConverted);

    if ($deleteOldFile) unlink($fileSourcePath);

    return file_exists($fileOutputPath);
  }
  
  /**
   * convertAVIFToJPEG
   *
   * @param  string $fileSourcePath
   * @param  string $fileOutputPath
   * @param  bool $deleteOldFile
   * 
   * @return bool
   */
  private function convertAVIFToJPEG(string $fileSourcePath, string $fileOutputPath, bool $deleteOldFile = false, int $quality = -1) : bool
  {
    $imageSource = imagecreatefromavif($fileSourcePath);
    $imageSourceWidth = imagesx($imageSource);
    $imageSourceHeight = imagesy($imageSource);

    $imageConverted = imagecreatetruecolor($imageSourceWidth, $imageSourceHeight);
    imagefill($imageConverted, 0, 0, imagecolorallocate($imageConverted, 255, 255, 255));
    imagealphablending($imageConverted, true);

    imagecopy($imageConverted, $imageSource, 0, 0, 0, 0, $imageSourceWidth, $imageSourceHeight);
    imagejpeg($imageConverted, $fileOutputPath, $quality);
    
    imagedestroy($imageSource);
    imagedestroy($imageConverted);

    if ($deleteOldFile) unlink($fileSourcePath);

    return file_exists($fileOutputPath);
  }
  
  /**
   * convertAVIFToPNG
   *
   * @param  string $fileSourcePath
   * @param  string $fileOutputPath
   * @param  bool $deleteOldFile
   * 
   * @return bool
   */
  private function convertAVIFToPNG(string $fileSourcePath, string $fileOutputPath, bool $deleteOldFile = false, int $quality = -1) : bool
  {
    $imageSource = imagecreatefromavif($fileSourcePath);
    $imageSourceWidth = imagesx($imageSource);
    $imageSourceHeight = imagesy($imageSource);

    $imageConverted = imagecreatetruecolor($imageSourceWidth, $imageSourceHeight);
    imageAlphaBlending($imageConverted, false);
    imageSaveAlpha($imageConverted, true);

    $image_transparent = imagecolorallocatealpha($imageConverted, 0, 0, 0, 127);
    imagefilledrectangle($imageConverted, 0, 0, $imageSourceWidth - 1, $imageSourceHeight - 1, $image_transparent);
    imagecopy($imageConverted, $imageSource, 0, 0, 0, 0, $imageSourceWidth, $imageSourceHeight);
    imagepng($imageConverted, $fileOutputPath, $quality);
    
    imagedestroy($imageSource);
    imagedestroy($imageConverted);

    if ($deleteOldFile) unlink($fileSourcePath);

    return file_exists($fileOutputPath);
  }
  
  /**
   * convertAVIFToWEBP
   *
   * @param  string $fileSourcePath
   * @param  string $fileOutputPath
   * @param  bool $deleteOldFile
   * 
   * @return bool
   */
  private function convertAVIFToWEBP(string $fileSourcePath, string $fileOutputPath, bool $deleteOldFile = false, int $quality = -1) : bool
  {
    $imageSource = imagecreatefromavif($fileSourcePath);
    $imageSourceWidth = imagesx($imageSource);
    $imageSourceHeight = imagesy($imageSource);

    $imageConverted = imagecreatetruecolor($imageSourceWidth, $imageSourceHeight);
    imageAlphaBlending($imageConverted, false);
    imageSaveAlpha($imageConverted, true);

    $image_transparent = imagecolorallocatealpha($imageConverted, 0, 0, 0, 127);
    imagefilledrectangle($imageConverted, 0, 0, $imageSourceWidth - 1, $imageSourceHeight - 1, $image_transparent);

    imagecopy($imageConverted, $imageSource, 0, 0, 0, 0, $imageSourceWidth, $imageSourceHeight);
    imagewebp($imageConverted, $fileOutputPath, $quality);

    imagedestroy($imageSource);
    imagedestroy($imageConverted);

    if ($deleteOldFile) unlink($fileSourcePath);

    return file_exists($fileOutputPath);
  }
}