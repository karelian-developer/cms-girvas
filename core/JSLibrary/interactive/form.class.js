/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';

//import {Interactive} from "../interactive.class.js";
import {ElementTextarea} from "./form/elementTextarea.class.js";
import {ElementInput} from "./form/elementInput.class.js";
import {ElementButton} from "./form/elementButton.class.js";
import {Interactive} from "../interactive.class.js";

/**
 * Интерактивная форма (экспериментальный класс)
 */
export class Form {
  /**
   * constructor
   * 
   * @param {*} element 
   */
  constructor(interactiveObject, element = null) {
    this.interactiveObject = interactiveObject;

    this.element = element;
    this.successCallback = (data) => {};
    this.failCallback = (error) => {};
  }
  
  /**
   * Инициализация формы
   * 
   * @param {*} attributes атрибуты
   * @param {*} successCallback колбэк-функция при успешной отправке формы
   * @param {*} failCallback колбэк-функция при неуспешной отправке формы
   */
  init(attributes = {}, successCallback = () => {}, failCallback = () => {}) {
    let element = document.createElement('div');
    let elementForm = document.createElement('form');

    if (typeof attributes.id != 'undefined') {
      elementForm.setAttribute('id', attributes.id);
    }

    if (typeof attributes.enctype != 'undefined') {
      elementForm.setAttribute('id', attributes.enctype);
    }

    if (typeof attributes.method != 'undefined') {
      elementForm.setAttribute('method', attributes.method);
    }

    if (typeof attributes.action != 'undefined') {
      elementForm.setAttribute('action', attributes.action);
    }

    if (typeof attributes.role != 'undefined') {
      elementForm.setAttribute('role', attributes.role);
    }

    elementForm.classList.add('form');

    element.append(elementForm);

    this.element = element;
  }

  /**
   * Замена элемента формы
   * 
   * @param {*} element 
   */
  replaceElement(element) {
    this.element = element;
  }

  /**
   * Проверка обязательных полей формы
   * 
   * @returns {boolean}
   */
  checkRequiredFields() {
    let arrayElements = [];

    let arrayInputs = this.element.querySelectorAll('input[required]');
    let arrayTextareas = this.element.querySelectorAll('textarea[required]');
    let arraySelects = this.element.querySelectorAll('select[required]');

    let emptyFieldDetected = false;

    arrayInputs.forEach((element) => {
      arrayElements.push(element);
    });

    arrayTextareas.forEach((element) => {
      arrayElements.push(element);
    });

    arraySelects.forEach((element) => {
      arrayElements.push(element);
    });

    arrayElements.forEach((element) => {
      if ((element.type === 'checkbox' || element.type === 'radio')) {
        if (!element.checked) {
          emptyFieldDetected = true;
        }
      } else {
        if (element.value.trim() === '') {
          emptyFieldDetected = true;
        }
      }
    });

    return (emptyFieldDetected) ? false : true;
  }

  /**
   * Создание элемента Textarea
   * 
   * @param {*} attributes 
   * @returns {ElementTextarea}
   */
  createElementTextarea(attributes = {}) {
    let element = new ElementTextarea(this);
    element.init(attributes);
    return element;
  }

  /**
   * Создание элемента Input
   * 
   * @param {*} attributes 
   * @returns {ElementInput}
   */
  createElementInput(attributes = {}) {
    let element = new ElementInput(this);
    element.init(attributes);
    return element;
  }

  /**
   * Создание элемента Button
   * 
   * @param {*} attributes 
   * @returns {ElementButton}
   */
  createElementButton(attributes = {}) {
    let element = new ElementButton(this);
    element.init(attributes);
    return element;
  }

  /**
   * Отправка данных формы
   * 
   * @param {*} senderParams 
   */
  send(senderParams = {}) {
    let locale, CMSCore = window.CMSCore;

    if (CMSCore.pages.hasOwnProperty('admin')) {
      if (CMSCore.pages.admin.hasOwnProperty('global')) {
        locale = window.CMSCore.locales.admin;
      }
    }

    if (CMSCore.pages.hasOwnProperty('default')) {
      if (CMSCore.pages.default.hasOwnProperty('global')) {
        locale = window.CMSCore.locales.base;
      }
    }

    if (CMSCore.pages.hasOwnProperty('install')) {
      if (CMSCore.pages.install.hasOwnProperty('global')) {
        locale = window.CMSCore.locales.install;
      }
    }

    let formElement, formMethod, formAction;

    formElement = this.element;
    formAction = (typeof senderParams.action == 'undefined') ? formElement.firstChild.getAttribute('action') : senderParams.action;
    formMethod = (typeof senderParams.method == 'undefined') ? formElement.firstChild.getAttribute('method') : senderParams.method;

    let request, requestURL;

    requestURL = formAction;

    request = new Interactive('request', {
      method: formMethod,
      url: formAction + `?localeMessage=${locale.name}`,
      data: formElement.firstChild
    });

    request.target.send();
  }

  assembly() {
    //
  }
}