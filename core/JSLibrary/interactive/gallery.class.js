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

export class Gallery {
  constructor(interactiveObject) {
    this.interactiveObject = interactiveObject;

    this.element = null;
    this.items = [];
    this.assembled = null;

    this.currentIndex = 0;
    this.modal = null;
  }

  addItem(image_url, caption = '') {
    this.items.push({
      url: image_url,
      caption: caption
    });
  }

  assemblyControllers() {
    const elementControllerLeft = document.createElement('button');
    elementControllerLeft.classList.add('controller__button');
    elementControllerLeft.classList.add('controller__button_move-left');
    elementControllerLeft.setAttribute('type', 'button');
    elementControllerLeft.innerHTML = `
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="15 18 9 12 15 6"/>
      </svg>
    `;

    const elementControllerRight = document.createElement('button');
    elementControllerRight.classList.add('controller__button');
    elementControllerRight.classList.add('controller__button_move-right');
    elementControllerRight.setAttribute('type', 'button');
    elementControllerRight.innerHTML = `
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="9 18 15 12 9 6"/>
      </svg>
    `;

    elementControllerLeft.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();

      this.prev();
    });

    elementControllerRight.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();

      this.next();
    });

    const elementControllers = document.createElement('div');
    elementControllers.classList.add('gallery__controllers');
    elementControllers.appendChild(elementControllerLeft);
    elementControllers.appendChild(elementControllerRight);

    return elementControllers;
  }

  assemblyItems() {
    const elementItems = document.createElement('div');
    elementItems.classList.add('gallery__items');

    this.items.forEach((item, itemIndex) => {
      const elementPicture = document.createElement('figure');
      elementPicture.classList.add('gallery__item');

      if (itemIndex === this.currentIndex) {
        elementPicture.classList.add('gallery__item_is-active');
      }

      const elementImage = document.createElement('img');
      elementImage.classList.add('gallery__image');
      elementImage.setAttribute('src', item.url);
      elementImage.setAttribute('alt', item.caption);
      elementImage.setAttribute('loading', 'lazy');

      elementImage.addEventListener('click', (event) => {
        event.preventDefault();

        this.openModal(itemIndex);
      });

      elementPicture.appendChild(elementImage);

      if (item.caption !== '') {
        const elementCaption = document.createElement('figcaption');
        elementCaption.classList.add('gallery__caption');
        elementCaption.textContent = item.caption;

        elementPicture.appendChild(elementCaption);
      }

      elementItems.appendChild(elementPicture);
    });

    return elementItems;
  }

  assemblyPreview() {
    const elementPreview = document.createElement('div');
    elementPreview.classList.add('gallery__preview');

    this.items.forEach((item, itemIndex) => {
      const elementPreviewItem = document.createElement('button');
      elementPreviewItem.classList.add('gallery__preview-item');
      elementPreviewItem.setAttribute('type', 'button');

      if (itemIndex === this.currentIndex) {
        elementPreviewItem.classList.add('gallery__preview-item_is-active');
      }

      const elementImage = document.createElement('img');
      elementImage.setAttribute('src', item.url);
      elementImage.setAttribute('alt', item.caption);

      elementPreviewItem.appendChild(elementImage);

      elementPreviewItem.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();

        this.currentIndex = itemIndex;
        this.updateActiveItem();
      });

      elementPreview.appendChild(elementPreviewItem);
    });

    return elementPreview;
  }

  prev() {
    if (this.currentIndex > 0) {
      this.currentIndex--;
    } else {
      this.currentIndex = this.items.length - 1;
    }

    this.updateActiveItem();
  }

  next() {
    if (this.currentIndex < this.items.length - 1) {
      this.currentIndex++;
    } else {
      this.currentIndex = 0;
    }

    this.updateActiveItem();
  }

  updateActiveItem() {
    const items = this.element.querySelectorAll('.gallery__item');
    const previewItems = this.element.querySelectorAll('.gallery__preview-item');

    items.forEach((item, itemIndex) => {
      if (itemIndex === this.currentIndex) {
        item.classList.add('gallery__item_is-active');
      } else {
        item.classList.remove('gallery__item_is-active');
      }
    });

    previewItems.forEach((previewItem, previewIndex) => {
      if (previewIndex === this.currentIndex) {
        previewItem.classList.add('gallery__preview-item_is-active');
      } else {
        previewItem.classList.remove('gallery__preview-item_is-active');
      }
    });
  }

  openModal(index) {
    this.currentIndex = index;

    const modalBody = document.createElement('div');
    modalBody.classList.add('gallery-modal');

    const modalImage = document.createElement('img');
    modalImage.classList.add('gallery-modal__image');
    modalImage.setAttribute('src', this.items[index].url);
    modalImage.setAttribute('alt', this.items[index].caption);

    modalBody.appendChild(modalImage);

    if (this.items[index].caption !== '') {
      const modalCaption = document.createElement('div');
      modalCaption.classList.add('gallery-modal__caption');
      modalCaption.textContent = this.items[index].caption;

      modalBody.appendChild(modalCaption);
    }

    const modal = new Interactive('modal', {
      title: 'Просмотр изображения',
      content: modalBody,
      width: window.innerWidth - 200
    });

    modal.target.addButton('Закрыть', () => {
      modal.target.close();
    });

    modal.assembly();
    document.body.appendChild(modal.target.element);
    modal.target.show();

    this.modal = modal;
  }

  assembly() {
    this.element = document.createElement('div');
    this.element.classList.add('gallery');

    this.element.appendChild(this.assemblyItems());

    if (this.items.length > 1) {
      this.element.appendChild(this.assemblyControllers());
      this.element.appendChild(this.assemblyPreview());
    }

    this.updateActiveItem();

    return this.element;
  }
}