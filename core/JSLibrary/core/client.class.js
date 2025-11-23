/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
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
    this.CSRFToken = '';
    this.setRestHash();
    this.initCSRFToken();
  }

  initCSRFToken() {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; _grv_csrf=`);
    
    if (parts.length === 2) {
      return decodeURIComponent(parts.pop().split(';').shift());
    }
    
    return '';
  }

  getCSRFToken() {
    return this.CSRFToken;
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

  static existsCookie(name) {
    let cookie = document.cookie;
    let cookiePrefix = name + "=";
    let begin = cookie.indexOf("; " + cookiePrefix);

    if (begin == -1) {
      return false;
    }

    return true;
  }

  static getCookie(name) {
    let cookie = document.cookie;
    let cookiePrefix = name + "=";
    let begin = cookie.indexOf("; " + cookiePrefix);

    if (begin == -1) {
      begin = cookie.indexOf(cookiePrefix);
      if (begin != 0) return null;
    } else {
      begin += 2;
      
      let end = document.cookie.indexOf(";", begin);
      if (end == -1) {
        end = cookie.length;
      }
    }

    return decodeURI(cookie.substring(begin + cookiePrefix.length, end));
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
  async isLogged() {
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