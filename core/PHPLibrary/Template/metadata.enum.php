<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Template;

enum EnumMetadata
{
  case AUTHOR_NAME;
  case AUTHOR_CODE_NAME;
  case AUTHOR_CODE_SERVER_NAME;
  case AUTHOR_CODE_CLIENT_NAME;
  case AUTHOR_DESIGNER_NAME;
  case AUTHOR_LAYOUT_NAME;
  case AUTHOR_SITE_LINK;
  case AUTHOR_SOCIAL_VK_LINK;
  case AUTHOR_SOCIAL_OK_LINK;
  case CATEGORY_NAME;
  case WEIGHT;
  case DATETIME_CREATED_UNIX;
  case DATETIME_UPDATED_UNIX;
  case VERSION;
}