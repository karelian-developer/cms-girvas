/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';

import {Interactive} from "../interactive.class.js";

/**
 * Интерактивный запрос
 */
export class Request {
  /**
   * constructor
   * 
   * @param {*} method 
   * @param {*} url 
   */
  constructor(interactiveObject, method, url, data = undefined) {
    this.interactiveObject = interactiveObject;

    this.setMethod(method);
    this.setURL(url);

    this.data = (data == undefined || data == null) ? undefined : new FormData(data);
    this.headers = {};
    this.showingNotification = true;

    if (window.CMSCore !== undefined) {
      if (window.CMSCore.client.CSRFToken !== '') {
        console.log(window.CMSCore.client.CSRFToken);
        this.headers['X-CSRF-Token'] = window.CMSCore.client.CSRFToken;
      }
    }
  }

  /**
   * Установить метод запроса
   * 
   * @param {*} value 
   */
  setMethod(value) {
    this.method = value;
  }

  /**
   * Установить URL для запроса
   * 
   * @param {*} value 
   */
  setURL(value) {
    this.url = value;
  }

  /**
   * Получить метод
   * 
   * @returns 
   */
  getMethod() {
    return this.method;
  }

  /**
   * Получить URL
   * 
   * @returns 
   */
  getURL() {
    return this.url;
  }

  /**
   * Добавить данные
   * 
   * @param {*} name 
   * @param {*} value 
   */
  addData(name, value) {
    this.data.append(name, value);
  }

  /**
   * Получить данные по ключу
   * 
   * @param {*} name 
   * @returns 
   */
  getData(name) {
    return this.data.get(name);
  }

  /**
   * Получить все данные по ключу
   * 
   * @param {*} name
   * @returns 
   */
  getDataAll(name) {
    return this.data.getAll(name);
  }

  /**
   * Получить случайную строку
   * 
   * @returns 
   */
  getRandomString() {
    return Math.random().toString(36).slice(2);
  }

  /**
   * Отправить запрос (ассинхронно)
   * 
   * @returns {Response}
   */
  async send() {
    let interactiveNotificationLoading;
    let requestMethod = this.getMethod();
    let requestURL = this.getURL();

    if (this.data != undefined && this.data != null) {
      this.addData('_grv_' + this.getRandomString(), this.getRandomString());
    }

    if (this.showingNotification) {
      interactiveNotificationLoading = new Interactive('notification');
      interactiveNotificationLoading.target.isPopup = true;
      interactiveNotificationLoading.target.setStatusCode(-1);
      interactiveNotificationLoading.target.setContent('<img src="/images/loading_24.gif">');
      interactiveNotificationLoading.target.assembly();

      interactiveNotificationLoading.target.show();
    }

    return fetch(requestURL, {
      method: requestMethod,
      body: this.data,
      headers: this.headers
    }).then((response) => {
      return (response.ok) ? response.json() : Promise.reject(response);
    }).then((data) => {
      if (this.showingNotification) {
        interactiveNotificationLoading.target.hide();
      }

      if (typeof(data.outputData.reload) != 'undefined') {
        setTimeout(() => {
          window.location.reload();
        }, 10);
      }

      if (typeof(data.outputData.href) != 'undefined') {
        setTimeout(() => {
          window.location.href = data.outputData.href;
        }, 10);
      }

      if (this.showingNotification) {
        if (data.hasOwnProperty('message')) {
          let interactiveNotification;
          
          interactiveNotification = new Interactive('notification');
          interactiveNotification.target.isPopup = true;
          interactiveNotification.target.setStatusCode(data.statusCode);
          interactiveNotification.target.setContent(data.message);
          interactiveNotification.target.assembly();

          interactiveNotification.target.show();
        } else {
          console.info('The handler did not transmit the message in the required format.');
        }
      }

      console.log(data);

      return data;
    }, (rejectionReason) => {
      let interactiveNotification = new Interactive('notification');
      interactiveNotification.target.isPopup = true;
      interactiveNotification.target.setStatusCode(0);
      interactiveNotification.target.setContent(rejectionReason);
      interactiveNotification.target.assembly();

      interactiveNotification.target.show();
    });
  }
}