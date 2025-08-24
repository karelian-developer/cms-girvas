<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link  https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license   https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
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
  public function sendEmail(string $from, string $to, string $subject, string $message) : void
  {
    $this->sendCommand("MAIL FROM: <{$from}>", '250');
    $this->sendCommand("RCPT TO: <{$to}>", '250');
    $this->sendCommand('DATA', '354');

    $headers = "From: {$from}\r\n";
    $headers .= "To: {$to}\r\n";
    $headers .= "Subject: {$subject}\r\n";
    $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
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
}