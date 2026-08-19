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

export class PageContentBlock {
  constructor(page, params = {}) {
    this.page = page;
    this.statusCode = this.page.getPageStatusCode()

    this.buttons = {save: null, delete: null, publish: null, unpublish: null};
  }

  init() {
    const searchParams = new URLParser();
    const elementForm = document.querySelector('[data-element="main-form"]');

    let locales;
    const interactiveLocaleChoices = new Interactive('choices');
    const interactiveTypesChoices = new Interactive('choices');
    
    this.page.core.locales.admin.getData().then((localeData) => {
      const contentTextareaElement = document.querySelector('[data-element="input-content"]');
      const descriptionTextareaElement = document.querySelector('[data-element="input-description"]');
      const titleInputElement = document.querySelector('[data-element="input-title"]');
      const nameInputElement = document.querySelector('[data-element="input-name"]');

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
          contentTextareaElement.setAttribute('name', 'content_block_content_' + locale.iso639_2);
          descriptionTextareaElement.setAttribute('name', 'content_block_description_' + locale.iso639_2);
          titleInputElement.setAttribute('name', 'content_block_title_' + locale.iso639_2);

          if (searchParams.getPathPart(3) !== null) {
            let request = new Interactive('request', {
              method: 'GET',
              url: '/handler/contentBlock/' + searchParams.getPathPart(3) + '?locale=' + locale.name
            });

            request.target.showingNotification = false;
    
            request.target.send().then((data) => {
              if (data.statusCode === 1) {
                contentTextareaElement.value = data.outputData.contentBlock.content;
                descriptionTextareaElement.value = data.outputData.contentBlock.description;
                titleInputElement.value = data.outputData.contentBlock.title;
              }
            });
          }
        }
      });

      interactiveLocaleChoices.assembly();

      let interactiveHeaderContainerElement = document.querySelector('[data-element="header-interactive"]');
      interactiveHeaderContainerElement.append(interactiveLocaleChoices.target.element);

      let interactiveChoicesSelectElement = interactiveHeaderContainerElement.querySelector('select');
      interactiveChoicesSelectElement.addEventListener('change', (event) => {
        this.page.core.locales.list.forEach((locale, localeIndex) => {
          if (locale.name === event.target.value) {
            contentTextareaElement.setAttribute('name', 'content_block_content_' + locale.iso639_2);
            descriptionTextareaElement.setAttribute('name', 'content_block_description_' + locale.iso639_2);
            titleInputElement.setAttribute('name', 'content_block_title_' + locale.iso639_2);
            
            if (searchParams.getPathPart(3) !== null) {
              let request = new Interactive('request', {
                method: 'GET',
                url: '/handler/contentBlock/' + searchParams.getPathPart(3) + '?locale=' + locale.name
              });
      
              request.target.send().then((data) => {
                if (data.statusCode === 1) {
                  contentTextareaElement.value = data.outputData.contentBlock.content;
                  descriptionTextareaElement.value = data.outputData.contentBlock.description;
                  titleInputElement.value = data.outputData.contentBlock.title;
                }
              });
            }
          }
        });
      });

      this.buttons.save = new Interactive('button');
      this.buttons.delete = new Interactive('button');

      this.buttons.delete.target.setLabel(localeData.BUTTON_DELETE_LABEL);
      this.buttons.save.target.setLabel(localeData.BUTTON_SAVE_LABEL);

      this.buttons.delete.target.setStyle('red');
      this.buttons.save.target.setStyle('green');

      this.buttons.save.target.setCallback((event) => {
        event.preventDefault();

        let form = new Interactive('form');
        form.target.replaceElement(elementForm);
        
        if (form.target.checkRequiredFields()) {
          const formData = new FormData(elementForm);

          let request = new Interactive('request', {
            method: searchParams.getPathPart(3) === null || this.statusCode === 404
              ? 'PUT'
              : 'PATCH',
            url: '/handler/contentBlock?localeMessage=' + window.CMSCore.locales.admin.name
          });
  
          request.target.data = formData;
  
          request.target.send().then((data) => {
            if (data.statusCode === 1 && searchParams.getPathPart(3) === null) {
              if (data.outputData.hasOwnProperty('contentBlock')) {
                let contentBlockData = data.outputData.contentBlock;
                window.location.href = '/admin/contentBlock/' + contentBlockData.id;
              }
            }
          });
        } else {
          this.page.showPopupNotification(rejectionReason, 0);
        }
      });

