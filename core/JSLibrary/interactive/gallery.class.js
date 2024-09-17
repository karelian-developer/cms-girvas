/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';

export class Gallery {
  constructor() {
    this.element = null;
    this.items = [];
    this.assembled = null;
  }

  addItem(image_url, caption = '') {
    this.items.push([{
      'url': image_url,
      'caption': caption
    }]);
  }

  assemblyControllers() {
    let elementControllerLeft = document.createElement('button');
    elementControllerLeft.classList.add('controller__button');
    elementControllerLeft.classList.add('controller__button_move-left');

    let elementControllerRight = document.createElement('button');
    elementControllerRight.classList.add('controller__button');
    elementControllerRight.classList.add('controller__button_move-right');

    let elementControllers = document.createElement('div');
    elementControllers.appendChild(elementControllerLeft);
    elementControllers.appendChild(elementControllerRight);

    return elementControllers;
  }

  assemblyItems() {
    this.items.forEach((item, itemIndex) => {
      let elementPicture = document.createElement('picture');
    });
  }
}