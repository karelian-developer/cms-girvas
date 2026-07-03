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
    $fileOutputBaseName = md5(sprintf('{GIRVAS:CONVERTER:%s:%d+%s}', $CMSSalt, time(), $salt));
    
    if (file_exists($fileOutputFolderPath)) {
      $convertToExtension = match ($convertTo) {
        EnumFileFormat::JPG => $convertToExtension = 'jpeg',
        EnumFileFormat::PNG => $convertToExtension = 'png',
        EnumFileFormat::WEBP => $convertToExtension = 'webp',
        EnumFileFormat::AVIF => $convertToExtension = 'avif',
        EnumFileFormat::GIF => $convertToExtension = 'gif',
        EnumFileFormat::PDF => $convertToExtension = 'pdf',
        default => ''
      };

      if ($convertToExtension === '') return false;

      $fileOutputName = $fileOutputBaseName . '.' . $convertToExtension;
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
      if ($fileSourcePath !== '' && file_exists($fileSourcePath)) {
        // Все варианты JPEG расширений
        $jpegExtensions = ['jpeg', 'jpg', 'jfif', 'pjpeg', 'jpe'];
        
        if (in_array($fileExtension, $jpegExtensions) && $convertToExtension === 'png') {
          $convertedResult = $this->convertJPEGToPNG($fileSourcePath, $fileOutputPath, $deleteOldFile, $quality);
        }

        if (in_array($fileExtension, $jpegExtensions) && $convertToExtension === 'webp') {
          $convertedResult = $this->convertJPEGToWEBP($fileSourcePath, $fileOutputPath, $deleteOldFile, $quality);
        }

        if (in_array($fileExtension, $jpegExtensions) && $convertToExtension === 'avif') {
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
        
        if ($fileExtension === 'webp' && in_array($convertToExtension, $jpegExtensions)) {
          $convertedResult = $this->convertWEBPToJPEG($fileSourcePath, $fileOutputPath, $deleteOldFile, $quality);
        }
        
        if ($fileExtension === 'webp' && $convertToExtension === 'png') {
          $convertedResult = $this->convertWEBPToPNG($fileSourcePath, $fileOutputPath, $deleteOldFile, $quality);
        }
        
        if ($fileExtension === 'webp' && $convertToExtension === 'avif') {
          $convertedResult = $this->convertWEBPToAVIF($fileSourcePath, $fileOutputPath, $deleteOldFile, $quality);
        }
        
        if ($fileExtension === 'avif' && in_array($convertToExtension, $jpegExtensions)) {
          $convertedResult = $this->convertAVIFToJPEG($fileSourcePath, $fileOutputPath, $deleteOldFile, $quality);
        }
        
        if ($fileExtension === 'avif' && $convertToExtension === 'png') {
          $convertedResult = $this->convertAVIFToPNG($fileSourcePath, $fileOutputPath, $deleteOldFile, $quality);
        }
        
        if ($fileExtension === 'avif' && $convertToExtension === 'webp') {
          $convertedResult = $this->convertAVIFToWEBP($fileSourcePath, $fileOutputPath, $deleteOldFile, $quality);
        }

        $isSourceJPEG = in_array($fileExtension, $jpegExtensions);
        $isTargetJPEG = in_array($convertToExtension, $jpegExtensions);

        if ($fileExtension === $convertToExtension || ($isSourceJPEG && $isTargetJPEG)) {
          if (file_exists($fileSourcePath)) {
            if ($fileExtension === 'gif') {
              $convertedResult = $this->sanitizeGIF($fileSourcePath, $fileOutputPath, $deleteOldFile);
            } else {
              $fileRenamed = rename($fileSourcePath, $fileOutputPath);
              if ($fileRenamed) {
                $convertedResult = true;
              }
            }
          }
        }
      }

      if ($convertedResult === true) {
        return [
          'extensionOld' => $fileExtension,
          'extensionNew' => $convertToExtension,
          'fileBaseName' => $fileOutputBaseName,
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
    if (!file_exists($fileSourcePath)) {
      return false;
    }

    $imageInfo = @getimagesize($fileSourcePath);
    if ($imageInfo === false) {
      return false;
    }

    $jpegTypes = [IMAGETYPE_JPEG, IMAGETYPE_JPEG2000];
    if (!in_array($imageInfo[2], $jpegTypes)) {
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mimeType = finfo_file($finfo, $fileSourcePath);
      finfo_close($finfo);
      
      if (!in_array($mimeType, ['image/jpeg', 'image/jfif', 'image/pjpeg'])) {
        return false;
      }
    }

    $imageSource = @imagecreatefromjpeg($fileSourcePath);
    
    if ($imageSource === false) {
      $imageData = file_get_contents($fileSourcePath);
      if ($imageData === false) {
        return false;
      }
      
      $imageSource = @imagecreatefromstring($imageData);
      if ($imageSource === false) {
        return false;
      }
    }

    $imageSourceWidth = imagesx($imageSource);
    $imageSourceHeight = imagesy($imageSource);

    $imageConverted = imagecreatetruecolor($imageSourceWidth, $imageSourceHeight);
    if ($imageConverted === false) {
      imagedestroy($imageSource);
      return false;
    }

    imagealphablending($imageConverted, false);
    imagesavealpha($imageConverted, true);

    imagecopy($imageConverted, $imageSource, 0, 0, 0, 0, $imageSourceWidth, $imageSourceHeight);
    
    $result = imagepng($imageConverted, $fileOutputPath, $quality);
    
    imagedestroy($imageSource);
    imagedestroy($imageConverted);

    if (!$result) {
      return false;
    }

    if ($deleteOldFile && file_exists($fileSourcePath)) {
      unlink($fileSourcePath);
    }

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
    if (!file_exists($fileSourcePath)) {
      return false;
    }

    if (!function_exists('imagewebp')) {
      return false;
    }

    $imageSource = @imagecreatefromjpeg($fileSourcePath);
    
    if ($imageSource === false) {
      $imageData = @file_get_contents($fileSourcePath);
      if ($imageData === false) {
        return false;
      }
      
      $startPos = strpos($imageData, "\xFF\xD8");
      if ($startPos !== false && $startPos > 0) {
        $imageData = substr($imageData, $startPos);
      }
      
      $imageSource = @imagecreatefromstring($imageData);
      if ($imageSource === false) {
        return false;
      }
    }

    $imageSourceWidth = imagesx($imageSource);
    $imageSourceHeight = imagesy($imageSource);

    $imageConverted = imagecreatetruecolor($imageSourceWidth, $imageSourceHeight);
    if ($imageConverted === false) {
      imagedestroy($imageSource);
      return false;
    }

    imagealphablending($imageConverted, false);
    imagesavealpha($imageConverted, true);

    imagecopy($imageConverted, $imageSource, 0, 0, 0, 0, $imageSourceWidth, $imageSourceHeight);
    
    $result = imagewebp($imageConverted, $fileOutputPath, $quality);

    imagedestroy($imageSource);
    imagedestroy($imageConverted);

    if (!$result) {
      return false;
    }

    if ($deleteOldFile && file_exists($fileSourcePath)) {
      unlink($fileSourcePath);
    }

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
    if (!file_exists($fileSourcePath)) {
      return false;
    }

    if (!function_exists('imageavif')) {
      return false;
    }

    $imageSource = @imagecreatefromjpeg($fileSourcePath);
    
    if ($imageSource === false) {
      $imageData = @file_get_contents($fileSourcePath);
      if ($imageData === false) {
        return false;
      }
      
      $startPos = strpos($imageData, "\xFF\xD8");
      if ($startPos !== false && $startPos > 0) {
        $imageData = substr($imageData, $startPos);
      }
      
      $imageSource = @imagecreatefromstring($imageData);
      if ($imageSource === false) {
        return false;
      }
    }

    $imageSourceWidth = imagesx($imageSource);
    $imageSourceHeight = imagesy($imageSource);

    $imageConverted = imagecreatetruecolor($imageSourceWidth, $imageSourceHeight);
    if ($imageConverted === false) {
      imagedestroy($imageSource);
      return false;
    }

    imagealphablending($imageConverted, false);
    imagesavealpha($imageConverted, true);

    imagecopy($imageConverted, $imageSource, 0, 0, 0, 0, $imageSourceWidth, $imageSourceHeight);
    
    $result = imageavif($imageConverted, $fileOutputPath, $quality);

    imagedestroy($imageSource);
    imagedestroy($imageConverted);

    if (!$result) {
      return false;
    }

    if ($deleteOldFile && file_exists($fileSourcePath)) {
      unlink($fileSourcePath);
    }

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
    if ($imageSource === false) {
      return false;
    }

    $imageSourceWidth = imagesx($imageSource);
    $imageSourceHeight = imagesy($imageSource);

    $imageConverted = imagecreatetruecolor($imageSourceWidth, $imageSourceHeight);
    if ($imageConverted === false) {
      return false;
    }
    
    imagefill($imageConverted, 0, 0, imagecolorallocate($imageConverted, 255, 255, 255));
    imagealphablending($imageConverted, true);

    imagecopy($imageConverted, $imageSource, 0, 0, 0, 0, $imageSourceWidth, $imageSourceHeight);
    imagejpeg($imageConverted, $fileOutputPath, $quality);
    
    imagedestroy($imageSource);
    imagedestroy($imageConverted);

    if ($deleteOldFile && file_exists($fileSourcePath)) {
      unlink($fileSourcePath);
    }

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
    if ($imageSource === false) {
      return false;
    }

    $imageSourceWidth = imagesx($imageSource);
    $imageSourceHeight = imagesy($imageSource);

    $imageConverted = imagecreatetruecolor($imageSourceWidth, $imageSourceHeight);
    if ($imageConverted === false) {
      return false;
    }

    imagealphablending($imageConverted, false);
    imagesavealpha($imageConverted, true);

    $imageTransparent = imagecolorallocatealpha($imageConverted, 0, 0, 0, 127);
    imagefill($imageConverted, 0, 0, $imageTransparent);

    imagecopy($imageConverted, $imageSource, 0, 0, 0, 0, $imageSourceWidth, $imageSourceHeight);
    imagewebp($imageConverted, $fileOutputPath, $quality);

    imagedestroy($imageSource);
    imagedestroy($imageConverted);

    if ($deleteOldFile && file_exists($fileSourcePath)) {
      unlink($fileSourcePath);
    }

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
    if ($imageSource === false) {
      return false;
    }

    $imageSourceWidth = imagesx($imageSource);
    $imageSourceHeight = imagesy($imageSource);

    $imageConverted = imagecreatetruecolor($imageSourceWidth, $imageSourceHeight);
    if ($imageConverted === false) {
      return false;
    }
    
    imagealphablending($imageConverted, false);
    imagesavealpha($imageConverted, true);

    $imageTransparent = imagecolorallocatealpha($imageConverted, 0, 0, 0, 127);
    imagefill($imageConverted, 0, 0, $imageTransparent);

    imagecopy($imageConverted, $imageSource, 0, 0, 0, 0, $imageSourceWidth, $imageSourceHeight);
    imageavif($imageConverted, $fileOutputPath, $quality);

    imagedestroy($imageSource);
    imagedestroy($imageConverted);

    if ($deleteOldFile && file_exists($fileSourcePath)) {
      unlink($fileSourcePath);
    }

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
    if ($imageSource === false) {
      return false;
    }

    $imageSourceWidth = imagesx($imageSource);
    $imageSourceHeight = imagesy($imageSource);

    $imageConverted = imagecreatetruecolor($imageSourceWidth, $imageSourceHeight);
    if ($imageConverted === false) {
      return false;
    }
    
    imagefill($imageConverted, 0, 0, imagecolorallocate($imageConverted, 255, 255, 255));
    imagealphablending($imageConverted, true);

    imagecopy($imageConverted, $imageSource, 0, 0, 0, 0, $imageSourceWidth, $imageSourceHeight);
    imagejpeg($imageConverted, $fileOutputPath, $quality);
    
    imagedestroy($imageSource);
    imagedestroy($imageConverted);

    if ($deleteOldFile && file_exists($fileSourcePath)) {
      unlink($fileSourcePath);
    }

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
    if ($imageSource === false) {
      return false;
    }

    $imageSourceWidth = imagesx($imageSource);
    $imageSourceHeight = imagesy($imageSource);

    $imageConverted = imagecreatetruecolor($imageSourceWidth, $imageSourceHeight);
    if ($imageConverted === false) {
      return false;
    }
    
    imagealphablending($imageConverted, false);
    imagesavealpha($imageConverted, true);

    $imageTransparent = imagecolorallocatealpha($imageConverted, 0, 0, 0, 127);
    imagefill($imageConverted, 0, 0, $imageTransparent);
    imagecopy($imageConverted, $imageSource, 0, 0, 0, 0, $imageSourceWidth, $imageSourceHeight);
    imagepng($imageConverted, $fileOutputPath, $quality);
    
    imagedestroy($imageSource);
    imagedestroy($imageConverted);

    if ($deleteOldFile && file_exists($fileSourcePath)) {
      unlink($fileSourcePath);
    }

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
    if ($imageSource === false) {
      return false;
    }

    $imageSourceWidth = imagesx($imageSource);
    $imageSourceHeight = imagesy($imageSource);

    $imageConverted = imagecreatetruecolor($imageSourceWidth, $imageSourceHeight);
    if ($imageConverted === false) {
      return false;
    }
    
    imagealphablending($imageConverted, false);
    imagesavealpha($imageConverted, true);

    $imageTransparent = imagecolorallocatealpha($imageConverted, 0, 0, 0, 127);
    imagefill($imageConverted, 0, 0, $imageTransparent);

    imagecopy($imageConverted, $imageSource, 0, 0, 0, 0, $imageSourceWidth, $imageSourceHeight);
    imageavif($imageConverted, $fileOutputPath, $quality);

    imagedestroy($imageSource);
    imagedestroy($imageConverted);

    if ($deleteOldFile && file_exists($fileSourcePath)) {
      unlink($fileSourcePath);
    }

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
    if ($imageSource === false) {
      return false;
    }

    $imageSourceWidth = imagesx($imageSource);
    $imageSourceHeight = imagesy($imageSource);

    $imageConverted = imagecreatetruecolor($imageSourceWidth, $imageSourceHeight);
    if ($imageConverted === false) {
      return false;
    }
    
    imagefill($imageConverted, 0, 0, imagecolorallocate($imageConverted, 255, 255, 255));
    imagealphablending($imageConverted, true);

    imagecopy($imageConverted, $imageSource, 0, 0, 0, 0, $imageSourceWidth, $imageSourceHeight);
    imagejpeg($imageConverted, $fileOutputPath, $quality);
    
    imagedestroy($imageSource);
    imagedestroy($imageConverted);

    if ($deleteOldFile && file_exists($fileSourcePath)) {
      unlink($fileSourcePath);
    }

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
    if ($imageSource === false) {
      return false;
    }

    $imageSourceWidth = imagesx($imageSource);
    $imageSourceHeight = imagesy($imageSource);

    $imageConverted = imagecreatetruecolor($imageSourceWidth, $imageSourceHeight);
    if ($imageConverted === false) {
      return false;
    }
    
    imagealphablending($imageConverted, false);
    imagesavealpha($imageConverted, true);

    $imageTransparent = imagecolorallocatealpha($imageConverted, 0, 0, 0, 127);
    imagefill($imageConverted, 0, 0, $imageTransparent);
    imagecopy($imageConverted, $imageSource, 0, 0, 0, 0, $imageSourceWidth, $imageSourceHeight);
    imagepng($imageConverted, $fileOutputPath, $quality);
    
    imagedestroy($imageSource);
    imagedestroy($imageConverted);

    if ($deleteOldFile && file_exists($fileSourcePath)) {
      unlink($fileSourcePath);
    }

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
    if ($imageSource === false) {
      return false;
    }

    $imageSourceWidth = imagesx($imageSource);
    $imageSourceHeight = imagesy($imageSource);

    $imageConverted = imagecreatetruecolor($imageSourceWidth, $imageSourceHeight);
    if ($imageConverted === false) {
      return false;
    }
    
    imagealphablending($imageConverted, false);
    imagesavealpha($imageConverted, true);

    $imageTransparent = imagecolorallocatealpha($imageConverted, 0, 0, 0, 127);
    imagefill($imageConverted, 0, 0, $imageTransparent);

    imagecopy($imageConverted, $imageSource, 0, 0, 0, 0, $imageSourceWidth, $imageSourceHeight);
    imagewebp($imageConverted, $fileOutputPath, $quality);

    imagedestroy($imageSource);
    imagedestroy($imageConverted);

    if ($deleteOldFile && file_exists($fileSourcePath)) {
      unlink($fileSourcePath);
    }

    return file_exists($fileOutputPath);
  }

  private function sanitizeGIF(string $fileSourcePath, string $fileOutputPath, bool $deleteOldFile = false) : bool
  {
    if (!file_exists($fileSourcePath)) {
      return false;
    }

    if (filesize($fileSourcePath) === 0) {
      return false;
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $fileSourcePath);
    finfo_close($finfo);
    
    if ($mimeType !== 'image/gif') {
      return false;
    }
    
    $handle = fopen($fileSourcePath, 'rb');
    if (!$handle) {
      return false;
    }
    
    $header = fread($handle, 6);
    fclose($handle);
    
    if (!in_array($header, ['GIF87a', 'GIF89a'])) {
      return false;
    }
    
    $handle = fopen($fileSourcePath, 'rb');
    if ($handle) {
      $start = fread($handle, 2048);
      fseek($handle, -2048, SEEK_END);
      $end = fread($handle, 2048);
      fclose($handle);
      
      $contentToCheck = $start . $end;
      
      if (preg_match('/<\?php|<\?=|<\?|<\s*\%|<\s*script\s*language\s*=\s*["\']?\s*php/i', $contentToCheck)) {
        return false;
      }
    }
    
    $imageSource = @imagecreatefromgif($fileSourcePath);
    if ($imageSource === false) {
      error_log("GD failed to validate image: " . $fileSourcePath);
      return false;
    }
    
    $width = imagesx($imageSource);
    $height = imagesy($imageSource);
    
    imagedestroy($imageSource);
    
    if ($width <= 0 || $height <= 0 || $width > 5000 || $height > 5000) {
      return false;
    }
    
    if (!copy($fileSourcePath, $fileOutputPath)) {
      return false;
    }
    
    if (!file_exists($fileOutputPath) || filesize($fileOutputPath) === 0) {
      return false;
    }
    
    if ($deleteOldFile && file_exists($fileSourcePath)) {
      if (!unlink($fileSourcePath)) {
        error_log("Failed to delete source file: " . $fileSourcePath);
      }
    }

    return true;
  }
}