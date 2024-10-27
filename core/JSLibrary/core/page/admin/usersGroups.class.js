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
      let interactiveNotification = new Interactive('notification');
      interactiveNotification.target.isPopup = true;
      interactiveNotification.target.setStatusCode(0);
      interactiveNotification.target.setContent(rejectionReason);
      interactiveNotification.target.assembly();

      interactiveNotification.target.show();
    }).then((localeData) => {

      let interactiveCreatePageButton = new Interactive('button');
      interactiveCreatePageButton.target.setLabel(localeData.BUTTON_NEW_USERS_GROUP_LABEL);
      interactiveCreatePageButton.target.setCallback(() => {
        window.location.href = `./userGroup`;
      });
      interactiveCreatePageButton.assembly();
    
      let interactiveContainerElement = document.querySelector('#E8548530785');
      interactiveContainerElement.append(interactiveCreatePageButton.target.element);

      let tableItemsUsersGroups = document.querySelectorAll('.table-users-groups__item');

      for (let tableItemUserGroup of tableItemsUsersGroups) {
        let userGroupID = tableItemUserGroup.getAttribute('data-user-group-id');
        let buttons = tableItemUserGroup.querySelectorAll('button[role]');

        for (let button of buttons) {
          button.addEventListener('click', (event) => {
            if (button.getAttribute('role') == 'user-group-edit') {
              window.location.href = `./userGroup/${userGroupID}`;
            }

            if (button.getAttribute('role') == 'user-group-remove') {
              let interactiveModal = new Interactive('modal', {
                title: localeData.MODAL_USERS_GROUP_DELETE_TITLE,
                content: localeData.MODAL_USERS_GROUP_DELETE_DESCRIPTION
              });
              
              interactiveModal.target.addButton(localeData.BUTTON_DELETE_LABEL, () => {
                let formData = new FormData();
                formData.append('user_group_id', userGroupID);

                let request = new Interactive('request', {
                  method: 'DELETE',
                  url: '/handler/usersGroup/' + userGroupID + '?localeMessage=' + window.CMSCore.locales.admin.name
                });
      
                request.target.data = formData;
      
                request.target.send().then((data) => {
                  if (data.statusCode == 1) {
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
      let interactiveNotification = new Interactive('notification');
      interactiveNotification.target.isPopup = true;
      interactiveNotification.target.setStatusCode(0);
      interactiveNotification.target.setContent(rejectionReason);
      interactiveNotification.target.assembly();

      interactiveNotification.target.show();
    });
  }
}