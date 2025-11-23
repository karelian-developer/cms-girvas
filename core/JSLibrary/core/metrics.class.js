/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';

import {Interactive} from '../interactive.class.js';
import {Client} from './client.class.js';

export class Metrics {
  constructor() {
    let clientMetricsToken = localStorage.getItem('_grv_mtoken');

    let formData = new FormData();

    formData.append('time', Math.round(new Date().getTime() / 1000));
    formData.append('current_url', document.location.href);
    formData.append('referrer_url', document.referrer);

    if (clientMetricsToken == null) {
      formData.append('is_visited_new', 1);
      localStorage.setItem('_grv_mtoken', this.generateUniqID(64));
    } else {
      formData.append('is_visited_new', 0);
    }

    fetch('/handler/metrics', {
      method: 'POST',
      headers: {
        'Metrics-Token': localStorage.getItem('_grv_mtoken'),
        'X-CSRF-Token': window.CMSCore.client.CSRFToken
      },
      body: formData
    }).then((response) => {
      return (response.ok) ? response.json() : Promise.reject(response);
    }).then((data) => {

    }, (rejectionReason) => {
      console.log(rejectionReason);
      let interactiveNotification = new Interactive('notification');
      interactiveNotification.target.isPopup = true;
      interactiveNotification.target.setStatusCode(0);
      interactiveNotification.target.setContent(rejectionReason);
      interactiveNotification.target.assembly();

      interactiveNotification.target.show();
    });
  }

  static convertTimeToTimestamp(value) {
    let date = new Date();
    date.setTime(value);

    return `${date.getFullYear()}/${(date.getMonth() + 1).toString().padStart(2, '0')}/${date.getDate().toString().padStart(2, '0')}`;
  }

  async getDataByTimestamp(time) {
    return fetch(`/handler/metrics?time=${Math.round(time / 1000)}`, {
      method: 'GET',
      headers: {
        'Metrics-Token': localStorage.getItem('_grv_mtoken')
      }
    }).then((response) => {
      return (response.ok) ? response.json() : Promise.reject(response);
    }).then((data) => {
      console.log(data);
      if (data.outputData.hasOwnProperty('data')) {
        return data.outputData.data;
      }

      return [];
    });
  }

  async getDataByRangeTimestamp(timeRangeStart, timeRangeEnd) {
    let timeStart = Math.ceil(timeRangeStart / 1000);
    let timeEnd = Math.ceil(timeRangeEnd / 1000);

    return fetch(`/handler/metrics?timeStart=${timeStart}&timeEnd=${timeEnd}`, {
      method: 'GET',
      headers: {
        'Metrics-Token': localStorage.getItem('_grv_mtoken')
      }
    }).then((response) => {
      return (response.ok) ? response.json() : Promise.reject(response);
    }).then((data) => {
      console.log(data);
      if (data.outputData.hasOwnProperty('data')) {
        return data.outputData.data;
      }

      return [];
    }, (rejectionReason) => {
      let interactiveNotification = new Interactive('notification');
      interactiveNotification.target.isPopup = true;
      interactiveNotification.target.setStatusCode(0);
      interactiveNotification.target.setContent(rejectionReason);
      interactiveNotification.target.assembly();

      interactiveNotification.target.show();
    });
  }

  generateUniqID(length) {
    let chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let result = '';

    let charsLength = chars.length;
    for ( let i = 0; i < length; i++ ) {
        result += chars.charAt(Math.floor(Math.random() * charsLength));
    }

    return result;
  }
}