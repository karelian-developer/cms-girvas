/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';

export class ElementTextarea {
  constructor(interactiveFormObject, element = null) {
    this.interactiveFormObject = interactiveFormObject;
    
    this.element = element;
  }

  init(attributes = {}) {
    let element = document.createElement('textarea');

    if (typeof attributes.id != 'undefined') {
      element.setAttribute('id', attributes.id);
    }

    if (typeof attributes.name != 'undefined') {
      element.setAttribute('name', attributes.name);
    }

    if (typeof attributes.placeholder != 'undefined') {
      element.setAttribute('placeholder', attributes.placeholder);
    }

    if (typeof attributes.role != 'undefined') {
      element.setAttribute('role', attributes.role);
    }

    if (typeof attributes.maxlength != 'undefined') {
      element.setAttribute('maxlength', attributes.maxlength);
    }

    if (typeof attributes.minlength != 'undefined') {
      element.setAttribute('minlength', attributes.minlength);
    }

    if (typeof attributes.cols != 'undefined') {
      element.setAttribute('cols', attributes.cols);
    }

    if (typeof attributes.rows != 'undefined') {
      element.setAttribute('rows', attributes.rows);
    }

    element.classList.add('form__textarea');

    this.element = element;
  }
}