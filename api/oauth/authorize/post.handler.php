<?php

/**
 * CMS «ГИРВАС»
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Репозиторий продукта
 * @copyright   Copyright (c) 2022 - 2026, ИП Шестаков А.Р.
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

if (!defined('IS_NOT_HACKED')) {
  http_response_code(503);
  die('An attempted hacker attack has been detected.');
}

use \core\PHPLibrary\OAuth\Client as OAuthClient;
use \core\PHPLibrary\OAuth\AuthCode as OAuthAuthCode;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  $handlerMessage = $handlerMessage ?? 'Method Not Allowed';
  $handlerStatusCode = $handlerStatusCode ?? 0;
  return;
}

/** @var string|null Действие пользователя (allow / deny) */
$action = $_POST['action'] ?? null;

/** @var string|null Идентификатор клиента */
$clientID = $_POST['client_id'] ?? null;

/** @var string|null redirectURI */
$redirectURI = $_POST['redirect_uri'] ?? null;

/** @var string|null Запрашиваемые scopes */
$scopes = $_POST['scope'] ?? '';

/** @var string|null Параметр state */
$state = $_POST['state'] ?? '';

/** @var string|null codeChallenge для PKCE */
$codeChallenge = $_POST['code_challenge'] ?? null;

/** @var string Метод codeChallenge */
$codeChallengeMethod = $_POST['code_challenge_method'] ?? 'S256';

// 1. Проверяем авторизацию пользователя
$CMSClient = $CMSCore->client;

if (!$CMSClient->isLogged(1)) {
  http_response_code(401);
  $handlerMessage = $handlerMessage ?? 'User not authenticated';
  $handlerStatusCode = $handlerStatusCode ?? 0;
  return;
}

$user = $CMSClient->getUser(1);

if ($user === null) {
  http_response_code(500);
  $handlerMessage = $handlerMessage ?? 'User session not found';
  $handlerStatusCode = $handlerStatusCode ?? 0;
  return;
}

// 2. Проверяем клиента
if (!OAuthClient::existsByClientID($CMSCore, $clientID)) {
  $errorParams = http_build_query([
    'error' => 'invalid_client',
    'state' => $state
  ]);
  header('Location: ' . $redirectURI . '?' . $errorParams);
  exit;
}

$oauthClient = OAuthClient::getByClientID($CMSCore, $clientID);
$oauthClient->initData(['isActive', 'isVerified', 'redirectURI']);

if (!$oauthClient->isActive() || !$oauthClient->isVerified()) {
  $errorParams = http_build_query([
    'error' => 'unauthorized_client',
    'state' => $state
  ]);
  header('Location: ' . $redirectURI . '?' . $errorParams);
  exit;
}

// 3. Обрабатываем отказ пользователя
if ($action === 'deny') {
  $errorParams = http_build_query([
    'error' => 'access_denied',
    'error_description' => 'The user denied the authorization request',
    'state' => $state
  ]);
  header('Location: ' . $redirectURI . '?' . $errorParams);
  exit;
}

// 4. Обрабатываем согласие пользователя
if ($action === 'allow') {
  // Создаём код авторизации
  $code = OAuthAuthCode::create($CMSCore, [
    'clientID' => $oauthClient->getID(),
    'userID' => $user->getID(),
    'scopes' => $scopes,
    'redirectURI' => $redirectURI,
    'codeChallenge' => $codeChallenge,
    'codeChallengeMethod' => $codeChallengeMethod,
    'expiresAt' => time() + 60, // 60 секунд
    'isRevoked' => false
  ]);

  if ($code === null) {
    $errorParams = http_build_query([
      'error' => 'server_error',
      'error_description' => 'Failed to create authorization code',
      'state' => $state
    ]);
    header('Location: ' . $redirectURI . '?' . $errorParams);
    exit;
  }

  $code->initData(['code']);

  // Перенаправляем с кодом
  $successParams = http_build_query([
    'code' => $code->getCode(),
    'state' => $state
  ]);

  header('Location: ' . $redirectURI . '?' . $successParams);
  exit;
}

// Некорректное действие
http_response_code(400);
$handlerMessage = $handlerMessage ?? 'Invalid action';
$handlerStatusCode = $handlerStatusCode ?? 0;