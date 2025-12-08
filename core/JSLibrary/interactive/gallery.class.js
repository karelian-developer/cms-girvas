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