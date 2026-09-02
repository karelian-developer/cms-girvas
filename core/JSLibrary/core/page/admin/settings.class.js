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

export class PageSettings {
  static buttonIcons = {
    trash: '<svg version="1.1" class="button__icon" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve"><rect x="13.9" y="16.1" width="37.3" height="47.9"></rect><path d="M41.4,10l1-7.3L24.7,0.3l-1,7.3L10.2,5.7l-1,7.3l44.6,6.2l1-7.3L41.4,10z M25.6,7.8l0.7-5l13.8,1.9l-0.7,5L25.6,7.8z"></path></svg>',
  };

  constructor(page, params = {}) {
    this.page = page;

    this.buttons = {save: null, sendTestEmail: null};
  }

  init() {
    let searchParams = new URLParser(), locales;
    let elementForm = document.querySelector('[data-element="main-form"]');
    let interactiveLocaleChoices = new Interactive('choices');
    
    this.page.core.locales.admin.getData().then((localeData) => {
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

      if (searchParams.getPathPart(3) === null || searchParams.getPathPart(3) === 'base') {
        let timezones, charsets, timezoneSelected, charsetSelected;
        const interactiveChoicesSettingsTimezone = new Interactive('choices');
        const interactiveChoicesSettingsCharset = new Interactive('choices');
        const interactiveChoicesSettingsBaseLocale = new Interactive('choices');
        const interactiveChoicesSettingsAdminLocale = new Interactive('choices');

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

          document.querySelector('[data-element="choice"][data-choice="timezone"]').prepend(interactiveChoicesSettingsTimezone.target.element);
          document.querySelector('[data-element="choice"][data-choice="charset"]').append(interactiveChoicesSettingsCharset.target.element);
          document.querySelector('[data-element="choice"][data-choice="locale-site"]').append(interactiveChoicesSettingsBaseLocale.target.element);
          document.querySelector('[data-element="choice"][data-choice="locale-admin"]').append(interactiveChoicesSettingsAdminLocale.target.element);
        });
      }

      if (searchParams.getPathPart(3) === 'files') {
        fetch('/handler/file-auto-convert-image-extension', {
          method: 'GET'
        }).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data) => {
          const extension = data.outputData.extension;
          const interactiveChoicesSettingsAutoConvertImageExtension = new Interactive('choices');

          ['webp', 'avif'].forEach((element, elementIndex) => {
            interactiveChoicesSettingsAutoConvertImageExtension.target.addItem(element.toUpperCase(), element);

            if (element === extension) {
              interactiveChoicesSettingsAutoConvertImageExtension.target.setItemSelectedIndex(elementIndex);
            }
          });

          interactiveChoicesSettingsAutoConvertImageExtension.target.setName('setting_files_auto_convert_file_image_extension');
          interactiveChoicesSettingsAutoConvertImageExtension.assembly();

          document.querySelector('[data-element="choice"][data-choice="convert-extension"]').prepend(interactiveChoicesSettingsAutoConvertImageExtension.target.element);
        });
      }

      if (searchParams.getPathPart(3) === 'users') {
        let buttons = {addField: null};

        let interactiveChoicesFieldsLocale = new Interactive('choices');

        interactiveChoicesFieldsLocale.target.setName('_users_additional_fields_locale');

        let tableAdditionalFieldsButtonContainer = document.querySelector('[data-element="button-add-field"]');
        
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
            let additionalFields = document.querySelectorAll('[data-element="additional-field-part-element"]');
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

        let profileAdditionalFieldsLocaleContainer = document.querySelector('[data-element="additional-fields-locale"]');
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

      if (searchParams.getPathPart(3) === 'entries') {
        let buttons = {addField: null};

        let interactiveChoicesFieldsLocale = new Interactive('choices');

        interactiveChoicesFieldsLocale.target.setName('_entries_additional_fields_locale');

        let tableAdditionalFieldsButtonContainer = document.querySelector('[data-element="button-add-field"]');
        
        buttons.addField = new Interactive('button');
        buttons.addField.target.setLabel(localeData.BUTTON_NEW_FIELD_LABEL);
        buttons.addField.target.setCallback((event) => {
          event.preventDefault();

          this.addEntriesAdditionalField(localeData, tableAdditionalFieldsButtonContainer);
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
          fetch('/handler/entries/additional-fields?locale=' + interactiveChoicesFieldsLocaleSelectElement.value + '&localeMessage=' + window.CMSCore.locales.admin.name, {method: 'GET'}).then((response) => {
            return (response.ok) ? response.json() : Promise.reject(response);
          }).then((data1) => {
            let additionalFields = document.querySelectorAll('[data-element="additional-field-part-element"]');
            additionalFields.forEach((element) => {
              element.remove();
            })

            let fields = data1.outputData.additionalFields;
            fields.forEach((field) => {
              this.addEntriesAdditionalField(localeData, tableAdditionalFieldsButtonContainer, {
                type: field.type,
                categoryID: field.categoryID,
                title: field.title,
                description: field.description,
                name: field.name
              });
            });
          });
        });

        let profileAdditionalFieldsLocaleContainer = document.querySelector('[data-element="additional-fields-locale"]');
        profileAdditionalFieldsLocaleContainer.append(interactiveChoicesFieldsLocale.target.element);

        // Получаем все установленные языковые пакеты
        fetch('/handler/entries/additional-fields?locale=' + window.CMSCore.locales.admin.name + '&localeMessage=' + window.CMSCore.locales.admin.name, {method: 'GET'}).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data) => {
          let fields = data.outputData.additionalFields;
          fields.forEach((field) => {
            this.addEntriesAdditionalField(localeData, tableAdditionalFieldsButtonContainer, {
              type: field.type,
              categoryID: field.categoryID,
              title: field.title,
              description: field.description,
              name: field.name
            });
          });
        });

        tableAdditionalFieldsButtonContainer.append(buttons.addField.target.element);
      }

      if (searchParams.getPathPart(3) === 'pages') {
        let buttons = {addField: null};

        let interactiveChoicesFieldsLocale = new Interactive('choices');

        interactiveChoicesFieldsLocale.target.setName('_static_pages_additional_fields_locale');

        let tableAdditionalFieldsButtonContainer = document.querySelector('[data-element="button-add-field"]');
        
        buttons.addField = new Interactive('button');
        buttons.addField.target.setLabel(localeData.BUTTON_NEW_FIELD_LABEL);
        buttons.addField.target.setCallback((event) => {
          event.preventDefault();

          this.addStaticPagesAdditionalField(localeData, tableAdditionalFieldsButtonContainer);
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
          fetch('/handler/pages/additional-fields?locale=' + interactiveChoicesFieldsLocaleSelectElement.value + '&localeMessage=' + window.CMSCore.locales.admin.name, {method: 'GET'}).then((response) => {
            return (response.ok) ? response.json() : Promise.reject(response);
          }).then((data1) => {
            let additionalFields = document.querySelectorAll('[data-element="additional-field-part-element"]');
            additionalFields.forEach((element) => {
              element.remove();
            })

            let fields = data1.outputData.additionalFields;
            fields.forEach((field) => {
              this.addStaticPagesAdditionalField(localeData, tableAdditionalFieldsButtonContainer, {
                type: field.type,
                title: field.title,
                description: field.description,
                name: field.name
              });
            });
          });
        });

        let profileAdditionalFieldsLocaleContainer = document.querySelector('[data-element="additional-fields-locale"]');
        profileAdditionalFieldsLocaleContainer.append(interactiveChoicesFieldsLocale.target.element);

        // Получаем все установленные языковые пакеты
        fetch('/handler/pages/additional-fields?locale=' + window.CMSCore.locales.admin.name + '&localeMessage=' + window.CMSCore.locales.admin.name, {method: 'GET'}).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data) => {
          let fields = data.outputData.additionalFields;
          fields.forEach((field) => {
            this.addStaticPagesAdditionalField(localeData, tableAdditionalFieldsButtonContainer, {
              type: field.type,
              title: field.title,
              description: field.description,
              name: field.name
            });
          });
        });

        tableAdditionalFieldsButtonContainer.append(buttons.addField.target.element);
      }

      if (searchParams.getPathPart(3) === 'email') {
        this.buttons.sendTestEmail = new Interactive('button');
        this.buttons.sendTestEmail.target.setLabel(localeData.BUTTON_SEND_TEST_EMAIL);
        this.buttons.sendTestEmail.target.setCallback((event) => {
          event.preventDefault();

          let formData = new FormData();
          formData.append('event', 'testSend');

          let request = new Interactive('request', {
            method: 'POST',
            url: '/handler/settings/email?localeMessage=' + window.CMSCore.locales.admin.name
          });

          request.target.data = formData;
          request.target.send();
        });
        
        this.buttons.sendTestEmail.assembly();
      }

      this.buttons.save = new Interactive('button');
      this.buttons.save.target.setLabel(localeData.BUTTON_SAVE_LABEL);
      this.buttons.save.target.setCallback((event) => {
        event.preventDefault();
        
        let elementForm = document.querySelector('[data-element="main-form"]');
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
          this.page.showPopupNotification(localeData.FORM_REQUIRED_FIELDS_IS_EMPTY, 0);
        }
      });
      this.buttons.save.assembly();

      let interactiveFormPanelContainer = document.querySelector('[data-element="panel"]');

      if (searchParams.getPathPart(3) === 'email') {
        interactiveFormPanelContainer.append(this.buttons.sendTestEmail.target.element);
      }

      interactiveFormPanelContainer.append(this.buttons.save.target.element);
    }, (rejectionReason) => {
      this.page.showPopupNotification(rejectionReason, 0);
    });
  }

  addUserAdditionalField(localeData, container, data = {}) {
    const cellHeaderElement = document.createElement('div');
    const additionalFieldInputTitle = document.createElement('input');
    const additionalFieldInputName = document.createElement('input');
    const additionalFieldInputDescription = document.createElement('textarea');
    
    cellHeaderElement.classList.add('cell');
    cellHeaderElement.classList.add('grid-table__cell');
    cellHeaderElement.classList.add('grid-table__cell_header');
    cellHeaderElement.innerText = data.title !== undefined
      ? `${localeData.PAGE_SETTINGS_SETTING_FIELD}: ${data.title}`
      : localeData.PAGE_SETTINGS_SETTING_NEW_FIELD;

    cellHeaderElement.setAttribute('data-element', 'additional-field-part-element');

    additionalFieldInputTitle.setAttribute('type', 'text');
    additionalFieldInputTitle.setAttribute('name', 'setting_users_additional_field_title[]');
    additionalFieldInputTitle.setAttribute('placeholder', localeData.PAGE_SETTINGS_SETTING_USERS_ADDITIONAL_FIELD_TITLE_PLACEHOLDER);
    additionalFieldInputTitle.setAttribute('required', 'required');
    additionalFieldInputName.setAttribute('pattern', '[a-z0-9_]+');
    additionalFieldInputName.setAttribute('type', 'text');
    additionalFieldInputName.setAttribute('name', 'setting_users_additional_field_name[]');
    additionalFieldInputName.setAttribute('placeholder', 'my_field');
    additionalFieldInputName.setAttribute('required', 'required');
    additionalFieldInputDescription.setAttribute('name', 'setting_users_additional_field_description[]');
    additionalFieldInputDescription.setAttribute('placeholder', localeData.PAGE_SETTINGS_SETTING_USERS_ADDITIONAL_FIELD_DESCRIPTION_PLACEHOLDER);

    additionalFieldInputTitle.classList.add('form__input');
    additionalFieldInputTitle.classList.add('form__input_text');
    additionalFieldInputName.classList.add('form__input');
    additionalFieldInputName.classList.add('form__input_text');
    additionalFieldInputDescription.classList.add('form__textarea');

    const cellElementsForType = this.createCellAdditionalFieldElements(
      localeData.PAGE_SETTINGS_SETTING_USERS_ADDITIONAL_FIELD_TYPE_FIELD_TITLE
    );

    /* Выпадающий список с типами полей */

    const interactiveChoicesTypeField = new Interactive('choices');
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

    interactiveChoicesTypeField.assembly();

    cellElementsForType[1].append(interactiveChoicesTypeField.target.element);

    const cellElementsForTitle = this.createCellAdditionalFieldElements(
      localeData.PAGE_SETTINGS_SETTING_USERS_ADDITIONAL_FIELD_TITLE_TITLE,
      additionalFieldInputTitle
    );

    const cellElementsForName = this.createCellAdditionalFieldElements(
      localeData.PAGE_SETTINGS_SETTING_USERS_ADDITIONAL_FIELD_TECHNICAL_NAME_TITLE,
      additionalFieldInputName
    );

    const cellElementsForDescription = this.createCellAdditionalFieldElements(
      localeData.PAGE_SETTINGS_SETTING_USERS_ADDITIONAL_FIELD_DESCRIPTION_TITLE,
      additionalFieldInputDescription
    );

    const buttonRemoveField = new Interactive('button');
    buttonRemoveField.target.setLabel(localeData.BUTTON_DELETE_LABEL);
    buttonRemoveField.target.setCallback((event) => {
      event.preventDefault();
      
      cellElementsForType.forEach(element => {
        element.remove();
      });

      cellElementsForTitle.forEach(element => {
        element.remove();
      });

      cellElementsForName.forEach(element => {
        element.remove();
      });

      cellElementsForDescription.forEach(element => {
        element.remove();
      });

      buttonRemoveField.target.element.parentElement.previousElementSibling.remove();
      buttonRemoveField.target.element.parentElement.remove();

      cellHeaderElement.remove();
    });

    buttonRemoveField.assembly();

    const cellElementsForEvents = this.createCellAdditionalFieldElements(
      '', buttonRemoveField.target.element
    );

    container.parentElement.parentElement.insertBefore(
      cellHeaderElement,
      container.parentElement.previousElementSibling
    );

    cellElementsForType.forEach(element => {
      container.parentElement.parentElement.insertBefore(
        element,
        container.parentElement.previousElementSibling
      );
    });

    cellElementsForTitle.forEach(element => {
      container.parentElement.parentElement.insertBefore(
        element,
        container.parentElement.previousElementSibling
      );
    });

    cellElementsForName.forEach(element => {
      container.parentElement.parentElement.insertBefore(
        element,
        container.parentElement.previousElementSibling
      );
    });

    cellElementsForDescription.forEach(element => {
      container.parentElement.parentElement.insertBefore(
        element,
        container.parentElement.previousElementSibling
      );
    });

    cellElementsForEvents.forEach(element => {
      container.parentElement.parentElement.insertBefore(
        element,
        container.parentElement.previousElementSibling
      );
    });

    additionalFieldInputTitle.value = data.title !== undefined
      ? data.title
      : '';

    additionalFieldInputName.value = data.name !== undefined
      ? data.name
      : '';

    additionalFieldInputDescription.value = data.description !== undefined
      ? data.description
      : '';
  }

  createCellAdditionalFieldElements(title, dataElement = null) {
    const cellTextElement = document.createElement('div');
    const cellTextTitleElement = document.createElement('div');
    const cellDataElement = document.createElement('div');

    cellTextElement.classList.add('cell');
    cellTextElement.classList.add('grid-table__cell');
    cellTextElement.classList.add('grid-table__cell_text');

    cellDataElement.classList.add('cell');
    cellDataElement.classList.add('grid-table__cell');
    cellDataElement.classList.add('grid-table__cell_data');

    cellTextElement.setAttribute('data-element', 'additional-field-part-element');
    cellDataElement.setAttribute('data-element', 'additional-field-part-element');
    
    cellTextTitleElement.classList.add('cell__title');
    cellTextTitleElement.innerText = title;
    
    cellTextElement.appendChild(cellTextTitleElement);

    if (dataElement !== null) {
      cellDataElement.appendChild(dataElement);
    }

    return [
      cellTextElement,
      cellDataElement
    ];
  }

  addEntriesAdditionalField(localeData, container, data = {}) {
    const cellHeaderElement = document.createElement('div');
    const additionalFieldInputTitle = document.createElement('input');
    const additionalFieldInputName = document.createElement('input');
    const additionalFieldInputDescription = document.createElement('textarea');
    
    cellHeaderElement.classList.add('cell');
    cellHeaderElement.classList.add('grid-table__cell');
    cellHeaderElement.classList.add('grid-table__cell_header');
    cellHeaderElement.innerText = data.title !== undefined
      ? `${localeData.PAGE_SETTINGS_SETTING_FIELD}: ${data.title}`
      : localeData.PAGE_SETTINGS_SETTING_NEW_FIELD;

    cellHeaderElement.setAttribute('data-element', 'additional-field-part-element');

    additionalFieldInputTitle.setAttribute('type', 'text');
    additionalFieldInputTitle.setAttribute('name', 'setting_entries_additional_field_title[]');
    additionalFieldInputTitle.setAttribute('placeholder', localeData.PAGE_SETTINGS_SETTING_ENTRIES_ADDITIONAL_FIELD_TITLE_PLACEHOLDER);
    additionalFieldInputTitle.setAttribute('required', 'required');
    additionalFieldInputName.setAttribute('pattern', '[a-z0-9_]+');
    additionalFieldInputName.setAttribute('type', 'text');
    additionalFieldInputName.setAttribute('name', 'setting_entries_additional_field_name[]');
    additionalFieldInputName.setAttribute('placeholder', 'my_field');
    additionalFieldInputName.setAttribute('required', 'required');
    additionalFieldInputDescription.setAttribute('name', 'setting_entries_additional_field_description[]');
    additionalFieldInputDescription.setAttribute('placeholder', localeData.PAGE_SETTINGS_SETTING_ENTRIES_ADDITIONAL_FIELD_DESCRIPTION_PLACEHOLDER);

    additionalFieldInputTitle.classList.add('form__input');
    additionalFieldInputTitle.classList.add('form__input_text');
    additionalFieldInputName.classList.add('form__input');
    additionalFieldInputName.classList.add('form__input_text');
    additionalFieldInputDescription.classList.add('form__textarea');

    const cellElementsForType = this.createCellAdditionalFieldElements(
      localeData.PAGE_SETTINGS_SETTING_ENTRIES_ADDITIONAL_FIELD_TYPE_FIELD_TITLE
    );

    const cellElementsForCategory = this.createCellAdditionalFieldElements(
      localeData.PAGE_SETTINGS_SETTING_ENTRIES_ADDITIONAL_FIELD_CATEGORY_FIELD_TITLE
    );

    /* Выпадающий список с типами полей */

    const interactiveChoicesTypeField = new Interactive('choices');
    interactiveChoicesTypeField.target.addItem('String', 'text');
    interactiveChoicesTypeField.target.addItem('Number', 'number');
    interactiveChoicesTypeField.target.addItem('Date', 'date');
    interactiveChoicesTypeField.target.addItem('Text', 'textarea');
    interactiveChoicesTypeField.target.setName('setting_entries_additional_field_type[]');
    
    if (typeof data.type != 'undefined') {
      switch (data.type) {
        case 'text': interactiveChoicesTypeField.target.setItemSelectedIndex(0); break;
        case 'number': interactiveChoicesTypeField.target.setItemSelectedIndex(1); break;
        case 'date': interactiveChoicesTypeField.target.setItemSelectedIndex(2); break;
        case 'textarea': interactiveChoicesTypeField.target.setItemSelectedIndex(3); break;
        default: interactiveChoicesTypeField.target.setItemSelectedIndex(0);
      }
    }

    interactiveChoicesTypeField.assembly();

    cellElementsForType[1].append(interactiveChoicesTypeField.target.element);

    /* Выпадающий список с категориями записей */

    let requestGetEntriesCategories = new Interactive('request', {
      method: 'GET',
      url: '/handler/entry/categories' + '?locale=' + window.CMSCore.locales.admin.name + '&localeMessage=' + window.CMSCore.locales.admin.name,
    });

    requestGetEntriesCategories.target.showingNotification = false;
    requestGetEntriesCategories.target.send().then((responseData) => {
      let interactiveChoicesCategoryField = new Interactive('choices');

      if (responseData.statusCode === 1 && responseData.outputData.hasOwnProperty('entriesCategories')) {
        let entriesCategories = responseData.outputData.entriesCategories;
        
        entriesCategories.forEach((entriesCategory, entriesCategoryIndex) => {
          interactiveChoicesCategoryField.target.addItem(entriesCategory.title, entriesCategory.id);

          if (entriesCategory.id === data.categoryID) {
            interactiveChoicesCategoryField.target.setItemSelectedIndex(entriesCategoryIndex);
          }
        });
        
        interactiveChoicesCategoryField.target.setName('setting_entries_additional_field_category_id[]');
        interactiveChoicesCategoryField.assembly();

        cellElementsForCategory[1].append(interactiveChoicesCategoryField.target.element);
      }
    });

    /* ======= */

    const cellElementsForTitle = this.createCellAdditionalFieldElements(
      localeData.PAGE_SETTINGS_SETTING_ENTRIES_ADDITIONAL_FIELD_TITLE_TITLE,
      additionalFieldInputTitle
    );

    const cellElementsForName = this.createCellAdditionalFieldElements(
      localeData.PAGE_SETTINGS_SETTING_ENTRIES_ADDITIONAL_FIELD_TECHNICAL_NAME_TITLE,
      additionalFieldInputName
    );

    const cellElementsForDescription = this.createCellAdditionalFieldElements(
      localeData.PAGE_SETTINGS_SETTING_ENTRIES_ADDITIONAL_FIELD_DESCRIPTION_TITLE,
      additionalFieldInputDescription
    );

    const buttonRemoveField = new Interactive('button');
    buttonRemoveField.target.setLabel(localeData.BUTTON_DELETE_LABEL);
    buttonRemoveField.target.setCallback((event) => {
      event.preventDefault();
      
      cellElementsForType.forEach(element => {
        element.remove();
      });

      cellElementsForCategory.forEach(element => {
        element.remove();
      });

      cellElementsForTitle.forEach(element => {
        element.remove();
      });

      cellElementsForName.forEach(element => {
        element.remove();
      });

      cellElementsForDescription.forEach(element => {
        element.remove();
      });

      buttonRemoveField.target.element.parentElement.previousElementSibling.remove();
      buttonRemoveField.target.element.parentElement.remove();

      cellHeaderElement.remove();
    });

    buttonRemoveField.assembly();

    const cellElementsForEvents = this.createCellAdditionalFieldElements(
      '', buttonRemoveField.target.element
    );

    container.parentElement.parentElement.insertBefore(
      cellHeaderElement,
      container.parentElement.previousElementSibling
    );

    cellElementsForType.forEach(element => {
      container.parentElement.parentElement.insertBefore(
        element,
        container.parentElement.previousElementSibling
      );
    });

    cellElementsForCategory.forEach(element => {
      container.parentElement.parentElement.insertBefore(
        element,
        container.parentElement.previousElementSibling
      );
    });

    cellElementsForTitle.forEach(element => {
      container.parentElement.parentElement.insertBefore(
        element,
        container.parentElement.previousElementSibling
      );
    });

    cellElementsForName.forEach(element => {
      container.parentElement.parentElement.insertBefore(
        element,
        container.parentElement.previousElementSibling
      );
    });

    cellElementsForDescription.forEach(element => {
      container.parentElement.parentElement.insertBefore(
        element,
        container.parentElement.previousElementSibling
      );
    });

    cellElementsForEvents.forEach(element => {
      container.parentElement.parentElement.insertBefore(
        element,
        container.parentElement.previousElementSibling
      );
    });

    additionalFieldInputTitle.value = data.title !== undefined
      ? data.title
      : '';

    additionalFieldInputName.value = data.name !== undefined
      ? data.name
      : '';

    additionalFieldInputDescription.value = data.description !== undefined
      ? data.description
      : '';
  }

  addStaticPagesAdditionalField(localeData, container, data = {}) {
    const cellHeaderElement = document.createElement('div');
    const additionalFieldInputTitle = document.createElement('input');
    const additionalFieldInputName = document.createElement('input');
    const additionalFieldInputDescription = document.createElement('textarea');
    
    cellHeaderElement.classList.add('cell');
    cellHeaderElement.classList.add('grid-table__cell');
    cellHeaderElement.classList.add('grid-table__cell_header');
    cellHeaderElement.innerText = data.title !== undefined
      ? `${localeData.PAGE_SETTINGS_SETTING_FIELD}: ${data.title}`
      : localeData.PAGE_SETTINGS_SETTING_NEW_FIELD;

    cellHeaderElement.setAttribute('data-element', 'additional-field-part-element');

    additionalFieldInputTitle.setAttribute('type', 'text');
    additionalFieldInputTitle.setAttribute('name', 'setting_static_pages_additional_field_title[]');
    additionalFieldInputTitle.setAttribute('placeholder', localeData.PAGE_SETTINGS_SETTING_STATIC_PAGES_ADDITIONAL_FIELD_TITLE_PLACEHOLDER);
    additionalFieldInputTitle.setAttribute('required', 'required');
    additionalFieldInputName.setAttribute('pattern', '[a-z0-9_]+');
    additionalFieldInputName.setAttribute('type', 'text');
    additionalFieldInputName.setAttribute('name', 'setting_static_pages_additional_field_name[]');
    additionalFieldInputName.setAttribute('placeholder', 'my_field');
    additionalFieldInputName.setAttribute('required', 'required');
    additionalFieldInputDescription.setAttribute('name', 'setting_static_pages_additional_field_description[]');
    additionalFieldInputDescription.setAttribute('placeholder', localeData.PAGE_SETTINGS_SETTING_STATIC_PAGES_ADDITIONAL_FIELD_DESCRIPTION_PLACEHOLDER);

    additionalFieldInputTitle.classList.add('form__input');
    additionalFieldInputTitle.classList.add('form__input_text');
    additionalFieldInputName.classList.add('form__input');
    additionalFieldInputName.classList.add('form__input_text');
    additionalFieldInputDescription.classList.add('form__textarea');

    const cellElementsForType = this.createCellAdditionalFieldElements(
      localeData.PAGE_SETTINGS_SETTING_STATIC_PAGES_ADDITIONAL_FIELD_TYPE_FIELD_TITLE
    );

    /* Выпадающий список с типами полей */

    const interactiveChoicesTypeField = new Interactive('choices');
    interactiveChoicesTypeField.target.addItem('String', 'text');
    interactiveChoicesTypeField.target.addItem('Number', 'number');
    interactiveChoicesTypeField.target.addItem('Date', 'date');
    interactiveChoicesTypeField.target.addItem('Text', 'textarea');
    interactiveChoicesTypeField.target.setName('setting_static_pages_additional_field_type[]');
    
    if (typeof data.type != 'undefined') {
      switch (data.type) {
        case 'text': interactiveChoicesTypeField.target.setItemSelectedIndex(0); break;
        case 'number': interactiveChoicesTypeField.target.setItemSelectedIndex(1); break;
        case 'date': interactiveChoicesTypeField.target.setItemSelectedIndex(2); break;
        case 'textarea': interactiveChoicesTypeField.target.setItemSelectedIndex(3); break;
        default: interactiveChoicesTypeField.target.setItemSelectedIndex(0);
      }
    }

    interactiveChoicesTypeField.assembly();

    cellElementsForType[1].append(interactiveChoicesTypeField.target.element);

    const cellElementsForTitle = this.createCellAdditionalFieldElements(
      localeData.PAGE_SETTINGS_SETTING_STATIC_PAGES_ADDITIONAL_FIELD_TITLE_TITLE,
      additionalFieldInputTitle
    );

    const cellElementsForName = this.createCellAdditionalFieldElements(
      localeData.PAGE_SETTINGS_SETTING_STATIC_PAGES_ADDITIONAL_FIELD_TECHNICAL_NAME_TITLE,
      additionalFieldInputName
    );

    const cellElementsForDescription = this.createCellAdditionalFieldElements(
      localeData.PAGE_SETTINGS_SETTING_STATIC_PAGES_ADDITIONAL_FIELD_DESCRIPTION_TITLE,
      additionalFieldInputDescription
    );

    const buttonRemoveField = new Interactive('button');
    buttonRemoveField.target.setLabel(localeData.BUTTON_DELETE_LABEL);
    buttonRemoveField.target.setCallback((event) => {
      event.preventDefault();
      
      cellElementsForType.forEach(element => {
        element.remove();
      });

      cellElementsForTitle.forEach(element => {
        element.remove();
      });

      cellElementsForName.forEach(element => {
        element.remove();
      });

      cellElementsForDescription.forEach(element => {
        element.remove();
      });

      buttonRemoveField.target.element.parentElement.previousElementSibling.remove();
      buttonRemoveField.target.element.parentElement.remove();

      cellHeaderElement.remove();
    });

    buttonRemoveField.assembly();

    const cellElementsForEvents = this.createCellAdditionalFieldElements(
      '', buttonRemoveField.target.element
    );

    container.parentElement.parentElement.insertBefore(
      cellHeaderElement,
      container.parentElement.previousElementSibling
    );

    cellElementsForType.forEach(element => {
      container.parentElement.parentElement.insertBefore(
        element,
        container.parentElement.previousElementSibling
      );
    });

    cellElementsForTitle.forEach(element => {
      container.parentElement.parentElement.insertBefore(
        element,
        container.parentElement.previousElementSibling
      );
    });

    cellElementsForName.forEach(element => {
      container.parentElement.parentElement.insertBefore(
        element,
        container.parentElement.previousElementSibling
      );
    });

    cellElementsForDescription.forEach(element => {
      container.parentElement.parentElement.insertBefore(
        element,
        container.parentElement.previousElementSibling
      );
    });

    cellElementsForEvents.forEach(element => {
      container.parentElement.parentElement.insertBefore(
        element,
        container.parentElement.previousElementSibling
      );
    });

    additionalFieldInputTitle.value = data.title !== undefined
      ? data.title
      : '';

    additionalFieldInputName.value = data.name !== undefined
      ? data.name
      : '';

    additionalFieldInputDescription.value = data.description !== undefined
      ? data.description
      : '';
  }
}