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
import {Utils} from "../../../utils.class.js";

export class PageWebChannel {
  constructor(page, params = {}) {
    this.page = page;

    this.buttons = {save: null, delete: null};
  }

  init() {
    let searchParams = new URLParser(), locales;
    let elementForm = document.querySelector('.form_webchannel');

    let interactiveLocalesChoices = new Interactive('choices');
    let interactiveChoicesEntriesCategories = new Interactive('choices');

    fetch('/handler/locales', {method: 'GET'}).then((response) => {
      return (response.ok) ? response.json() : Promise.reject(response);
    }).then((data) => {
      locales = data.outputData.locales;
      return window.CMSCore.locales.admin.getData();
    }).then((localeData) => {
      let urlInputElement = document.querySelector('[role="feedURL"]');

      this.buttons.delete = new Interactive('button');
      this.buttons.delete.target.setLabel(localeData.BUTTON_DELETE_LABEL);
      this.buttons.delete.target.setCallback((event) => {
        event.preventDefault();
        
        let interactiveModal = new Interactive('modal', {
          title: localeData.MODAL_WEB_CHANNEL_DELETE_TITLE,
          content: localeData.MODAL_WEB_CHANNEL_DELETE_DESCRIPTION
        });
        
        interactiveModal.target.addButton(localeData.BUTTON_DELETE_LABEL, () => {
          let formData = new FormData();
          formData.append('web_channel_id', searchParams.getPathPart(3));
          
          let request = new Interactive('request', {
            method: 'DELETE',
            url: '/handler/webChannel/' + searchParams.getPathPart(3) + '?localeMessage=' + window.CMSCore.locales.admin.name
          });
  
          request.target.data = formData;
  
          request.target.send().then((data) => {
            if (data.statusCode == 1) {
              window.location.href = '/admin/webChannels';
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

      this.buttons.save = new Interactive('button');
      this.buttons.save.target.setLabel(localeData.BUTTON_SAVE_LABEL);
      this.buttons.save.target.setCallback((event) => {
        event.preventDefault();
        
        let form = new Interactive('form');
        form.target.replaceElement(elementForm);
        
        if (form.target.checkRequiredFields()) {
          let formData = new FormData(elementForm);

          let request = new Interactive('request', {
            method: (searchParams.getPathPart(3) == null) ? 'PUT' : 'PATCH',
            url: '/handler/webChannel?localeMessage=' + window.CMSCore.locales.admin.name
          });

          request.target.data = formData;

          request.target.send().then((data) => {
            if (data.statusCode == 1 && searchParams.getPathPart(3) == null) {
              if (data.outputData.hasOwnProperty('webChannel')) {
                let webChannelData = data1.outputData.webChannel;
                window.location.href = '/admin/webChannel/' + webChannelData.id;
              }
            }
          });
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

      if (searchParams.getPathPart(3) != null) {
        let webChannelsTypes;
        let interactiveChoicesWebChannelsTypes = new Interactive('choices');
        let webChannelDescriptionTextareaElement = document.querySelector('[role="webChannelDescription"]');
        let webChannelTitleInputElement = document.querySelector('[role="webChannelTitle"]');

        fetch('/handler/webChannels/types?localeMessage=' + window.CMSCore.locales.admin.name, {
          method: 'GET'
        }).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data1) => {
          webChannelsTypes = data1.outputData.webChannelsTypes;
          return fetch('/handler/webChannel/' + searchParams.getPathPart(3) + '?localeMessage=' + window.CMSCore.locales.admin.name, {method: 'GET'});
        }).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data1) => {
          webChannelsTypes.forEach((type, typeIndex) => {
            interactiveChoicesWebChannelsTypes.target.addItem(type.title, type.id);
          });

          webChannelsTypes.forEach((type, typeIndex) => {
            if (type.id == data1.outputData.webChannel.typeID) {
              interactiveChoicesWebChannelsTypes.target.setItemSelectedIndex(typeIndex);
            }
          });

          interactiveChoicesWebChannelsTypes.target.setName('web_channel_type_id');
          interactiveChoicesWebChannelsTypes.assembly();

          document.querySelector('#TC6474387201').append(interactiveChoicesWebChannelsTypes.target.element);
        });

        let entriesCategories;

        fetch('/handler/entries/categories?locale=' + window.CMSCore.locales.admin.name + '&localeMessage=' + window.CMSCore.locales.admin.name, {
          method: 'GET'}
        ).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data1) => {
          entriesCategories = data1.outputData.entriesCategories;
          return fetch('/handler/webChannel/' + searchParams.getPathPart(3) + '?localeMessage=' + window.CMSCore.locales.admin.name, {method: 'GET'});
        }).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data1) => {
          entriesCategories.forEach((entryCategory, entryCategoryIndex) => {
            interactiveChoicesEntriesCategories.target.addItem(entryCategory.title, entryCategory.id);
          });

          entriesCategories.forEach((entryCategory, entryCategoryIndex) => {
            if (entryCategory.id == data1.outputData.webChannel.entriesCategoryID) {
              interactiveChoicesEntriesCategories.target.setItemSelectedIndex(entryCategoryIndex);
            }
          });

          interactiveChoicesEntriesCategories.target.setName('web_channel_entries_category_id');
          interactiveChoicesEntriesCategories.assembly();

          document.querySelector('#TC6474387200').append(interactiveChoicesEntriesCategories.target.element);
        });

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

          interactiveLocalesChoices.target.addItem(localeTemplate.innerHTML, localeName);
        });

        locales.forEach((locale, localeIndex) => {
          if (locale.name === window.CMSCore.locales.admin.name) {
            interactiveLocalesChoices.target.setItemSelectedIndex(localeIndex);
          }

          if (locale.name === window.CMSCore.locales.admin.name) {
            webChannelDescriptionTextareaElement.setAttribute('name', 'web_channel_description_' + locale.iso639_2);
            webChannelTitleInputElement.setAttribute('name', 'web_channel_title_' + locale.iso639_2);
            
            let request = new Interactive('request', {
              method: 'GET',
              url: '/handler/webChannel/' + searchParams.getPathPart(3) + '?locale=' + locale.name + '&localeMessage=' + window.CMSCore.locales.admin.name
            });

            request.target.showingNotification = false;

            request.target.send().then((data) => {
              if (data.statusCode == 1 && data.outputData.hasOwnProperty('webChannel')) {
                webChannelDescriptionTextareaElement.value = data.outputData.webChannel.description;
                webChannelTitleInputElement.value = data.outputData.webChannel.title;
              }
            });
          }
        });

        interactiveLocalesChoices.assembly();

        let interactiveContainerElement = document.querySelector('#E8548530785');
        interactiveContainerElement.append(interactiveLocalesChoices.target.element);

        urlInputElement.addEventListener('input', (event) => {
          /** @var {String} */
          let inputValue = event.target.value;
  
          /** @var {Utils} */
          let utils = new Utils();
          /** @var {UString} */
          let uString = utils.createString(inputValue);
  
          event.target.value = uString.translitToEN(true);
        });

        let interactiveChoicesSelectElement = interactiveContainerElement.querySelector('select');
        interactiveChoicesSelectElement.addEventListener('change', (event) => {
          locales.forEach((locale, localeIndex) => {
            if (locale.name == event.target.value) {
              webChannelDescriptionTextareaElement.setAttribute('name', 'web_channel_description_' + locale.iso639_2);
              webChannelTitleInputElement.setAttribute('name', 'web_channel_title_' + locale.iso639_2);
              
              let request = new Interactive('request', {
                method: 'GET',
                url: '/handler/webChannel/' + searchParams.getPathPart(3) + '?locale=' + locale.name + '&localeMessage=' + window.CMSCore.locales.admin.name
              });
  
              request.target.showingNotification = false;
  
              request.target.send().then((data) => {
                if (data.statusCode == 1 && data.outputData.hasOwnProperty('webChannel')) {
                  webChannelDescriptionTextareaElement.value = data.outputData.webChannel.description;
                  webChannelTitleInputElement.value = data.outputData.webChannel.title;
                }
              });
            }
          });
        });
      } else {
        fetch('/handler/webChannels/types?localeMessage=' + window.CMSCore.locales.admin.name, {
          method: 'GET'
        }).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data1) => {
          let webChannelsTypes = data1.outputData.webChannelsTypes;
          let interactiveChoicesWebChannelsTypes = new Interactive('choices');
  
          webChannelsTypes.forEach((type, typeIndex) => {
            interactiveChoicesWebChannelsTypes.target.addItem(type.title, type.id);
          });
  
          interactiveChoicesWebChannelsTypes.target.setName('web_channel_type_id');
          interactiveChoicesWebChannelsTypes.assembly();
  
          document.querySelector('#TC6474387201').append(interactiveChoicesWebChannelsTypes.target.element);
  
          return fetch('/handler/entries/categories?localeMessage=' + window.CMSCore.locales.admin.name, {method: 'GET'});
        }).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data1) => {
          let entriesCategories = data1.outputData.entriesCategories;
          let interactiveChoicesEntriesCategories = new Interactive('choices');
  
          entriesCategories.forEach((entryCategory, entryCategoryIndex) => {
            interactiveChoicesEntriesCategories.target.addItem(entryCategory.title, entryCategory.id);
          });
  
          interactiveChoicesEntriesCategories.target.setName('web_channel_entries_category_id');
          interactiveChoicesEntriesCategories.assembly();
  
          document.querySelector('#TC6474387200').append(interactiveChoicesEntriesCategories.target.element);
        });
      }

      if (searchParams.getPathPart(3) == null) {
        this.buttons.delete.target.element.style.display = 'none';
        this.buttons.save.target.element.style.display = 'flex';
      } else {
        this.buttons.delete.target.element.style.display = 'flex';
        this.buttons.save.target.element.style.display = 'flex';
      }

      let interactiveFormPanelContainer = document.querySelector('#SYSTEM_E3724126170');
      interactiveFormPanelContainer.append(this.buttons.delete.target.element);
      interactiveFormPanelContainer.append(this.buttons.save.target.element);
    });
  }
}