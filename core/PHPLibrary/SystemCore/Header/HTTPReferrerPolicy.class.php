<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\SystemCore\Header {
  class HTTPReferrerPolicy {
    static public function get_label(EnumHTTPReferrerPolicy $enum) : string {
      return match ($enum) {
        EnumHTTPReferrerPolicy::NO_REFFERER => 'no-referrer',
        EnumHTTPReferrerPolicy::NO_REFFERER_WHEN_DOWNGRADE => 'no-referrer-when-downgrade',
        EnumHTTPReferrerPolicy::ORIGIN => 'origin',
        EnumHTTPReferrerPolicy::ORIGIN_WHEN_CROSS_ORIGIN => 'origin-when-cross-origin',
        EnumHTTPReferrerPolicy::SAME_ORIGIN => 'same-origin',
        EnumHTTPReferrerPolicy::STRICT_ORIGIN => 'strict-origin',
        EnumHTTPReferrerPolicy::STRICT_ORIGIN_WHEN_CROSS_ORIGIN => 'strict-origin-when-cross-origin',
        EnumHTTPReferrerPolicy::UNSAFE_URL => 'unsafe-url'
      }
    }
  }
}

?>