/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';

export class ElementInput {
  constructor(interactiveFormObject, element = null) {
    this.interactiveFormObject = interactiveFormObject;
    
    this.element = element;
  }

  init(attributes = {}) {
    let element = document.createElement('input');

    if (typeof attributes.id != 'undefined') {
      element.setAttribute('id', attributes.id);
    }

    if (typeof attributes.name != 'undefined') {
      element.setAttribute('name', attributes.name);
    }

    if (typeof attributes.type != 'undefined') {
      element.setAttribute('type', attributes.type);
    }

    if (typeof attributes.placeholder != 'undefined') {
      element.setAttribute('placeholder', attributes.placeholder);
    }

    if (typeof attributes.role != 'undefined') {
      element.setAttribute('role', attributes.role);
    }

    element.classList.add('form__input');

    if (element.hasAttribute('type')) {
      let elementType = element.getAttribute('type');
      element.classList.add(`form__input_${elementType.toLowerCase()}`);
    }

    this.element = element;
  }
}