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

import {Interactive} from '../interactive.class.js';
import {Client} from './client.class.js';

export class Metrics {
  constructor(core) {
    this.core = core;
    this.sendMetrics();
  }

  // ==========================================
  // ОТПРАВКА ДАННЫХ МЕТРИК
  // ==========================================
  sendMetrics() {
    const clientMetricsToken = localStorage.getItem('_grv_mtoken');
    const tokenDate = localStorage.getItem('_grv_mtoken_date');
    const today = new Date().toDateString();

    const formData = new FormData();
    formData.append('time', Math.round(new Date().getTime() / 1000));
    formData.append('current_url', document.location.href);
    formData.append('referrer_url', document.referrer || '');

    // ==========================================
    // ИСПРАВЛЕНИЕ: is_visited_new
    // ==========================================
    let isNewVisitor = false;

    if (clientMetricsToken == null || tokenDate !== today) {
      // Новый токен или новый день
      const newToken = this.generateUniqID(64);
      localStorage.setItem('_grv_mtoken', newToken);
      localStorage.setItem('_grv_mtoken_date', today);
      isNewVisitor = true;
    }

    formData.append('is_visited_new', isNewVisitor ? 1 : 0);

    // ==========================================
    // ЛОГ ДЛЯ ОТЛАДКИ
    // ==========================================
    console.log('📤 Отправка метрик:', {
      token: localStorage.getItem('_grv_mtoken')?.substring(0, 20) + '...',
      isNew: isNewVisitor,
      url: document.location.href,
      referrer: document.referrer || '(direct)',
      date: today
    });

    // ==========================================
    // ОТПРАВКА
    // ==========================================
    fetch('/handler/metrics', {
      method: 'POST',
      headers: {
        'Metrics-Token': localStorage.getItem('_grv_mtoken'),
        'X-CSRF-Token': this.core.client.CSRFToken
      },
      body: formData
    }).then((response) => {
      return response.ok ? response.json() : Promise.reject(response);
    }).then((data) => {
      console.log('✅ Метрики отправлены:', data);
    }).catch((rejectionReason) => {
      console.error('❌ Ошибка отправки метрик:', rejectionReason);
      // Показываем уведомление только в режиме отладки
      if (this.core.debugLevel > 1) {
        let interactiveNotification = new Interactive('notification');
        interactiveNotification.target.isPopup = true;
        interactiveNotification.target.setStatusCode(0);
        interactiveNotification.target.setContent('Ошибка отправки метрики: ' + rejectionReason);
        interactiveNotification.target.assembly();
        interactiveNotification.target.show();
      }
    });
  }

  // ==========================================
  // СТАТИЧЕСКИЕ МЕТОДЫ
  // ==========================================
  static convertTimeToTimestamp(value) {
    let date = new Date();
    date.setTime(value);
    return `${date.getFullYear()}/${(date.getMonth() + 1).toString().padStart(2, '0')}/${date.getDate().toString().padStart(2, '0')}`;
  }

  // ==========================================
  // ПОЛУЧЕНИЕ ДАННЫХ
  // ==========================================
  async getDataByTimestamp(time) {
    return fetch(`/handler/metrics?time=${Math.round(time / 1000)}`, {
      method: 'GET',
      headers: {
        'Metrics-Token': localStorage.getItem('_grv_mtoken')
      }
    }).then((response) => {
      return response.ok ? response.json() : Promise.reject(response);
    }).then((data) => {
      if (data.outputData?.hasOwnProperty('data')) {
        return data.outputData.data;
      }
      return [];
    }).catch((error) => {
      console.error('❌ Ошибка получения данных:', error);
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
      return response.ok ? response.json() : Promise.reject(response);
    }).then((data) => {
      if (data.outputData?.hasOwnProperty('data')) {
        return data.outputData.data;
      }
      return [];
    }).catch((error) => {
      console.error('❌ Ошибка получения данных:', error);
      return [];
    });
  }

  // ==========================================
  // ГЕНЕРАЦИЯ ТОКЕНА
  // ==========================================
  generateUniqID(length) {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let result = '';
    const charsLength = chars.length;
    for (let i = 0; i < length; i++) {
      result += chars.charAt(Math.floor(Math.random() * charsLength));
    }
    return result;
  }
}