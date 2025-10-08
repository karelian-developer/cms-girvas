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
    let inputTipsAll = [], inputsArray = this.element.querySelectorAll('input');

    let inputTips = this.element.querySelectorAll('.input-tip');
    let inputsGroupTips = this.element.querySelectorAll('.inputs-group-tip');

    if (inputsArray.length > 0) {
      inputsArray.forEach((element) => {
        const elementStyle = element.getComputedStyle();

        if (element.hasAttribute('cmsg-password-checker')) {
          const passwordProgressLevelElement = document.createElement('div');
          const passwordProgressLevelContainerElement = document.createElement('div');

          passwordProgressLevelContainerElement.append(passwordProgressLevelElement);

          passwordProgressLevelContainerElement.style.height = '5px';
          passwordProgressLevelContainerElement.style.width = '100%';
          passwordProgressLevelContainerElement.style.position = 'absolute';
          passwordProgressLevelContainerElement.style.top = elementStyle.height + 'px';

          passwordProgressLevelElement.style.height = '100%';
          passwordProgressLevelElement.style.width = '25%';
          passwordProgressLevelElement.style.backgroundColor = 'red';
          
          element.after(passwordProgressLevelContainerElement);

          element.addEventListener('input', (event) => {
            let passwordPoints = 0;

            if (event.target.value.length >= 6) {
              passwordPoints += 5;
            }

            if (event.target.value.match(/[a-z]/)) {
              passwordPoints += 5;
            }

            if (event.target.value.match(/[A-Z]/)) {
              passwordPoints += 5;
            }

            if (event.target.value.match(/[0-9]/)) {
              passwordPoints += 5;
            }

            if (event.target.value.match(/[\!\@\#\$\%\&]/)) {
              passwordPoints += 10;
            }

            if (passwordPoints >= 5 && passwordPoints < 15) {
              passwordProgressLevelElement.style.width = '25%';
              passwordProgressLevelElement.style.backgroundColor = 'red';
            }

            if (passwordPoints >= 15 && passwordPoints < 20) {
              passwordProgressLevelElement.style.width = '50%';
              passwordProgressLevelElement.style.backgroundColor = 'orange';
            }

            if (passwordPoints >= 20 && passwordPoints < 30) {
              passwordProgressLevelElement.style.width = '75%';
              passwordProgressLevelElement.style.backgroundColor = 'yellow';
            }

            if (passwordPoints >= 30) {
              passwordProgressLevelElement.style.width = '100%';
              passwordProgressLevelElement.style.backgroundColor = 'green';
            }
          });
        }
      });
    }

    if (inputTips.length > 0) {
      inputTips.forEach((element) => {
        inputTipsAll.push(element);
      });
    }
    if (inputsGroupTips.length > 0) {
      inputsGroupTips.forEach((element) => {
        inputTipsAll.push(element);
      });
    }

    if (inputTipsAll.length > 0) {
      inputTipsAll.forEach((element) => {
        let elementRole = element.getAttribute('role');
        if (elementRole == 'passwords-show') {
          let elementParentElement = element.parentElement;
          elementParentElement.style.position = 'relative';

          let inputsPasswordElements = elementParentElement.querySelectorAll('input[type="password"]');

          if (inputsPasswordElements.length > 0) {
            inputsPasswordElements.forEach((inputElement) => {
              element.addEventListener('click', (event) => {
                let inputElementType = inputElement.getAttribute('type');

                if (inputElementType == 'password') {
                  inputElement.setAttribute('type', 'text');
                } else {
                  inputElement.setAttribute('type', 'password');
                }
              });
            });
          }
        }
      });
    }

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