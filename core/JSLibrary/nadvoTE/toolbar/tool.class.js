/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';

import {Interactive} from '../../interactive.class.js';

export class Tool {
  constructor(editor, data) {
    this.editor = editor;

    this.setName(data.name);
    this.setType(data.type);
    this.setIconPath(data.iconPath);
    this.setElement(data.element);
    this.setElementIcon(data.iconPath);

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
}