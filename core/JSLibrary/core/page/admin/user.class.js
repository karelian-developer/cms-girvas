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

export class PageUser {
  constructor(page, params = {}) {
    this.page = page;

    this.buttons = {save: null, delete: null, block: null, unblock: null};
  }

  init() {
    let searchParams = new URLParser(), locales, userData, usersGroups;
    let elementForm = document.querySelector('[data-element="main-form"]');
    
    const interactiveChoicesUsersGroups = new Interactive('choices');

    fetch('/handler/locales', {method: 'GET'}).then((response) => {
      return (response.ok) ? response.json() : Promise.reject(response);
    }).then((data) => {
      locales = data.outputData.locales;
      return fetch('/handler/usersGroups' + '?locale=' + window.CMSCore.locales.admin.name + '&localeMessage=' + window.CMSCore.locales.admin.name, {method: 'GET'});
    }, (rejectionReason) => {
      this.page.showPopupNotification(rejectionReason, 0);
    }).then((response) => {
      return (response.ok) ? response.json() : Promise.reject(response);
    }).then((data) => {
      usersGroups = data.outputData.usersGroups;
      usersGroups.forEach((usersGroup) => {
        interactiveChoicesUsersGroups.target.addItem(usersGroup.title, usersGroup.id);
      });

      interactiveChoicesUsersGroups.target.setName('user_group_id');

      return fetch('/handler/user/' + searchParams.getPathPart(3) + '?localeMessage=' + window.CMSCore.locales.admin.name, {method: 'GET'});
    }, (rejectionReason) => {
      this.page.showPopupNotification(rejectionReason, 0);
    }).then((response) => {
      return (response.ok) ? response.json() : Promise.reject(response);
    }).then((data) => {
      userData = data.outputData.user;

      return window.CMSCore.locales.admin.getData();
    }, (rejectionReason) => {
      this.page.showPopupNotification(rejectionReason, 0);
    }).then((localeData) => {
      const userPasswordInput = document.querySelector('[data-element="input-password"]');
      const userPasswordRepeatInput = document.querySelector('[data-element="input-password-repeat"]');

      if (userPasswordInput !== null) {
        userPasswordInput.addEventListener('change', (event) => {
          event.preventDefault();

          if (event.target.value !== '') {
            userPasswordInput.setAttribute('required', '');
            userPasswordRepeatInput.setAttribute('required', '');
          } else {
            if (userPasswordRepeatInput.value === '') {
              userPasswordInput.removeAttribute('required');
              userPasswordRepeatInput.removeAttribute('required');
            }
          }
        });
      }

      if (userPasswordRepeatInput !== null) {
        userPasswordRepeatInput.addEventListener('change', (event) => {
          event.preventDefault();

          if (event.target.value !== '') {
            userPasswordInput.setAttribute('required', '');
            userPasswordRepeatInput.setAttribute('required', '');
          } else {
            if (userPasswordRepeatInput.value === '') {
              userPasswordInput.removeAttribute('required');
              userPasswordRepeatInput.removeAttribute('required');
            }
          }
        });
      }

      if (searchParams.getPathPart(3) !== null) {
        usersGroups.forEach((usersGroup, usersGroupIndex) => {
          if (usersGroup.id === userData.groupID) {
            interactiveChoicesUsersGroups.target.setItemSelectedIndex(usersGroupIndex);
          }
        });
      }

      this.buttons.save = new Interactive('button');
      this.buttons.save.target.setLabel(localeData.BUTTON_SAVE_LABEL);
      this.buttons.save.target.setCallback((event) => {
        event.preventDefault();
        
        let form = new Interactive('form');
        form.target.replaceElement(elementForm);

        if (form.target.checkRequiredFields()) {
          let request = new Interactive('request', {
            method: (searchParams.getPathPart(3) === null) ? 'PUT' : 'PATCH',
            url: '/handler/user?localeMessage=' + window.CMSCore.locales.admin.name,
            data: elementForm
          });

          request.target.send().then((data) => {
            if (data.statusCode === 1 && searchParams.getPathPart(3) === null) {
              if (data.outputData.hasOwnProperty('user')) {
                let userData = data.outputData.user;
                window.location.href = '/admin/user/' + userData.id;
              }
            }
          });
        } else {
          this.page.showPopupNotification(localeData.FORM_REQUIRED_FIELDS_IS_EMPTY, 0);
        }
      });
      this.buttons.save.assembly();
  
      this.buttons.block = new Interactive('button');
      this.buttons.block.target.setLabel(localeData.BUTTON_BAN_LABEL);
      this.buttons.block.target.setCallback((event) => {
        event.preventDefault();
        
        let formData = new FormData();
        formData.append('user_id', searchParams.getPathPart(3));
        formData.append('user_is_block', 1);
  
        let request = new Interactive('request', {
          method: 'PATCH',
          url: '/handler/user?localeMessage=' + window.CMSCore.locales.admin.name
        });

        request.target.data = formData;

        request.target.send().then((data) => {
          if (data.statusCode === 1) {
            this.buttons.unblock.target.element.style.display = 'flex';
            this.buttons.block.target.element.style.display = 'none';
          }
        });
      });
      this.buttons.block.assembly();
  
      this.buttons.unblock = new Interactive('button');
      this.buttons.unblock.target.setLabel(localeData.BUTTON_UNBAN_LABEL);
      this.buttons.unblock.target.setCallback((event) => {
        event.preventDefault();
        
        let formData = new FormData();
        formData.append('user_id', searchParams.getPathPart(3));
        formData.append('user_is_block', 0);
  
        let request = new Interactive('request', {
          method: 'PATCH',
          url: '/handler/user?localeMessage=' + window.CMSCore.locales.admin.name
        });

        request.target.data = formData;

        request.target.send().then((data) => {
          if (data.statusCode === 1) {
            this.buttons.unblock.target.element.style.display = 'none';
            this.buttons.block.target.element.style.display = 'flex';
          }
        });
      });
      this.buttons.unblock.assembly();
  
      this.buttons.delete = new Interactive('button');
      this.buttons.delete.target.setLabel(localeData.BUTTON_DELETE_LABEL);
      this.buttons.delete.target.setCallback((event) => {
        event.preventDefault();
  
        let interactiveModal = new Interactive('modal', {
          title: localeData.MODAL_USER_DELETE_TITLE,
          content: localeData.MODAL_USER_DELETE_DESCRIPTION
        });
        
        interactiveModal.target.addButton(localeData.BUTTON_DELETE_LABEL, () => {
          let formData = new FormData();
          formData.append('user_id', searchParams.getPathPart(3));
  
          let request = new Interactive('request', {
            method: 'DELETE',
            url: '/handler/user?localeMessage=' + window.CMSCore.locales.admin.name
          });

          request.target.data = formData;

          request.target.send().then((data) => {
            if (data.statusCode === 1) {
              window.location.href = '/admin/users';
            }
          });
        });
  
        interactiveModal.target.addButton(localeData.BUTTON_CANCEL_LABEL, () => {
          interactiveModal.target.close();
        });
  
        interactiveModal.assembly();
        document.body.appendChild(interactiveModal.target.element);
        interactiveModal.target.show();
      });

      this.buttons.delete.assembly();
  
      if (searchParams.getPathPart(3) === null) {
        this.buttons.unblock.target.element.style.display = 'none';
        this.buttons.block.target.element.style.display = 'none';
        this.buttons.delete.target.element.style.display = 'none';
        this.buttons.save.target.element.style.display = 'flex';
      } else {
        this.buttons.unblock.target.element.style.display = (userData.isBlocked) ? 'flex' : 'none';
        this.buttons.block.target.element.style.display = (userData.isBlocked) ? 'none' : 'flex';
        this.buttons.delete.target.element.style.display = 'flex';
        this.buttons.save.target.element.style.display = 'flex';
      }

      interactiveChoicesUsersGroups.assembly();
  
      const interactiveFormPanelContainer = document.querySelector('[data-element="panel"]');
      const interactiveChoicesUsersGroupsContainer = document.querySelector('[data-element="choice"][data-choice="group"]');

      if (interactiveFormPanelContainer !== null) {
        interactiveFormPanelContainer.append(this.buttons.delete.target.element);
        interactiveFormPanelContainer.append(this.buttons.unblock.target.element);
        interactiveFormPanelContainer.append(this.buttons.block.target.element);
        interactiveFormPanelContainer.append(this.buttons.save.target.element);
      }

      if (interactiveChoicesUsersGroupsContainer !== null) {
        interactiveChoicesUsersGroupsContainer.append(interactiveChoicesUsersGroups.target.element);
      }
    }, (rejectionReason) => {
      this.page.showPopupNotification(rejectionReason, 0);
    });
  }
}