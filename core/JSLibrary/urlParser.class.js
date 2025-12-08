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

/**
 * Парсер адресной строки браузера
 */
export class URLParser {
  /**
   * constructor
   */
  constructor() {
    this.path = this.getPathArray();
  }

  /**
   * Получить часть адреса по индексу
   * 
   * @param {Number} index 
   * @returns 
   */
  getPathPart(index) {
    let string = (typeof(this.path[index]) != 'undefined') ? this.path[index] : null;
    string = (string != null) ? string.split('?') : null;
    return (string != null) ? string[0] : null;
  }

  /**
   * Получить весь адрес в виде массива
   * 
   * @returns {Array}
   */
  getPathArray() {
    return window.location.pathname.split('/');
  }

  /**
   * Получить весь адрес в виде строки
   * 
   * @returns {String}
   */
  getPathString() {
    return window.location.pathname;
  }
  
  /**
   * Получить значение параметра адреса
   * 
   * @param {*} name 
   * @returns 
   */
  getParam(name) {
    /** @var {URLSearchParams} */
    let urlSearchParams = new URLSearchParams(window.location.search);

    if (urlSearchParams.has(name)) {
      return urlSearchParams.get(name);
    }

    return null;
  }
}