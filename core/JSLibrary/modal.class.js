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

class Modal {
  constructor(modalContent) {
    this.timeout = null;
    this.init(modalContent);
  }

  static initCallerElement(element) {
    element.addEventListener('click', (event) => {
      event.preventDefault();

      fetch('/handler/modal?name=' + element.getAttribute('data-modal-call') + '&' + element.getAttribute('data-modal-params'), {
        method: 'GET'
      }).then((response) => {
        console.log(`Sended: ${element.getAttribute('data-modal-params')}`);
        return response.json();
      }).then((data) => {
        console.log(data.outputData.template);
        let modal = new Modal(data.outputData.template);
      }).catch((error) => {
        console.error(error);
      });
    });
  }

  init(modalContent) {
    this.element = this.createElementDiv();
    this.element.classList.add('modal');
    this.element.append(modalContent);

    this.wrapperElement = this.createElementDiv();
    this.wrapperElement.classList.add('modal-wrapper');

    this.setElement(this.element);
    document.body.appendChild(this.wrapperElement);
    let modalElementAppended = this.wrapperElement.appendChild(this.getElement());
    let form = new Form(modalElementAppended.querySelector('form'));
    form.initFormElement();
    form.modalParent = this;

    let modalClosersElements = this.getElement().querySelectorAll('[data-modal-closer]');
    for (let modalCloserElement of modalClosersElements) {
      modalCloserElement.addEventListener('click', (event) => {
        event.preventDefault();
        this.remove();
      });
    }

    this.timeout = setTimeout(() => {
      this.wrapperElement.classList.add('modal-wrapper_is-showed');
      this.element.classList.add('modal_is-showed');
    }, 10);
  }

  remove() {
    this.wrapperElement.classList.remove('modal-wrapper_is-showed');
    this.element.classList.remove('modal_is-showed');

    this.timeout = setTimeout(() => {
      this.wrapperElement.remove();
      delete this;
    }, 10);
  }

  setElement(value) {
    this.element = value;
  }

  getElement() {
    return this.element;
  }

  createWrapperElement() {
    let wrapperElement = this.createElementDiv();
    wrapperElement.classList.add('modal-wrapper');
    return wrapperElement;
  }

  createModalElement() {
    let modalElement = this.createElementDiv();
    modalElement.classList.add('modal');
    return modalElement;
  }

  createWrapper() {
    let wrapperElement = this.createElementDiv();
    wrapperElement.classList.add('modal-wrapper');
    return wrapperElement;
  }

  createElementDiv() {
    return document.createElement('div');
  }
}