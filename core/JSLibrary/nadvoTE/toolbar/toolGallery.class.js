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

export class ToolGallery extends Tool {
  constructor(editor, element) {
    super(editor, {
      name: 'gallery',
      type: 'button',
      iconPath: '/core/JSLibrary/nadvoTE/images/icons/toolbar/gallery.svg',
      element: element
    });

    this.modal = null;
    this.imagesListGroup = 0;
    this.filesPath = '';
    this.selectedFiles = [];

    this.initClickEvent();
    this.bindHotkey('Ctrl+Shift+G');
  }

  async getMediaFilesArray(directory = '') {
    const fetchURL = directory === ''
      ? '/handler/media?extensions=png,jpeg,webp,jpg,gif,avif'
      : '/handler/media?directory=' + encodeURIComponent(directory) + '&extensions=png,jpeg,webp,jpg,gif,avif';

    return await fetch(fetchURL, {method: 'GET'})
      .then((response) => response.json())
      .then((data) => data.outputData.items);
  }

  addImageItem(data, end = true) {
    const fileName = data.fullname;
    const fileURL = data.URL === undefined ? '' : data.URL;
    const fileExtension = data.extension;
    const fileIsDirectory = data.isDirectory;

    const targetElement = document.querySelector('#SYSTEM_MODAL_6438654857');
    const imagesListElement = targetElement.querySelector('ul');

    const listItemElement = document.createElement('li');
    listItemElement.classList.add('media-list__item');
    listItemElement.classList.add('item');
    listItemElement.setAttribute('data-file-name', fileName);
    listItemElement.setAttribute('data-file-url', fileURL);

    if (fileIsDirectory === true) {
      listItemElement.classList.add('media-list__item_is-directory');
    }

    const isSelected = this.selectedFiles.some((file) => file.url === fileURL);

    if (isSelected) {
      listItemElement.classList.add('media-list__item_is-selected');
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
          items.forEach((item) => {
            this.addImageItem(item);
          });
        });

        return false;
      }

      listItemElement.classList.toggle('media-list__item_is-selected');

      if (listItemElement.classList.contains('media-list__item_is-selected')) {
        this.selectedFiles.push({
          url: fileURL,
          name: fileName
        });
      } else {
        this.selectedFiles = this.selectedFiles.filter((file) => file.url !== fileURL);
      }

      console.log('[NADVO TE] Selected files', this.selectedFiles);
    });

    if (end) {
      imagesListElement.appendChild(listItemElement);
    } else {
      const firstItem = imagesListElement.querySelector('li');
      firstItem.after(listItemElement);
    }
  }

  clearImagesList() {
    const targetElement = document.querySelector('#SYSTEM_MODAL_6438654857');
    const imagesListItemsElements = targetElement.querySelectorAll('li');

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
        if (fileIndex < input.files.length - 1) {
          this.imageUpload(input, fileIndex + 1);
        }

        this.addImageItem(data.outputData.file, false);

        const targetElement = document.querySelector('#SYSTEM_MODAL_6438654857');
        const imagesListItemsElements = targetElement.querySelectorAll('li');
        imagesListItemsElements[imagesListItemsElements.length - 2].remove();
      }
    });
  }

  initClickEvent() {
    super.addClickEvent(() => {
      console.log(`[NADVO TE] Tool ${this.name} clicked!`);

      this.editor.saveCursorPosition();

      this.selectedFiles = [];
      this.imagesListGroup = 0;
      this.filesPath = '';

      const modalBodyContent = document.createElement('div');
      modalBodyContent.classList.add('file-manager');

      const mediaContainerElement = document.createElement('div');
      mediaContainerElement.classList.add('file-manager__files-container');
      mediaContainerElement.setAttribute('id', 'SYSTEM_MODAL_6438654857');

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

      const formElement = document.createElement('form');
      formElement.classList.add('form');
      formElement.classList.add('file-manager__form');
      formElement.append(inputFilesElement);

      const inputsGroupContainer = document.createElement('div');
      inputsGroupContainer.classList.add('file-manager__fixed-panel');
      inputsGroupContainer.append(formElement);

      modalBodyContent.append(inputsGroupContainer);
      modalBodyContent.append(mediaContainerElement);

      this.modal = new Interactive('modal', {
        title: 'Вставить галерею',
        content: modalBodyContent,
        width: window.innerWidth - 100
      });

      let self = this;

      this.modal.target.onClose(() => {
        self.selectedFiles = [];
        self.imagesListGroup = 0;
      });

      this.modal.target.addButton('Вставить', () => {
        if (this.selectedFiles.length === 0) {
          return;
        }

        const galleryMarkdown = this.buildGalleryMarkdown(this.selectedFiles);

        this.editor.textarea.insertStringAtLastCursor(galleryMarkdown);

        this.modal.target.close();
      });

      this.modal.target.addButton('Отмена', () => {
        this.modal.target.close();
      });

      this.modal.assembly();
      document.body.appendChild(this.modal.target.element);
      this.modal.target.show();

      this.getMediaFilesArray().then((data) => {
        const targetElement = document.querySelector('#SYSTEM_MODAL_6438654857');
        targetElement.style.position = 'relative';

        const mediaListElement = document.createElement('ul');
        mediaListElement.classList.add('media-list');
        mediaListElement.classList.add('file-manager__media-list');

        const mediaListItemUploadElement = document.createElement('li');
        mediaListItemUploadElement.classList.add('media-list__item');

        const interactiveButtonUpload = new Interactive('button');
        interactiveButtonUpload.target.setLabel('Загрузить');
        interactiveButtonUpload.target.setCallback(() => {
          inputFilesElement.click();
        });
        interactiveButtonUpload.assembly();

        const interactiveButtonNavPrev = new Interactive('button');
        interactiveButtonNavPrev.target.setLabel('<');
        interactiveButtonNavPrev.target.setCallback(() => {
          const itemsinGroupCount = 23;

          if (this.imagesListGroup > 0) {
            this.imagesListGroup--;

            this.clearImagesList();
            this.getMediaFilesArray(this.filesPath).then((items) => {
              items.forEach((item, itemIndex) => {
                if (
                  itemIndex >= (itemsinGroupCount * this.imagesListGroup) &&
                  itemIndex < (itemsinGroupCount * this.imagesListGroup) + itemsinGroupCount
                ) {
                  this.addImageItem(item);
                }
              });
            });
          }
        });
        interactiveButtonNavPrev.assembly();

        const interactiveButtonNavNext = new Interactive('button');
        interactiveButtonNavNext.target.setLabel('>');
        interactiveButtonNavNext.target.setCallback(() => {
          this.getMediaFilesArray(this.filesPath).then((items) => {
            const itemsinGroupCount = 23;
            const groupsCount = Math.ceil(items.length / itemsinGroupCount);

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

  buildGalleryMarkdown(files) {
    const lines = [];

    lines.push('[gallery]');

    files.forEach((file) => {
      const title = file.name || '';
      lines.push(`![${title}](${file.url})`);
    });

    lines.push('[/gallery]');

    return lines.join('\n');
  }
}