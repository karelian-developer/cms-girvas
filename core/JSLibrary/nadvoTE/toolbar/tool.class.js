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
    this.setElement(data.element);

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

  setElement(element) {
    this.element = element;
  }

  setElementIcon(path) {
    fetch(path, {
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

  /**
   * Привязка горячей клавиши
   *
   * @param {string} hotkey Например: 'Ctrl+B'
   */
  bindHotkey(hotkey) {
    document.addEventListener('keydown', (event) => {
      const hotkeyParts = hotkey.toLowerCase().split('+');
      const hotkeyKey = hotkeyParts.pop();

      const needsCtrl = hotkeyParts.includes('ctrl');
      const needsShift = hotkeyParts.includes('shift');
      const needsAlt = hotkeyParts.includes('alt');

      const ctrlOk = needsCtrl ? (event.ctrlKey || event.metaKey) : true;
      const shiftOk = needsShift ? event.shiftKey : true;
      const altOk = needsAlt ? event.altKey : true;

      const keyMap = {
        'b': 'KeyB',
        'i': 'KeyI',
        'u': 'KeyU',
        'k': 'KeyK',
        'z': 'KeyZ',
        'y': 'KeyY',
        'c': 'KeyC'
      };

      const expectedCode = keyMap[hotkeyKey] || hotkeyKey.toUpperCase();

      if (ctrlOk && shiftOk && altOk && event.code === expectedCode) {
        event.preventDefault();
        this.element.click();
      }
    });
  }
}