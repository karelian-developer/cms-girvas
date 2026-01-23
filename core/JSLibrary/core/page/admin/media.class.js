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

export class PageMedia {
  static buttonIcons = {
    trash: '<svg version="1.1" class="button__icon" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve"><rect x="13.9" y="16.1" width="37.3" height="47.9"></rect><path d="M41.4,10l1-7.3L24.7,0.3l-1,7.3L10.2,5.7l-1,7.3l44.6,6.2l1-7.3L41.4,10z M25.6,7.8l0.7-5l13.8,1.9l-0.7,5L25.6,7.8z"></path></svg>',
    link: '<svg version="1.1" class="button__icon" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve"><path d="M30.7,29l4.8,4.8c1.3,1.3,1.3,3.5,0,4.9L18.3,55.9c-1.3,1.3-3.5,1.3-4.9,0l-5.3-5.3c-1.3-1.3-1.3-3.5,0-4.9l14-14L20.6,27 L4.9,42.6c-3.1,3.1-3.1,8.1,0,11.1l5.3,5.3c3.1,3.1,8.1,3.1,11.1,0l17.3-17.3c3.1-3.1,3.1-8.1,0-11.1l-5-5L30.7,29z"></path><path d="M33.4,35.1l-4.9-4.9c-1.3-1.3-1.3-3.5,0-4.9L45.7,8.1c1.3-1.3,3.5-1.3,4.9,0l5.3,5.3c1.3,1.3,1.3,3.5,0,4.9L42.8,31.4 l1.4,4.8l14.9-14.9c3.1-3.1,3.1-8.1,0-11.1l-5.3-5.3c-3.1-3.1-8.1-3.1-11.1,0L25.4,22.2c-3.1,3.1-3.1,8.1,0,11.1l5.3,5.3 c0.3,0.3,0.6,0.5,0.9,0.8L33.4,35.1z"></path></svg>',
    open: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" class="button__icon"><g id="Слой_2" data-name="Слой 2"><rect y="26.42" width="18.42" height="11.17"/><rect x="26.42" width="11.17" height="18.42"/><rect x="45.58" y="26.42" width="18.42" height="11.17"/><rect x="26.42" y="45.58" width="11.17" height="18.42"/></g></svg>'
  };

  constructor(page, params = {}) {
    this.page = page;

    this.buttons = {upload: null};
    this.localeData = null;
  }

  initMediaElement(element) {
    let buttons = {};
    let fileName, fileURL, isDirectory;

    fileName = element.getAttribute('data-file-name');
    fileURL = element.getAttribute('data-file-url');
    isDirectory = element.getAttribute('data-is-directory');

    buttons.delete = new Interactive('button');
    buttons.link = new Interactive('button');
    buttons.open = new Interactive('button');

    buttons.delete.target.setLabel(PageMedia.buttonIcons.trash);
    buttons.delete.target.setCallback((event) => {
      event.preventDefault();
      
      let interactiveModal = new Interactive('modal', {
        title: this.localeData.MODAL_MEDIA_DELETE_TITLE,
        content: this.localeData.MODAL_MEDIA_DELETE_DESCRIPTION
      });
      
      interactiveModal.target.addButton(this.localeData.BUTTON_DELETE_LABEL, () => {
        let formData = new FormData();
        formData.append('media_file_fullname', fileName);

        let request = new Interactive('request', {
          method: 'DELETE',
          url: '/handler/media?localeMessage=' + window.CMSCore.locales.admin.name
        });

        request.target.data = formData;

        request.target.send().then((data) => {
          if (data.statusCode === 1) {
            element.remove();
            interactiveModal.target.close();
          }
        });
      });

      interactiveModal.target.addButton(this.localeData.BUTTON_CANCEL_LABEL, () => {
        interactiveModal.target.close();
      });

      interactiveModal.assembly();
      document.body.appendChild(interactiveModal.target.element);
      interactiveModal.target.show();
    });

    buttons.link.target.setLabel(PageMedia.buttonIcons.link);
    buttons.link.target.setCallback((event) => {
      event.preventDefault();
      navigator.clipboard.writeText(fileURL);

      this.page.showPopupNotification(this.localeData.POPUP_SLIDE_RELATIVE_LINK_COPIED, 1);
    });

    buttons.open = new Interactive('button');
    buttons.open.target.setLabel(PageMedia.buttonIcons.open);
    buttons.open.target.setCallback((event) => {
      event.preventDefault();

      const locationURL = new URL(location.href);
      locationURL.searchParams.set('directory', fileURL);
      location.href = locationURL.toString();
    });

    buttons.delete.assembly();
    buttons.link.assembly();
    buttons.open.assembly();

    const elementControllerElement = element.querySelector('[data-role="controller-panel"]');
    if (elementControllerElement !== null) {
      elementControllerElement.appendChild(buttons.delete.target.element);
      elementControllerElement.appendChild(buttons.link.target.element);

      if (isDirectory !== null) {
        elementControllerElement.appendChild(buttons.open.target.element);
      }
    }
  }

