/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';

import {Interactive} from './interactive.class.js';

/**
 * Форма (устаревшее)
 */
export class Form {
  /**
   * constructor
   * 
   * @param {HTMLFormElement} element
   */
  constructor(element, locale) {
    this.modalParent = null;
    this.timeout = null;
    this.locale = locale;
    this.setFormElement(element);
  }

  /**
   * Назначить элемент формы
   * 
   * @param {HTMLFormElement} element 
   */
  setFormElement(element) {
    /** @type {HTMLFormElement} */
    this.element = element;
  }

  /**
   * Инициализация элемента формы
   */
  initFormElement() {
    this.element.addEventListener('submit', (event) => {
      event.preventDefault();
      this.send(event);

      return false;
    });
  }

  getFormMethod(submitEvent) {
    if (submitEvent.submitter.hasAttribute('formmethod')) {
      if (submitEvent.submitter.getAttribute('formmethod') != '') {
        return submitEvent.submitter.getAttribute('formmethod');
      }
    }

    return (this.element.hasAttribute('method')) ? this.element.getAttribute('method') : 'POST';
  }

  getFormAction() {
    return (this.element.hasAttribute('action')) ? this.element.getAttribute('action') : '/handler';
  }

  send(event) {
    let submitter, submitterName, submitterMethod;

    submitter = event.submitter;
    submitterName = (submitter.hasAttribute('name')) ? submitter.getAttribute('name') : 'submitter_anomymous';
    submitterMethod = (submitter.hasAttribute('formmethod')) ? submitter.getAttribute('formmethod') : 'POST';

    let form, formMethod, formAction;

    form = event.target;
    formMethod = (event.target.hasAttribute('method')) ? event.target.getAttribute('method') : 'POST';
    formAction = (event.target.hasAttribute('action')) ? event.target.getAttribute('action') : '/';

    let request, requestMethod, requestURL;

    requestMethod = (submitter.hasAttribute('formmethod')) ? submitterMethod : formMethod;
    requestURL = formAction;

    request = new Interactive('request', {
      method: requestMethod,
      url: formAction + `?localeMessage=${this.locale.name}`,
      data: this.element
    });

    request.target.send();
  }
}