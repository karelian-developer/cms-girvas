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

import {Interactive} from "../interactive.class.js";

export class Modal {
  constructor(interactiveObject, modalTitle, modalDescription, modalContent, modalWidth) {
    this.interactiveObject = interactiveObject;

    this.title = modalTitle;
    this.description = modalDescription;
    this.content = modalContent;
    this.width = modalWidth;
    this.opacity = 0;
    this.element = null;
    this.buttons = [];
    this.onCloseCallbackFunction = () => {};
  }

  show() {
    let elementWrapper = this.element.querySelector('.interactive__modal-wrapper');
    let interval = setInterval(() => {
      if (this.opacity < 1) {
        this.opacity += 0.01;
        elementWrapper.style.opacity = this.opacity;
      } else {
        clearInterval(interval);
      }
    }, 1);
  }

  hide() {
    let elementWrapper = this.element.querySelector('.interactive__modal-wrapper');
    let interval = setInterval(() => {
      if (this.opacity > 0) {
        this.opacity -= 0.01;
        elementWrapper.style.opacity = this.opacity;
      } else {
        clearInterval(interval);
      }
    }, 1);
  }

  onClose(callback) {
    this.onCloseCallbackFunction = callback;
  }

  close() {
    let elementWrapper = this.element.querySelector('.interactive__modal-wrapper');
    let interval = setInterval(() => {
      if (this.opacity > 0) {
        this.opacity -= 0.01;
        elementWrapper.style.opacity = this.opacity;
      } else {
        clearInterval(interval);
        this.element.remove();

        this.onCloseCallbackFunction();
      }
    }, 1);
  }

  addButton(buttonLabel, callbackFunction) {
    let interactiveButton = new Interactive('button');
    interactiveButton.target.setLabel(buttonLabel);
    interactiveButton.target.setCallback(callbackFunction);
    interactiveButton.assembly();

    let elementButton = interactiveButton.target.element.querySelector('.interactive__button');
    elementButton.classList.add('modal__footer-button');

    this.buttons.push(interactiveButton);
  }

  assemblyHeaderContainer(modalTitle = 'Modal window') {
    let elementHeader = document.createElement('div');
    elementHeader.classList.add('modal__header-container');

    let elementHeaderLabel = document.createElement('div');
    elementHeaderLabel.innerHTML = modalTitle;
    elementHeaderLabel.classList.add('modal__header-label');

    let interactiveButtonClose = new Interactive('button');
    interactiveButtonClose.target.setLabel('X');
    interactiveButtonClose.target.setCallback(() => {
      this.close();
    });
    interactiveButtonClose.assembly();

    let elementButtonClose = interactiveButtonClose.target.element.querySelector('.interactive__button');
    elementButtonClose.classList.add('modal__header-button');

    elementHeader.append(elementHeaderLabel);
    elementHeader.append(interactiveButtonClose.target.element);

    return elementHeader;
  }

  assemblyContentContainer(modalContent = 'Modal body content') {
    let elementBody = document.createElement('div');
    elementBody.append(modalContent);
    elementBody.classList.add('modal__body-container');

    return elementBody;
  }

  assemblyDescriptionContainer(string = '') {
    let element = document.createElement('div');
    element.append(string);
    element.classList.add('modal__description-container');

    return element;
  }

  assemblyFooterContainer() {
    let elementFooter = document.createElement('div');
    elementFooter.classList.add('modal__footer-container');

    this.buttons.forEach((button, buttonIndex) => {
      elementFooter.appendChild(button.target.element);
    });

    return elementFooter;
  }

  assemblyWrapper() {
    let elementWrapper = document.createElement('div');
    elementWrapper.classList.add('interactive__modal-wrapper');
    elementWrapper.style.opacity = 0;

    return elementWrapper;
  }

  assembly() {
    let childrenElements = {
      header: this.assemblyHeaderContainer(this.title),
      description: this.assemblyDescriptionContainer(this.description),
      content: this.assemblyContentContainer(this.content),
      footer: this.assemblyFooterContainer(),
      wrapper: this.assemblyWrapper()
    };

    let modalElement = document.createElement('div');
    modalElement.classList.add('interactive__modal');
    modalElement.style.width = this.width + 'px';

    modalElement.appendChild(childrenElements.header);

    if (this.description != null && this.description != '') {
      childrenElements.content.appendChild(childrenElements.description);
    }

    modalElement.appendChild(childrenElements.content);
    modalElement.appendChild(childrenElements.footer);
    childrenElements.wrapper.appendChild(modalElement);
    
    let element = document.createElement('div');
    element.appendChild(childrenElements.wrapper);

    this.element = element;
  }
}