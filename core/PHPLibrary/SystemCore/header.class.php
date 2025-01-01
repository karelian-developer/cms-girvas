<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\SystemCore {
  use \core\PHPLibrary\SystemCore\Header\HTTPReferrerPolicy as HeaderHTTPReferrerPolicy;
  use \core\PHPLibrary\SystemCore\Header\EnumHTTPReferrerPolicy as HeaderEnumHTTPReferrerPolicy;

  class Header {
    // Информационные ответы
    const HTTP_RESPONSE_CODE_LABEL_100 = '100 Continue';
    const HTTP_RESPONSE_CODE_LABEL_101 = '101 Switching Protocols';
    const HTTP_RESPONSE_CODE_LABEL_102 = '102 Processing';
    const HTTP_RESPONSE_CODE_LABEL_103 = '103 Early Hints';

    // Успешные ответы
    const HTTP_RESPONSE_CODE_LABEL_200 = '200 OK';
    const HTTP_RESPONSE_CODE_LABEL_201 = '201 Created';
    const HTTP_RESPONSE_CODE_LABEL_202 = '202 Accepted';
    const HTTP_RESPONSE_CODE_LABEL_203 = '203 Non-Authoritative Information';
    const HTTP_RESPONSE_CODE_LABEL_204 = '204 No Content';
    const HTTP_RESPONSE_CODE_LABEL_205 = '205 Reset Content';
    const HTTP_RESPONSE_CODE_LABEL_206 = '206 Partial Content';
    const HTTP_RESPONSE_CODE_LABEL_207 = '207 Multi-Status';
    const HTTP_RESPONSE_CODE_LABEL_208 = '208 Already Reported';
    const HTTP_RESPONSE_CODE_LABEL_226 = '226 IM Used';

    // Сообщения о перенаправлении
    const HTTP_RESPONSE_CODE_LABEL_300 = '300 Multiple Choices';
    const HTTP_RESPONSE_CODE_LABEL_301 = '301 Moved Permanently';
    const HTTP_RESPONSE_CODE_LABEL_302 = '302 Found';
    const HTTP_RESPONSE_CODE_LABEL_303 = '303 See Other';
    const HTTP_RESPONSE_CODE_LABEL_304 = '304 Not Modified';
    const HTTP_RESPONSE_CODE_LABEL_305 = '305 Use Proxy';
    const HTTP_RESPONSE_CODE_LABEL_306 = '306 unused';
    const HTTP_RESPONSE_CODE_LABEL_307 = '307 Temporary Redirect';
    const HTTP_RESPONSE_CODE_LABEL_308 = '308 Permanent Redirect';

    // Ответы об ошибках клиента
    const HTTP_RESPONSE_CODE_LABEL_400 = '400 Bad Request';
    const HTTP_RESPONSE_CODE_LABEL_401 = '401 Unauthorized';
    const HTTP_RESPONSE_CODE_LABEL_402 = '402 Payment Required';
    const HTTP_RESPONSE_CODE_LABEL_403 = '403 Forbidden';
    const HTTP_RESPONSE_CODE_LABEL_404 = '404 Not Found';
    const HTTP_RESPONSE_CODE_LABEL_405 = '405 Method Not Allowed';
    const HTTP_RESPONSE_CODE_LABEL_406 = '406 Not Acceptable';
    const HTTP_RESPONSE_CODE_LABEL_407 = '407 Proxy Authentication Required';
    const HTTP_RESPONSE_CODE_LABEL_408 = '408 Request Timeout';
    const HTTP_RESPONSE_CODE_LABEL_409 = '409 Conflict';
    const HTTP_RESPONSE_CODE_LABEL_410 = '410 Gone';
    const HTTP_RESPONSE_CODE_LABEL_411 = '411 Length Required';
    const HTTP_RESPONSE_CODE_LABEL_412 = '412 Precondition Failed';
    const HTTP_RESPONSE_CODE_LABEL_413 = '413 Content Too Large';
    const HTTP_RESPONSE_CODE_LABEL_414 = '414 URI Too Long';
    const HTTP_RESPONSE_CODE_LABEL_415 = '415 Unsupported Media Type';
    const HTTP_RESPONSE_CODE_LABEL_416 = '416 Range Not Satisfiable';
    const HTTP_RESPONSE_CODE_LABEL_417 = '417 Expectation Failed';
    const HTTP_RESPONSE_CODE_LABEL_418 = '418 I\'m a teapot';
    const HTTP_RESPONSE_CODE_LABEL_421 = '421 Misdirected Request';
    const HTTP_RESPONSE_CODE_LABEL_422 = '422 Unprocessable Content';
    const HTTP_RESPONSE_CODE_LABEL_423 = '423 Locked';
    const HTTP_RESPONSE_CODE_LABEL_424 = '424 Failed Dependency';
    const HTTP_RESPONSE_CODE_LABEL_425 = '425 Too Early';
    const HTTP_RESPONSE_CODE_LABEL_426 = '426 Upgrade Required';
    const HTTP_RESPONSE_CODE_LABEL_428 = '428 Precondition Required';
    const HTTP_RESPONSE_CODE_LABEL_429 = '429 Too Many Requests';
    const HTTP_RESPONSE_CODE_LABEL_431 = '431 Request Header Fields Too Large';
    const HTTP_RESPONSE_CODE_LABEL_451 = '451 Unavailable For Legal Reasons';

    // Ответы об ошибках сервера
    const HTTP_RESPONSE_CODE_LABEL_500 = '500 Internal Server Error';
    const HTTP_RESPONSE_CODE_LABEL_501 = '501 Not Implemented';
    const HTTP_RESPONSE_CODE_LABEL_502 = '502 Bad Gateway';
    const HTTP_RESPONSE_CODE_LABEL_503 = '503 Service Unavailable';
    const HTTP_RESPONSE_CODE_LABEL_504 = '504 Gateway Timeout';
    const HTTP_RESPONSE_CODE_LABEL_505 = '505 HTTP Version Not Supported';
    const HTTP_RESPONSE_CODE_LABEL_506 = '506 Variant Also Negotiates';
    const HTTP_RESPONSE_CODE_LABEL_507 = '507 Insufficient Storage';
    const HTTP_RESPONSE_CODE_LABEL_508 = '508 Loop Detected';
    const HTTP_RESPONSE_CODE_LABEL_510 = '510 Not Extended';
    const HTTP_RESPONSE_CODE_LABEL_511 = '511 Network Authentication Required';

    static public function add(EnumHeader $enum_header, mixed $value) : bool {
      if ($enum_header === EnumHeader::HTTP_RESPONSE_CODE) {
        if (is_numeric($value)) {
          $string = self::get_http_response_code_label($value);

          if (!empty($string)) {
            header(sprintf('HTTP/1.1 %s', $string));
            return true;
          }
        }
      }

      if ($enum_header === EnumHeader::HTTP_LOCATION) {
        if (filter_var($value, FILTER_VALIDATE_URL) !== false) {
          header(sprintf('Location: %s', $value));
          return true;
        }
      }

      if ($enum_header === EnumHeader::HTTP_CONTENT_SECURITY_POLICY) {
        $string = '';
  
        if (is_string($value)) {
          $string = sprintf('Content-Security-Policy: %s', $value);
        }
  
        if (is_array($value)) {
          $string = sprintf('Content-Security-Policy: %s', implode('; ', $value));
        }
  
        if (!empty($string)) {
          header($string);
          return true;
        }
      }

      return false;
    }

    static public function get_http_response_code_label(int $code) : string {
      $constant_name = sprintf('EnumHeader::HTTP_RESPONSE_CODE_LABEL_%d', $code);
      return defined($constant_name) ? constant($constant_name) : '';
    }
  }
}

?>