  uploadFile(inputElement, fileIndex) {
    let formData = new FormData();
    formData.append('mediaFile', inputElement.files[fileIndex]);

    let request = new Interactive('request', {
      method: 'POST',
      url: '/handler/media?localeMessage=' + window.CMSCore.locales.admin.name
    });

    request.target.data = formData;

    request.target.send().then((data) => {
      const APIStatusCode = data?.statusCode;
      const APIOutputData = data?.outputData;
      const APIOutputDataFile = APIOutputData !== undefined
        ? APIOutputData
        : {};

      if (APIStatusCode === 1 && APIOutputData.hasOwnProperty('file')) {
        let fileName, fileURL;

        fileName = data.outputData.file.fullname;
        fileURL = data.outputData.file.url;

        let listItemElement = document.createElement('li');
        listItemElement.classList.add('media-list__item');
        listItemElement.classList.add('item');
        listItemElement.style.backgroundImage = `url('${fileURL}')`;
        listItemElement.setAttribute('data-file-name', fileName);
        listItemElement.setAttribute('data-file-url', fileURL);

        let listItemControllerContainerElement = document.createElement('div');
        listItemControllerContainerElement.classList.add('item__controller-panel');
        listItemControllerContainerElement.classList.add('controller-panel');
        listItemControllerContainerElement.setAttribute('role', 'controller-panel');

        let listItemTitleContainerElement = document.createElement('div');
        listItemTitleContainerElement.classList.add('item__title-container');

        let listItemTitleElement = document.createElement('div');
        listItemTitleElement.classList.add('item__title');
        listItemTitleElement.innerText = fileName;

        listItemElement.appendChild(listItemControllerContainerElement);
        listItemTitleContainerElement.appendChild(listItemTitleElement);
        listItemElement.appendChild(listItemTitleContainerElement);

        let mediaListElement = document.querySelector('#E9453667589');
        let mediaListItems = mediaListElement.querySelectorAll('li');
        mediaListItems[0].before(listItemElement);

        this.initMediaElement(listItemElement);
      }

      if (data.statusCode === 1 && fileIndex < (inputElement.files.length - 1)) {
        this.uploadFile(inputElement, fileIndex + 1);
      }
    });
  }

  initUploaderInput(inputElement) {
    inputElement.addEventListener('change', (event) => {
      event.preventDefault();

      if (inputElement.files.length > 0) {
        this.uploadFile(inputElement, 0);
      }
    });
  }

  init() {
    let locales;
    let interactiveContainerPagePanelElement = document.querySelector('#E8548530785');

    let mediaUploaderInput = document.querySelector('.form__input_file');
    if (mediaUploaderInput != null) {
      mediaUploaderInput.setAttribute('accept', 'image/png, image/jpeg, image/gif, image/webp, image/avif');

      this.initUploaderInput(mediaUploaderInput);

      fetch('/handler/locales', {method: 'GET'}).then((response) => {
        return (response.ok) ? response.json() : Promise.reject(response);
      }).then((data) => {
        locales = data.outputData.locales;
        return window.CMSCore.locales.admin.getData();
      }, (rejectionReason) => {
        this.page.showPopupNotification(rejectionReason, 0);
      }).then((localeData) => {
        this.localeData = localeData;

        let listElements = document.querySelectorAll('.media-list__item');
        for (let listElement of listElements) {
          this.initMediaElement(listElement);
        }

        this.buttons.upload = new Interactive('button');
        this.buttons.upload.target.setLabel(this.localeData.BUTTON_UPLOAD_LABEL);
        this.buttons.upload.target.setCallback((event) => {
          event.preventDefault();
          mediaUploaderInput.click();
        });
        this.buttons.upload.assembly();

        interactiveContainerPagePanelElement.append(this.buttons.upload.target.element);
      }, (rejectionReason) => {
        this.page.showPopupNotification(rejectionReason, 0);
      });
    }
  }
}