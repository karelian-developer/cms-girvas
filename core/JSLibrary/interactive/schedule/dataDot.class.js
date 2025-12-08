/**
 * CMS «ГИРВАС»
 * 
 * Включена в Реестр российского программного обеспечения Минцифры РФ.
 * Реестровый номер: №25012 от 27.11.2024
 * 
 * @copyright Copyright (c) 2021 - 2026, ИП Шестаков А.Р., «Карельский разработчик».
 *             Все права защищены.
 * @license   https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 * @see       https://gitflic.ru/project/garbalo/cms-girvas Репозиторий продукта
 * @see       https://cms-girvas.ru Сайт продукта
 * @author    Андрей Шестаков <andrey.shestakov@karelian-developer.ru>
 * @support   support@karelian-developer.ru
 */

'use strict';

export class DataDot {
  constructor(x, y) {
    this.collision = false;
    this.x = x;
    this.y = y;

    this.data = null;
    this.label = null;
  }

  setData(value) {
    this.data = value;
  }

  getData() {
    return this.data;
  }

  setLabel(value) {
    this.label = value;
  }

  getLabel() {
    return this.label;
  }
}