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
    this.currentDate = this.getDateFromURL();
    this.monthDisplay = null;
  }

  // ==========================================
  // ПОЛУЧИТЬ ДАТУ ИЗ URL
  // ==========================================
  getDateFromURL() {
    try {
      const urlParams = new URLSearchParams(window.location.search);
      const dateParam = urlParams.get('date');
      
      if (dateParam) {
        let parsed;
        
        // Нормализация: YYYY-MM → YYYY-MM-01
        if (/^\d{4}-\d{2}$/.test(dateParam)) {
          parsed = new Date(dateParam + '-01');
        } else {
          parsed = new Date(dateParam);
        }
        
        if (!isNaN(parsed.getTime())) {
          console.log('📅 Дата из URL:', parsed.toLocaleDateString());
          return parsed;
        }
      }
    } catch (e) {
      console.warn('⚠️ Ошибка парсинга даты из URL:', e);
    }
    
    console.log('📅 Используем текущую дату');
    return new Date();
  }

  // ==========================================
  // ОБНОВЛЕНИЕ ОТОБРАЖЕНИЯ МЕСЯЦА
  // ==========================================
  updateMonthDisplay() {
    if (!this.monthDisplay) return;
    const monthNames = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
                        'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
    this.monthDisplay.textContent = 
      `${monthNames[this.currentDate.getMonth()]} ${this.currentDate.getFullYear()}`;
  }

  // ==========================================
  // ИНИЦИАЛИЗАЦИЯ
  // ==========================================
  init() {
    const searchParams = new URLParser();
    
    this.page.core.locales.admin.getData().then((localeData) => {
      const analyticApp = document.querySelector('#analytic-app');

      if (analyticApp !== null) {
        // ==========================================
        // ДОБАВЛЯЕМ ОТОБРАЖЕНИЕ МЕСЯЦА
        // ==========================================
        const titleContainer = document.querySelector('.page__title-container');
        if (titleContainer) {
          const monthDisplay = document.createElement('span');
          monthDisplay.className = 'analytics-month-display';
          monthDisplay.style.cssText = 'font-size:16px;font-weight:600;color:#555;margin-left:12px;';
          titleContainer.appendChild(monthDisplay);
          this.monthDisplay = monthDisplay;
          this.updateMonthDisplay();
        }

        const attendanceScheduleContainerElement = analyticApp.querySelector('[data-role="attendance-schedule"]');
        
        const scheduleContainerElement = this.scheduleContainerElementCreate();
        attendanceScheduleContainerElement.append(scheduleContainerElement);
        
        const scheduleParentElement = scheduleContainerElement.parentElement;
        const scheduleParentElementWidth = scheduleParentElement.offsetWidth || 800;

        scheduleContainerElement.setAttribute('width', `${scheduleParentElementWidth}px`);
        scheduleContainerElement.setAttribute('height', '400px');

        this.loadChartData(scheduleContainerElement);
      }

      // ==========================================
      // ОБРАБОТКА ФОРМ
      // ==========================================
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

  // ==========================================
  // ЗАГРУЗКА ДАННЫХ ДЛЯ ГРАФИКА
  // ==========================================
  loadChartData(canvasElement) {
    const year = this.currentDate.getFullYear();
    const month = this.currentDate.getMonth();
    const firstDate = new Date(year, month, 1);
    const lastDate = new Date(year, month + 1, 0);
    const daysInMonth = lastDate.getDate();

    console.log(`📅 Загружаем данные за: ${firstDate.toLocaleDateString()} — ${lastDate.toLocaleDateString()}`);

    const container = canvasElement.parentElement;
    container.innerHTML = `
      <div class="analytics-loader" style="padding:40px;text-align:center;">
        <div style="display:inline-block;width:30px;height:30px;border:3px solid #eee;border-top-color:#2196F3;border-radius:50%;animation:spin 0.8s linear infinite;"></div>
        <p style="margin-top:10px;color:#999;">Загрузка данных...</p>
      </div>
    `;

    const newCanvas = document.createElement('canvas');
    newCanvas.setAttribute('width', canvasElement.width || 800);
    newCanvas.setAttribute('height', canvasElement.height || 400);
    newCanvas.style.width = '100%';
    newCanvas.style.height = '100%';
    
    container.innerHTML = '';
    container.append(newCanvas);

    window.CMSCore.metrics.getDataByRangeTimestamp(firstDate.getTime(), lastDate.getTime())
      .then((metricsData) => {
        this.buildChart(newCanvas, metricsData, daysInMonth);
      })
      .catch((error) => {
        console.error('❌ Ошибка загрузки данных:', error);
        container.innerHTML = `
          <div style="padding:40px;text-align:center;color:#d32f2f;">
            <p>Ошибка загрузки данных</p>
            <p style="font-size:12px;color:#999;">${error.message}</p>
          </div>
        `;
      });
  }

  // ==========================================
  // ПОСТРОЕНИЕ ГРАФИКА
  // ==========================================
  buildChart(canvasElement, metricsData, daysInMonth) {
    const searchParams = new URLParser();
    const container = canvasElement.parentElement;
    
    if (!metricsData || metricsData.length === 0) {
      const monthNames = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
                          'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
      container.innerHTML = `
        <div style="padding:40px;text-align:center;color:#999;">
          <p>Нет данных за ${monthNames[this.currentDate.getMonth()]} ${this.currentDate.getFullYear()}</p>
        </div>
      `;
      return;
    }

    const scheduleAttendance = new Interactive('schedule', {
      canvasElement: canvasElement,
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

    scheduleAttendance.target.setFrameSize(
      canvasElement.width - 50,
      canvasElement.height - 50 - 40
    );

    scheduleAttendance.target.addGroup('Просмотры');
    scheduleAttendance.target.addGroup('Визиты');
    scheduleAttendance.target.addGroup('Посещения');
    
    const isMainAnalytics = searchParams.getPathPart(4) === null;

    const dailyData = {};
    metricsData.forEach((data) => {
      const time = data.metrics.time * 1000;
      const date = new Date(time);
      const day = date.getDate();
      dailyData[day] = data;
    });

    const targetObject = document.querySelector('article.page[data-name]');
    const targetName = targetObject?.getAttribute('data-name');

    for (let day = 1; day <= daysInMonth; day++) {
      const data = dailyData[day];
      const dayIndex = day - 1;
      
      let urlsTotalViews = 0;
      let visits0 = [];
      let visits1 = [];
      
      if (data) {
        // Считаем просмотры
        for (let token in data.metrics.views) {
          let urls = data.metrics.views[token].urls;
          for (let url in urls) {
            if (!url || typeof url !== 'string' || url.trim() === '') continue;
            
            let isMatch = false;
            
            if (isMainAnalytics) {
              isMatch = true;
            } else if (targetName) {
              const urlLower = url.toLowerCase();
              const targetLower = targetName.toLowerCase();
              
              if (urlLower.includes(`/page/${targetLower}`) || 
                  urlLower.includes(`/page/${targetLower}?`) ||
                  urlLower.includes(`/page/${targetLower}&`) ||
                  urlLower.includes(`/entry/${targetLower}`) || 
                  urlLower.includes(`/entry/${targetLower}?`) ||
                  urlLower.includes(`/entry/${targetLower}&`)) {
                isMatch = true;
              }
            }
            
            if (isMatch) {
              urlsTotalViews += urls[url];
            }
          }
        }
        
        // Фильтруем визиты и посещения
        if (isMainAnalytics) {
          visits0 = data.metrics.visits0 || [];
          visits1 = data.metrics.visits1 || [];
        } else if (targetName) {
          let filteredVisits0 = [];
          let filteredVisits1 = [];
          
          for (let token in data.metrics.views) {
            let urls = data.metrics.views[token].urls;
            let hasMatch = false;
            
            for (let url in urls) {
              if (!url || typeof url !== 'string') continue;
              
              const urlLower = url.toLowerCase();
              const targetLower = targetName.toLowerCase();
              
              if (urlLower.includes(`/page/${targetLower}`) || 
                  urlLower.includes(`/page/${targetLower}?`) ||
                  urlLower.includes(`/entry/${targetLower}`) || 
                  urlLower.includes(`/entry/${targetLower}?`)) {
                hasMatch = true;
                break;
              }
            }
            
            if (hasMatch) {
              if (data.metrics.visits0?.includes(token)) {
                filteredVisits0.push(token);
              }
              if (data.metrics.visits1?.includes(token)) {
                filteredVisits1.push(token);
              }
            }
          }
          
          visits0 = filteredVisits0;
          visits1 = filteredVisits1;
        }
      }
      
      scheduleAttendance.target.addData(0, dayIndex, urlsTotalViews);
      scheduleAttendance.target.addData(1, dayIndex, visits0.length);
      scheduleAttendance.target.addData(2, dayIndex, visits1.length);
    }

    scheduleAttendance.target.types[0].setColor('#EE82EE');
    scheduleAttendance.target.types[1].setColor('#5B92E5');
    scheduleAttendance.target.types[2].setColor('#088567');
    
    scheduleAttendance.target.buildData();
    scheduleAttendance.target.init();
    scheduleAttendance.assembly();
  }

  // ==========================================
  // СОЗДАНИЕ CANVAS
  // ==========================================
  scheduleContainerElementCreate() {
    const canvas = document.createElement('canvas');
    canvas.style.width = '100%';
    canvas.style.height = '100%';
    return canvas;
  }
}