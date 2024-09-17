/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';

export class Button {
  constructor() {
    this.element = null;
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
    let element = document.createElement('div');

    let buttonElement = document.createElement('button');
    buttonElement.classList.add('interactive__button');
    buttonElement.addEventListener('click', this.callback);

    if (this.isDisabled()) {
      buttonElement.setAttribute('disabled', 'disabled');
    }

    if (this.iconUrl != null) {
      let buttonIconElement = document.createElement('img');
      buttonIconElement.classList.add('interactive__button-icon');
      buttonIconElement.setAttribute('scr', this.iconUrl);
      buttonElement.append(buttonIconElement);
    }

    let buttonLabelElement = document.createElement('span');
    buttonLabelElement.classList.add('interactive__button-label');
    buttonLabelElement.innerHTML = (this.label != null) ? this.label : 'Button';
    buttonElement.append(buttonLabelElement);

    element.append(buttonElement);

    this.element = element;
  }
}