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

export class PageEntry {
  constructor(page, params = {}) {
    this.page = page;

    this.buttons = {save: null, delete: null, publish: null, unpublish: null};
  }

  init() {
    let searchParams = new URLParser();
    let elementForm = document.querySelector('.form_entry');

    let locales;
    let interactiveLocaleChoices = new Interactive('choices');
    let interactiveCategoriesChoices = new Interactive('choices');
    
    fetch('/handler/locales', {method: 'GET'}).then((response) => {
      return (response.ok) ? response.json() : Promise.reject(response);
    }).then((data) => {
      locales = data.outputData.locales;
      return window.CMSCore.locales.admin.getData();
    }).then((localeData) => {
      let contentTextareaElement = document.querySelector('[role="entryContent"]');
      let descriptionTextareaElement = document.querySelector('[role="entryDescription"]');
      let titleInputElement = document.querySelector('[role="entryTitle"]');
      let keywordsInputElement = document.querySelector('[role="entryKeywords"]');
      let urlInputElement = document.querySelector('[role="entryURL"]');

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
          contentTextareaElement.setAttribute('name', 'entry_content_' + locale.iso639_2);
          descriptionTextareaElement.setAttribute('name', 'entry_description_' + locale.iso639_2);
          titleInputElement.setAttribute('name', 'entry_title_' + locale.iso639_2);
          keywordsInputElement.setAttribute('name', 'entry_keywords_' + locale.iso639_2);

          if (searchParams.getPathPart(3) != null) {
            let request = new Interactive('request', {
              method: 'GET',
              url: '/handler/entry/' + searchParams.getPathPart(3) + '?locale=' + locale.name
            });

            request.target.showingNotification = false;
    
            request.target.send().then((data) => {
              if (data.statusCode == 1) {
                contentTextareaElement.value = data.outputData.entry.content;
                descriptionTextareaElement.value = data.outputData.entry.description;
                titleInputElement.value = data.outputData.entry.title;
                keywordsInputElement.value = data.outputData.entry.keywords.join(', ');
              }
            });
          }
        }
      });

      interactiveLocaleChoices.assembly();

      let interactiveContainerElement = document.querySelector('#E8548530785');
      interactiveContainerElement.append(interactiveLocaleChoices.target.element);

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
            contentTextareaElement.setAttribute('name', 'entry_content_' + locale.iso639_2);
            descriptionTextareaElement.setAttribute('name', 'entry_description_' + locale.iso639_2);
            titleInputElement.setAttribute('name', 'entry_title_' + locale.iso639_2);
            keywordsInputElement.setAttribute('name', 'entry_keywords_' + locale.iso639_2);
            
