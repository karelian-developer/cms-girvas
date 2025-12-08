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