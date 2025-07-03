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

export class PageFeed {
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
    }, (rejectionReason) => {
      let interactiveNotification = new Interactive('notification');
      interactiveNotification.target.isPopup = true;
      interactiveNotification.target.setStatusCode(0);
      interactiveNotification.target.setContent(rejectionReason);
      interactiveNotification.target.assembly();

      interactiveNotification.target.show();
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
            url: '/handler/feed/' + searchParams.getPathPart(3) + '?localeMessage=' + window.CMSCore.locales.admin.name
          });
  
          request.target.data = formData;
  
          request.target.send().then((data) => {
            if (data.statusCode === 1) {
              window.location.href = '/admin/feeds';
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
            method: (searchParams.getPathPart(3) === null) ? 'PUT' : 'PATCH',
            url: '/handler/feed?localeMessage=' + window.CMSCore.locales.admin.name
          });

          request.target.data = formData;

          request.target.send().then((data) => {
            if (data.statusCode === 1 && searchParams.getPathPart(3) === null) {
              if (data.outputData.hasOwnProperty('feed')) {
                let feedData = data.outputData.feed;
                window.location.href = '/admin/feed/' + feedData.id;
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

      if (searchParams.getPathPart(3) !== null) {
        let feedsTypes;
        let interactiveChoicesWebChannelsTypes = new Interactive('choices');
        let feedDescriptionTextareaElement = document.querySelector('[role="feedDescription"]');
        let feedTitleInputElement = document.querySelector('[role="feedTitle"]');

        fetch('/handler/feeds/types?localeMessage=' + window.CMSCore.locales.admin.name, {
          method: 'GET'
        }).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data1) => {
          feedsTypes = data1.outputData.feedsTypes;
          return fetch('/handler/feed/' + searchParams.getPathPart(3) + '?localeMessage=' + window.CMSCore.locales.admin.name, {method: 'GET'});
        }).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data1) => {
          feedsTypes.forEach((type, typeIndex) => {
            interactiveChoicesWebChannelsTypes.target.addItem(type.title, type.id);
          });

          feedsTypes.forEach((type, typeIndex) => {
            if (type.id === data1.outputData.feed.typeID) {
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
          return fetch('/handler/feed/' + searchParams.getPathPart(3) + '?localeMessage=' + window.CMSCore.locales.admin.name, {method: 'GET'});
        }).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data1) => {
          entriesCategories.forEach((entryCategory, entryCategoryIndex) => {
            interactiveChoicesEntriesCategories.target.addItem(entryCategory.title, entryCategory.id);
          });

          entriesCategories.forEach((entryCategory, entryCategoryIndex) => {
            if (entryCategory.id === data1.outputData.feed.entriesCategoryID) {
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
            feedDescriptionTextareaElement.setAttribute('name', 'web_channel_description_' + locale.iso639_2);
            feedTitleInputElement.setAttribute('name', 'web_channel_title_' + locale.iso639_2);
            
            let request = new Interactive('request', {
              method: 'GET',
              url: '/handler/feed/' + searchParams.getPathPart(3) + '?locale=' + locale.name + '&localeMessage=' + window.CMSCore.locales.admin.name
            });

            request.target.showingNotification = false;

            request.target.send().then((data) => {
              if (data.statusCode === 1 && data.outputData.hasOwnProperty('feed')) {
                feedDescriptionTextareaElement.value = data.outputData.feed.description;
                feedTitleInputElement.value = data.outputData.feed.title;
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
          uString.source = uString.translitToEN(true);
          uString.source = uString.source.toLowerCase();
          uString.source = uString.source.replace(/[^a-z0-9\-]/, '');

          event.target.value = uString.source;
        });

        let interactiveChoicesSelectElement = interactiveContainerElement.querySelector('select');
        interactiveChoicesSelectElement.addEventListener('change', (event) => {
          locales.forEach((locale, localeIndex) => {
            if (locale.name === event.target.value) {
              feedDescriptionTextareaElement.setAttribute('name', 'web_channel_description_' + locale.iso639_2);
              feedTitleInputElement.setAttribute('name', 'web_channel_title_' + locale.iso639_2);
              
              let request = new Interactive('request', {
                method: 'GET',
                url: '/handler/feed/' + searchParams.getPathPart(3) + '?locale=' + locale.name + '&localeMessage=' + window.CMSCore.locales.admin.name
              });
  
              request.target.showingNotification = false;
  
              request.target.send().then((data) => {
                if (data.statusCode === 1 && data.outputData.hasOwnProperty('feed')) {
                  feedDescriptionTextareaElement.value = data.outputData.feed.description;
                  feedTitleInputElement.value = data.outputData.feed.title;
                }
              });
            }
          });
        });
      } else {
        fetch('/handler/feeds/types?localeMessage=' + window.CMSCore.locales.admin.name, {
          method: 'GET'
        }).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data1) => {
          let feedsTypes = data1.outputData.feedsTypes;
          let interactiveChoicesWebChannelsTypes = new Interactive('choices');
  
          feedsTypes.forEach((type, typeIndex) => {
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

      if (searchParams.getPathPart(3) === null) {
        this.buttons.delete.target.element.style.display = 'none';
        this.buttons.save.target.element.style.display = 'flex';
      } else {
        this.buttons.delete.target.element.style.display = 'flex';
        this.buttons.save.target.element.style.display = 'flex';
      }

      let interactiveFormPanelContainer = document.querySelector('#SYSTEM_E3724126170');
      interactiveFormPanelContainer.append(this.buttons.delete.target.element);
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
}