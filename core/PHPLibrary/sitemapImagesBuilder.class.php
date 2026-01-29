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

namespace core\PHPLibrary;

use \DOMDocument as DOMDocument;

/**
 * Сборщик карты сайта в XML-формате
 */
final class SitemapImagesBuilder
{
  private DOMDocument $document;
  private array $images = [];
  public string $assembled = '';

  /**
   * __construct
   * 
   * @param CoreInterface $CMSCore
   */
  public function __construct(
    private CoreInterface $CMSCore
  ) {
    $this->setDocument();
  }

  /**
   * Назначить пустой документа
   * 
   * @return void
   */
  private function setDocument() : void
  {
    $this->document = new DOMDocument('1.0', 'UTF-8');
  }

  /**
   * Добавить изображение
   * 
   * @param string $imageUrl Полный URL изображения
   * @param string|null $caption Заголовок/описание изображения
   * @param string|null $title Заголовок изображения
   * @param string|null $license Лицензия изображения
   * @param string|null $geoLocation Географическое местоположение
   * 
   * @return void
   */
  public function addImage(
    string $imageUrl, 
    ?string $caption = null, 
    ?string $title = null, 
    ?string $license = null, 
    ?string $geoLocation = null
  ) : void
  {
    array_push($this->images, [
      'imageURL' => $imageUrl,
      'caption' => $caption,
      'title' => $title,
      'license' => $license,
      'GEOLocation' => $geoLocation
    ]);
  }

  /**
   * Загрузить изображения из metadata.json
   * 
   * @param string $jsonFilePath Путь к файлу metadata.json
   * @param string $baseUrl Базовый URL сайта
   * 
   * @return int Количество загруженных изображений
   */
  public function loadFromMetadata(string $jsonFilePath, string $baseUrl) : int
  {
    if (!file_exists($jsonFilePath)) {
      file_put_contents($jsonFilePath, json_encode([]));
    }

    $jsonContent = file_get_contents($jsonFilePath);
    $metadata = json_decode($jsonContent, true);

    if (!$metadata) {
      throw new \Exception("Неверный формат JSON файла: " . $jsonFilePath);
    }

    $count = 0;
    
    foreach ($metadata as $filename => $imageData) {
      $imageUrl = rtrim($baseUrl, '/') . '/uploads/media/' . urlencode($filename);
      
      $caption = $imageData['description'] ?? null;
      $additionalCaption = $imageData['additionalDescription'] ?? null;
      
      if ($caption && $additionalCaption) {
        $fullCaption = $caption . '. ' . $additionalCaption;
      } elseif ($caption) {
        $fullCaption = $caption;
      } elseif ($additionalCaption) {
        $fullCaption = $additionalCaption;
      } else {
        $fullCaption = null;
      }
      
      $title = $imageData['description'] ?? pathinfo($filename, PATHINFO_FILENAME);
      $license = $imageData['license'] ?? null;
      $geoLocation = $imageData['GEOLocation'] ?? null;
      
      $this->addImage($imageUrl, $fullCaption, $title, $license, $geoLocation);
      $count++;
    }

    return $count;
  }

  /**
   * Сборка карты сайта
   * 
   * @return void
   */
  public function assembly() : void
  {
    $elementURLSet = $this->document->createElement('urlset');
    
    $elementURLSetAttributeXMLns = $this->document->createAttribute('xmlns');
    $elementURLSetAttributeXMLns->value = 'http://www.sitemaps.org/schemas/sitemap/0.9';
    $elementURLSet->appendChild($elementURLSetAttributeXMLns);
    
    $elementURLSetAttributeXMLnsImage = $this->document->createAttribute('xmlns:image');
    $elementURLSetAttributeXMLnsImage->value = 'http://www.google.com/schemas/sitemap-image/1.1';
    $elementURLSet->appendChild($elementURLSetAttributeXMLnsImage);

    foreach ($this->images as $image) {
      $elementURL = $this->document->createElement('url');
      
      $elementLoc = $this->document->createElement('loc', $image['imageURL']);
      $elementLastmod = $this->document->createElement('lastmod', date('Y-m-d'));
      $elementChangefreq = $this->document->createElement('changefreq', 'monthly');
      $elementPriority = $this->document->createElement('priority', '0.5');

      $elementURL->appendChild($elementLoc);
      $elementURL->appendChild($elementLastmod);
      $elementURL->appendChild($elementChangefreq);
      $elementURL->appendChild($elementPriority);

      $elementImage = $this->document->createElement('image:image');
      $elementImageLoc = $this->document->createElement('image:loc', $image['imageURL']);
      $elementImage->appendChild($elementImageLoc);

      if (!empty($image['caption'])) {
        $elementImageCaption = $this->document->createElement('image:caption', $image['caption']);
        $elementImage->appendChild($elementImageCaption);
      }

      if (!empty($image['title'])) {
        $elementImageTitle = $this->document->createElement('image:title', $image['title']);
        $elementImage->appendChild($elementImageTitle);
      }

      if (!empty($image['license'])) {
        $elementImageLicense = $this->document->createElement('image:license', $image['license']);
        $elementImage->appendChild($elementImageLicense);
      }

      if (!empty($image['geo_location'])) {
        $elementImageGeoLocation = $this->document->createElement('image:geo_location', $image['GEOLocation']);
        $elementImage->appendChild($elementImageGeoLocation);
      }

      $elementURL->appendChild($elementImage);
      $elementURLSet->appendChild($elementURL);
    }

    $this->document->appendChild($elementURLSet);
    $this->assembled = $this->document->saveXML();
  }
}