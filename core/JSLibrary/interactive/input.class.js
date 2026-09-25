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

export class Input {
  constructor(interactiveObject) {
    this.interactiveObject = interactiveObject;

    this.element = null;
    this.value = '';
    this.type = 'text';
    this.placeholder = null;
    this.callback = (event) => {
      event.preventDefault();
    };
    this.disabled = false;
    this.assembled = null;
  }

  show() {
    if (this.assembled != null) {
      this.assembled.style.display = 'block';
    } else if (this.element != null) {
      this.element.style.display = 'block';
    }
  }

  hide() {
    if (this.assembled != null) {
      this.assembled.style.display = 'none';
    } else if (this.element != null) {
      this.element.style.display = 'none';
    }
  }

  enable() {
    this.disabled = false;

    if (this.element != null) {
      const inputElement = this.element.querySelector('input');
      if (inputElement != null) {
        inputElement.removeAttribute('disabled');
      }
    }
  }

  disable() {
    this.disabled = true;

    if (this.element != null) {
      const inputElement = this.element.querySelector('input');
      if (inputElement != null) {
        inputElement.setAttribute('disabled', 'disabled');
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
      const inputElement = this.element.querySelector('input');
      if (inputElement != null) {
        inputElement.value = value;
      }
    }
  }

  getValue() {
    return this.value;
  }

  setType(value) {
    this.type = value;

    if (this.element != null) {
      const inputElement = this.element.querySelector('input');
      if (inputElement != null) {
        inputElement.setAttribute('type', value);
      }
    }
  }

  getType() {
    return this.type;
  }

  setPlaceholder(value) {
    this.placeholder = value;

    if (this.element != null) {
      const inputElement = this.element.querySelector('input');
      if (inputElement != null) {
        inputElement.setAttribute('placeholder', value);
      }
    }
  }

  getPlaceholder() {
    return this.placeholder;
  }

  assembly() {
    const element = document.createElement('div');
    const inputElement = document.createElement('input');

    inputElement.classList.add('interactive__input');

    inputElement.setAttribute('type', this.type);

    if (this.placeholder !== null && this.placeholder !== '') {
      inputElement.setAttribute('placeholder', this.placeholder);
    }

    if (this.value !== null && this.value !== '') {
      inputElement.value = this.value;
    }

    if (this.isDisabled()) {
      inputElement.setAttribute('disabled', 'disabled');
    }

    inputElement.addEventListener('input', (event) => {
      this.value = event.target.value;
    });

    inputElement.addEventListener('click', this.callback);

    element.append(inputElement);
    this.element = element;
  }
}