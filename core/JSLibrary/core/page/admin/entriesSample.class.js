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

export class PageEntriesSample {
  constructor(page, params = {}) {
    this.page = page;
    
    this.buttons = {save: null, delete: null};
  }

  init() {
    let searchParams = new URLParser();
    let elementForm = document.querySelector('[data-element="main-form"]');

    const interactiveLocaleChoices = new Interactive('choices');
    const interactiveCategoriesChoices = new Interactive('choices');
    const interactiveSortTypeChoices = new Interactive('choices');

    this.page.core.locales.admin.getData().then((localeData) => {
      let urlInputElement = document.querySelector('[data-element="input-name"]');
      let titleInputElement = document.querySelector('[data-element="input-title"]');
      let descriptionTextareaElement = document.querySelector('[data-element="input-description"]');

      this.page.core.locales.list.forEach((locale, localeIndex) => {
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

      this.page.core.locales.list.forEach((locale, localeIndex) => {
        if (locale.name === window.CMSCore.locales.admin.name) {
          interactiveLocaleChoices.target.setItemSelectedIndex(localeIndex);
        }

        if (locale.name === window.CMSCore.locales.admin.name) {
          descriptionTextareaElement.setAttribute('name', 'entries_sample_description_' + locale.iso639_2);
          titleInputElement.setAttribute('name', 'entries_sample_title_' + locale.iso639_2);

          if (searchParams.getPathPart(3) != null) {
            let request = new Interactive('request', {
              method: 'GET',
              url: `/handler/entries/sample/${searchParams.getPathPart(3)}?locale=${window.CMSCore.locales.admin.name}`
            });
    
            request.target.showingNotification = false;
            
            let entriesSampleData;
            request.target.send().then((data) => {
              if (data.statusCode == 1 && data.outputData.hasOwnProperty('entriesSample')) {
                entriesSampleData = data.outputData.entriesSample;

                descriptionTextareaElement.value = data.outputData.entriesSample.description;
                titleInputElement.value = data.outputData.entriesSample.title;
              }

            }, (rejectionReason) => {
              this.page.showPopupNotification(rejectionReason, 0);
            });
          }
        }
      });

      interactiveLocaleChoices.assembly();

      let interactiveContainerElement = document.querySelector('[data-element="header-interactive"]');
      interactiveContainerElement.append(interactiveLocaleChoices.target.element);

      let interactiveSortByChoiceMultipleContainerElement = document.querySelector('[data-element="choice"][data-choice="sort-method"]');
      if (interactiveSortByChoiceMultipleContainerElement != null) {
        let entriesSampleData;

        fetch(`/handler/entries/sample/${searchParams.getPathPart(3)}?locale=${window.CMSCore.locales.admin.name}`, {method: 'GET'}).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data) => {
          entriesSampleData = data.outputData.entriesSample;

          return fetch(`/handler/entries/sample/sorttypes?locale=${window.CMSCore.locales.admin.name}&dataType=names`, {method: 'GET'});
        }, (rejectionReason) => {
          this.page.showPopupNotification(rejectionReason, 0);
        }).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data) => {
          if (data.outputData.hasOwnProperty('types')) {
            if (data.outputData.types.length > 0) {
              data.outputData.types.forEach((type, typeIndex) => {
                let typeTitleLocaleName = `PAGE_ENTRIES_SAMPLE_SORT_TYPE_${type.name}_LABEL`;
                let typeTitle = localeData.hasOwnProperty(typeTitleLocaleName) ? localeData[typeTitleLocaleName] : '[ ??? ]';
                interactiveSortTypeChoices.target.addItem(typeTitle, type.id);
                
                console.log(entriesSampleData);
                if (typeof(entriesSampleData) != 'undefined') {
                  if (entriesSampleData.sortTypeID == type.id) {
                    interactiveSortTypeChoices.target.setItemSelectedIndex(typeIndex);
                  }
                }
              });

              interactiveSortTypeChoices.target.setName('entries_sample_sort_type_id');
              interactiveSortTypeChoices.assembly();
            }
          }
          
          interactiveSortByChoiceMultipleContainerElement.append(interactiveSortTypeChoices.target.element);
        });
      }

      let interactiveCategoriesChoiceMultipleContainerElement = document.querySelector('[data-element="choice"][data-choice="categories"]');
      if (interactiveCategoriesChoiceMultipleContainerElement != null) {
        let entriesCategories, entriesSampleCategories;
        
        fetch(`/handler/entries/categories?locale=${window.CMSCore.locales.admin.name}`, {method: 'GET'}).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data) => {
          entriesCategories = data.outputData.entriesCategories;
          
          if (entriesCategories.length > 0) {
            entriesCategories.forEach((category) => {
              interactiveCategoriesChoices.target.addItem(category.title, category.id);
            });
          }

          interactiveCategoriesChoices.target.setName('entries_sample_categories_id[]');
          interactiveCategoriesChoices.target.isDisclosed = true;
          interactiveCategoriesChoices.target.isMultiple = true;

          let sampleID = (searchParams.getPathPart(3) != null) ? searchParams.getPathPart(3) : 0;
          return fetch(`/handler/entries/sample/${sampleID}/categories?locale=${window.CMSCore.locales.admin.name}`, {method: 'GET'});
        }, (rejectionReason) => {
          this.page.showPopupNotification(rejectionReason, 0);
        }).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data) => {
          if (data.outputData.hasOwnProperty('entriesSample')) {
            if (data.outputData.entriesSample.hasOwnProperty('categories')) {
              data.outputData.entriesSample.categories.forEach((category) => {
                interactiveCategoriesChoices.target.items.forEach((item) => {
                  if (item.value == category.id) {
                    item.isSelected = true;
                  }
                });
              });
            }
          }

          interactiveCategoriesChoices.assembly();

          interactiveCategoriesChoiceMultipleContainerElement.append(interactiveCategoriesChoices.target.element);
        });
      }

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
        let entryTitleInputElement = document.querySelector('[data-element="input-title"]');
        let entryDescriptionTextareaElement = document.querySelector('[data-element="input-description"]');
        
        this.page.core.locales.list.forEach((locale, localeIndex) => {
          if (locale.name == event.target.value) {
            entryTitleInputElement.setAttribute('name', 'entries_sample_title_' + locale.iso639_2);
            entryDescriptionTextareaElement.setAttribute('name', 'entries_sample_description_' + locale.iso639_2);

            if (searchParams.getPathPart(3) != null) {
              let request = new Interactive('request', {
                method: 'GET',
                url: '/handler/entries/sample/' + searchParams.getPathPart(3) + '?locale=' + locale.name + '&localeMessage=' + window.CMSCore.locales.admin.name
              });
      
              request.target.showingNotification = false;
      
              request.target.send().then((data) => {
                if (data.statusCode == 1 && data.outputData.hasOwnProperty('entriesSample')) {
                  entryDescriptionTextareaElement.value = data.outputData.entriesSample.description;
                  entryTitleInputElement.value = data.outputData.entriesSample.title;
                }
              });
            }
          }
        });
      });

      this.buttons.save = new Interactive('button');
      this.buttons.delete = new Interactive('button');

      this.buttons.save.target.setLabel(localeData.BUTTON_SAVE_LABEL);
      this.buttons.delete.target.setLabel(localeData.BUTTON_DELETE_LABEL);
      
      this.buttons.save.target.setStyle('green');
      this.buttons.delete.target.setStyle('red');

      this.buttons.save.target.setCallback((event) => {
        event.preventDefault();

        elementForm = document.querySelector('[data-element="main-form"]');

        let form = new Interactive('form');
        form.target.replaceElement(elementForm);
        
        if (form.target.checkRequiredFields()) {
          let formData = new FormData(elementForm);
          let fetchLink = searchParams.getPathPart(3) === null
            ? '/handler/entries/sample?localeMessage=' + window.CMSCore.locales.admin.name
            : '/handler/entries/sample/' + searchParams.getPathPart(3) + '?localeMessage=' + window.CMSCore.locales.admin.name;
          let fetchMethod = searchParams.getPathPart(3) === null ? 'PUT' : 'PATCH';

          let request = new Interactive('request', {
            method: fetchMethod,
            url: fetchLink
          });
  
          request.target.data = formData;
  
          request.target.send().then((data) => {
            if (data.statusCode == 1 && searchParams.getPathPart(3) == null) {
              let entriesSampleData = data.outputData.entriesSample;
              window.location.href = '/admin/entriesSample/' + entriesSampleData.id;
            }
          });
        } else {
          this.page.showPopupNotification(localeData.FORM_REQUIRED_FIELDS_IS_EMPTY, 0);
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
          formData.append('entries_sample_id', searchParams.getPathPart(3));

          let request = new Interactive('request', {
            method: 'DELETE',
            url: '/handler/entries/sample/' + searchParams.getPathPart(3) + '?localeMessage=' + window.CMSCore.locales.admin.name
          });

          request.target.data = formData;

          request.target.send().then((data) => {
            if (data.statusCode == 1) {
              window.location.href = '/admin/entriesSamples';
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