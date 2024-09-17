/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
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