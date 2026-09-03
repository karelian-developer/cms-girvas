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

import {Metrics} from "../../metrics.class.js";
import {Interactive} from "../../../interactive.class.js";
import {URLParser} from "../../../urlParser.class.js";

export class PageAnalytics {
  constructor(page, params = {}) {
    this.page = page;
  }

  getDateFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    const dateParam = urlParams.get('date');
    
    if (dateParam) {
      const parsed = new Date(dateParam);
      if (!isNaN(parsed.getTime())) {
        return parsed;
      }
    }
    
    return new Date();
  }

  init() {
    const searchParams = new URLParser();
    
    this.page.core.locales.admin.getData().then((localeData) => {
      const analyticApp = document.querySelector('#analytic-app');

      if (analyticApp !== null) {
        const attendanceScheduleContainerElement = analyticApp.querySelector('[data-role="attendance-schedule"]');
        
        // Создаём canvas
        const scheduleContainerElement = this.scheduleContainerElementCreate();
        attendanceScheduleContainerElement.append(scheduleContainerElement);
        
        const scheduleParentElement = scheduleContainerElement.parentElement;
        const scheduleParentElementWidth = scheduleParentElement.offsetWidth || 800;

        scheduleContainerElement.setAttribute('width', `${scheduleParentElementWidth}px`);
        scheduleContainerElement.setAttribute('height', '400px');

        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();
        
        const firstDate = new Date(year, month, 1);
        const lastDate = new Date(year, month + 1, 0);
        
        console.log(`📅 Загружаем данные за: ${firstDate.toLocaleDateString()} — ${lastDate.toLocaleDateString()}`);
        
        window.CMSCore.metrics.getDataByRangeTimestamp(firstDate.getTime(), lastDate.getTime()).then((metricsData) => {
          const scheduleAttendance = new Interactive('schedule', {
            canvasElement: scheduleContainerElement,
            type: 'linear',
            zoomable: true,
            minZoom: 0.5,
            maxZoom: 5,
            zoomStep: 0.1,
            showNavigator: true,
            padding: { top: 30, right: 30, bottom: 40, left: 50 },
            height: 'auto', 
            minHeight: 250,
            maxHeight: 600
          });

          scheduleAttendance.target.setFrameSize(scheduleContainerElement.width - 50, scheduleContainerElement.height - 50 - 40);
          scheduleAttendance.target.addGroup('Просмотры');

          if (searchParams.getPathPart(4) === null) {
            scheduleAttendance.target.addGroup('Визиты');
            scheduleAttendance.target.addGroup('Посещения');
          }

          metricsData.forEach((data) => {
            let urlsTotalViews = 0;
            let time = data.metrics.time * 1000;
            let date = new Date(time);
            let day = date.getDate() - 1;

            const pathPart4 = searchParams.getPathPart(4);
            const targetObject = document.querySelector('article.page[data-name]');
            const targetName = targetObject?.getAttribute('data-name');

            // ==========================================
            // СЧИТАЕМ ПРОСМОТРЫ, ВИЗИТЫ И ПОСЕЩЕНИЯ
            // ==========================================
            let visits0 = [];
            let visits1 = [];

            for (let token in data.metrics.views) {
              let urls = data.metrics.views[token].urls;
              let urlTransfers = data.metrics.views[token].url_transfers || [];

              for (let url in urls) {
                // Проверяем, подходит ли URL под текущую страницу
                let isMatch = false;
                
                if (pathPart4 === null) {
                  // Главная аналитика — все просмотры
                  isMatch = true;
                } else if (targetName) {
                  // Аналитика конкретной записи/страницы
                  const urlLower = url.toLowerCase();
                  const targetLower = targetName.toLowerCase();
                  
                  // Проверяем /entry/name или /page/name
                  if (urlLower.includes(`/entry/${targetLower}`) || 
                      urlLower.includes(`/page/${targetLower}`) ||
                      urlLower.includes(`/entry/${targetLower}?`) ||
                      urlLower.includes(`/page/${targetLower}?`)) {
                    isMatch = true;
                  }
                }
                
                if (isMatch) {
                  urlsTotalViews += urls[url];
                  
                  // ==========================================
                  // СЧИТАЕМ ВИЗИТЫ И ПОСЕЩЕНИЯ
                  // ==========================================
                  for (let transferIndex in urlTransfers) {
                    for (let transfer in urlTransfers[transferIndex]) {
                      let transferData = urlTransfers[transferIndex][transfer];
                      let urlReferral = transferData.referral;
                      let visitedIsNew = transferData.is_visited_new;
                      
                      // Визиты: переходы (не прямые заходы)
                      if (transfer !== urlReferral && urlReferral && urlReferral !== '') {
                        if (visits0.indexOf(token) === -1) {
                          visits0.push(token);
                        }
                      }
                      
                      // Посещения: новые посетители
                      if (visitedIsNew && visits1.indexOf(token) === -1) {
                        visits1.push(token);
                      }
                    }
                  }
                }
              }
            }

            // ==========================================
            // ДОБАВЛЯЕМ ДАННЫЕ В ГРАФИК
            // ==========================================
            scheduleAttendance.target.addData(0, day, urlsTotalViews);

            // Визиты и посещения добавляем ВСЕГДА, если есть данные
            // Если группы ещё нет — создаём
            if (scheduleAttendance.target.types.length < 2) {
              scheduleAttendance.target.addGroup('Визиты');
            }
            if (scheduleAttendance.target.types.length < 3) {
              scheduleAttendance.target.addGroup('Посещения');
            }
            
            scheduleAttendance.target.addData(1, day, visits0.length);
            scheduleAttendance.target.addData(2, day, visits1.length);

            // Устанавливаем цвета
            scheduleAttendance.target.types[0].setColor('#EE82EE');
            scheduleAttendance.target.types[1].setColor('#5B92E5');
            scheduleAttendance.target.types[2].setColor('#088567');
          });
    
          scheduleAttendance.target.buildData();
          scheduleAttendance.target.init();
          scheduleAttendance.assembly();
        });
      }

      if (searchParams.getPathPart(3) === 'form' && searchParams.getPathPart(4) !== null) {
        const formID = searchParams.getPathPart(4);

        const tableItems = document.querySelectorAll('[data-element="form-data"]');
        for (let tableItem of tableItems) {
          const formDataID = tableItem.getAttribute('data-id');
          const panelElement = tableItem.querySelector('[data-element="panel"]');
          const panelEventElements = panelElement.querySelectorAll('[data-event]');

          for (let eventElement of panelEventElements) {
            eventElement.addEventListener('click', (event) => {
              event.preventDefault();

              if (eventElement.getAttribute('data-event') === 'remove') {
                const interactiveModal = new Interactive('modal', {
                  title: localeData.MODAL_FORM_DELETE_TITLE,
                  content: localeData.MODAL_FORM_DELETE_DESCRIPTION
                });
                
                interactiveModal.target.addButton(localeData.BUTTON_DELETE_LABEL, () => {
                  const formData = new FormData();
                  formData.append('form_id', formID);
                  formData.append('form_data_id', formDataID);

                  const request = new Interactive('request', {
                    method: 'DELETE',
                    url: '/handler/form/data?localeMessage=' + window.CMSCore.locales.admin.name
                  });
        
                  request.target.data = formData;
                  request.target.send().then((data) => {
                    if (data.statusCode === 1) {
                      window.location.href = '/admin/analytics/form/' + formID;
                    }
                  });
                });

                interactiveModal.target.addButton(localeData.BUTTON_CANCEL_LABEL, () => {
                  interactiveModal.target.close();
                });

                interactiveModal.assembly();
                document.body.appendChild(interactiveModal.target.element);
                interactiveModal.target.show();
              }
            });
          }
        }
      }
    }, (rejectionReason) => {
      this.page.showPopupNotification(rejectionReason, 0);
    });
  }

  scheduleContainerElementCreate() {
    const canvas = document.createElement('canvas');
    canvas.style.width = '100%';
    canvas.style.height = '100%';
    return canvas;
  }
}