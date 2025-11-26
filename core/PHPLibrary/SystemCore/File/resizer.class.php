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

final class Resizer implements InterfaceResizer
{
  private ?CMSCore $CMSCore = null;
      
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
   * Создание копий изображений с шириной от 8 пикселей до исходной ширины
   * Примечание: Каждый размер кратен 8-ми
   */
  public function multipleResize(string $sourcePath, string $outputDir) : array
  {
    if (!file_exists($sourcePath)) {
      throw new InvalidArgumentException("Source file not found: {$sourcePath}");
    }

    $imageInfo = getimagesize($sourcePath);
    if ($imageInfo === false) {
      throw new RuntimeException("Invalid image file: {$sourcePath}");
    }

    list($originalWidth, $originalHeight, $type) = $imageInfo;

    $image = match($type) {
      IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
      IMAGETYPE_PNG => imagecreatefrompng($sourcePath),
      IMAGETYPE_GIF => imagecreatefromgif($sourcePath),
      IMAGETYPE_WEBP => imagecreatefromwebp($sourcePath),
      IMAGETYPE_AVIF => imagecreatefromavif($sourcePath),
      default => throw new RuntimeException("Unsupported image type")
    };

    if ($image === false) {
      throw new RuntimeException("Failed to load image: {$sourcePath}");
    }

    if (!is_dir($outputDir)) {
      mkdir($outputDir, 0774, true);
    }

    $createdFiles = [];
    $baseName = pathinfo($sourcePath, PATHINFO_FILENAME);
    $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);

    $width = 8;
    while ($width <= $originalWidth) {
      $newHeight = (int)($originalHeight * ($width / $originalWidth));
      $resizedImage = imagecreatetruecolor($width, $newHeight);

      if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_WEBP || $type === IMAGETYPE_AVIF) {
        imagealphablending($resizedImage, false);
        imagesavealpha($resizedImage, true);
        $transparent = imagecolorallocatealpha($resizedImage, 0, 0, 0, 127);
        imagefill($resizedImage, 0, 0, $transparent);
      }

      imagecopyresampled(
        $resizedImage, $image,
        0, 0, 0, 0,
        $width, $newHeight,
        $originalWidth, $originalHeight
      );

      $outputPath = $outputDir . '/' . $width . '.' . $extension;

      $success = match($type) {
        IMAGETYPE_JPEG => imagejpeg($resizedImage, $outputPath),
        IMAGETYPE_PNG => imagepng($resizedImage, $outputPath),
        IMAGETYPE_GIF => imagegif($resizedImage, $outputPath),
        IMAGETYPE_WEBP => imagewebp($resizedImage, $outputPath),
        IMAGETYPE_AVIF => imageavif($resizedImage, $outputPath),
      };

      if ($success) {
        $createdFiles[$width] = $outputPath;
      }

      imagedestroy($resizedImage);
      $width *= 2;
    }

    imagedestroy($image);
    return $createdFiles;
  }
}