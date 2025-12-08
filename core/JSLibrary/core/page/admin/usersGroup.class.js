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
import {Utils} from "../../../utils.class.js";

export class PageUsersGroup {
  constructor(page, params = {}) {
    this.page = page;
    
    this.buttons = {save: null, delete: null};
  }

  init() {
    let searchParams = new URLParser(), locales;
    const elementForm = document.querySelector('[data-element="main-form"]');

    fetch('/handler/locales', {method: 'GET'}).then((response) => {
      return (response.ok) ? response.json() : Promise.reject(response);
    }).then((data) => {
      locales = data.outputData.locales;
      return window.CMSCore.locales.admin.getData();
    }, (rejectionReason) => {
      this.page.showPopupNotification(rejectionReason, 0);
    }).then((localeData) => {
      const interactiveChoicesLocales = new Interactive('choices');

      const urlInputElement = document.querySelector('[data-element="input-name"]');
      const usersGroupTitleInputElement = document.querySelector('[data-element="input-title"]');

      locales.forEach((locale, localeIndex) => {
        let localeTitle = locale.title;
        let localeIconURL = locale.iconURL;
        let localeName = locale.name;

        let localeIconImageElement = document.createElement('img');
        localeIconImageElement.setAttribute('src', localeIconURL);
        localeIconImageElement.setAttribute('alt', localeTitle);

        let localeLabelElement = document.createElement('span');
        localeLabelElement.innerText = localeTitle;

        let localeTemplate = document.createElement('template');
        localeTemplate.innerHTML += localeIconImageElement.outerHTML;
        localeTemplate.innerHTML += localeLabelElement.outerHTML;

        interactiveChoicesLocales.target.addItem(localeTemplate.innerHTML, localeName);

        if (locale.name === window.CMSCore.locales.admin.name) {
          interactiveChoicesLocales.target.setItemSelectedIndex(localeIndex);
        }

        if (locale.name === window.CMSCore.locales.admin.name) {
          usersGroupTitleInputElement.setAttribute('name', 'user_group_title_' + locale.iso639_2);

          if (searchParams.getPathPart(3) != null) {
            let request = new Interactive('request', {
              method: 'GET',
              url: '/handler/usersGroup/' + searchParams.getPathPart(3) + '?locale=' + locale.name + '&localeMessage=' + window.CMSCore.locales.admin.name
            });

            request.target.showingNotification = false;

            request.target.send().then((data) => {
              if (data.statusCode === 1 && data.outputData.hasOwnProperty('usersGroup')) {
                let usersGroupData = data.outputData.usersGroup;
                usersGroupTitleInputElement.value = usersGroupData.title;
              }
            });
          }
        }
      });

      interactiveChoicesLocales.assembly();

      let interactiveContainerElement = document.querySelector('[data-element="header-interactive"]');
      interactiveContainerElement.append(interactiveChoicesLocales.target.element);

      urlInputElement.addEventListener('input', (event) => {
        /** @var {String} */
        let inputValue = event.target.value;

        /** @var {Utils} */
        let utils = new Utils();
        /** @var {UString} */
        let uString = utils.createString(inputValue);
        uString.source = uString.translitToEN(true);
        uString.source = uString.source.toLowerCase();
        uString.source = uString.source.replace(/[^a-z0-9\-]/, '');

        event.target.value = uString.source;
      });

      let interactiveChoicesSelectElement = interactiveContainerElement.querySelector('select');
      interactiveChoicesSelectElement.addEventListener('change', (event) => {
        locales.forEach((locale, localeIndex) => {
          if (locale.name === event.target.value) {
            usersGroupTitleInputElement.setAttribute('name', 'user_group_title_' + locale.iso639_2);

            if (searchParams.getPathPart(3) != null) {
              let request = new Interactive('request', {
                method: 'GET',
                url: '/handler/usersGroup/' + searchParams.getPathPart(3) + '?locale=' + event.target.value + '&localeMessage=' + window.CMSCore.locales.admin.name
              });
  
              request.target.showingNotification = false;
  
              request.target.send().then((data) => {
                if (data.statusCode === 1 && data.outputData.hasOwnProperty('usersGroup')) {
                  let usersGroupData = data.outputData.usersGroup;
                  usersGroupTitleInputElement.value = usersGroupData.title;
                }
              });
            }
          }
        });
      });
      
      this.buttons.save = new Interactive('button');
      this.buttons.save.target.setLabel(localeData.BUTTON_SAVE_LABEL);
      this.buttons.save.target.setCallback((event) => {
        event.preventDefault();
        
        let form = new Interactive('form');
        form.target.replaceElement(elementForm);

        if (form.target.checkRequiredFields()) {
          let formData = new FormData(elementForm);

          let request = new Interactive('request', {
            method: (searchParams.getPathPart(3) === null) ? 'PUT' : 'PATCH',
            url: '/handler/usersGroup?localeMessage=' + window.CMSCore.locales.admin.name
          });

          request.target.data = formData;

          request.target.send().then((data) => {
            if (data.statusCode === 1 && searchParams.getPathPart(3) === null) {
              let usersGroupData = data.outputData.usersGroup;
              window.location.href = '/admin/userGroup/' + usersGroupData.id;
            }
          });
        } else {
          this.page.showPopupNotification(localeData.FORM_REQUIRED_FIELDS_IS_EMPTY, 0);
        }
      });
      this.buttons.save.assembly();

      this.buttons.delete = new Interactive('button');
      this.buttons.delete.target.setLabel(localeData.BUTTON_DELETE_LABEL);
      this.buttons.delete.target.setCallback((event) => {
        event.preventDefault();

        let interactiveModal = new Interactive('modal', {
          title: localeData.MODAL_USERS_GROUP_DELETE_TITLE,
          content: localeData.MODAL_USERS_GROUP_DELETE_DESCRIPTION
        });
        
        interactiveModal.target.addButton(localeData.BUTTON_DELETE_LABEL, () => {
          let formData = new FormData();
          formData.append('user_group_id', searchParams.getPathPart(3));

          let request = new Interactive('request', {
            method: 'DELETE',
            url: '/handler/usersGroup/' + searchParams.getPathPart(3) + '?localeMessage=' + window.CMSCore.locales.admin.name
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
      });
      this.buttons.delete.assembly();

      if (searchParams.getPathPart(3) === null) {
        this.buttons.delete.target.element.style.display = 'none';
        this.buttons.save.target.element.style.display = 'flex';
      } else {
        this.buttons.delete.target.element.style.display = 'flex';
        this.buttons.save.target.element.style.display = 'flex';
      }
  
      let interactiveFormPanelContainer = document.querySelector('[data-element="panel"]');
      interactiveFormPanelContainer.append(this.buttons.delete.target.element);
      interactiveFormPanelContainer.append(this.buttons.save.target.element);
    }, (rejectionReason) => {
      this.page.showPopupNotification(rejectionReason, 0);
    });
  }
}