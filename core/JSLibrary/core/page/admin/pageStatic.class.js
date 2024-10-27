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

export class PagePageStatic {
  constructor(page, params = {}) {
    this.page = page;

    this.buttons = {save: null, delete: null, publish: null, unpublish: null};
  }

  init() {
    let searchParams = new URLParser(), locales;

    let elementForm = document.querySelector('.form_page-static');
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
      let contentTextareaElement = document.querySelector('[role="pageStaticContent"]');
      let descriptionTextareaElement = document.querySelector('[role="pageStaticDescription"]');
      let titleInputElement = document.querySelector('[role="pageStaticTitle"]');
      let keywordsInputElement = document.querySelector('[role="pageStaticKeywords"]');
      let urlInputElement = document.querySelector('[role="pageStaticURL"]');

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
          contentTextareaElement.setAttribute('name', 'page_static_content_' + locale.iso639_2);
          descriptionTextareaElement.setAttribute('name', 'page_static_description_' + locale.iso639_2);
          titleInputElement.setAttribute('name', 'page_static_title_' + locale.iso639_2);
          keywordsInputElement.setAttribute('name', 'page_static_keywords_' + locale.iso639_2);
            
          if (searchParams.getPathPart(3) != null) {
            let request = new Interactive('request', {
              method: 'GET',
              url: '/handler/pageStatic/' + searchParams.getPathPart(3) + '?locale=' + locale.name + '&localeMessage=' + window.CMSCore.locales.admin.name
            });

            request.target.showingNotification = false;
  
            request.target.send().then((data) => {
              if (data.statusCode == 1 && data.outputData.hasOwnProperty('pageStatic')) {
                contentTextareaElement.value = data.outputData.pageStatic.content;
                descriptionTextareaElement.value = data.outputData.pageStatic.description;
                titleInputElement.value = data.outputData.pageStatic.title;
                keywordsInputElement.value = data.outputData.pageStatic.keywords.join(', ');
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
            contentTextareaElement.setAttribute('name', 'page_static_content_' + locale.iso639_2);
            descriptionTextareaElement.setAttribute('name', 'page_static_description_' + locale.iso639_2);
            titleInputElement.setAttribute('name', 'page_static_title_' + locale.iso639_2);
            keywordsInputElement.setAttribute('name', 'page_static_keywords_' + locale.iso639_2);
            
            if (searchParams.getPathPart(3) != null) {
              let request = new Interactive('request', {
                method: 'GET',
                url: '/handler/pageStatic/' + searchParams.getPathPart(3) + '?locale=' + locale.name + '&localeMessage=' + window.CMSCore.locales.admin.name
              });

              request.target.showingNotification = false;
    
              request.target.send().then((data) => {
                if (data.statusCode == 1 && data.outputData.hasOwnProperty('pageStatic')) {
                  contentTextareaElement.value = data.outputData.pageStatic.content;
                  descriptionTextareaElement.value = data.outputData.pageStatic.description;
                  titleInputElement.value = data.outputData.pageStatic.title;
                  keywordsInputElement.value = data.outputData.pageStatic.keywords.join(', ');
                }
              });
            }
          }
        });
      });

