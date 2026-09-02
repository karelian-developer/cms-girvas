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

import {Tool} from './tool.class.js';
import {Interactive} from '../../interactive.class.js';

export class ToolImage extends Tool {
  constructor(editor, element) {
    super(editor, {
      name: 'image',
      type: 'button',
      iconPath: '/core/JSLibrary/nadvoTE/images/icons/toolbar/image.svg',
      element: element
    });

    this.modal = null;
    this.imagesListGroup = 0;
    this.filesPath = '';
    this.initClickEvent();
  }

  async getMediaFilesArray(directory = '') {
    const fetchURL = directory === '' ? '/handler/media?extensions=png,jpeg,webp,jpg,gif,avif' : '/handler/media?directory=' + encodeURIComponent(directory) + '&extensions=png,jpeg,webp,jpg,gif,avif';
    return await fetch(fetchURL, {
      method: 'GET'
    }).then((response) => {
      return response.json();
    }).then((data) => {
      return data.outputData.items;
    });
  }

  addImageItem(data, end = true) {
    let fileName, fileURL, fileExtension, fileIsDirectory;

    fileName = data.fullname;
    fileURL = data.URL === undefined ? '' : data.URL;;
    fileExtension = data.extension;
    fileIsDirectory = data.isDirectory;

    const targetElement = document.querySelector('#SYSTEM_MODAL_6438654856');
    const imagesListElement = targetElement.querySelector('ul');
    const imagesListItemsElements = targetElement.querySelectorAll('li');

    const listItemElement = document.createElement('li');
    listItemElement.classList.add('media-list__item');
    listItemElement.classList.add('item');
    listItemElement.setAttribute('data-file-name', fileName);
    listItemElement.setAttribute('data-file-url', fileURL);

    if (fileIsDirectory === true) {
      listItemElement.classList.add('media-list__item_is-directory');
    }

    const listItemBodyContainerElement = document.createElement('div');
    listItemBodyContainerElement.classList.add('media-list__item-body');

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

    listItemElement.addEventListener('click', (event) => {
      event.preventDefault();

      if (fileIsDirectory) {
        this.filesPath = fileURL;
        this.imagesListGroup = 0;

        this.clearImagesList();
        this.getMediaFilesArray(this.filesPath).then((items) => {
          items.forEach((item, itemIndex) => {
            this.addImageItem(item);
          });
        });
        return false;
      }

      const inputImageLabelElement = this.modal.target.element.querySelector('[name="image_label"]');
      const imageLabel = inputImageLabelElement.value;

      this.editor.textarea.replaceStringSelection(
        `![${imageLabel}](${fileURL})`
      );

      this.modal.target.close();
    });

    if (end) {
      imagesListElement.appendChild(listItemElement);
    } else {
      imagesListItemsElements[0].after(listItemElement);
    }
  }

  clearImagesList() {
    let targetElement = document.querySelector('#SYSTEM_MODAL_6438654856');
    let imagesListItemsElements = targetElement.querySelectorAll('li');

    imagesListItemsElements.forEach((element, elementIndex) => {
      if (elementIndex > 0) {
        element.remove();
      }
    });
  }

  imageUpload(input, fileIndex) {
    const formData = new FormData();
    formData.append('mediaFile', input.files[fileIndex]);

    const request = new Interactive('request', {
      method: 'POST',
      url: '/handler/media?localeMessage=' + window.CMSCore.locales.admin.name
    });

    request.target.data = formData;

    request.target.send().then((data) => {
      if (data.statusCode === 1) {
        if (fileIndex < input.files.length) {
          this.imageUpload(input, fileIndex + 1);
        }

        this.addImageItem(data.outputData.file, false);

        const targetElement = document.querySelector('#SYSTEM_MODAL_6438654856');
        const imagesListItemsElements = targetElement.querySelectorAll('li');
        imagesListItemsElements[imagesListItemsElements.length - 2].remove();
      }
    });
  }

