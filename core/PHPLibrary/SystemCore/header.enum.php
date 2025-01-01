<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\SystemCore {
  enum EnumHeader {
    case HTTP_RESPONSE_CODE;
    case HTTP_LOCATION;
    case HTTP_CONTENT_SECURITY_POLICY;
    case HTTP_REFERRER_POLICY;
    case HTTP_X_CONTENT_TYPE_OPTIONS;
  }
}

?>