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

export class PageEntriesCategory {
  constructor(page, params = {}) {
    this.page = page;
    
    this.buttons = {save: null, delete: null};
  }

  init() {
    let searchParams = new URLParser();
    let elementForm = document.querySelector('[data-element="main-form"]');

    let locales;
    const interactiveLocaleChoices = new Interactive('choices');
    const interactiveParentChoices = new Interactive('choices');

    fetch('/handler/locales', {method: 'GET'}).then((response) => {
      return (response.ok) ? response.json() : Promise.reject(response);
    }).then((data) => {
      locales = data.outputData.locales;
      return window.CMSCore.locales.admin.getData();
    }, (rejectionReason) => {
      this.page.showPopupNotification(rejectionReason, 0);
    }).then((localeData) => {
      const urlInputElement = document.querySelector('[data-element="input-url"]');
      const titleInputElement = document.querySelector('[data-element="input-title"]');
      const SEOTitleInputElement = document.querySelector('[data-element="input-seo-title"]');
      const descriptionTextareaElement = document.querySelector('[data-element="input-description"]');
      const SEODescriptionTextareaElement = document.querySelector('[data-element="input-seo-description"]');
      const keywordsTextareaElement = document.querySelector('[data-element="input-keywords"]');

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

        interactiveLocaleChoices.target.addItem(localeTemplate.innerHTML, localeName);
      });

      locales.forEach((locale, localeIndex) => {
        if (locale.name === window.CMSCore.locales.admin.name) {
          interactiveLocaleChoices.target.setItemSelectedIndex(localeIndex);
        }

        if (locale.name === window.CMSCore.locales.admin.name) {
          titleInputElement.setAttribute('name', 'entries_category_title_' + locale.iso639_2);
          SEOTitleInputElement.setAttribute('name', 'entries_category_seo_title_' + locale.iso639_2);
          descriptionTextareaElement.setAttribute('name', 'entries_category_description_' + locale.iso639_2);
          SEODescriptionTextareaElement.setAttribute('name', 'entries_category_seo_description_' + locale.iso639_2);
          keywordsTextareaElement.setAttribute('name', 'entries_category_keywords_' + locale.iso639_2);

          if (searchParams.getPathPart(3) != null) {
            let request = new Interactive('request', {
              method: 'GET',
              url: '/handler/entry/category/' + searchParams.getPathPart(3) + '?locale=' + locale.name
            });
    
            request.target.showingNotification = false;
    
            request.target.send().then((data) => {
              if (data.statusCode === 1 && data.outputData.hasOwnProperty('entriesCategory')) {
                titleInputElement.value = data.outputData.entriesCategory.title;
                SEOTitleInputElement.value = data.outputData.entriesCategory.SEOTitle;
                descriptionTextareaElement.value = data.outputData.entriesCategory.description;
                SEODescriptionTextareaElement.value = data.outputData.entriesCategory.SEODescription;
                keywordsTextareaElement.value = data.outputData.entriesCategory.keywords.join(', ');
              }
            });
          }
        }
      });
      interactiveLocaleChoices.assembly();

      let interactiveContainerElement = document.querySelector('[data-element="header-interactive"]');
      interactiveContainerElement.append(interactiveLocaleChoices.target.element);

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
        const titleInputElement = document.querySelector('[data-element="input-title"]');
        const SEOTitleInputElement = document.querySelector('[data-element="input-seo-title"]');
        const descriptionTextareaElement = document.querySelector('[data-element="input-description"]');
        const SEODescriptionTextareaElement = document.querySelector('[data-element="input-seo-description"]');
        const keywordsTextareaElement = document.querySelector('[data-element="input-keywords"]');
        
        locales.forEach((locale, localeIndex) => {
          if (locale.name === event.target.value) {
            titleInputElement.setAttribute('name', 'entries_category_title_' + locale.iso639_2);
            SEOTitleInputElement.setAttribute('name', 'entries_category_seo_title_' + locale.iso639_2);
            descriptionTextareaElement.setAttribute('name', 'entries_category_description_' + locale.iso639_2);
            SEODescriptionTextareaElement.setAttribute('name', 'entries_category_seo_description_' + locale.iso639_2);
            keywordsTextareaElement.setAttribute('name', 'entries_category_keywords_' + locale.iso639_2);

            if (searchParams.getPathPart(3) != null) {
              let request = new Interactive('request', {
                method: 'GET',
                url: '/handler/entry/category/' + searchParams.getPathPart(3) + '?locale=' + locale.name + '&localeMessage=' + window.CMSCore.locales.admin.name
              });
      
              request.target.showingNotification = false;
      
              request.target.send().then((data) => {
                if (data.statusCode === 1 && data.outputData.hasOwnProperty('entriesCategory')) {
                  titleInputElement.value = data.outputData.entriesCategory.title;
                  SEOTitleInputElement.value = data.outputData.entriesCategory.SEOTitle;
                  descriptionTextareaElement.value = data.outputData.entriesCategory.description;
                  SEODescriptionTextareaElement.value = data.outputData.entriesCategory.SEODescription;
                  keywordsTextareaElement.value = data.outputData.entriesCategory.keywords.join(', ');
                }
              });
            }
          }
        });
      });

      let entriesCategoryData = {}, entriesCategoriesData = {};
      
      fetch('/handler/entry/category/' + searchParams.getPathPart(3) + '?locale=' + window.CMSCore.locales.admin.name + '&localeMessage=' + window.CMSCore.locales.admin.name, {
        method: 'GET'
      }).then((response) => {
        return (response.ok) ? response.json() : Promise.reject(response);
      }).then((data1) => {
        if (data1.statusCode === 1) {
          entriesCategoryData = data1.outputData.entriesCategory;
        }

        return fetch('/handler/entry/categories' + '?locale=' + window.CMSCore.locales.admin.name + '&localeMessage=' + window.CMSCore.locales.admin.name, {method: 'GET'});
      }, (rejectionReason) => {
        this.page.showPopupNotification(rejectionReason, 0);
      }).then((response) => {
        return (response.ok) ? response.json() : Promise.reject(response);
      }).then((data1) => {
        if (data1.statusCode === 1) {
          entriesCategoriesData = data1.outputData.entriesCategories;

          interactiveParentChoices.target.addItem('', 0);

          entriesCategoriesData.forEach((entriesCategory, entriesCategoryIndex) => {
            if (entriesCategoryData.id !== entriesCategory.id) {
              interactiveParentChoices.target.addItem(entriesCategory.title, entriesCategory.id);
            }

            if (entriesCategory.id === entriesCategoryData.parentID) {
              interactiveParentChoices.target.setItemSelectedIndex(entriesCategoryIndex + 1);
            }
          });

          interactiveParentChoices.target.setName('entries_category_parent_id');
          interactiveParentChoices.assembly();

          let interactiveContainer = document.querySelector('[data-element="choice"][data-choice="parent-category"]');
          interactiveContainer.innerHTML = '';
          interactiveContainer.append(interactiveParentChoices.target.element);
        }
      }, (rejectionReason) => {
        this.page.showPopupNotification(rejectionReason, 0);
      });

      this.buttons.save = new Interactive('button');
      this.buttons.delete = new Interactive('button');

      this.buttons.save.target.setLabel(localeData.BUTTON_SAVE_LABEL);
      this.buttons.delete.target.setLabel(localeData.BUTTON_DELETE_LABEL);
      
      this.buttons.save.target.setStyle('green');
      this.buttons.delete.target.setStyle('red');

      this.buttons.save.target.setCallback((event) => {
        event.preventDefault();

        let form = new Interactive('form');
        form.target.replaceElement(elementForm);
        
        if (form.target.checkRequiredFields()) {
          let formData = new FormData(elementForm);
          let fetchLink = (searchParams.getPathPart(3) === null) ? '/handler/entry/category?localeMessage=' + window.CMSCore.locales.admin.name : '/handler/entry/category/' + searchParams.getPathPart(3) + '?localeMessage=' + window.CMSCore.locales.admin.name;
          let fetchMethod = (searchParams.getPathPart(3) === null) ? 'PUT' : 'PATCH';

          let request = new Interactive('request', {
            method: fetchMethod,
            url: fetchLink
          });
  
          request.target.data = formData;
  
          request.target.send().then((data) => {
            if (data.statusCode === 1 && searchParams.getPathPart(3) === null) {
              let entriesCategoryData = data.outputData.entriesCategory;
              window.location.href = '/admin/entriesCategory/' + entriesCategoryData.id;
            }
          });
        } else {
          this.page.showPopupNotification(rejectionReason, 0);
        }
      });

      this.buttons.delete.target.setCallback((event) => {
        event.preventDefault();

        let interactiveModal = new Interactive('modal', {
          title: localeData.MODAL_ENTRIES_CATEGORY_DELETE_TITLE,
          content: localeData.MODAL_ENTRIES_CATEGORY_DELETE_DESCRIPTION
        });
        
        interactiveModal.target.addButton(localeData.BUTTON_DELETE_LABEL, () => {
          let formData = new FormData();
          formData.append('entries_category_id', searchParams.getPathPart(3));

          let request = new Interactive('request', {
            method: 'DELETE',
            url: '/handler/entry/category/' + searchParams.getPathPart(3) + '?localeMessage=' + window.CMSCore.locales.admin.name
          });

          request.target.send().then((data) => {
            if (data.statusCode === 1) {
              window.location.href = '/admin/entriesCategories';
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

      this.buttons.save.assembly();
      this.buttons.delete.assembly();

      if (searchParams.getPathPart(3) === null) {
        this.buttons.delete.target.element.style.display = 'none';
        this.buttons.save.target.element.style.display = 'flex';
      } else {
        this.buttons.delete.target.element.style.display = 'flex';
        this.buttons.save.target.element.style.display = 'flex';
      }

      let interactiveContainer = document.querySelector('[data-element="panel"]');
      interactiveContainer.append(this.buttons.delete.target.element);
      interactiveContainer.append(this.buttons.save.target.element);
    }, (rejectionReason) => {
      this.page.showPopupNotification(rejectionReason, 0);
    });
  }
}