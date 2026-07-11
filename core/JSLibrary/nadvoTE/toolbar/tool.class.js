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

import {Interactive} from '../../interactive.class.js';

export class Tool {
  constructor(editor, data) {
    this.editor = editor;

    this.setName(data.name);
    this.setType(data.type);
    this.setIconPath(data.iconPath);
    this.setInteractiveElement(data.interactiveElement);

    if (data.interactiveElement !== null) {
      this.setElement(data.interactiveElement.element);
    } else {
      this.setElement(data.element);
    }

    if (data.type === 'button') {
      this.setElementIcon(data.iconPath);
    }

    console.log(`[NADVO TE] Tool ${data.name} created.`);
  }
  
  setType(value) {
    this.type = value;
  }
  
  setName(value) {
    this.name = value;
  }
  
  setIconPath(value) {
    this.iconPath = value;
  }

  setInteractiveElement(element) {
    this.interactiveElement = element;
  }

  setElement(element) {
    this.element = element;
  }

  setElementIcon(path) {
    fetch(path,  {
      method: 'GET',
      headers: {
        'Content-Type': 'image/svg+xml'
      }
    }).then((response) => {
      if (this.type == 'button') {
        let button = this.element.querySelector('button');
        button.style.padding = 0;
        if (response.status == 200) {
          response.text().then((text) => {
            button.innerHTML = text;

            let buttonIcon = button.querySelector('svg');
            buttonIcon.classList.add('interactive__button-icon');
            buttonIcon.style.marginRight = 0;
          });
        } else {
          button.innerHTML = this.name;
        }
      }
    }).catch((error) => {
      console.error(error);
    });
  }

  addClickEvent(callback) {
    this.element.addEventListener('click', (event) => {
      event.preventDefault();

      callback();
    });
  }

  addChangeEvent(callback) {
    const selectElement = this.element.querySelector('select');

    selectElement.addEventListener('change', (event) => {
      event.preventDefault();

      callback();
    });
  }
}