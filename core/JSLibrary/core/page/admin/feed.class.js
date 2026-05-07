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

export class PageFeed {
  constructor(page, params = {}) {
    this.page = page;

    this.buttons = {save: null, delete: null};
  }

  init() {
    let searchParams = new URLParser(), locales;
    let elementForm = document.querySelector('[data-element="main-form"]');

    const interactiveLocalesChoices = new Interactive('choices');
    const interactiveChoicesEntriesCategories = new Interactive('choices');

    fetch('/handler/locales', {method: 'GET'}).then((response) => {
      return (response.ok) ? response.json() : Promise.reject(response);
    }).then((data) => {
      locales = data.outputData.locales;

      return window.CMSCore.locales.admin.getData();
    }, (rejectionReason) => {
      this.page.showPopupNotification(rejectionReason, 0);
    }).then((localeData) => {
      const urlInputElement = document.querySelector('[data-element="input-url"]');

      this.buttons.save = new Interactive('button');
      this.buttons.delete = new Interactive('button');

      this.buttons.save.target.setLabel(localeData.BUTTON_SAVE_LABEL);
      this.buttons.delete.target.setLabel(localeData.BUTTON_DELETE_LABEL);
      
      this.buttons.save.target.setStyle('green');
      this.buttons.delete.target.setStyle('red');

      this.buttons.delete.target.setCallback((event) => {
        event.preventDefault();
        
        let interactiveModal = new Interactive('modal', {
          title: localeData.MODAL_FEED_DELETE_TITLE,
          content: localeData.MODAL_FEED_DELETE_DESCRIPTION
        });
        
        interactiveModal.target.addButton(localeData.BUTTON_DELETE_LABEL, () => {
          let formData = new FormData();
          formData.append('feed_id', searchParams.getPathPart(3));
          
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
          this.page.showPopupNotification(localeData.FORM_REQUIRED_FIELDS_IS_EMPTY, 0);
        }
      });

      this.buttons.save.assembly();
      this.buttons.delete.assembly();

      if (searchParams.getPathPart(3) !== null) {
        let feedsTypes;
        let interactiveChoicesWebChannelsTypes = new Interactive('choices');
        let feedTitleInputElement = document.querySelector('[data-element="input-title"]');
        let feedDescriptionTextareaElement = document.querySelector('[data-element="input-description"]');

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

          interactiveChoicesWebChannelsTypes.target.setName('feed_type_id');
          interactiveChoicesWebChannelsTypes.assembly();

          document.querySelector('[data-element="choice"][data-choice="specification"]').append(interactiveChoicesWebChannelsTypes.target.element);
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

          interactiveChoicesEntriesCategories.target.setName('feed_entries_category_id');
          interactiveChoicesEntriesCategories.assembly();

          document.querySelector('[data-element="choice"][data-choice="category"]').append(interactiveChoicesEntriesCategories.target.element);
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
            feedDescriptionTextareaElement.setAttribute('name', 'feed_description_' + locale.iso639_2);
            feedTitleInputElement.setAttribute('name', 'feed_title_' + locale.iso639_2);
            
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

        const interactiveContainerElement = document.querySelector('[data-element="header-interactive"]');
        interactiveContainerElement.append(interactiveLocalesChoices.target.element);

        urlInputElement.addEventListener('input', (event) => {
          let oldValue = event.target.value;
          let cursorPos = event.target.selectionStart;

          let utils = new Utils();
          let uString = utils.createString(oldValue);
          uString.source = uString.translitToEN(true);
          uString.source = uString.source.toLowerCase();
          uString.source = uString.source.replace(/[^a-z0-9\-]/g, '');

          let newValue = uString.source;

          if (oldValue === newValue) return;

          if (Math.abs(newValue.length - oldValue.length) > 1) {
            event.target.value = newValue;
            event.target.setSelectionRange(newValue.length, newValue.length);

            return;
          }

          let removedBefore = 0;
          for (let i = 0; i < cursorPos; i++) {
            if (!/[a-z0-9\-]/.test(oldValue[i].toLowerCase())) {
              removedBefore++;
            }
          }

          event.target.value = newValue;

          let newCursorPos = cursorPos - removedBefore;
          
          if (newValue.length >= oldValue.length) {
            newCursorPos++;
          }
          
          if (newCursorPos < 0) newCursorPos = 0;
          if (newCursorPos > newValue.length) newCursorPos = newValue.length;
          
          event.target.setSelectionRange(newCursorPos, newCursorPos);
        });

        let interactiveChoicesSelectElement = interactiveContainerElement.querySelector('select');
        interactiveChoicesSelectElement.addEventListener('change', (event) => {
          locales.forEach((locale, localeIndex) => {
            if (locale.name === event.target.value) {
              feedDescriptionTextareaElement.setAttribute('name', 'feed_description_' + locale.iso639_2);
              feedTitleInputElement.setAttribute('name', 'feed_title_' + locale.iso639_2);
              
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
  
          interactiveChoicesWebChannelsTypes.target.setName('feed_type_id');
          interactiveChoicesWebChannelsTypes.assembly();
  
          document.querySelector('[data-element="choice"][data-choice="specification"]').append(interactiveChoicesWebChannelsTypes.target.element);
  
          return fetch('/handler/entries/categories?localeMessage=' + window.CMSCore.locales.admin.name, {method: 'GET'});
        }).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data1) => {
          let entriesCategories = data1.outputData.entriesCategories;
          let interactiveChoicesEntriesCategories = new Interactive('choices');
  
          entriesCategories.forEach((entryCategory, entryCategoryIndex) => {
            interactiveChoicesEntriesCategories.target.addItem(entryCategory.title, entryCategory.id);
          });
  
          interactiveChoicesEntriesCategories.target.setName('feed_entries_category_id');
          interactiveChoicesEntriesCategories.assembly();
  
          document.querySelector('[data-element="choice"][data-choice="category"]').append(interactiveChoicesEntriesCategories.target.element);
        });
      }

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