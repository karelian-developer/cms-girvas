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
use \core\PHPLibrary\OAuth\Token as OAuthToken;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  $handlerMessage = $handlerMessage ?? 'Method Not Allowed';
  $handlerStatusCode = $handlerStatusCode ?? 0;
  return;
}

// ============================================================
// 1. Извлечение параметров
// ============================================================

/** @var string|null Тип гранта */
$grantType = $_POST['grant_type'] ?? null;

/** @var string|null Идентификатор клиента */
$clientID = $_POST['client_id'] ?? null;

/** @var string|null Секрет клиента */
$clientSecret = $_POST['client_secret'] ?? null;

/** @var string|null Код авторизации (для authorization_code) */
$code = $_POST['code'] ?? null;

/** @var string|null redirectURI (для authorization_code) */
$redirectURI = $_POST['redirect_uri'] ?? null;

/** @var string|null codeVerifier для PKCE */
$codeVerifier = $_POST['code_verifier'] ?? null;

/** @var string|null refreshToken (для refresh_token) */
$refreshToken = $_POST['refresh_token'] ?? null;

/** @var string|null Запрашиваемые scopes */
$scopes = $_POST['scope'] ?? '';

// ============================================================
// 2. Базовая валидация
// ============================================================

if ($grantType === null || $clientID === null) {
  http_response_code(400);
  $handlerMessage = $handlerMessage ?? 'Missing required parameters: grant_type, client_id';
  $handlerStatusCode = $handlerStatusCode ?? 0;
  return;
}

// ============================================================
// 3. Аутентификация клиента
// ============================================================

if (!OAuthClient::existsByClientID($CMSCore, $clientID)) {
  http_response_code(401);
  $handlerMessage = $handlerMessage ?? 'Invalid client credentials';
  $handlerStatusCode = $handlerStatusCode ?? 0;
  return;
}

$oauthClient = OAuthClient::getByClientID($CMSCore, $clientID);
$oauthClient->initData(['*']);

// Проверяем активность и верификацию
if (!$oauthClient->isActive() || !$oauthClient->isVerified()) {
  http_response_code(401);
  $handlerMessage = $handlerMessage ?? 'Client is not active or not verified';
  $handlerStatusCode = $handlerStatusCode ?? 0;
  return;
}

// Проверяем client_secret
if ($clientSecret === null || !$oauthClient->verifySecret($clientSecret)) {
  http_response_code(401);
  $handlerMessage = $handlerMessage ?? 'Invalid client credentials';
  $handlerStatusCode = $handlerStatusCode ?? 0;
  return;
}

// Проверяем IP клиента по белому списку
$clientIP = $_SERVER['REMOTE_ADDR'];
if (!$oauthClient->isIPAllowed($clientIP)) {
  http_response_code(403);
  $handlerMessage = $handlerMessage ?? 'Client IP not allowed';
  $handlerStatusCode = $handlerStatusCode ?? 0;
  return;
}

// ============================================================
// 4. Обработка грантов
// ============================================================

