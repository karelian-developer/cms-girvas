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
use \core\PHPLibrary\SystemCore\Locale as CMSLocale;

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  http_response_code(405);
  $handlerMessage = $handlerMessage ?? 'Method Not Allowed';
  $handlerStatusCode = $handlerStatusCode ?? 0;
  return;
}

/** @var string|null Идентификатор клиента */
$clientID = $_GET['client_id'] ?? null;

/** @var string|null Запрашиваемый redirectURI */
$redirectURI = $_GET['redirect_uri'] ?? null;

/** @var string|null Тип ответа (должен быть 'code') */
$responseType = $_GET['response_type'] ?? null;

/** @var string|null Запрашиваемые scopes */
$scopes = $_GET['scope'] ?? '';

/** @var string|null Параметр state для защиты от CSRF */
$state = $_GET['state'] ?? '';

/** @var string|null codeChallenge для PKCE */
$codeChallenge = $_GET['code_challenge'] ?? null;

/** @var string Метод codeChallenge (по умолчанию S256) */
$codeChallengeMethod = $_GET['code_challenge_method'] ?? 'S256';

// 1. Проверяем обязательные параметры
if ($clientID === null || $redirectURI === null || $responseType === null) {
  http_response_code(400);
  $handlerMessage = $handlerMessage ?? 'Missing required parameters: client_id, redirect_uri, response_type';
  $handlerStatusCode = $handlerStatusCode ?? 0;
  return;
}

// 2. Проверяем response_type
if ($responseType !== 'code') {
  // Перенаправляем с ошибкой на redirectURI клиента
  $errorParams = http_build_query([
    'error' => 'unsupported_response_type',
    'state' => $state
  ]);
  header('Location: ' . $redirectURI . '?' . $errorParams);
  exit;
}

// 3. Проверяем существование клиента
if (!OAuthClient::existsByClientID($CMSCore, $clientID)) {
  $errorParams = http_build_query([
    'error' => 'invalid_client',
    'error_description' => 'Client not found',
    'state' => $state
  ]);
  header('Location: ' . $redirectURI . '?' . $errorParams);
  exit;
}

$oauthClient = OAuthClient::getByClientID($CMSCore, $clientID);
$oauthClient->initData(['*']);

// 4. Проверяем активность и верификацию клиента
if (!$oauthClient->isActive() || !$oauthClient->isVerified()) {
  $errorParams = http_build_query([
    'error' => 'unauthorized_client',
    'error_description' => 'Client is not active or not verified',
    'state' => $state
  ]);
  header('Location: ' . $redirectURI . '?' . $errorParams);
  exit;
}

// 5. Проверяем redirectURI
if (!$oauthClient->isRedirectURIAllowed($redirectURI)) {
  http_response_code(400);
  $handlerMessage = $handlerMessage ?? 'Invalid redirect_uri';
  $handlerStatusCode = $handlerStatusCode ?? 0;
  return;
}

// 6. Проверяем grant_type
if (!$oauthClient->supportsGrantType('authorization_code')) {
  $errorParams = http_build_query([
    'error' => 'unauthorized_client',
    'error_description' => 'Grant type authorization_code not allowed for this client',
    'state' => $state
  ]);
  header('Location: ' . $redirectURI . '?' . $errorParams);
  exit;
}

// 7. Проверяем scopes
if (!empty($scopes) && !$oauthClient->areScopesAllowed($scopes)) {
  $errorParams = http_build_query([
    'error' => 'invalid_scope',
    'error_description' => 'Requested scopes exceed client allowed scopes',
    'state' => $state
  ]);
  header('Location: ' . $redirectURI . '?' . $errorParams);
  exit;
}

// 8. Проверяем авторизацию пользователя на сайте компании
$CMSClient = $CMSCore->client;

if (!$CMSClient->isLogged(1)) {
  // Сохраняем параметры авторизации в сессию
  $_SESSION['oauth_authorize_params'] = [
    'client_id' => $clientID,
    'redirect_uri' => $redirectURI,
    'response_type' => $responseType,
    'scope' => $scopes,
    'state' => $state,
    'code_challenge' => $codeChallenge,
    'code_challenge_method' => $codeChallengeMethod
  ];

  // Перенаправляем на страницу входа
  header('Location: /login?return_to=/handler/oauth/authorize?' . http_build_query($_GET));
  exit;
}

// 9. Пользователь авторизован — показываем consent-форму
$user = $CMSClient->getUser(1);

if ($user === null) {
  http_response_code(500);
  $handlerMessage = $handlerMessage ?? 'User session not found';
  $handlerStatusCode = $handlerStatusCode ?? 0;
  return;
}

$user->initData(['login', 'email', 'metadata']);

// Формируем consent-страницу
$CMSLocale = $CMSCore->locale;
$CMSLocale->setTypeName('handler');
$CMSLocale->initPathes();

$theme = $CMSCore->getTheme();

// Человекочитаемые описания scopes
$scopeDescriptions = [
  'profile' => $CMSLocale->getSingleValueByKey('OAUTH_SCOPE_PROFILE'),
  'email' => $CMSLocale->getSingleValueByKey('OAUTH_SCOPE_EMAIL'),
  'read' => $CMSLocale->getSingleValueByKey('OAUTH_SCOPE_READ'),
  'write' => $CMSLocale->getSingleValueByKey('OAUTH_SCOPE_WRITE')
];

$requestedScopesArray = !empty($scopes) ? explode(' ', $scopes) : [];

$handlerOutputData['client'] = [
  'name' => $oauthClient->getName(),
  'description' => $oauthClient->getDescription(),
  'ownerEmail' => $oauthClient->getOwnerEmail()
];

$handlerOutputData['user'] = [
  'login' => $user->getLogin(),
  'email' => $user->getEmail()
];

$handlerOutputData['scopes'] = [];
foreach ($requestedScopesArray as $scope) {
  $handlerOutputData['scopes'][] = [
    'name' => $scope,
    'description' => $scopeDescriptions[$scope] ?? $scope
  ];
}

$handlerOutputData['form_action'] = '/handler/oauth/authorize';
$handlerOutputData['hidden_fields'] = [
  'client_id' => $clientID,
  'redirect_uri' => $redirectURI,
  'response_type' => $responseType,
  'scope' => $scopes,
  'state' => $state,
  'code_challenge' => $codeChallenge,
  'code_challenge_method' => $codeChallengeMethod
];

$handlerMessage = $handlerMessage ?? 'Consent form displayed';
$handlerStatusCode = $handlerStatusCode ?? 1;