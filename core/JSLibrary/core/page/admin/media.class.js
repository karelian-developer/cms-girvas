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
    buttons.view = new Interactive('button');

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
      const fileNameWithoutExtension = fileName.replace(/\.[^.]+$/, '');

      const requestMetadata = new Interactive('request', {
        method: 'GET',
        url: '/handler/media/metadata?directory=' + filePath
          + '&fileName=' + fileNameWithoutExtension + '.' + fileExtension
          + '&localeMessage=' + window.CMSCore.locales.admin.name
      });

      requestMetadata.target.send().then((data) => {
        const modalFileEditorBodyContent = document.createElement('div');
        modalFileEditorBodyContent.classList.add('file-editor');

        const inputDescriptionElement = document.createElement('textarea');
        inputDescriptionElement.setAttribute('placeholder', 'Название файла');
        inputDescriptionElement.setAttribute('name', 'file_description');
        inputDescriptionElement.setAttribute('required', '');
        inputDescriptionElement.classList.add('form__textarea');

        const inputAdditionalDescriptionElement = document.createElement('textarea');
        inputAdditionalDescriptionElement.setAttribute('placeholder', 'Описание файла');
        inputAdditionalDescriptionElement.setAttribute('name', 'file_additional_description');
        inputAdditionalDescriptionElement.classList.add('form__textarea');

        const inputLicenseElement = document.createElement('textarea');
        inputLicenseElement.setAttribute('placeholder', 'Лицензия (URL)');
        inputLicenseElement.setAttribute('name', 'file_license');
        inputLicenseElement.setAttribute('type', 'url');
        inputLicenseElement.classList.add('form__textarea');

        const inputGEOLocationElement = document.createElement('input');
        inputGEOLocationElement.setAttribute('placeholder', 'RU');
        inputGEOLocationElement.setAttribute('name', 'file_geo_location');
        inputGEOLocationElement.setAttribute('pattern', '^(A[^ABCHJKNPVY]|B[^CKPUX]|C[^BEJPQST]|D[EJKMOZ]|E[CEGHRST]|F[IJKMOR]|G[^CJKOVXZ]|H[KMNRTU]|I[DEL-OQ-T]|J[EMOP]|K[EGHIMNPRWYZ]|L[ABCIKR-VY]|M[^BIJ]|N[ACEFGILOPRUZ]|O[M]|P[^BEIJLOXY]|QA|R[EOSUW]|S[^FPQUW]|T[^BEIPQSUXY]|U[AGMSYZ]|V[ACEGINU]|W[FS]|XK|Y[ET]|Z[AMW])$');
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
          const metadata = data?.outputData?.metadata;

          if (metadata !== undefined) {
            inputDescriptionElement.value = metadata.description;
            inputAdditionalDescriptionElement.value = metadata.additionalDescription;
            inputLicenseElement.value = metadata.license;
            inputGEOLocationElement.value = metadata.GEOLocation;
          }
        }
      });
    });

    buttons.view.target.setLabel(this.icons.search);
    buttons.view.target.setCallback((event) => {
      event.preventDefault();
      window.open(window.location.origin + fileURL);
    });

    buttons.delete.assembly();
    buttons.link.assembly();
    buttons.open.assembly();
    buttons.edit.assembly();
    buttons.view.assembly();

    const elementControllerElement = element.querySelector('[data-role="controller-panel"]');
    if (elementControllerElement !== null) {
      if (isDirectory !== null) {
        elementControllerElement.appendChild(buttons.link.target.element);
        elementControllerElement.appendChild(buttons.open.target.element);
      } else {
        elementControllerElement.appendChild(buttons.delete.target.element);
        elementControllerElement.appendChild(buttons.link.target.element);
        elementControllerElement.appendChild(buttons.edit.target.element);
        elementControllerElement.appendChild(buttons.view.target.element);
      }
    }
  }

  uploadFile(inputElement, fileIndex) {
    const imageRegex = /^image/i;

    const file = inputElement.files[fileIndex];
    const mimeType = file.type;

    const formData = new FormData();
    formData.append('mediaFile', file);

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
        let fileName, fileURL, fileExtension;

        fileName = data.outputData.file.fullname;
        fileURL = data.outputData.file.URL;
        fileExtension = data.outputData.file.extension;

        const listItemElement = document.createElement('li');
        listItemElement.classList.add('media-list__item');
        listItemElement.classList.add('item');
        listItemElement.setAttribute('data-file-name', fileName);
        listItemElement.setAttribute('data-file-url', fileURL);
        listItemElement.setAttribute('data-file-extension', fileExtension);

        if (imageRegex.test(mimeType)) {
          const listItemBodyContainerElement = document.createElement('div');
          listItemBodyContainerElement.classList.add('media-list__item-body');

          const listItemControllerContainerElement = document.createElement('div');
          listItemControllerContainerElement.classList.add('media-list__item-controllers');
          listItemControllerContainerElement.classList.add('item-controllers');
          listItemControllerContainerElement.setAttribute('data-role', 'controller-panel');

          const listItemExtensionElement = document.createElement('span');
          listItemExtensionElement.classList.add('media-list__item-extension');
          listItemExtensionElement.innerText = fileExtension;

          const listItemImageElement = document.createElement('img');
          listItemImageElement.classList.add('media-list__item-preview');
          listItemImageElement.setAttribute('src', fileURL);
          listItemImageElement.setAttribute('alt', fileName);

          const listItemTitleContainerElement = document.createElement('div');
          listItemTitleContainerElement.classList.add('media-list__item-title');

          const listItemTitleElement = document.createElement('span');
          listItemTitleElement.classList.add('media-list__item-label');
          listItemTitleElement.innerText = fileName;

          listItemTitleContainerElement.appendChild(listItemTitleElement);
          listItemElement.appendChild(listItemTitleContainerElement);

          listItemBodyContainerElement.appendChild(listItemExtensionElement);
          listItemBodyContainerElement.appendChild(listItemImageElement);
          listItemBodyContainerElement.appendChild(listItemTitleContainerElement);

          listItemElement.appendChild(listItemBodyContainerElement);
          listItemElement.appendChild(listItemControllerContainerElement);
        } else {
          const listItemBodyContainerElement = document.createElement('div');
          listItemBodyContainerElement.classList.add('media-list__item-body');

          const listItemControllerContainerElement = document.createElement('div');
          listItemControllerContainerElement.classList.add('media-list__item-controllers');
          listItemControllerContainerElement.classList.add('item-controllers');
          listItemControllerContainerElement.setAttribute('data-role', 'controller-panel');

          const listItemIconContainer = document.createElement('div');
          listItemIconContainer.classList.add('media-list__item-icon-container');

          const listItemIcon = document.createElement('img');
          listItemIcon.classList.add('media-list__item-icon');
          listItemIcon.setAttribute('src', '/images/admin/icons/file.svg');
          listItemIcon.setAttribute('alt', fileName);
          
          const listItemIconLabel = document.createElement('div');
          listItemIconLabel.classList.add('media-list__item-icon-label');
          listItemIconLabel.innerText = fileExtension;

          const listItemTitleContainerElement = document.createElement('div');
          listItemTitleContainerElement.classList.add('media-list__item-title');

          const listItemTitleElement = document.createElement('span');
          listItemTitleElement.classList.add('media-list__item-label');
          listItemTitleElement.innerText = fileName;

          listItemIconContainer.appendChild(listItemIcon);
          listItemIconContainer.appendChild(listItemIconLabel);

          listItemTitleContainerElement.appendChild(listItemTitleElement);
          listItemElement.appendChild(listItemTitleContainerElement);

          listItemBodyContainerElement.appendChild(listItemIconContainer);
          listItemBodyContainerElement.appendChild(listItemTitleContainerElement);

          listItemElement.appendChild(listItemBodyContainerElement);
          listItemElement.appendChild(listItemControllerContainerElement);
        }

        const mediaListElement = document.querySelector('#E9453667589');
        const mediaListItems = mediaListElement.querySelectorAll('li');
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
    const interactiveSortChoices = new Interactive('choices');
    const interactiveContainerPagePanelElement = document.querySelector('#E8548530785');

    let mediaUploaderInput = document.querySelector('.form__input_file');
    if (mediaUploaderInput !== null) {
      mediaUploaderInput.setAttribute('accept', 'image/png, image/jpeg, image/gif, image/webp, image/avif, application/pdf, .pdf');

      this.page.core.locales.admin.getData().then((localeData) => {
        this.localeData = localeData;

        return this.page.core.loadIcons('/images/admin/icons/buttons').then((icons) => {
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

        interactiveSortChoices.target.setWidth('280px');

        interactiveSortChoices.target.addItem(this.localeData.SORT_BY_CREATEDTIMESTAMP_INCREASE, 'by_createdtimestamp_increase');
        interactiveSortChoices.target.addItem(this.localeData.SORT_BY_CREATEDTIMESTAMP_DECREASE, 'by_createdtimestamp_decrease');
        interactiveSortChoices.target.addItem(this.localeData.SORT_BY_ALPHABET_INCREASE, 'by_alphabet_increase');
        interactiveSortChoices.target.addItem(this.localeData.SORT_BY_ALPHABET_DECREASE, 'by_alphabet_decrease');

        const currentSort = this.page.core.searchParams.getParam('sort') ?? 'by_alphabet_increase';
        switch (currentSort) {
          case 'by_createdtimestamp_increase': interactiveSortChoices.target.setItemSelectedIndex(0); break;
          case 'by_createdtimestamp_decrease': interactiveSortChoices.target.setItemSelectedIndex(1); break;
          case 'by_alphabet_increase': interactiveSortChoices.target.setItemSelectedIndex(2); break;
          case 'by_alphabet_decrease': interactiveSortChoices.target.setItemSelectedIndex(3); break;
          default: interactiveSortChoices.target.setItemSelectedIndex(2); break;
        }

        interactiveSortChoices.assembly();

        const paginationListPage = document.querySelectorAll('.main__page .page__pagination > ul');
        
        paginationListPage.forEach(paginationElement => {
          const paginationListItemsPage = paginationElement.querySelectorAll('li');
          
          paginationListItemsPage.forEach(listElement => {
            const linkElement = listElement.querySelector('a');
            const linkElementHref = linkElement.getAttribute('href');

            linkElement.setAttribute('href', linkElementHref + '&sort=' + currentSort);
          });
        });

        this.buttons.upload = new Interactive('button');
        this.buttons.upload.target.setLabel(this.localeData.BUTTON_UPLOAD_LABEL);

        this.buttons.upload.target.setCallback((event) => {
          event.preventDefault();
          mediaUploaderInput.click();
        });

        this.buttons.upload.assembly();

        interactiveSortChoices.target.elementSelect.addEventListener('change', () => {
          const currentURL = new URL(window.location.href);
          currentURL.searchParams.set('sort', interactiveSortChoices.target.getValue());
          window.location.href = currentURL.toString();
        });

        interactiveContainerPagePanelElement.append(interactiveSortChoices.target.element);
        interactiveContainerPagePanelElement.append(this.buttons.upload.target.element);
      });
    }
  }
}