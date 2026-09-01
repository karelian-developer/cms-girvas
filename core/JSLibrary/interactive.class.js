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

import {Button} from './interactive/button.class.js';
import {Input} from './interactive/input.class.js';
import {Choices} from './interactive/choices.class.js';
import {Schedule} from './interactive/schedule.class.js';
import {Modal} from './interactive/modal.class.js';
import {Form} from './interactive/form.class.js';
import {Request} from './interactive/request.class.js';
import {Notification} from './interactive/notification.class.js';
import {Slider} from './interactive/slider.class.js';
import {DataSearcher} from './interactive/dataSearcher.class.js';
import {Tabs} from './interactive/tabs.class.js';

export class Interactive {
  constructor(interactiveName, interactiveParams = {}) {
    this.id = this.generateUniqueID();
    this.name = '';

    let data = {};

    if (interactiveName == 'schedule') {
      data.canvasElement = (Object.hasOwn(interactiveParams, 'canvasElement')) ? interactiveParams.canvasElement : null;
      data.type = (Object.hasOwn(interactiveParams, 'type')) ? interactiveParams.type : 'linear';
    }

    if (interactiveName == 'modal') {
      data.title = (Object.hasOwn(interactiveParams, 'title')) ? interactiveParams.title : 'Anonymous modal';
      data.description = (Object.hasOwn(interactiveParams, 'description')) ? interactiveParams.description : '';
      data.content = (Object.hasOwn(interactiveParams, 'content')) ? interactiveParams.content : '';
      data.width = (Object.hasOwn(interactiveParams, 'width')) ? interactiveParams.width : 300;
    }

    if (interactiveName == 'request') {
      data.method = (Object.hasOwn(interactiveParams, 'method')) ? interactiveParams.method : 'POST';
      data.url = (Object.hasOwn(interactiveParams, 'url')) ? interactiveParams.url : '/';
      data.data = (Object.hasOwn(interactiveParams, 'data')) ? interactiveParams.data : undefined;
    }

    if (interactiveName == 'choices') {
      data.isDisclosed = (Object.hasOwn(interactiveParams, 'isDisclosed')) ? interactiveParams.isDisclosed : false;
    }

    if (interactiveName == 'tabs') {
      data.type = (Object.hasOwn(interactiveParams, 'type')) ? interactiveParams.type : 'pills';
      data.orientation = (Object.hasOwn(interactiveParams, 'orientation')) ? interactiveParams.orientation : 'horizontal';
      data.isMultiple = (Object.hasOwn(interactiveParams, 'isMultiple')) ? interactiveParams.isMultiple : false;
      data.width = (Object.hasOwn(interactiveParams, 'width')) ? interactiveParams.width : 'auto';
    }

    switch (interactiveName) {
      case 'button': this.target = new Button(this); break;
      case 'input': this.target = new Input(this); break;
      case 'choices': this.target = new Choices(this, data.isDisclosed); break;
      case 'schedule': this.target = new Schedule(this, data.canvasElement, data.type); break;
      case 'form': this.target = new Form(this); break;
      case 'modal': this.target = new Modal(this, data.title, data.content, data.description, data.width); break;
      case 'request': this.target = new Request(this, data.method, data.url, data.data); break;
      case 'notification': this.target = new Notification(this); break;
      case 'slider': this.target = new Slider(this); break;
      case 'dataSearcher': this.target = new DataSearcher(this); break;
      case 'tabs': 
        this.target = new Tabs(
          this, 
          {
            type: data.type || 'pills',
            orientation: data.orientation || 'horizontal',
            isMultiple: data.isMultiple || false,
            width: data.width || 'auto'
          }
        ); 
        break;
    }

    if (typeof(window.CMSCore) != 'undefined') {
      window.CMSCore.debugLog(2, 'CMSInteractive', `Element "${interactiveName}" (ID: ${this.id}) created!`, true);
    }
  }

  setName(value) {
    this.name = value;
  }

  getName() {
    return this.name;
  }

  generateRandomInt(min, max) {
    min = Math.ceil(min);
    max = Math.floor(max);

    return Math.floor(Math.random() * (max - min) + min);
  }

  generateUniqueID() {
    let randomNumber = this.generateRandomInt(100000000000, 999999999999), resultID = 0;

    let interactiveRepetitiveElement = document.querySelector(`[cmsg-interactive-uid="${randomNumber.toString(16)}"]`);
    if (interactiveRepetitiveElement != null) {
      return interactiveRepetitiveElement.generateUniqueID();
    } else {
      resultID = randomNumber;
    }

    return resultID.toString(16).toUpperCase();
  }

  assembly() {
    this.target.assembly();
    this.target.element.setAttribute('cmsg-interactive-uid', this.id);

    if (this.name !== '') {
      this.target.element.setAttribute('cmsg-interactive-name', this.name);
    }
    
    this.target.element.classList.add(`interactive`);

    let classModificatorName = `interactive_${this.target.constructor.name.toLocaleLowerCase()}`;
    this.target.element.classList.add(classModificatorName);
  }
}