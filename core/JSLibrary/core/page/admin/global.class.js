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

import {Interactive} from "../../../interactive.class.js";
import {ElementButton} from "../../../interactive/form/elementButton.class.js";
import {ElementTextarea} from "../../../interactive/form/elementTextarea.class.js";
import {URLParser} from "../../../urlParser.class.js";
import {EntryComment} from "./../entry/comment.class.js";

/**
 * Глобально
 */
export class PageGlobal {
  /**
   * constructor
   * 
   * @param {*} params 
   */
  constructor(page, params = {}) {
    this.page = page;

    this.buttons = {checkVersion: null, toSite: null};
  }

  /**
   * Инициализация
   */
  init() {
    let searchParams = new URLParser(), locales;
    let globalButtonsContainerElement = document.querySelector('#SYSTEM_E3724126421');
    
    let navigationBurgerElement = document.querySelector('[role="mainNavigationBurger"]');
    if (navigationBurgerElement != null) {
      navigationBurgerElement.addEventListener('click', (event) => {
        navigationBurgerElement.classList.toggle('burger_is-active');
      });
    }

    // Подгрузка локализаций
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
      let mainNavigationItemExitElement = document.querySelector('[role="mainNavigationExit"]');
      if (mainNavigationItemExitElement != null) {
        mainNavigationItemExitElement.addEventListener('click', (event) => {
          event.preventDefault();

          let formData = new FormData();

          let request = new Interactive('request', {
            method: 'POST',
            url: '/handler/client/session-end?level=2&localeMessage=' + window.CMSCore.locales.admin.name
          });

          request.target.data = formData;

          request.target.send().then((data) => {
            if (data.statusCode === 1 && data.outputData.hasOwnProperty('result')) {
              let result = data.outputData.result;

              if (result === true) {
                window.location.reload();
              }
            }
          });
        });
      }

      if (globalButtonsContainerElement != null) {
        // Кнопка "Сайт производителя"
        this.buttons.siteDeveloper = new Interactive('button');
        this.buttons.siteDeveloper.target.setLabel(localeData.BUTTON_SITE_DEVELOPER);
        this.buttons.siteDeveloper.target.setCallback((event) => {
          event.preventDefault();
          window.open('https://www.garbalo.com', '_blank');
        });

        // Кнопка "Проверить обновления"
        this.buttons.checkVersion = new Interactive('button');
        this.buttons.checkVersion.target.setLabel(localeData.BUTTON_CHECK_UPDATES);
        this.buttons.checkVersion.target.setCallback((event) => {
          event.preventDefault();

          window.CMSCore.getCMSVersion().then((data) => {
            const request = new Interactive('request', {
              method: 'GET',
              url: `https://repository.cms-girvas.ru/system-checker?currentVersion=${data}`
            });

            request.target.showingNotification = false;

            request.target.send().then((checkerData) => {
              console.log(checkerData);
              if (checkerData.outputData.hasOwnProperty('needToUpdate')) {
                const needToUpdate = checkerData.outputData.needToUpdate;
                const lastVersionTitle = checkerData.outputData.title;
                const lastVersionPostURL = checkerData.outputData.postURL;
  
                const interactiveNotificationLoading = new Interactive('notification');
                interactiveNotificationLoading.target.isPopup = true;

                if (needToUpdate) {
                  interactiveNotificationLoading.target.setStatusCode(1);
                  interactiveNotificationLoading.target.setContent(`${localeData.UPDATE_CHECKER_NEW_VERSION} [${data} => ${lastVersionTitle}]: <a href="${lastVersionPostURL}" target="_blank">${lastVersionPostURL}</a>`);
                } else {
                  interactiveNotificationLoading.target.setStatusCode(-1);
                  interactiveNotificationLoading.target.setContent(localeData.UPDATE_CHECKER_CURRENT_VERSION);
                }

                interactiveNotificationLoading.target.assembly();
                interactiveNotificationLoading.target.show();
              }
            });
          });
        });

        // Кнопка "Перейти на сайт"
        this.buttons.toSite = new Interactive('button');
        this.buttons.toSite.target.setLabel(localeData.BUTTON_GO_TO_SITE);
        this.buttons.toSite.target.setCallback((event) => {
          event.preventDefault();

          window.open('/', '_blank');
        });

        this.buttons.siteDeveloper.assembly();
        this.buttons.checkVersion.assembly();
        this.buttons.toSite.assembly();

        globalButtonsContainerElement.append(this.buttons.siteDeveloper.target.element);
        globalButtonsContainerElement.append(this.buttons.checkVersion.target.element);
        globalButtonsContainerElement.append(this.buttons.toSite.target.element);
      }
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