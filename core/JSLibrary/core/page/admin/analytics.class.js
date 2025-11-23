/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';

import {Metrics} from "../../metrics.class.js";
import {Interactive} from "../../../interactive.class.js";
import {URLParser} from "../../../urlParser.class.js";

export class PageAnalytics {
  constructor(page, params = {}) {
    this.page = page;
  }

  init() {
    const searchParams = new URLParser();
    let locales;

    fetch('/handler/locales', {method: 'GET'}).then((response) => {
      return (response.ok) ? response.json() : Promise.reject(response);
    }).then((data) => {
      locales = data.outputData.locales;
      return window.CMSCore.locales.admin.getData();
    }, (rejectionReason) => {
      this.page.showPopupNotification(rejectionReason, 0);
    }).then((localeData) => {
      const analyticApp = document.querySelector('#analytic-app');

      if (analyticApp !== null) {
        const attendanceScheduleContainerElement = analyticApp.querySelector('[role="attendance-schedule"]');
        
        const scheduleContainerElement = this.scheduleContainerElementCreate();
        const scheduleParentElement = scheduleContainerElement.parentElement;
        const scheduleParentElementWidth = scheduleParentElement.offsetWidth;

        const firstDate = new Date(), lastDate = new Date();

        attendanceScheduleContainerElement.innerHTML = '';
        attendanceScheduleContainerElement.append(scheduleContainerElement);

        scheduleContainerElement.setAttribute('width', `${scheduleParentElementWidth}px`);
        scheduleContainerElement.setAttribute('height', '400px');

        firstDate.setDate(1);
        lastDate.setMonth(firstDate.getMonth() + 1);
        lastDate.setDate(0);
        
        window.CMSCore.metrics.getDataByRangeTimestamp(firstDate.getTime(), lastDate.getTime()).then((metricsData) => {
          let scheduleAttendance = new Interactive('schedule', {
            canvasElement: scheduleContainerElement,
            type: 'linear'
          });

          scheduleAttendance.target.setFrameSize(scheduleContainerElement.width - 50, scheduleContainerElement.height - 50 - 40);
          scheduleAttendance.target.addGroup('Просмотры');

          if (searchParams.getPathPart(4) === null) {
            scheduleAttendance.target.addGroup('Визиты');
            scheduleAttendance.target.addGroup('Посещения');
          }

          metricsData.forEach((data) => {
            let urlsTotalViews = 0, visits0 = [], visits1 = [];
            let time = data.metrics.time * 1000;
            let date = new Date();

            date.setTime(time);

            for (let token in data.metrics.views) {
              let urls = data.metrics.views[token].urls;
              let urlTransfers = data.metrics.views[token].url_transfers;

              for (let url in urls) {
                if (searchParams.getPathPart(4) === null) {
                  urlsTotalViews += urls[url];
                } else {
                  let urlObject = new URL(url);
                  let urlPathParts = urlObject.pathname.split('/');

                  let targetObjectName = document.querySelector('article.page[data-name]');

                  if (targetObjectName !== null) {
                    if (urlPathParts[2] === targetObjectName.getAttribute('data-name')) {
                      urlsTotalViews += urls[url];
                    }
                  }
                }
              }

              if (searchParams.getPathPart(4) === null) {
                for (let transferIndex in urlTransfers) {
                  for (let transfer in urlTransfers[transferIndex]) {
                    let urlReferral = urlTransfers[transferIndex][transfer].referral;
                    let visitedIsNew = urlTransfers[transferIndex][transfer].is_visited_new;
                    
                    if (transfer !== urlReferral) {
                      if (visits0.indexOf(token) != -1) {
                        if ((urlTransfers[transferIndex][transfer].time * 1000) + (30 * 60 * 1000) < new Date().getTime()) {
                          visits0.push(token);
                        }
                      } else {
                        visits0.push(token);
                      }
                    }

                    if (transfer !== urlReferral) {
                      if (visits1.indexOf(token) === -1) {
                        if (visitedIsNew) {
                          visits1.push(token);
                        }
                      } 
                    }
                  }
                }
              }
            }

            scheduleAttendance.target.addData(0, date.getDate() - 1, urlsTotalViews);

            if (searchParams.getPathPart(4) === null) {
              scheduleAttendance.target.addData(1, date.getDate() - 1, visits0.length);
              scheduleAttendance.target.addData(2, date.getDate() - 1, visits1.length);
            }

            scheduleAttendance.target.types[0].setColor('#EE82EE');

            if (searchParams.getPathPart(4) === null) {
              scheduleAttendance.target.types[1].setColor('#5B92E5');
              scheduleAttendance.target.types[2].setColor('#088567');
            }
          });
    
          scheduleAttendance.target.buildData();
          scheduleAttendance.target.init();
          scheduleAttendance.assembly();
        });
      }

      if (searchParams.getPathPart(3) === 'form' && searchParams.getPathPart(4) !== null) {
        const formID = searchParams.getPathPart(3);

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
    return document.createElement('canvas');
  }
}