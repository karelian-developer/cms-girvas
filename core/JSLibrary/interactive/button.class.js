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

export class Button {
  constructor(interactiveObject) {
    this.interactiveObject = interactiveObject;
    
    this.element = null;
    this.style = 'default';
    this.label = null;
    this.iconUrl = null;
    this.callback = (event) => {
      event.preventDefault();
    };
    this.disabled = false;
    this.assembled = null;
  }

  show() {
    this.assembled.style.display = 'block';
  }

  hide() {
    this.assembled.style.display = 'none';
  }

  enable() {
    this.disabled = false;

    if (this.element != null) {
      let button = this.element.querySelector('button');

      if (button != null) {
        button.removeAttribute('disabled');
      }
    }
  }

  disable() {
    this.disabled = true;

    if (this.element != null) {
      let button = this.element.querySelector('button');

      if (button != null) {
        button.setAttribute('disabled', 'disabled');
      }
    }
  }

  setStyle(name) {
    this.style = name;
  }

  getStyle() {
    return this.style;
  }

  isDisabled() {
    return this.disabled;
  }

  setCallback(callbackFunction) {
    this.callback = callbackFunction;
  }

  setLabel(value) {
    this.label = value;
  }

  setIconURL(value) {
    this.iconUrl = value;
  }

  assembly() {
    const element = document.createElement('div');
    const buttonElement = document.createElement('button');
    const buttonIconElement = document.createElement('img');
    const buttonLabelElement = document.createElement('span');

    buttonElement.classList.add('interactive__button');
    buttonElement.addEventListener('click', this.callback);

    if (this.style !== null || this.style !== '') {
      buttonElement.classList.add('interactive__button_' + this.style);
    }

    if (this.isDisabled()) {
      buttonElement.setAttribute('disabled', 'disabled');
    }

    if (this.iconUrl != null) {
      buttonIconElement.classList.add('interactive__button-icon');
      buttonIconElement.setAttribute('scr', this.iconUrl);
      buttonElement.append(buttonIconElement);
    }

    buttonLabelElement.classList.add('interactive__button-label');
    buttonLabelElement.innerHTML = (this.label != null) ? this.label : 'Button';
    buttonElement.append(buttonLabelElement);

    element.append(buttonElement);

    this.element = element;
  }
}