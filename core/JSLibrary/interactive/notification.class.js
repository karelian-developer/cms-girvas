/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';

export class Notification {
  constructor() {
    this.element = null;
    this.title = '';
    this.content = '';
    this.footer = '';
    this.statusCode = -1;
    this.isPopup = false;
    this.liveTimer = null;

    this.containerTarget = document.body;
  }

  setTitle(string) {
    this.title = string;
  }

  setContent(string) {
    this.content = string;
  }

  setFooter(string) {
    this.footer = string;
  }

  setStatusCode(value) {
    this.statusCode = (typeof(value) == 'number') ? value : -1;
  }

  assembly() {
    let element, titleElement, contentElement, footerElement;
    
    element = document.createElement('div');
    element.classList.add('notification');

    switch (this.statusCode) {
      case 0: element.classList.add('notification_error'); break;
      case 1: element.classList.add('notification_success'); break;
      case 2: element.classList.add('notification_info'); break;
      default: element.classList.add('notification_default');
    }

    if (this.isPopup) {
      element.classList.add('notification_is-popup');
    }

    titleElement = document.createElement('div');
    titleElement.classList.add('notification__title');

    contentElement = document.createElement('div');
    contentElement.classList.add('notification__content');

    footerElement = document.createElement('div');
    footerElement.classList.add('notification__footer');

    if (this.title != '') {
      element.append(titleElement);
    }

    element.append(contentElement);

    if (this.footer != '') {
      element.append(footerElement);
    }

    this.element = element;
  }

  startLiveTimer() {
    this.liveTimer = setTimeout(() => {
      this.hide();
    }, 10000);
  }

  show() {
    if (!this.isPopup && this.containerTarget != null) {
      this.containerTarget.innerHTML = '';
    }

    this.containerTarget.append(this.element);

    if (this.isPopup) {
      setTimeout(() => {
        let contentElement = this.element.querySelector('.notification__content');
        contentElement.innerHTML = this.content;
        
        this.element.classList.add('notification_is-showed');

        this.startLiveTimer();
      }, 10);
    }
  }

  hide() {
    setTimeout(() => {
      this.element.classList.remove('notification_is-showed');

      setTimeout(() => {
        this.element.remove();
        this.delete();
      }, 200);
    }, 10);
  }

  delete() {
    delete this;
  }
}