switch ($grantType) {
  
  // ----------------------------------------------------------
  // Authorization Code Grant
  // ----------------------------------------------------------
  case 'authorization_code':
    
    // Проверяем поддержку гранта
    if (!$oauthClient->supportsGrantType('authorization_code')) {
      http_response_code(400);
      $handlerMessage = $handlerMessage ?? 'Grant type authorization_code not allowed for this client';
      $handlerStatusCode = $handlerStatusCode ?? 0;
      return;
    }

    // Проверяем обязательные параметры
    if ($code === null || $redirectURI === null) {
      http_response_code(400);
      $handlerMessage = $handlerMessage ?? 'Missing required parameters: code, redirect_uri';
      $handlerStatusCode = $handlerStatusCode ?? 0;
      return;
    }

    // Получаем код авторизации
    if (!OAuthAuthCode::existsByCode($CMSCore, $code)) {
      http_response_code(400);
      $handlerMessage = $handlerMessage ?? 'Invalid authorization code';
      $handlerStatusCode = $handlerStatusCode ?? 0;
      return;
    }

    $authCode = OAuthAuthCode::getByCode($CMSCore, $code);
    $authCode->initData(['*']);

    // Проверяем валидность кода
    if (!$authCode->isValid()) {
      http_response_code(400);
      $handlerMessage = $handlerMessage ?? 'Authorization code expired or revoked';
      $handlerStatusCode = $handlerStatusCode ?? 0;
      return;
    }

    // Проверяем, что код принадлежит этому клиенту
    if ($authCode->getClientID() !== $oauthClient->getID()) {
      http_response_code(400);
      $handlerMessage = $handlerMessage ?? 'Authorization code was issued to another client';
      $handlerStatusCode = $handlerStatusCode ?? 0;
      return;
    }

    // Проверяем redirectURI
    if (!hash_equals($authCode->getRedirectURI(), $redirectURI)) {
      http_response_code(400);
      $handlerMessage = $handlerMessage ?? 'redirect_uri mismatch';
      $handlerStatusCode = $handlerStatusCode ?? 0;
      return;
    }

    // Проверяем PKCE
    if ($codeVerifier === null) {
      http_response_code(400);
      $handlerMessage = $handlerMessage ?? 'Missing code_verifier for PKCE';
      $handlerStatusCode = $handlerStatusCode ?? 0;
      return;
    }

    if (!$authCode->verifyCodeChallenge($codeVerifier)) {
      http_response_code(400);
      $handlerMessage = $handlerMessage ?? 'Invalid code_verifier';
      $handlerStatusCode = $handlerStatusCode ?? 0;
      return;
    }

    // Проверяем лимит токенов клиента
    $activeTokensCount = OAuthToken::getActiveCountByClientID($CMSCore, $oauthClient->getID());
    if ($activeTokensCount >= $oauthClient->getMaxTokens()) {
      http_response_code(429);
      $handlerMessage = $handlerMessage ?? 'Token limit exceeded for this client';
      $handlerStatusCode = $handlerStatusCode ?? 0;
      return;
    }

    // Отзываем код авторизации (одноразовый)
    $authCode->revoke();

    // Создаём токены
    $token = OAuthToken::create($CMSCore, [
      'clientID' => $oauthClient->getID(),
      'userID' => $authCode->getUserID(),
      'scopes' => $authCode->getScopes(),
      'expiresAt' => time() + $oauthClient->getTokenTTL(),
      'isRevoked' => false
    ]);

    if ($token === null) {
      http_response_code(500);
      $handlerMessage = $handlerMessage ?? 'Failed to create access token';
      $handlerStatusCode = $handlerStatusCode ?? 0;
      return;
    }

    $token->initData(['accessToken', 'refreshToken', 'expiresAt']);

    // Формируем ответ
    $handlerOutputData['access_token'] = $token->getAccessToken();
    $handlerOutputData['token_type'] = 'Bearer';
    $handlerOutputData['expires_in'] = $oauthClient->getTokenTTL();
    $handlerOutputData['refresh_token'] = $token->getRefreshToken();
    $handlerOutputData['scope'] = $authCode->getScopes();

    $handlerMessage = $handlerMessage ?? 'Token issued successfully';
    $handlerStatusCode = $handlerStatusCode ?? 1;
    break;

  // ----------------------------------------------------------
  // Refresh Token Grant
  // ----------------------------------------------------------
  case 'refresh_token':
    
    // Проверяем поддержку гранта
    if (!$oauthClient->supportsGrantType('refresh_token')) {
      http_response_code(400);
      $handlerMessage = $handlerMessage ?? 'Grant type refresh_token not allowed for this client';
      $handlerStatusCode = $handlerStatusCode ?? 0;
      return;
    }

    // Проверяем обязательные параметры
    if ($refreshToken === null) {
      http_response_code(400);
      $handlerMessage = $handlerMessage ?? 'Missing required parameter: refresh_token';
      $handlerStatusCode = $handlerStatusCode ?? 0;
      return;
    }

    // Получаем токен по refreshToken
    $token = OAuthToken::getByRefreshToken($CMSCore, $refreshToken);

    if ($token === null) {
      http_response_code(400);
      $handlerMessage = $handlerMessage ?? 'Invalid refresh token';
      $handlerStatusCode = $handlerStatusCode ?? 0;
      return;
    }

    $token->initData(['*']);

    // Проверяем, что токен принадлежит этому клиенту
    if ($token->getClientID() !== $oauthClient->getID()) {
      http_response_code(400);
      $handlerMessage = $handlerMessage ?? 'Refresh token was issued to another client';
      $handlerStatusCode = $handlerStatusCode ?? 0;
      return;
    }

    // Проверяем, что токен не отозван
    if ($token->isRevoked()) {
      http_response_code(400);
      $handlerMessage = $handlerMessage ?? 'Refresh token has been revoked';
      $handlerStatusCode = $handlerStatusCode ?? 0;
      return;
    }

    // Ротация: отзываем старый токен
    $token->revoke();

    // Создаём новый токен
    $newToken = OAuthToken::create($CMSCore, [
      'clientID' => $oauthClient->getID(),
      'userID' => $token->getUserID(),
      'scopes' => !empty($scopes) ? $scopes : $token->getScopes(),
      'expiresAt' => time() + $oauthClient->getTokenTTL(),
      'isRevoked' => false
    ]);

    if ($newToken === null) {
      http_response_code(500);
      $handlerMessage = $handlerMessage ?? 'Failed to refresh access token';
      $handlerStatusCode = $handlerStatusCode ?? 0;
      return;
    }

    $newToken->initData(['accessToken', 'refreshToken', 'expiresAt']);

    // Формируем ответ
    $handlerOutputData['access_token'] = $newToken->getAccessToken();
    $handlerOutputData['token_type'] = 'Bearer';
    $handlerOutputData['expires_in'] = $oauthClient->getTokenTTL();
    $handlerOutputData['refresh_token'] = $newToken->getRefreshToken();
    $handlerOutputData['scope'] = $newToken->getScopes();

    $handlerMessage = $handlerMessage ?? 'Token refreshed successfully';
    $handlerStatusCode = $handlerStatusCode ?? 1;
    break;

  // ----------------------------------------------------------
  // Неизвестный грант
  // ----------------------------------------------------------
  default:
    http_response_code(400);
    $handlerMessage = $handlerMessage ?? 'Unsupported grant_type';
    $handlerStatusCode = $handlerStatusCode ?? 0;
    break;
}