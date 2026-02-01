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
  constructor(page, params = {}) {
    this.page = page;
    this.icons = {};

    this.buttons = {upload: null};
    this.localeData = null;
  }

  initMediaElement(element) {
    let buttons = {};
    let fileName, fileURL, fileExtension, isDirectory;

    fileName = element.getAttribute('data-file-name');
    fileURL = element.getAttribute('data-file-url');
    fileExtension = element.getAttribute('data-file-extension');
    isDirectory = element.getAttribute('data-is-directory');

    buttons.delete = new Interactive('button');
    buttons.link = new Interactive('button');
    buttons.open = new Interactive('button');
    buttons.edit = new Interactive('button');

    buttons.delete.target.setLabel(this.icons.trash);
    buttons.delete.target.setCallback((event) => {
      event.preventDefault();
      
      let interactiveModal = new Interactive('modal', {
        title: this.localeData.MODAL_MEDIA_DELETE_TITLE,
        content: this.localeData.MODAL_MEDIA_DELETE_DESCRIPTION
      });
      
      interactiveModal.target.addButton(this.localeData.BUTTON_DELETE_LABEL, () => {
        let formData = new FormData();
        formData.append('file_fullname', fileName);

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

    buttons.link.target.setLabel(this.icons.link);
    buttons.link.target.setCallback((event) => {
      event.preventDefault();
      navigator.clipboard.writeText(fileURL);

      this.page.showPopupNotification(this.localeData.POPUP_SLIDE_RELATIVE_LINK_COPIED, 1);
    });

    buttons.open.target.setLabel(this.icons.target);
    buttons.open.target.setCallback((event) => {
      event.preventDefault();

      const locationURL = new URL(location.href);
      locationURL.searchParams.set('directory', fileURL);
      location.href = locationURL.toString();
    });

    buttons.edit.target.setLabel(this.icons.edit);
    buttons.edit.target.setCallback((event) => {
      event.preventDefault();

      const filePath = fileURL.split('/').slice(0, -1).join('/');

      const requestMetadata = new Interactive('request', {
        method: 'GET',
        url: '/handler/media/metadata?directory=' + filePath + '&fileName=' + fileName + '.' + fileExtension + '&localeMessage=' + window.CMSCore.locales.admin.name
      });

      requestMetadata.target.send().then((data) => {
        const modalFileEditorBodyContent = document.createElement('div');
        modalFileEditorBodyContent.classList.add('file-editor');

        const inputDescriptionElement = document.createElement('textarea');
        inputDescriptionElement.setAttribute('placeholder', 'Название файла');
        inputDescriptionElement.setAttribute('name', 'file_description');
        inputDescriptionElement.classList.add('form__textarea');

        const inputAdditionalDescriptionElement = document.createElement('textarea');
        inputAdditionalDescriptionElement.setAttribute('placeholder', 'Описание файла');
        inputAdditionalDescriptionElement.setAttribute('name', 'file_additional_description');
        inputAdditionalDescriptionElement.classList.add('form__textarea');

        const inputLicenseElement = document.createElement('textarea');
        inputLicenseElement.setAttribute('placeholder', 'Лицензия файла');
        inputLicenseElement.setAttribute('name', 'file_license');
        inputLicenseElement.classList.add('form__textarea');

        const inputGEOLocationElement = document.createElement('input');
        inputGEOLocationElement.setAttribute('placeholder', 'Геолокация');
        inputGEOLocationElement.setAttribute('name', 'file_geo_location');
        inputGEOLocationElement.classList.add('form__input');

        const formElement = document.createElement('form');
        formElement.classList.add('form');
        formElement.classList.add('file-editor__form');
        formElement.append(inputDescriptionElement);
        formElement.append(inputAdditionalDescriptionElement);
        formElement.append(inputLicenseElement);
        formElement.append(inputGEOLocationElement);

        modalFileEditorBodyContent.append(formElement);

        const modalFileEditor = new Interactive('modal',
          {
            title: "Изменить файл",
            content: modalFileEditorBodyContent,
            width: window.innerWidth - 100
          }
        );

        modalFileEditor.target.addButton('Сохранить', () => {
          const inputDescriptionElementQS = modalFileEditor.target.element.querySelector('[name="file_description"]');
          const inputAdditionalDescriptionElementQS = modalFileEditor.target.element.querySelector('[name="file_additional_description"]');
          const inputLicenseElementQS = modalFileEditor.target.element.querySelector('[name="file_license"]');
          const inputGEOLocationElementQS = modalFileEditor.target.element.querySelector('[name="file_geo_location"]');
          
          const fileDescription = inputDescriptionElementQS.value;
          const fileAdditionalDescription = inputAdditionalDescriptionElementQS.value;
          const fileLicense = inputLicenseElementQS.value;
          const fileGEOLocation = inputGEOLocationElementQS.value;

          const formData = new FormData();
          formData.append('file_fullname', fileName);
          formData.append('file_extension', fileExtension);
          formData.append('file_description', fileDescription);
          formData.append('file_additional_description', fileAdditionalDescription);
          formData.append('file_license', fileLicense);
          formData.append('file_geo_location', fileGEOLocation);

          const requestFileEditor = new Interactive('request', {
            method: 'PATCH',
            url: '/handler/media?localeMessage=' + window.CMSCore.locales.admin.name
          });

          requestFileEditor.target.data = formData;

          requestFileEditor.target.send().then((data1) => {
            if (data1.statusCode === 1) {
              // ...
            }
          });

          modalFileEditor.target.close();
        });

        modalFileEditor.target.addButton('Отмена', () => {
          modalFileEditor.target.close();
        });

        modalFileEditor.assembly();
        document.body.appendChild(modalFileEditor.target.element);
        modalFileEditor.target.show();

        if (data.statusCode === 1) {
          console.log(data);
          inputDescriptionElement.value = data?.description;
          inputAdditionalDescriptionElement.value = data?.additionalDescription;
          inputLicenseElement.value = data?.license;
          inputGEOLocationElement.value = data?.GEOLocation;
        }
      });
    });

    buttons.delete.assembly();
    buttons.link.assembly();
    buttons.open.assembly();
    buttons.edit.assembly();

    const elementControllerElement = element.querySelector('[data-role="controller-panel"]');
    if (elementControllerElement !== null) {
      if (isDirectory !== null) {
        elementControllerElement.appendChild(buttons.link.target.element);
        elementControllerElement.appendChild(buttons.open.target.element);
      } else {
        elementControllerElement.appendChild(buttons.delete.target.element);
        elementControllerElement.appendChild(buttons.link.target.element);
        elementControllerElement.appendChild(buttons.edit.target.element);
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
        fileURL = data.outputData.file.URL;

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

      fetch('/handler/locales', {method: 'GET'}).then((response) => {
        return (response.ok) ? response.json() : Promise.reject(response);
      }).then((data) => {
        locales = data.outputData.locales;
        return window.CMSCore.locales.admin.getData();
      }, (rejectionReason) => {
        this.page.showPopupNotification(rejectionReason, 0);
      }).then((localeData) => {
        this.localeData = localeData;

        return this.page.core.loadIcons('/images/admin/icons').then((icons) => {
          return icons;
        });
      }, (rejectionReason) => {
        this.page.showPopupNotification(rejectionReason, 0);
      }).then((icons) => {
        this.icons = icons;
        this.initUploaderInput(mediaUploaderInput);

        const listElements = document.querySelectorAll('.media-list__item');
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
      });
    }
  }
}