  initClickEvent() {
    super.addClickEvent(() => {
      console.log(`[NADVO TE] Tool ${this.name} clicked!`);

      const selection = this.editor.getSelectionString() || '';

      if (selection) {
        this.editor.clearSelection();
      }

      const modalBodyContent = document.createElement('div');
      modalBodyContent.classList.add('file-manager');

      const mediaContainerElement = document.createElement('div');
      mediaContainerElement.classList.add('file-manager__files-container');
      mediaContainerElement.setAttribute('id', 'SYSTEM_MODAL_6438654856');

      const inputFilesElement = document.createElement('input');
      inputFilesElement.setAttribute('type', 'file');
      inputFilesElement.setAttribute('accept', 'image/png, image/jpeg, image/gif, image/webp, image/avif');
      inputFilesElement.setAttribute('multiple', 'multiple');
      inputFilesElement.style.display = 'none';
      inputFilesElement.addEventListener('change', (event) => {
        if (inputFilesElement.files.length > 0) {
          console.log(`[NADVO TE] New images upload...`);
          this.imageUpload(inputFilesElement, 0);
          inputFilesElement.value = '';
        }
      });

      const inputImageLabelElement = document.createElement('input');
      inputImageLabelElement.setAttribute('placeholder', 'Подпись изображения');
      inputImageLabelElement.setAttribute('name', 'image_label');
      inputImageLabelElement.classList.add('form__input');
      inputImageLabelElement.value = selection;

      const inputImageLinkElement = document.createElement('input');
      inputImageLinkElement.classList.add('form__input');
      inputImageLinkElement.setAttribute('placeholder', '../image.webp');
      inputImageLinkElement.setAttribute('name', 'image_link');

      const formElement = document.createElement('form');
      formElement.classList.add('form');
      formElement.classList.add('file-manager__form');
      formElement.append(inputFilesElement);
      formElement.append(inputImageLabelElement);
      formElement.append(inputImageLinkElement);
      
      const inputsGroupContainer = document.createElement('div');
      inputsGroupContainer.classList.add('file-manager__fixed-panel');
      inputsGroupContainer.append(formElement);

      modalBodyContent.append(inputsGroupContainer);
      modalBodyContent.append(mediaContainerElement);

      this.modal = new Interactive('modal',
        {
          title: "Вставить изображение",
          content: modalBodyContent,
          width: window.innerWidth - 100
        }
      );
      
      let self = this;

      this.modal.target.onClose(() => {
        self.imagesListGroup = 0;
      });

      this.modal.target.addButton('Вставить', () => {
        const inputImageLabelElement = this.modal.target.element.querySelector('[name="image_label"]');
        const inputImageLinkElement = this.modal.target.element.querySelector('[name="image_link"]');
        
        const imageLabel = inputImageLabelElement.value;
        const imageLink = inputImageLinkElement.value;
        
        this.editor.textarea.replaceStringSelection(
          `![${imageLabel}](${imageLink})`
        );

        this.modal.target.close();
      });

      this.modal.target.addButton('Отмена', () => {
        this.modal.target.close();
      });

      this.modal.assembly();
      document.body.appendChild(this.modal.target.element);
      this.modal.target.show();

      this.getMediaFilesArray().then((data) => {
        let targetElement = document.querySelector('#SYSTEM_MODAL_6438654856');
        targetElement.style.position = 'relative';

        let mediaListElement = document.createElement('ul');
        mediaListElement.classList.add('media-list');
        mediaListElement.classList.add('file-manager__media-list');
        
        let mediaListItemUploadElement = document.createElement('li');
        mediaListItemUploadElement.classList.add('media-list__item');

        let interactiveButtonUpload = new Interactive('button');
        interactiveButtonUpload.target.setLabel('Загрузить');
        interactiveButtonUpload.target.setCallback(() => {
          inputFilesElement.click();
        });
        interactiveButtonUpload.assembly();

        let interactiveButtonNavPrev = new Interactive('button');
        interactiveButtonNavPrev.target.setLabel('<');
        interactiveButtonNavPrev.target.setCallback(() => {
          let itemsinGroupCount = 23;

          if (this.imagesListGroup > 0) {
            this.imagesListGroup--;

            this.clearImagesList();
            this.getMediaFilesArray(this.filesPath).then((items) => {
              items.forEach((item, itemIndex) => {
                if (itemIndex >= (itemsinGroupCount * this.imagesListGroup) && itemIndex < (itemsinGroupCount * this.imagesListGroup) + itemsinGroupCount) {
                  this.addImageItem(item);
                }
              });
            });
          }
        });
        interactiveButtonNavPrev.assembly();

        let interactiveButtonNavNext = new Interactive('button');
        interactiveButtonNavNext.target.setLabel('>');
        interactiveButtonNavNext.target.setCallback(() => {
          this.getMediaFilesArray(this.filesPath).then((items) => {
            let itemsinGroupCount = 23;
            let groupsCount = Math.ceil(items.length / itemsinGroupCount);
            if (this.imagesListGroup < groupsCount - 1) {
              this.clearImagesList();
              this.imagesListGroup++;

              items.forEach((item, itemIndex) => {
                if (
                  itemIndex >= (itemsinGroupCount * this.imagesListGroup) &&
                  itemIndex < (itemsinGroupCount * this.imagesListGroup) + itemsinGroupCount
                ) {
                  this.addImageItem(item);
                }
              });
            }
          });

        });
        interactiveButtonNavNext.assembly();

        mediaListItemUploadElement.appendChild(interactiveButtonUpload.target.element);
        mediaListElement.appendChild(mediaListItemUploadElement);

        targetElement.appendChild(mediaListElement);

        data.forEach((item, itemIndex) => {
          if (itemIndex < 23) {
            this.addImageItem(item);
          }
        });

        interactiveButtonNavPrev.target.element.classList.add('file-manager__controller');
        interactiveButtonNavPrev.target.element.classList.add('file-manager__controller_left');

        interactiveButtonNavNext.target.element.classList.add('file-manager__controller');
        interactiveButtonNavNext.target.element.classList.add('file-manager__controller_right');

        targetElement.appendChild(interactiveButtonNavPrev.target.element);
        targetElement.appendChild(interactiveButtonNavNext.target.element);
      });
    });
  }
}