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
 * @copyright   Copyright (c) 2021 - 2026, ИП Шестаков А.Р., «Карельский разработчик» (https://карельский-разработчик.рф/)
 * Все права защищены.
 * 
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 * @author      Андрей Шестаков <andrey.shestakov@karelian-developer.ru>
 * 
 * @support     support@karelian-developer.ru
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