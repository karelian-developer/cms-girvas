/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';

import {Interactive} from "../../../interactive.class.js";
import {URLParser} from "../../../urlParser.class.js";

export class PageUsersGroups {
  constructor(page, params = {}) {
    this.page = page;
  }

  init() {
    let searchParams = new URLParser(), locales;

    fetch('/handler/locales', {method: 'GET'}).then((response) => {
      return (response.ok) ? response.json() : Promise.reject(response);
    }).then((data) => {
      locales = data.outputData.locales;
      return window.CMSCore.locales.admin.getData();
    }, (rejectionReason) => {
      this.page.showPopupNotification(rejectionReason, 0);
    }).then((localeData) => {

      let interactiveCreatePageButton = new Interactive('button');
      interactiveCreatePageButton.target.setLabel(localeData.BUTTON_NEW_USERS_GROUP_LABEL);
      interactiveCreatePageButton.target.setCallback(() => {
        window.location.href = `./userGroup`;
      });
      interactiveCreatePageButton.assembly();
    
      const interactiveContainerElement = document.querySelector('#E8548530785');
      interactiveContainerElement.append(interactiveCreatePageButton.target.element);

      const tableItems = document.querySelectorAll('[data-element="users-group"]');
      for (let tableItem of tableItems) {
        const usersGroupID = tableItem.getAttribute('data-id');
        const panelElement = tableItem.querySelector('[data-element="panel"]');
        const panelEventElements = panelElement.querySelectorAll('[data-event]');

        for (let eventElement of panelEventElements) {
          eventElement.addEventListener('click', (event) => {
            if (eventElement.getAttribute('data-event') === 'remove') {
              let interactiveModal = new Interactive('modal', {
                title: localeData.MODAL_USERS_GROUP_DELETE_TITLE,
                content: localeData.MODAL_USERS_GROUP_DELETE_DESCRIPTION
              });
              
              interactiveModal.target.addButton(localeData.BUTTON_DELETE_LABEL, () => {
                let formData = new FormData();
                formData.append('user_group_id', usersGroupID);

                let request = new Interactive('request', {
                  method: 'DELETE',
                  url: '/handler/usersGroup/' + usersGroupID + '?localeMessage=' + window.CMSCore.locales.admin.name
                });
      
                request.target.data = formData;
      
                request.target.send().then((data) => {
                  if (data.statusCode === 1) {
                    window.location.href = '/admin/usersGroups';
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
    }, (rejectionReason) => {
      this.page.showPopupNotification(rejectionReason, 0);
    });
  }
}