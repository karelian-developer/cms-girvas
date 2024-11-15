/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
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
    this.initClickEvent();
  }

  async getMediaFilesArray() {
    return await fetch('/handler/media', {
      method: 'GET'
    }).then((response) => {
      return response.json();
    }).then((data) => {
      return data.outputData.items;
    });
  }

  addImageItem(fileURL, end = true) {
    let targetElement = document.querySelector('#SYSTEM_MODAL_6438654856');
    let imagesListElement = targetElement.querySelector('ul');
    let imagesListItemsElements = targetElement.querySelectorAll('li');

    let mediaListItemElement = document.createElement('li');
    mediaListItemElement.classList.add('media-list__item');
    mediaListItemElement.style.backgroundImage = `url("${fileURL}")`;
    mediaListItemElement.setAttribute('data-media-url', fileURL);

    mediaListItemElement.addEventListener('click', (event) => {
      event.preventDefault();

      let inputImageLabelElement = this.modal.target.element.querySelector('[name="image_label"]');
      let imageLabel = inputImageLabelElement.value;

      this.editor.textarea.replaceStringSelection(
        `![${imageLabel}](${fileURL})`
      );

      this.modal.target.close();
    });

    if (end) {
      imagesListElement.appendChild(mediaListItemElement);
    } else {
      imagesListItemsElements[0].after(mediaListItemElement);
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
    let formData = new FormData();
    formData.append('mediaFile', input.files[fileIndex]);

    fetch('/handler/media', {
      method: 'POST',
      body: formData
    }).then((response) => {
      return response.json();
    }).then((data) => {
      if (data.statusCode == 1) {
        if (fileIndex < input.files.length) {
          this.imageUpload(input, fileIndex + 1);
        }

        this.addImageItem(data.outputData.file.url, false);

        let targetElement = document.querySelector('#SYSTEM_MODAL_6438654856');
        let imagesListItemsElements = targetElement.querySelectorAll('li');
        imagesListItemsElements[imagesListItemsElements.length - 2].remove();
      }
    });
  }

  initClickEvent() {
    super.addClickEvent(() => {
      console.log(`[NADVO TE] Tool ${this.name} clicked!`);

      let modalBodyContent = document.createElement('div');
      let mediaContainerElement = document.createElement('div');
      mediaContainerElement.setAttribute('id', 'SYSTEM_MODAL_6438654856');

      let inputFilesElement = document.createElement('input');
      inputFilesElement.setAttribute('type', 'file');
      inputFilesElement.setAttribute('multiple', 'multiple');
      inputFilesElement.style.display = 'none';
      inputFilesElement.addEventListener('change', (event) => {
        if (inputFilesElement.files.length > 0) {
          console.log(`[NADVO TE] New images upload...`);
          this.imageUpload(inputFilesElement, 0);
        }
      });

      let inputImageLabelElement = document.createElement('input');
      inputImageLabelElement.setAttribute('placeholder', 'Подпись изображения');
      inputImageLabelElement.setAttribute('name', 'image_label');
      inputImageLabelElement.classList.add('form__input');
      inputImageLabelElement.style.width = '100%';
      inputImageLabelElement.style.marginBottom = '10px';
      let inputImageLinkElement = document.createElement('input');
      inputImageLinkElement.classList.add('form__input');
      inputImageLinkElement.setAttribute('placeholder', '../image.webp');
      inputImageLinkElement.setAttribute('name', 'image_link');
      inputImageLinkElement.style.width = '100%';

      let formElement = document.createElement('form');
      formElement.classList.add('form');
      formElement.append(inputFilesElement);
      formElement.append(inputImageLabelElement);
      formElement.append(inputImageLinkElement);
      
      let inputsGroupContainer = document.createElement('div');
      inputsGroupContainer.append(formElement);

      modalBodyContent.append(mediaContainerElement);
      modalBodyContent.append(inputsGroupContainer);

      this.modal = new Interactive('modal', {title: "Вставить изображение", content: modalBodyContent, width: window.innerWidth - 400});
      
      let self = this;
      this.modal.target.onClose(() => {
        self.imagesListGroup = 0;
      });
      this.modal.target.addButton('Вставить', () => {
        let inputImageLabelElement = this.modal.target.element.querySelector('[name="image_label"]');
        let inputImageLinkElement = this.modal.target.element.querySelector('[name="image_link"]');
        
        let imageLabel = inputImageLabelElement.value;
        let imageLink = inputImageLinkElement.value;
        
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
        mediaListElement.classList.add('list-reset');
        
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
            this.getMediaFilesArray().then((items) => {
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
          this.getMediaFilesArray().then((items) => {
            let itemsinGroupCount = 23;
            let groupsCount = Math.ceil(items.length / itemsinGroupCount);
            if (this.imagesListGroup < groupsCount - 1) {
              this.clearImagesList();
              this.imagesListGroup++;

              items.forEach((item, itemIndex) => {
                if (itemIndex >= (itemsinGroupCount * this.imagesListGroup) && itemIndex < (itemsinGroupCount * this.imagesListGroup) + itemsinGroupCount) {
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

        let listItemsElementStyle = targetElement.currentStyle || window.getComputedStyle(targetElement);
        let listItemsElementCurrentHeight = parseInt(listItemsElementStyle.height);

        interactiveButtonNavPrev.target.element.style.position = 'absolute';
        interactiveButtonNavPrev.target.element.style.left = '0px';
        interactiveButtonNavPrev.target.element.style.top = (listItemsElementCurrentHeight / 2) + 'px';
        interactiveButtonNavPrev.target.element.style.zIndex = '101';
        interactiveButtonNavNext.target.element.style.position = 'absolute';
        interactiveButtonNavNext.target.element.style.right = '0px';
        interactiveButtonNavNext.target.element.style.top = (listItemsElementCurrentHeight / 2) + 'px';
        interactiveButtonNavNext.target.element.style.zIndex = '101';
        targetElement.appendChild(interactiveButtonNavPrev.target.element);
        targetElement.appendChild(interactiveButtonNavNext.target.element);
      });
    });
  }
}