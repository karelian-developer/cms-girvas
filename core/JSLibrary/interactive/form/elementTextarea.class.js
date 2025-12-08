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