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

namespace core\PHPLibrary\SystemCore\Header;

class HTTPReferrerPolicy
{
  static public function getLabel(EnumHTTPReferrerPolicy $enum) : string
  {
    return match ($enum) {
      EnumHTTPReferrerPolicy::NO_REFFERER => 'no-referrer',
      EnumHTTPReferrerPolicy::NO_REFFERER_WHEN_DOWNGRADE => 'no-referrer-when-downgrade',
      EnumHTTPReferrerPolicy::ORIGIN => 'origin',
      EnumHTTPReferrerPolicy::ORIGIN_WHEN_CROSS_ORIGIN => 'origin-when-cross-origin',
      EnumHTTPReferrerPolicy::SAME_ORIGIN => 'same-origin',
      EnumHTTPReferrerPolicy::STRICT_ORIGIN => 'strict-origin',
      EnumHTTPReferrerPolicy::STRICT_ORIGIN_WHEN_CROSS_ORIGIN => 'strict-origin-when-cross-origin',
      EnumHTTPReferrerPolicy::UNSAFE_URL => 'unsafe-url'
    };
  }
}