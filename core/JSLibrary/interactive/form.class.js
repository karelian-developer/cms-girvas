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

    const form = formElement.firstChild;
    const formMethod = form.getAttribute('method') || 'POST';
    const formAction = form.getAttribute('action') || '/';

    if (formMethod.toUpperCase() === 'GET') {
      // Собираем данные формы вручную
      const formData = new FormData(form);
      const searchParams = new URLSearchParams();
      
      for (let [key, value] of formData.entries()) {
        if (key === 'value') {  // поле поиска
          searchParams.append(key, value);
        }
      }
      
      // Добавляем localeMessage
      if (locale) {
        searchParams.append('localeMessage', locale.name)
      };
      
      // Формируем полный URL
      const fullURL = formAction + '?' + searchParams.toString();
      
      const request = new Interactive('request', {
        method: 'GET',
        url: fullURL
      });
      
      request.target.send(true);
    } else {
      const request = new Interactive('request', {
        method: 'POST',
        url: formAction + `?localeMessage=${locale.name}`,
        data: form
      });

      request.target.send(true);
    }
  }

  assembly() {
    //
  }
}