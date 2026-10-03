/**
 * CMS «ГИРВАС»
 * 
 * Включена в Реестр российского программного обеспечения Минцифры РФ.
 * Реестровый номер: №25012 от 27.11.2024
 * 
 * @copyright Copyright (c) 2021 - 2027, ИП Шестаков А.Р., «Карельский разработчик».
 *             Все права защищены.
 * @license   https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 * @see       https://gitflic.ru/project/garbalo/cms-girvas Репозиторий продукта
 * @see       https://cms-girvas.ru Сайт продукта
 * @author    Андрей Шестаков <andrey.shestakov@karelian-developer.ru>
 * @support   support@karelian-developer.ru
 */

'use strict';

import {Interactive} from '../interactive.class.js';
import {Locale} from './locale.class.js';
import {URLParser} from '../urlParser.class.js';

/**
 * Клиент
 */
export class Client {
  constructor(core) {
    this.core = core;
    this.isLogged = false;
    this.IPAddress = '0.0.0.0';
    this.CSRFToken = '';
    this.setRestHash();
    this.initCSRFToken();

    this.isNewVisitor = this.checkIsNewVisitor();
  }

  checkIsNewVisitor() {
    const token = localStorage.getItem('_grv_mtoken');
    const tokenDate = localStorage.getItem('_grv_mtoken_date');
    const today = new Date().toDateString();
    
    // Если нет токена или дата не сегодня — новый посетитель
    return token === null || tokenDate !== today;
  }

  initCSRFToken() {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; _grv_csrf=`);

    if (parts.length === 2) {
      this.CSRFToken = decodeURIComponent(parts.pop().split(';').shift());
    }
  }

  getCSRFToken() {
    const match = document.cookie.match(/_grv_csrf=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : this.CSRFToken;
  }

  setRestHash() {
    this.getIPAddress().then((ipAddress) => {
      let date = new Date();
      date.setHours(0);
      date.setMinutes(0);
      date.setSeconds(0);
      date.setMilliseconds(0);

      let address = ipAddress.replaceAll('.', '');
      document.cookie = `_grv_rest=${Number(address) * (Math.round(Math.asin(1) * address.length) << 3)}${date.getTime() / 1000};path=/`;
    });
  }

  static setCookie(name, value, days = 30) {
    let date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));

    let expires = "expires=" + date.toUTCString();
    document.cookie = `${name}=${value};${expires};path=/`;
  }

  /**
   * Проверить наличие cookie
   * 
   * @param {string} name
   * @returns {boolean}
   */
  static existsCookie(name) {
    const cookiePrefix = name + '=';

    return document.cookie
      .split(';')
      .some((item) => item.trim().startsWith(cookiePrefix));
  }

  /**
   * Получить cookie по имени
   * 
   * @param {string} name
   * @returns {string|null}
   */
  static getCookie(name) {
    const cookiePrefix = name + '=';
    const cookies = document.cookie.split(';');

    for (let i = 0; i < cookies.length; i++) {
      const cookie = cookies[i].trim();

      if (cookie.startsWith(cookiePrefix)) {
        return decodeURIComponent(cookie.substring(cookiePrefix.length));
      }
    }

    return null;
  }

  /**
   * Получение IP-адреса клиента
   * @returns {String}
   */
  async getIPAddress() {
    let request = new Interactive('request', {
      method: 'GET',
      url: '/handler/client/ip-address'
    });

    request.target.showingNotification = false;

    return request.target.send().then((data) => {
      if (data.outputData.hasOwnProperty('result')) {
        return data.outputData.result; 
      }

      return '0.0.0.0';
    });
  }

  /**
   * Проверка авторизации клиента
   * @returns {Boolean}
   */
  async checkLogged() {
    let request = new Interactive('request', {
      method: 'GET',
      url: '/handler/client/is-logged'
    });

    request.target.showingNotification = false;

    return request.target.send().then((data) => {
      if (data.outputData.hasOwnProperty('result')) {
        return data.outputData.result; 
      }

      return false;
    });
  }
}