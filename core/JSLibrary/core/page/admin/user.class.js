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
import {URLParser} from "../../../urlParser.class.js";

export class PageUser {
  constructor(page, params = {}) {
    this.page = page;

    this.buttons = {save: null, delete: null, block: null, unblock: null, export: null, anonymize: null};
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
      this.buttons.delete = new Interactive('button');
      this.buttons.block = new Interactive('button');
      this.buttons.unblock = new Interactive('button');
      this.buttons.export = new Interactive('button');
      this.buttons.anonymize = new Interactive('button');

      this.buttons.save.target.setLabel(localeData.BUTTON_SAVE_LABEL);
      this.buttons.delete.target.setLabel(localeData.BUTTON_DELETE_LABEL);
      this.buttons.block.target.setLabel(localeData.BUTTON_BAN_LABEL);
      this.buttons.unblock.target.setLabel(localeData.BUTTON_UNBAN_LABEL);
      this.buttons.export.target.setLabel(localeData.PAGE_USER_BUTTON_EXPORT_SUBJECT_DATA);
      this.buttons.anonymize.target.setLabel(localeData.PAGE_USER_BUTTON_ANONYMIZE);
      
      this.buttons.save.target.setStyle('green');
      this.buttons.delete.target.setStyle('red');
      this.buttons.block.target.setStyle('red');
      this.buttons.unblock.target.setStyle('green');
      this.buttons.export.target.setStyle('default');
      this.buttons.anonymize.target.setStyle('red');

      this.buttons.anonymize.target.setCallback((event) => {
        event.preventDefault();
        this.handleAnonymize(searchParams.getPathPart(3), userData, localeData);
      });

      this.buttons.export.target.setCallback((event) => {
        event.preventDefault();
        this.handleExport(searchParams.getPathPart(3), userData, localeData);
      });

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

      this.buttons.anonymize.assembly();
      this.buttons.export.assembly();
      this.buttons.save.assembly();
      this.buttons.delete.assembly();
      this.buttons.block.assembly();
      this.buttons.unblock.assembly();

      if (userData.isAnonymized) {
        this.buttons.anonymize.target.element.style.display = 'none';
      }
  
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
        if (searchParams.getPathPart(3) !== null) {
          interactiveFormPanelContainer.append(this.buttons.export.target.element);
          
          if (!userData.isAnonymized) {
            interactiveFormPanelContainer.append(this.buttons.anonymize.target.element);
          }
        }

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

  handleExport(userID, userData, localeData) {
    const modal = new Interactive('modal', {
      title: localeData.MODAL_SUBJECT_DATA_EXPORT_TITLE,
      content: localeData.MODAL_SUBJECT_DATA_EXPORT_DESCRIPTION.replace('{LOGIN}', userData.login || '')
    });

    modal.target.addButton(localeData.BUTTON_SUBJECT_DATA_EXPORT_SUBMIT, async () => {
      const formData = new FormData();
      formData.append('_grv_' + Math.random().toString(36).slice(2), Math.random().toString(36).slice(2));

      const headers = {};
      if (window.CMSCore && window.CMSCore.client && window.CMSCore.client.getCSRFToken() !== '') {
        headers['X-CSRF-Token'] = window.CMSCore.client.getCSRFToken();
      }

      try {
        const response = await fetch(
          '/handler/user/export/' + userID + '?localeMessage=' + window.CMSCore.locales.admin.name,
          { method: 'POST', body: formData, headers: headers, credentials: 'same-origin' }
        );

        if (!response.ok) {
          try {
            const errorData = await response.json();
            this.page.showPopupNotification(errorData.message || 'Ошибка экспорта', 0);
          } catch (e) {
            this.page.showPopupNotification('Ошибка экспорта: ' + response.status, 0);
          }
          modal.target.close();
          return;
        }

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'subject_' + (userData.login || userID) + '_' + new Date().toISOString().slice(0, 19).replace(/[:T]/g, '-') + '.zip';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
      } catch (error) {
        this.page.showPopupNotification('Ошибка сети: ' + error, 0);
      }
      modal.target.close();
    });

    modal.target.addButton(localeData.BUTTON_CANCEL_LABEL, () => modal.target.close());

    modal.assembly();
    document.body.appendChild(modal.target.element);
    modal.target.show();
  }

  /**
   * Обезличить ПДн пользователя
   *
   * @param {string} userID
   * @param {object} userData
   * @param {object} localeData
   */
  handleAnonymize(userID, userData, localeData) {
    const loginInput = document.querySelector('[data-element="input-login"]');
    const login = loginInput ? loginInput.value : (userData.login || '');

    const modal = new Interactive('modal', {
      title: localeData.MODAL_ANONYMIZE_TITLE || 'Обезличивание ПДн',
      content: (localeData.MODAL_ANONYMIZE_DESCRIPTION || 'Вы собираетесь обезличить персональные данные пользователя «{LOGIN}». Действие необратимо. Продолжить?').replace('{LOGIN}', login)
    });

    // Поле причины
    const reasonTextarea = document.createElement('textarea');
    reasonTextarea.classList.add('form__textarea');
    reasonTextarea.setAttribute('placeholder', localeData.PAGE_USER_ANONYMIZE_REASON_PLACEHOLDER || 'Причина (например, истечение срока хранения)');
    modal.target.content = reasonTextarea;

    modal.target.addButton(localeData.BUTTON_ANONYMIZE_SUBMIT || 'Обезличить', async () => {
      const formData = new FormData();
      formData.append('user_id', userID);
      formData.append('reason', reasonTextarea.value.trim() || 'retention_expired');

      const match = document.cookie.match(/_grv_csrf=([^;]+)/);
      const freshToken = match ? decodeURIComponent(match[1]) : '';

      const headers = {};
      if (freshToken !== '') {
        headers['X-CSRF-Token'] = freshToken;
      }

      try {
        const response = await fetch(
          '/handler/user/anonymize?localeMessage=' + window.CMSCore.locales.admin.name,
          { method: 'POST', body: formData, headers: headers, credentials: 'same-origin' }
        );

        const data = await response.json();

        if (data.statusCode === 1) {
          this.page.showPopupNotification(data.message, 1);
          window.location.reload();
        } else {
          this.page.showPopupNotification(data.message, 0);
        }
      } catch (error) {
        this.page.showPopupNotification('Ошибка сети: ' + error, 0);
      }

      modal.target.close();
    });

    modal.target.addButton(localeData.BUTTON_CANCEL_LABEL, () => modal.target.close());

    modal.assembly();
    document.body.appendChild(modal.target.element);
    modal.target.show();
  }
}