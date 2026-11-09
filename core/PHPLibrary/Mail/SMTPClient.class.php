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
 * @copyright   Copyright (c) 2021 - 2027, ИП Шестаков А.Р., «Карельский разработчик» (https://карельский-разработчик.рф/)
 * Все права защищены.
 * 
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 * @author      Андрей Шестаков <andrey.shestakov@karelian-developer.ru>
 * 
 * @support     support@karelian-developer.ru
 */

namespace core\PHPLibrary\Mail;

use \Exception as Exception;

class SMTPClient
{
  private mixed $socket;
  private int $timeout = 30;

  /**
   * __construct
   * 
   * @param string $host
   * @param int $port
   * @param ?string $username
   * @param ?string $password
   */
  public function __construct(
    private string $host,
    private int $port,
    private ?string $username = null,
    private ?string $password = null,
  ) {}

  /**
   * Установить таймаут
   * 
   * @param int $value
   * 
   * @return void
   */
  public function setTimeout(int $value) : void
  {
    $this->timeout = $value;
  }

  /**
   * Подключение клиент
   * 
   * @return bool
   */
  public function connect() : self
  {
    $protocol = match ($this->port) {
      465 => 'ssl',
      587 => 'tcp',
      25 => 'tcp',
      default => 'ssl'
    };

    if ($this->port === 465) {
      $context = stream_context_create([
        'ssl' => [
          'verify_peer' => false,
          'verify_peer_name' => false,
          'allow_self_signed' => true,
          'crypto_method' => STREAM_CRYPTO_METHOD_SSLv23_CLIENT
        ]
      ]);
    } else {
      $context = null;
    }

    $address = $protocol . '://' . $this->host . ':' . $this->port;
    $this->socket = stream_socket_client($address, $errorNo, $errorString, $this->timeout, STREAM_CLIENT_CONNECT, $context);

    if (!$this->socket) {
      throw new Exception("Connection failed: {$errorNo} - {$errorString}");
    }

    if ($this->port === 587) {
      $this->startTLS();
    }

    $response = fgets($this->socket);
    if (strpos($response, '220') === false) {
      throw new Exception("SMTP error: {$response}");
    }
    
    return $this;
  }

  /**
   * Отправить команду клиенту
   * 
   * @param string $command
   * @param string $expectedCode
   * 
   * @return string
   */
  public function sendCommand(string $command, string $expectedCode) : string
  {
    fwrite($this->socket, $command . "\r\n");
    $response = $this->getResponse();
    
    if (strpos($response, $expectedCode) !== 0) {
      throw new Exception("Command failed: {$command} - Response: {$response}");
    }
    
    return $response;
  }

  private function getResponse()
  {
    $response = '';
    
    while (true) {
      $line = fgets($this->socket);
      if ($line === false) {
        throw new Exception("No response from server");
      }
      
      $response .= $line;
      
      // Если строка начинается с кода и пробела (не дефиса) - это конец ответа
      if (preg_match('/^\d{3} /', $line)) {
        break;
      }
    }
    
    return trim($response);
  }

  /**
   * Авторизация клиента
   * 
   * @return void
   */
  public function login() : void
  {
    if ($this->username === null || $this->password === null) {
      throw new Exception('SMTPClient: для авторизации не заданы username/password.');
    }

    $this->sendCommand('EHLO localhost', '250');
    $this->sendCommand('AUTH LOGIN', '334');
    $this->sendCommand(base64_encode($this->username), '334');
    $this->sendCommand(base64_encode($this->password), '235');
  }
  
  /**
   * Отправить электронное письмо
   * 
   * @param string $from
   * @param string $to
   * @param string $subject
   * @param string $message
   * 
   * @return void
   */
  public function sendEmail(string $from, string $to, string $subject, string $message, bool $isHTML = false) : void
  {
    $this->sendCommand("MAIL FROM: <{$from}>", '250');
    $this->sendCommand("RCPT TO: <{$to}>", '250');
    $this->sendCommand('DATA', '354');

    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

    $headers  = "From: {$from}\r\n";
    $headers .= "To: {$to}\r\n";
    $headers .= "Subject: {$encodedSubject}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "X-Mailer: CMS GIRVAS\r\n";
    $headers .= $isHTML
      ? "Content-Type: text/html; charset=UTF-8\r\n"
      : "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: 8bit\r\n";
    $headers .= "Date: " . date('r') . "\r\n";

    fwrite($this->socket, $headers . "\r\n" . $message . "\r\n.\r\n");

    $response = fgets($this->socket);
    if (strpos($response, '250') === false) {
      throw new Exception("Email sending failed: {$response}");
    }
  }

  /**
   * Отключить клиент
   * 
   * @return void
   */
  public function disconnect() : void
  {
    $this->sendCommand('QUIT', '221');
    fclose($this->socket);
  }

  /**
   * Начать TLS-подключение
   * 
   * @return void
   */
  private function startTLS() : void
  {
    $this->sendCommand('STARTTLS', '220');
    stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
  }

  /**
   * Определяет адрес отправителя на основании конфигурации SMTP.
   *
   * @param array<string, mixed> $smtpConfiguration
   * @param string|null $fallbackDomain Домен для fallback (если в конфиге пусто)
   * @return string
   */
  public static function resolveFromEmail(array $smtpConfiguration, ?string $fallbackDomain = null) : string
  {
    $username = trim((string)($smtpConfiguration['username'] ?? ''));
    $domain   = trim((string)($smtpConfiguration['domain'] ?? ''));

    if ($domain === '' && $fallbackDomain !== null) {
      $domain = ltrim(trim($fallbackDomain), '.');
    }

    if ($username === '') {
      $candidate = $domain !== '' ? 'no-reply@' . $domain : '';
    } elseif (filter_var($username, FILTER_VALIDATE_EMAIL)) {
      $candidate = $username;
    } else {
      $candidate = $domain !== '' ? $username . '@' . $domain : '';
    }

    if ($candidate === '' || !filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
      error_log(sprintf(
        '[SMTPClient] Невалидный адрес отправителя "%s" (username="%s", domain="%s"). Использован fallback.',
        $candidate,
        $username,
        $domain
      ));

      if ($domain !== '' && filter_var('no-reply@' . $domain, FILTER_VALIDATE_EMAIL)) {
        return 'no-reply@' . $domain;
      }

      throw new Exception('SMTPClient: не удалось определить валидный адрес отправителя.');
    }

    return $candidate;
  }

  /**
   * Возвращает локализованный набор текстов письма
   *
   * @param array<string, mixed> $emailTexts
   * @param string $requestedLocale  Например, "ru_RU"
   * @param string $baseLocale       Например, "ru_RU"
   * @return array<string, string>
   */
  public static function pickLocaleTexts(array $emailTexts, string $requestedLocale, string $baseLocale) : array
  {
    $candidates = array_unique([$requestedLocale, $baseLocale]);

    foreach ($candidates as $localeName) {
      if (isset($emailTexts[$localeName]) && is_array($emailTexts[$localeName]) && !empty($emailTexts[$localeName])) {
        return $emailTexts[$localeName];
      }
    }

    foreach ($emailTexts as $localeName => $texts) {
      if (is_array($texts) && !empty($texts)) {
        return $texts;
      }
    }

    return [];
  }

  /**
   * Разворачивает именованные плейсхолдеры вида {KEY} в шаблоне.
   *
   * @param string $template
   * @param array<string, string> $replacements
   * @return string
   */
  public static function renderTemplate(string $template, array $replacements) : string
  {
    if ($template === '' || empty($replacements)) {
      return $template;
    }

    $pairs = [];
    foreach ($replacements as $key => $value) {
      $pairs['{' . $key . '}'] = (string) $value;
    }

    return strtr($template, $pairs);
  }

  /**
   * Рендерит Markdown-контент в HTML через NadvoParse.
   *
   * @param string $markdown
   * @return string
   */
  public static function renderMarkdown(string $markdown) : string
  {
    if (trim($markdown) === '') {
      return '';
    }

    $parser = new \core\PHPLibrary\NadvoParse();
    return $parser->parse($markdown);
  }
}