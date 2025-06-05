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
    const element = document.createElement('input');
    const elementAttributes = ['id', 'name', 'type', 'placeholder', 'role'];
    
    elementAttributes.forEach(attribute => {
      if (attributes[attribute] !== undefined) {
        element.setAttribute(attribute, attributes[attribute]);
      }
    });

    if (attributes.required !== undefined) {
      if (attributes.required) {
        element.setAttribute('required', '');
      }
    }

    element.classList.add('form__input');

    if (element.hasAttribute('type')) {
      const elementType = element.getAttribute('type').toLowerCase();
      element.classList.add(`form__input_${elementType}`);
    }

    this.element = element;
  }
}