            if (searchParams.getPathPart(3) != null) {
              let request = new Interactive('request', {
                method: 'GET',
                url: '/handler/entry/' + searchParams.getPathPart(3) + '?locale=' + locale.name
              });
      
              request.target.send().then((data) => {
                if (data.statusCode == 1) {
                  contentTextareaElement.value = data.outputData.entry.content;
                  descriptionTextareaElement.value = data.outputData.entry.description;
                  titleInputElement.value = data.outputData.entry.title;
                  keywordsInputElement.value = data.outputData.entry.keywords.join(', ');
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
            method: (searchParams.getPathPart(3) == null) ? 'PUT' : 'PATCH',
            url: '/handler/entry?localeMessage=' + window.CMSCore.locales.admin.name
          });
  
          request.target.data = formData;
  
          request.target.send().then((data) => {
            if (data.statusCode == 1 && searchParams.getPathPart(3) == null) {
              if (data.outputData.hasOwnProperty('entry')) {
                let entryData = data.outputData.entry;
                window.location.href = '/admin/entry/' + entryData.id;
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

      this.buttons.delete = new Interactive('button');
      this.buttons.delete.target.setLabel(localeData.BUTTON_DELETE_LABEL);
      this.buttons.delete.target.setCallback((event) => {
        event.preventDefault();

        let interactiveModal = new Interactive('modal', {
          title: localeData.MODAL_ENTRY_DELETE_TITLE,
          content: localeData.MODAL_ENTRY_DELETE_DESCRIPTION
        });
        
        interactiveModal.target.addButton(localeData.BUTTON_DELETE_LABEL, () => {
          let formData = new FormData();
          formData.append('entry_id', searchParams.getPathPart(3));

          let request = new Interactive('request', {
            method: 'DELETE',
            url: '/handler/entry/' + searchParams.getPathPart(3) + '?localeMessage=' + window.CMSCore.locales.admin.name,
          });

          request.target.data = formData;

          request.target.send().then((data) => {
            if (data.statusCode == 1) {
              window.location.href = '/admin/entries';
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

      this.buttons.publish = new Interactive('button');
      this.buttons.publish.target.setLabel(localeData.BUTTON_PUBLISH_LABEL);
      this.buttons.publish.target.setCallback((event) => {
        event.preventDefault();

        let formData = new FormData();
        formData.append('entry_id', searchParams.getPathPart(3));
        formData.append('entry_is_published', 1);

        let request = new Interactive('request', {
          method: 'PATCH',
          url: '/handler/entry/' + searchParams.getPathPart(3) + '?localeMessage=' + window.CMSCore.locales.admin.name,
        });

        request.target.data = formData;

        request.target.send().then((data) => {
          if (data.statusCode == 1) {
            this.buttons.unpublish.target.element.style.display = 'flex';
            this.buttons.publish.target.element.style.display = 'none';
          }
        });
      });
      this.buttons.publish.assembly();

      this.buttons.unpublish = new Interactive('button');
      this.buttons.unpublish.target.setLabel(localeData.BUTTON_UNPUBLISH_LABEL);
      this.buttons.unpublish.target.setCallback((event) => {
        event.preventDefault();

        let formData = new FormData();
        formData.append('entry_id', searchParams.getPathPart(3));
        formData.append('entry_is_published', 0);

        let request = new Interactive('request', {
          method: 'PATCH',
          url: '/handler/entry/' + searchParams.getPathPart(3) + '?localeMessage=' + window.CMSCore.locales.admin.name,
        });

        request.target.data = formData;

        request.target.send().then((data) => {
          if (data.statusCode == 1) {
            this.buttons.unpublish.target.element.style.display = 'none';
            this.buttons.publish.target.element.style.display = 'flex';
          }
        });
      });
      this.buttons.unpublish.assembly();

      if (searchParams.getPathPart(3) == null) {
        let request = new Interactive('request', {
          method: 'GET',
          url: '/handler/entry/categories' + '?locale=' + window.CMSCore.locales.admin.name + '&localeMessage=' + window.CMSCore.locales.admin.name,
        });

        request.target.showingNotification = false;

        request.target.send().then((data) => {
          if (data.statusCode == 1 && data.outputData.hasOwnProperty('entriesCategories')) {
            let entriesCategories = data.outputData.entriesCategories;
            
            entriesCategories.forEach((entriesCategory, entriesCategoryIndex) => {
              interactiveCategoriesChoices.target.addItem(entriesCategory.title, entriesCategory.id);
            });
            
            interactiveCategoriesChoices.target.setName('entry_category_id');
            interactiveCategoriesChoices.assembly();
    
            let interactiveContainer = document.querySelector('#TC6474389611');
            interactiveContainer.append(interactiveCategoriesChoices.target.element);
          }
        });

        this.buttons.unpublish.target.element.style.display = 'none';
        this.buttons.publish.target.element.style.display = 'none';
        this.buttons.delete.target.element.style.display = 'none';
        this.buttons.save.target.element.style.display = 'flex';
      } else {
        let interactiveButtonPreviewUpload = new Interactive('button');

        let previewBlockElement = document.querySelector('#SYSTEM_E3754926184');
        let previewBlockContentContainerElement = previewBlockElement.querySelector('.page-aside__block-content');
        
        let previewFormElement = document.createElement('form');
        previewFormElement.setAttribute('formmethod', 'PATCH');
        previewFormElement.classList.add('form');
        previewFormElement.classList.add('form-entry-preview');

        let previewFormInputFileElement = document.createElement('input');
        previewFormInputFileElement.setAttribute('type', 'file');
        previewFormInputFileElement.setAttribute('name', 'entry_preview');
        previewFormInputFileElement.style.display = 'none';

        previewFormInputFileElement.addEventListener('change', (event) => {
          event.preventDefault();

          let file = event.target.files[0], fileReader = new FileReader();

          if (!fileReader) {
            console.error(`[CMSCore] ${localeData.REPORT_JS_CMSCORE_ERROR_FILEREADER_IS_NOT_SUPPORTED}.`);
            return;
          }

          if (event.target.files.length == 0) {
            console.error(`[CMSCore] ${localeData.REPORT_JS_CMSCORE_ERROR_IMAGES_WHERE_NOT_LOADED}.`);
            return;
          }

          fileReader.onload = (event) => {
            let imageElement = document.createElement('img');
            imageElement.setAttribute('src', fileReader.result);
            imageElement.style.width = '100%';
            previewImageContainerElement.innerHTML = '';
            previewImageContainerElement.appendChild(imageElement);
    
            let formData = new FormData();
            formData.append('entry_event_save', true);
            formData.append('entry_id', searchParams.getPathPart(3));
            formData.append('entry_preview', fileReader.result);

            let request = new Interactive('request', {
              method: 'PATCH',
              url: '/handler/entry?localeMessage=' + window.CMSCore.locales.admin.name
            });
    
            request.target.data = formData;
            request.target.send();
          };

          fileReader.onerror = (event) => {
            console.error(fileReader.result);
          };

          fileReader.readAsDataURL(file);
        });

        interactiveButtonPreviewUpload.target.setLabel(localeData.BUTTON_UPLOAD_COVER_LABEL);
        interactiveButtonPreviewUpload.target.setCallback((event) => {
          event.preventDefault();
          previewFormInputFileElement.click();
        });
        interactiveButtonPreviewUpload.assembly();

        let previewImageContainerElement = document.createElement('div');
        previewImageContainerElement.classList.add('form-entry-preview__container-image');

        let entryData;

        fetch('/handler/entry/' + searchParams.getPathPart(3) + '?localeMessage=' + window.CMSCore.locales.admin.name, {
          method: 'GET'
        }).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data1) => {
          if (data1.statusCode == 1) {
            entryData = data1.outputData.entry;
            
            if (entryData.previewURL != '') {
              let imageElement = document.createElement('img');
              imageElement.setAttribute('src', entryData.previewURL);
              imageElement.style.width = '100%';

              previewImageContainerElement.innerHTML = '';
              previewImageContainerElement.appendChild(imageElement);
            }

            previewFormElement.appendChild(previewFormInputFileElement);
            previewFormElement.appendChild(interactiveButtonPreviewUpload.target.element);
            previewBlockContentContainerElement.appendChild(previewImageContainerElement);
            previewBlockContentContainerElement.appendChild(previewFormElement);

            this.buttons.unpublish.target.element.style.display = (entryData.isPublished) ? 'flex' : 'none';
            this.buttons.publish.target.element.style.display = (entryData.isPublished) ? 'none' : 'flex';
            this.buttons.delete.target.element.style.display = 'flex';
            this.buttons.save.target.element.style.display = 'flex';
          } else {
            this.buttons.unpublish.target.element.style.display = 'none';
            this.buttons.publish.target.element.style.display = 'none';
            this.buttons.delete.target.element.style.display = 'none';
            this.buttons.save.target.element.style.display = 'flex';
          }

          let interactiveNotificationLoading = new Interactive('notification');
          interactiveNotificationLoading.target.isPopup = true;
          interactiveNotificationLoading.target.setStatusCode(data1.statusCode);
          interactiveNotificationLoading.target.setContent(data1.message);
          interactiveNotificationLoading.target.assembly();
          interactiveNotificationLoading.target.show();
          
          return fetch('/handler/entry/categories' + '?locale=' + window.CMSCore.locales.admin.name + '&localeMessage=' + window.CMSCore.locales.admin.name, {method: 'GET'});
        }).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data1) => {
          if (data1.statusCode == 1) {
            let entriesCategories = data1.outputData.entriesCategories;
            
            entriesCategories.forEach((entriesCategory, entriesCategoryIndex) => {
              interactiveCategoriesChoices.target.addItem(entriesCategory.title, entriesCategory.id);
            });

            entriesCategories.forEach((entriesCategory, entriesCategoryIndex) => {
              if (entriesCategory.id == entryData.categoryID) {
                interactiveCategoriesChoices.target.setItemSelectedIndex(entriesCategoryIndex);
              }
            });
            
            interactiveCategoriesChoices.target.setName('entry_category_id');
            interactiveCategoriesChoices.assembly();
    
            let interactiveContainer = document.querySelector('#TC6474389611');
            interactiveContainer.append(interactiveCategoriesChoices.target.element);
          }
        });
      }

      let interactiveContainer = document.querySelector('#SYSTEM_E3724126170');
      interactiveContainer.append(this.buttons.delete.target.element);
      interactiveContainer.append(this.buttons.unpublish.target.element);
      interactiveContainer.append(this.buttons.publish.target.element);
      interactiveContainer.append(this.buttons.save.target.element);
    });
  }
}