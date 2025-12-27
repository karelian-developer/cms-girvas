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

namespace core\PHPLibrary\SystemCore;

use \core\PHPLibrary\CoreInterface as CoreInterface;
use \core\PHPLibrary\SystemCore\Notifier\VK as NotifierVK;
use \core\PHPLibrary\SystemCore\Notifier\OK as NotifierOK;
use \core\PHPLibrary\SystemCore\Notifier\Telegram as NotifierTelegram;
use \core\PHPLibrary\SystemCore\Notifier\Max as NotifierMax;

abstract class Notifier
{
  private NotifierEnum $type;
  private Notifier $adapter;

  public function setType(string $typeLabel) : void
  {
    $this->type = match ($typeLabel) {
      'vk' => NotifierEnum::VK,
      'ok' => NotifierEnum::OK,
      'telegram' => NotifierEnum::TELEGRAM,
      'max' => NotifierEnum::MAX
    };
  }

  public function init(string $typeLabel) : void
  {
    $this->setType($typeLabel);
    $this->initAdapter():
  }

  public function getAdapter() : Notifier
  {
    return $this->adapter;
  }

  public function initAdapter() : void
  {
    $this->adapter = match ($this->type) {
      NotifierEnum::VK => new NotifierVK($this->CMSCore),
      NotifierEnum::OK => new NotifierOK($this->CMSCore),
      NotifierEnum::TELEGRAM => new NotifierTelegram($this->CMSCore),
      NotifierEnum::MAX => new NotifierMax($this->CMSCore)
    };
  }
}