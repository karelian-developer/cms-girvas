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
    let searchParams = new URLParser();
    let locales;

    fetch('/handler/locales', {method: 'GET'}).then((response) => {
      return (response.ok) ? response.json() : Promise.reject(response);
    }).then((data) => {
      locales = data.outputData.locales;
      return window.CMSCore.locales.admin.getData();
    }, (rejectionReason) => {
      let interactiveNotification = new Interactive('notification');
      interactiveNotification.target.isPopup = true;
      interactiveNotification.target.setStatusCode(0);
      interactiveNotification.target.setContent(rejectionReason);
      interactiveNotification.target.assembly();

      interactiveNotification.target.show();
    }).then((localeData) => {
      let analyticApp = document.querySelector('#analytic-app');
      let attendanceScheduleContainerElement = analyticApp.querySelector('[role="attendance-schedule"]');
      attendanceScheduleContainerElement.innerHTML = '';

      let scheduleContainerElement = this.scheduleContainerElementCreate();
      attendanceScheduleContainerElement.append(scheduleContainerElement);

      let scheduleParentElement = scheduleContainerElement.parentElement;
      let scheduleParentElementWidth = scheduleParentElement.offsetWidth;

      scheduleContainerElement.setAttribute('width', `${scheduleParentElementWidth}px`);
      scheduleContainerElement.setAttribute('height', '400px');

      let firstDate = new Date(), lastDate = new Date();

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

                if (targetObjectName != null) {
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
                  console.log(visitedIsNew);

                  if (transfer != urlReferral) {
                    if (visits0.indexOf(token) != -1) {
                      if ((urlTransfers[transferIndex][transfer].time * 1000) + (30 * 60 * 1000) < new Date().getTime()) {
                        visits0.push(token);
                      }
                    } else {
                      visits0.push(token);
                    }
                  }

                  if (transfer != urlReferral) {
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
    });
  }

  scheduleContainerElementCreate() {
    return document.createElement('canvas');
  }
}