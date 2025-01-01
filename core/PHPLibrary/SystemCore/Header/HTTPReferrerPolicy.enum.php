<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\SystemCore\Header {
  enum EnumHTTPReferrerPolicy {
    case NO_REFFERER;
    case NO_REFFERER_WHEN_DOWNGRADE;
    case ORIGIN;
    case ORIGIN_WHEN_CROSS_ORIGIN;
    case SAME_ORIGIN;
    case STRICT_ORIGIN;
    case STRICT_ORIGIN_WHEN_CROSS_ORIGIN;
    case UNSAFE_URL;
  }
}

?>