      this.buttons.save = new Interactive('button');
      this.buttons.save.target.setLabel('Сохранить');
      this.buttons.save.target.setCallback((event) => {
        event.preventDefault();
        
        let form = new Interactive('form');
        form.target.replaceElement(elementForm);

        if (form.target.checkRequiredFields()) {
          let formData = new FormData(elementForm);

          let request = new Interactive('request', {
            method: (searchParams.getPathPart(3) == null) ? 'PUT' : 'PATCH',
            url: '/handler/pageStatic?localeMessage=' + window.CMSCore.locales.admin.name
          });

          request.target.data = formData;

          request.target.send().then((data) => {
            if (data.statusCode == 1 && searchParams.getPathPart(3) == null) {
              if (data.outputData.hasOwnProperty('pageStatic')) {
                let pageData = data.outputData.pageStatic;
                window.location.href = '/admin/page/' + pageData.id;
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
          title: localeData.MODAL_PAGE_DELETE_TITLE,
          content: localeData.MODAL_PAGE_DELETE_DESCRIPTION
        });
        
        interactiveModal.target.addButton(localeData.BUTTON_DELETE_LABEL, () => {
          let formData = new FormData();
          formData.append('page_static_id', searchParams.getPathPart(3));

          let request = new Interactive('request', {
            method: 'DELETE',
            url: '/handler/pageStatic/' + searchParams.getPathPart(3) + '?localeMessage=' + window.CMSCore.locales.admin.name
          });

          request.target.data = formData;

          request.target.send().then((data) => {
            if (data.statusCode == 1) {
              window.location.href = '/admin/pages';
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
        formData.append('page_static_id', searchParams.getPathPart(3));
        formData.append('page_static_is_published', 1);

        let request = new Interactive('request', {
          method: 'PATCH',
          url: '/handler/pageStatic/' + searchParams.getPathPart(3) + '?localeMessage=' + window.CMSCore.locales.admin.name
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
        formData.append('page_static_id', searchParams.getPathPart(3));
        formData.append('page_static_is_published', 0);

        let request = new Interactive('request', {
          method: 'PATCH',
          url: '/handler/pageStatic/' + searchParams.getPathPart(3) + '?localeMessage=' + window.CMSCore.locales.admin.name
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
        previewFormElement.classList.add('form-page-static-preview');

        let previewFormInputFileElement = document.createElement('input');
        previewFormInputFileElement.setAttribute('type', 'file');
        previewFormInputFileElement.setAttribute('name', 'page_static_preview');
        previewFormInputFileElement.style.display = 'none';

        previewFormInputFileElement.addEventListener('change', (event) => {
          event.preventDefault();

          let file = event.target.files[0], fileReader = new FileReader();

          if (!fileReader) {
            console.error(localeData.REPORT_JS_CMSCORE_ERROR_FILEREADER_IS_NOT_SUPPORTED);
            return;
          }

          if (event.target.files.length == 0) {
            console.error(localeData.REPORT_JS_CMSCORE_ERROR_IMAGES_WHERE_NOT_LOADED);
            return;
          }

          fileReader.onload = (event) => {
            let imageElement = document.createElement('img');
            imageElement.setAttribute('src', fileReader.result);
            imageElement.style.width = '100%';
            previewImageContainerElement.innerHTML = '';
            previewImageContainerElement.appendChild(imageElement);
    
            let formData = new FormData();
            formData.append('page_static_event_save', true);
            formData.append('page_static_id', searchParams.getPathPart(3));
            formData.append('page_static_preview', fileReader.result);

            let request = new Interactive('request', {
              method: 'PATCH',
              url: '/handler/pageStatic?localeMessage=' + window.CMSCore.locales.admin.name
            });
    
            request.target.data = formData;
    
            request.target.send().then((data) => {
              if (data.statusCode == 1) {
                this.buttons.unpublish.target.element.style.display = 'none';
                this.buttons.publish.target.element.style.display = 'flex';
              }
            });
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
        previewImageContainerElement.classList.add('form-page-static-preview__container-image');

        fetch('/handler/pageStatic/' + searchParams.getPathPart(3) + '?localeMessage=' + window.CMSCore.locales.admin.name, {
          method: 'GET'
        }).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data1) => {
          if (data1.statusCode == 1) {
            let pageData = data1.outputData.pageStatic;
            
            if (pageData.previewURL != '') {
              let imageElement = document.createElement('img');
              imageElement.setAttribute('src', pageData.previewURL);
              imageElement.style.width = '100%';

              previewImageContainerElement.innerHTML = '';
              previewImageContainerElement.appendChild(imageElement);
            }

            previewFormElement.appendChild(previewFormInputFileElement);
            previewFormElement.appendChild(interactiveButtonPreviewUpload.target.element);
            previewBlockContentContainerElement.appendChild(previewImageContainerElement);
            previewBlockContentContainerElement.appendChild(previewFormElement);

            this.buttons.unpublish.target.element.style.display = (pageData.isPublished) ? 'flex' : 'none';
            this.buttons.publish.target.element.style.display = (pageData.isPublished) ? 'none' : 'flex';
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
        });
      }

      let interactiveContainer = document.querySelector('#SYSTEM_E3724126170');
      interactiveContainer.append(this.buttons.delete.target.element);
      interactiveContainer.append(this.buttons.unpublish.target.element);
      interactiveContainer.append(this.buttons.publish.target.element);
      interactiveContainer.append(this.buttons.save.target.element);
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