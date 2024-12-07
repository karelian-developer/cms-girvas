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

export class PageSettings {
  static buttonIcons = {
    trash: '<svg version="1.1" class="button__icon" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve"><rect x="13.9" y="16.1" width="37.3" height="47.9"></rect><path d="M41.4,10l1-7.3L24.7,0.3l-1,7.3L10.2,5.7l-1,7.3l44.6,6.2l1-7.3L41.4,10z M25.6,7.8l0.7-5l13.8,1.9l-0.7,5L25.6,7.8z"></path></svg>',
  };

  constructor(page, params = {}) {
    this.page = page;

    this.buttons = {save: null};
  }

  init() {
    let searchParams = new URLParser(), locales;
    let elementForm = document.querySelector('.form_settings');
    let interactiveLocaleChoices = new Interactive('choices');
    
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
      let checkboxesInputsElements = document.querySelectorAll('[type="checkbox"]');
      if (checkboxesInputsElements.length > 0) {
        checkboxesInputsElements.forEach((element, elementIndex) => {
          let logicBlockTargetElement;

          if (element.hasAttribute('data-logic-block')) {
            let logicBlock = element.getAttribute('data-logic-block');
            logicBlockTargetElement = document.getElementById(logicBlock);
            
            if (!element.checked) {
              logicBlockTargetElement.setAttribute('disabled', 'disabled');
            }
          }

          let statusBlock = element.getAttribute('data-status-block');
          let statusBlockTargetElement = document.getElementById(statusBlock);
          element.addEventListener('change', (event) => {
            statusBlockTargetElement.value = (!element.checked) ? 'off' : 'on';

            if (element.hasAttribute('data-logic-block')) {
              if (logicBlockTargetElement.hasAttribute('disabled')) {
                logicBlockTargetElement.removeAttribute('disabled');
              } else {
                logicBlockTargetElement.setAttribute('disabled', 'disabled');
              }
            }
          });
        });
      }

      if (searchParams.getPathPart(3) == null || searchParams.getPathPart(3) == 'base') {
        let timezones, charsets, timezoneSelected, charsetSelected;
        let interactiveChoicesSettingsTimezone = new Interactive('choices');
        let interactiveChoicesSettingsCharset = new Interactive('choices');
        let interactiveChoicesSettingsBaseLocale = new Interactive('choices');
        let interactiveChoicesSettingsAdminLocale = new Interactive('choices');

        fetch('/handler/timezones', {
          method: 'GET'
        }).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data) => {
          timezones = data.outputData.timezones;
          return fetch('/handler/timezone?localeMessage=' + window.CMSCore.locales.admin.name, {method: 'GET'});
        }).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data) => {
          timezoneSelected = data.outputData.timezone;

          timezones.forEach((timezone, timezoneIndex) => {
            let timezoneElement = document.createElement('span');
            timezoneElement.innerText = `${timezone.name} (${timezone.utc})`;
            let timezoneElementTemplate = document.createElement('template');
            timezoneElementTemplate.innerHTML += timezoneElement.outerHTML;

            interactiveChoicesSettingsTimezone.target.addItem(timezoneElementTemplate.innerHTML, timezone.name);
          });

          timezones.forEach((timezone, timezoneIndex) => {
            if (timezone.name === timezoneSelected.name) {
              interactiveChoicesSettingsTimezone.target.setItemSelectedIndex(timezoneIndex);
            }
          });

          interactiveChoicesSettingsTimezone.target.setName('setting_base_timezone');
          interactiveChoicesSettingsTimezone.assembly();

          return fetch('/handler/charsets?localeMessage=' + window.CMSCore.locales.admin.name, {method: 'GET'});
        }).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data) => {
          charsets = data.outputData.charsets;
          return fetch('/handler/charset?localeMessage=' + window.CMSCore.locales.admin.name, {method: 'GET'});
        }).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data) => {
          charsetSelected = data.outputData.charset;

          charsets.forEach((charset, charsetIndex) => {
            interactiveChoicesSettingsCharset.target.addItem(charset, charset);
          });

          charsets.forEach((charset, charsetIndex) => {
            if (charset === charsetSelected) {
              interactiveChoicesSettingsCharset.target.setItemSelectedIndex(charsetIndex);
            }
          });

          interactiveChoicesSettingsCharset.target.setName('setting_base_site_charset');
          interactiveChoicesSettingsCharset.assembly();

          return fetch('/handler/ew-status?localeMessage=' + window.CMSCore.locales.admin.name, {method: 'GET'});
        }).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data) => {
          let engineeringWorksStatus = data.outputData.status;
          
          let engineeringWorksHiddenInput = document.getElementById('I1474308110');
          engineeringWorksHiddenInput.value = engineeringWorksStatus;

          locales.forEach((locale, localeIndex) => {
            let localeTitle = locale.title;
            let localeIconURL = locale.iconURL;
            let localeName = locale.name;
            let localeISO639_2 = locale.iso639_2;

            let localeIconImageElement = document.createElement('img');
            localeIconImageElement.setAttribute('src', localeIconURL);
            localeIconImageElement.setAttribute('alt', localeTitle);

            let localeLabelElement = document.createElement('span');
            localeLabelElement.innerText = localeTitle;

            let localeTemplate = document.createElement('template');
            localeTemplate.innerHTML += localeIconImageElement.outerHTML;
            localeTemplate.innerHTML += localeLabelElement.outerHTML;

            interactiveChoicesSettingsBaseLocale.target.addItem(localeTemplate.innerHTML, localeName);
          });

          locales.forEach((locale, localeIndex) => {
            if (locale.name === window.CMSCore.locales.base.name) {
              interactiveChoicesSettingsBaseLocale.target.setItemSelectedIndex(localeIndex);
            }
          });

          interactiveChoicesSettingsBaseLocale.target.setName('setting_base_locale')
          interactiveChoicesSettingsBaseLocale.assembly();

          locales.forEach((locale, localeIndex) => {
            let localeTitle = locale.title;
            let localeIconURL = locale.iconURL;
            let localeName = locale.name;
            let localeISO639_2 = locale.iso639_2;

            let localeIconImageElement = document.createElement('img');
            localeIconImageElement.setAttribute('src', localeIconURL);
            localeIconImageElement.setAttribute('alt', localeTitle);

            let localeLabelElement = document.createElement('span');
            localeLabelElement.innerText = localeTitle;

            let localeTemplate = document.createElement('template');
            localeTemplate.innerHTML += localeIconImageElement.outerHTML;
            localeTemplate.innerHTML += localeLabelElement.outerHTML;

            interactiveChoicesSettingsAdminLocale.target.addItem(localeTemplate.innerHTML, localeName);
          });

          locales.forEach((locale, localeIndex) => {
            if (locale.name === window.CMSCore.locales.admin.name) {
              interactiveChoicesSettingsAdminLocale.target.setItemSelectedIndex(localeIndex);
            }
          });

          interactiveChoicesSettingsAdminLocale.target.setName('setting_base_admin_locale');
          interactiveChoicesSettingsAdminLocale.assembly();

          document.querySelector('#TC6474389679').prepend(interactiveChoicesSettingsTimezone.target.element);
          document.querySelector('#TC6474389682').append(interactiveChoicesSettingsCharset.target.element);
          document.querySelector('#TC6474389680').append(interactiveChoicesSettingsBaseLocale.target.element);
          document.querySelector('#TC6474389681').append(interactiveChoicesSettingsAdminLocale.target.element);
        });
      }

      if (searchParams.getPathPart(3) == 'users') {
        let buttons = {addField: null};

        let interactiveChoicesFieldsLocale = new Interactive('choices');

        interactiveChoicesFieldsLocale.target.setName('_users_additional_fields_locale');

        let tableAdditionalFields = document.querySelector('[role="profile-table-additional-fields"]');
        let tableAdditionalFieldsButtonContainer = document.querySelector('[role="profile-add-field"]');
        
        buttons.addField = new Interactive('button');
        buttons.addField.target.setLabel(localeData.BUTTON_NEW_FIELD_LABEL);
        buttons.addField.target.setCallback((event) => {
          event.preventDefault();

          this.addUserAdditionalField(localeData, tableAdditionalFieldsButtonContainer);
        });
        buttons.addField.assembly();

        locales.forEach((locale, localeIndex) => {
          let localeIconImageElement = document.createElement('img');
          let localeLabelElement = document.createElement('span');
          let localeTemplate = document.createElement('template');

          localeIconImageElement.setAttribute('src', locale.iconURL);
          localeIconImageElement.setAttribute('alt', locale.title);

          localeLabelElement.innerText = locale.title;
          localeTemplate.innerHTML += localeIconImageElement.outerHTML;
          localeTemplate.innerHTML += localeLabelElement.outerHTML;

          interactiveChoicesFieldsLocale.target.addItem(localeTemplate.innerHTML, locale.name);

          if (locale.name === window.CMSCore.locales.admin.name) {
            interactiveChoicesFieldsLocale.target.setItemSelectedIndex(localeIndex);
          }
        });

        interactiveChoicesFieldsLocale.assembly();
        
        let interactiveChoicesFieldsLocaleSelectElement = interactiveChoicesFieldsLocale.target.element.querySelector('select');
        interactiveChoicesFieldsLocaleSelectElement.addEventListener('change', (event) => {
          fetch('/handler/profile/additional-fields?locale=' + interactiveChoicesFieldsLocaleSelectElement.value + '&localeMessage=' + window.CMSCore.locales.admin.name, {method: 'GET'}).then((response) => {
            return (response.ok) ? response.json() : Promise.reject(response);
          }).then((data1) => {
            let additionalFields = document.querySelectorAll('[role="additional-field"]');
            additionalFields.forEach((element) => {
              element.remove();
            })

            let fields = data1.outputData.additionalFields;
            fields.forEach((field) => {
              this.addUserAdditionalField(localeData, tableAdditionalFieldsButtonContainer, {
                type: field.type,
                title: field.title,
                description: field.description,
                name: field.name
              });
            });
          });
        });

        let profileAdditionalFieldsLocaleContainer = document.querySelector('[role="profile-additional-fields-locale"]');
        profileAdditionalFieldsLocaleContainer.append(interactiveChoicesFieldsLocale.target.element);

        // Получаем все установленные языковые пакеты
        fetch('/handler/profile/additional-fields?locale=' + window.CMSCore.locales.admin.name + '&localeMessage=' + window.CMSCore.locales.admin.name, {method: 'GET'}).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data) => {
          let fields = data.outputData.additionalFields;
          fields.forEach((field) => {
            this.addUserAdditionalField(localeData, tableAdditionalFieldsButtonContainer, {
              type: field.type,
              title: field.title,
              description: field.description,
              name: field.name
            });
          });
        });

        tableAdditionalFieldsButtonContainer.append(buttons.addField.target.element);
      }

      this.buttons.save = new Interactive('button');
      this.buttons.save.target.setLabel(localeData.BUTTON_SAVE_LABEL);
      this.buttons.save.target.setCallback((event) => {
        event.preventDefault();
        
        let elementForm = document.querySelector('.form_settings');
        let form = new Interactive('form');
        form.target.replaceElement(elementForm);

        if (form.target.checkRequiredFields()) {
          let formData = new FormData(elementForm);

          let request = new Interactive('request', {
            method: 'POST',
            url: '/handler/settings?localeMessage=' + window.CMSCore.locales.admin.name
          });

          request.target.data = formData;
          request.target.send();
        } else {
          let interactiveNotification;
        
          interactiveNotification = new Interactive('notification');
          interactiveNotification.target.isPopup = true;
          interactiveNotification.target.setStatusCode(0);
          interactiveNotification.target.setContent(localeData.FORM_REQUIRED_FIELDS_IS_EMPTY);
          interactiveNotification.target.assembly();

          interactiveNotification.target.show();
        }
      });
      this.buttons.save.assembly();

      let interactiveFormPanelContainer = document.querySelector('#SYSTEM_E3724126170');
      interactiveFormPanelContainer.append(this.buttons.save.target.element);
    }, (rejectionReason) => {
      let interactiveNotification = new Interactive('notification');
      interactiveNotification.target.isPopup = true;
      interactiveNotification.target.setStatusCode(0);
      interactiveNotification.target.setContent(rejectionReason);
      interactiveNotification.target.assembly();

      interactiveNotification.target.show();
    });
  }

  addUserAdditionalField(localeData, container, data = {}) {
    let tableRow = document.createElement('tr');
    let tableCellTypeField = document.createElement('td');
    let tableCellTitleField = document.createElement('td');
    let tableCellNameField = document.createElement('td');
    let tableCellDescriptionField = document.createElement('td');
    let tableCellEventField = document.createElement('td');
    let additionalFieldInputTitle = document.createElement('input');
    let additionalFieldInputName = document.createElement('input');
    let additionalFieldInputDescription = document.createElement('textarea');
    
    tableRow.setAttribute('role', 'additional-field');
    additionalFieldInputTitle.setAttribute('type', 'text');
    additionalFieldInputTitle.setAttribute('name', 'setting_users_additional_field_title[]');
    additionalFieldInputTitle.setAttribute('placeholder', 'My field');
    additionalFieldInputTitle.setAttribute('required', 'required');
    additionalFieldInputName.setAttribute('pattern', '[a-z0-9_]+');
    additionalFieldInputName.setAttribute('type', 'text');
    additionalFieldInputName.setAttribute('name', 'setting_users_additional_field_name[]');
    additionalFieldInputName.setAttribute('placeholder', 'my_field');
    additionalFieldInputName.setAttribute('required', 'required');
    additionalFieldInputDescription.setAttribute('name', 'setting_users_additional_field_description[]');
    additionalFieldInputDescription.setAttribute('placeholder', localeData.SETTINGS_PAGE_SETTING_USERS_ADDITIONAL_FIELD_DESCRIPTION_PLACEHOLDER);
    
    tableRow.classList.add('table__row');
    tableCellTypeField.classList.add('table__cell');
    tableCellTitleField.classList.add('table__cell');
    tableCellNameField.classList.add('table__cell');
    tableCellDescriptionField.classList.add('table__cell');
    additionalFieldInputTitle.classList.add('form__input');
    additionalFieldInputTitle.classList.add('form__input_text');
    additionalFieldInputName.classList.add('form__input');
    additionalFieldInputName.classList.add('form__input_text');
    additionalFieldInputDescription.classList.add('form__textarea');

    let interactiveChoicesTypeField = new Interactive('choices');
    interactiveChoicesTypeField.target.addItem('String', 'text');
    interactiveChoicesTypeField.target.addItem('Number', 'number');
    interactiveChoicesTypeField.target.addItem('Date', 'date');
    interactiveChoicesTypeField.target.addItem('Text', 'textarea');
    interactiveChoicesTypeField.target.setName('setting_users_additional_field_type[]');

    if (typeof data.type != 'undefined') {
      switch (data.type) {
        case 'text': interactiveChoicesTypeField.target.setItemSelectedIndex(0); break;
        case 'number': interactiveChoicesTypeField.target.setItemSelectedIndex(1); break;
        case 'date': interactiveChoicesTypeField.target.setItemSelectedIndex(2); break;
        case 'textarea': interactiveChoicesTypeField.target.setItemSelectedIndex(3); break;
        default: interactiveChoicesTypeField.target.setItemSelectedIndex(0);
      }
    }

    let buttons = {delete: null};
    buttons.delete = new Interactive('button');
    buttons.delete.target.setLabel(PageSettings.buttonIcons.trash);
    buttons.delete.target.setCallback((event) => {
      event.preventDefault();
      tableRow.remove();
    });

    buttons.delete.assembly();

    interactiveChoicesTypeField.assembly();

    if (typeof data.title != 'undefined') {
      additionalFieldInputTitle.value = data.title;
    }

    if (typeof data.name != 'undefined') {
      additionalFieldInputName.value = data.name;
    }

    if (typeof data.description != 'undefined') {
      additionalFieldInputDescription.innerText = data.description;
    }

    tableCellTypeField.append(interactiveChoicesTypeField.target.element);
    tableCellTitleField.append(additionalFieldInputTitle);
    tableCellNameField.append(additionalFieldInputName);
    tableCellDescriptionField.append(additionalFieldInputDescription);
    tableCellEventField.append(buttons.delete.target.element);

    tableRow.append(tableCellTypeField);
    tableRow.append(tableCellTitleField);
    tableRow.append(tableCellNameField);
    tableRow.append(tableCellDescriptionField);
    tableRow.append(tableCellEventField);

    container.parentElement.before(tableRow);
  }
}