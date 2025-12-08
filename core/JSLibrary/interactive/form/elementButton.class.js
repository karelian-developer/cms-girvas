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

import {Interactive} from "../../interactive.class.js";

export class ElementButton {
  constructor(interactiveFormObject, element = null) {
    this.interactiveFormObject = interactiveFormObject;
    
    this.element = element;
    this.stringLabel = 'Click';
    this.iconLabel = '???';
    this.clickEvent = (event) => {
      event.preventDefault();
    };
  }

  setStringLabel(string) {
    this.stringLabel = string;
  }

  setClickEvent(callback) {
    this.clickEvent = callback;
  }

  init(attributes = {}) {
    let interactiveElement = new Interactive('button');
    interactiveElement.target.setLabel(this.stringLabel);
    interactiveElement.target.setCallback(this.clickEvent);
    interactiveElement.assembly();

    if (typeof attributes.role != 'undefined') {
      interactiveElement.target.element.setAttribute('role', attributes.role);
    }
    
    this.element = interactiveElement.target.element;
  }
}