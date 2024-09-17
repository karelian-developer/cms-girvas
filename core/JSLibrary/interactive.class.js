/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';


import {Button} from './interactive/button.class.js';
import {Choices} from './interactive/choices.class.js';
import {Schedule} from './interactive/schedule.class.js';
import {Modal} from './interactive/modal.class.js';
import {Form} from './interactive/form.class.js';
import {Request} from './interactive/request.class.js';
import {Notification} from './interactive/notification.class.js';
import {Slider} from './interactive/slider.class.js';

export class Interactive {
  constructor(interactiveName, interactiveParams = {}) {
    this.id = this.generateUniqueID();

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

    switch (interactiveName) {
      case 'button': this.target = new Button(); break;
      case 'choices': this.target = new Choices(); break;
      case 'schedule': this.target = new Schedule(data.canvasElement, data.type); break;
      case 'form': this.target = new Form(); break;
      case 'modal': this.target = new Modal(data.title, data.content, data.description, data.width); break;
      case 'request': this.target = new Request(data.method, data.url, data.data); break;
      case 'notification': this.target = new Notification(); break;
      case 'slider': this.target = new Slider(); break;
    }

    if (typeof(window.CMSCore) != 'undefined') {
      window.CMSCore.debugLog(2, 'CMSInteractive', `Element "${interactiveName}" (ID: ${this.id}) created!`, true);
    }
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

    this.target.element.classList.add(`interactive`);
    this.target.element.classList.add(`interactive_${this.target.constructor.name.toLocaleLowerCase()}`);
  }
}