      this.buttons.delete.target.setCallback((event) => {
        event.preventDefault();

        let interactiveModal = new Interactive('modal', {
          title: localeData.MODAL_CONTENT_BLOCK_DELETE_TITLE,
          content: localeData.MODAL_CONTENT_BLOCK_DELETE_DESCRIPTION
        });
        
        interactiveModal.target.addButton(localeData.BUTTON_DELETE_LABEL, () => {
          let formData = new FormData();
          formData.append('content_block_id', searchParams.getPathPart(3));

          let request = new Interactive('request', {
            method: 'DELETE',
            url: '/handler/contentBlock/' + searchParams.getPathPart(3) + '?localeMessage=' + window.CMSCore.locales.admin.name,
          });

          request.target.data = formData;

          request.target.send().then((data) => {
            if (data.statusCode === 1) {
              window.location.href = '/admin/contentBlocks';
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
        const request = new Interactive('request', {
          method: 'GET',
          url: '/handler/contentBlocks/types' + '?locale=' + window.CMSCore.locales.admin.name + '&localeMessage=' + window.CMSCore.locales.admin.name,
        });

        request.target.showingNotification = false;

        request.target.send().then((responseContentBlocksTypes) => {
          if (responseContentBlocksTypes.statusCode === 1 && responseContentBlocksTypes.outputData.hasOwnProperty('contentBlocksTypes')) {
            const contentBlocksTypes = responseContentBlocksTypes.outputData.contentBlocksTypes;
            
            contentBlocksTypes.forEach((contentBlocksType, contentBlocksTypeIndex) => {
              interactiveTypesChoices.target.addItem(contentBlocksType.title, contentBlocksType.id);
            });
            
            interactiveTypesChoices.target.setName('content_block_type_id');
            interactiveTypesChoices.assembly();
            
            const interactiveContainer = document.querySelector('[data-element="choice"][data-choice="type"]');
            interactiveContainer.append(interactiveTypesChoices.target.element);
          }
        });

        this.buttons.delete.target.element.style.display = 'none';
        this.buttons.save.target.element.style.display = 'flex';
      } else {
        let contentBlockData;

        fetch('/handler/contentBlock/' + searchParams.getPathPart(3) + '?localeMessage=' + window.CMSCore.locales.admin.name, {
          method: 'GET'
        }).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data1) => {
          if (data1.statusCode === 1) {
            contentBlockData = data1.outputData.contentBlock;

            this.buttons.delete.target.element.style.display = 'flex';
            this.buttons.save.target.element.style.display = 'flex';
          } else {
            this.buttons.delete.target.element.style.display = 'none';
            this.buttons.save.target.element.style.display = 'flex';
          }
          
          return fetch('/handler/contentBlocks/types' + '?locale=' + window.CMSCore.locales.admin.name + '&localeMessage=' + window.CMSCore.locales.admin.name, {method: 'GET'});
        }, (rejectionReason) => {
          this.page.showPopupNotification(rejectionReason, 0);
        }).then((response) => {
          return (response.ok) ? response.json() : Promise.reject(response);
        }).then((data1) => {
          if (data1.statusCode === 1) {
            let contentBlocksTypes = data1.outputData.contentBlocksTypes;
            
            contentBlocksTypes.forEach((contentBlockType, index) => {
              interactiveTypesChoices.target.addItem(contentBlockType.title, contentBlockType.id);
            });

            if (this.statusCode !== 404) {
              contentBlocksTypes.forEach((contentBlockType, index) => {
                if (contentBlockType.id === contentBlockData.typeID) {
                  interactiveTypesChoices.target.setItemSelectedIndex(index);
                }
              });
            }
            
            interactiveTypesChoices.target.setName('content_block_type_id');
            interactiveTypesChoices.assembly();
    
            const interactiveContainer = document.querySelector('[data-element="choice"][data-choice="type"]');
            interactiveContainer.append(interactiveTypesChoices.target.element);
          }
        }, (rejectionReason) => {
          this.page.showPopupNotification(rejectionReason, 0);
        });
      }

      let interactiveFooterContainer = document.querySelector('[data-element="panel"]');
      interactiveFooterContainer.append(this.buttons.delete.target.element);
      interactiveFooterContainer.append(this.buttons.save.target.element);
    });
  }
}