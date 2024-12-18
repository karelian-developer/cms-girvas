/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';

export class Input {
  constructor(interactiveObject) {
    this.interactiveObject = interactiveObject;

    this.element = null;
    this.value = null;
    this.type = null;
    this.callback = (event) => {
      event.preventDefault();
    };
    this.disabled = false;
    this.assembled = null;

    this.setType('text');
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
      let button = this.element.querySelector('input');

      if (button != null) {
        button.removeAttribute('disabled');
      }
    }
  }

  disable() {
    this.disabled = true;

    if (this.element != null) {
      let button = this.element.querySelector('input');

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

  setValue(value) {
    this.value = value;

    if (this.element != null) {
      let inputElement = this.element.querySelector('input');
      if (inputElement != null) {
        inputElement.value = value;
      }
    }
  }

  setType(value) {
    this.type = value;

    if (this.element != null) {
      let inputElement = this.element.querySelector('input');
      if (inputElement != null) {
        inputElement.setAttribute('type', value);
      }
    }
  }

  setPlaceholder(value) {
    if (this.element != null) {
      let inputElement = this.element.querySelector('input');
      if (inputElement != null) {
        inputElement.setAttribute('placeholder', value);
      }
    }
  }

  getType() {
    return this.type;
  }

  getValue() {
    return this.value;
  }

  assembly() {
    let element = document.createElement('div');

    let inputElement = document.createElement('input');
    inputElement.classList.add('interactive__input');
    inputElement.addEventListener('click', this.callback);

    if (this.isDisabled()) {
      inputElement.setAttribute('disabled', 'disabled');
    }

    inputElement.addEventListener('input', (event) => {
      this.value = event.target.value;
    })

    inputElement.value = this.value;
    element.append(inputElement);
    
    this.element = element;
  }
}