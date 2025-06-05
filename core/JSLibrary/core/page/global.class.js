/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';

import {Interactive} from "../../interactive.class.js";

export class PageGlobal {
  constructor(page, params = {}) {
    this.page = page;
  }

  /**
   * SYSTEM_GE_IMC_00000001 | Интерактивный элемент вызова окна авторизации пользователя
   * SYSTEM_GE_IMC_00000002 | 
   */

  /**
   * Глобальная инициализация страницы
   */
  init() {
    let locales;

    /** @var {HTMLElement} */
    let navigationBurgerElement = document.querySelector('[role="navagation-burger"]');
    if (navigationBurgerElement != null) {
      navigationBurgerElement.addEventListener('click', (event) => {
        navigationBurgerElement.classList.toggle('is-active');
      });
    }

    fetch('/handler/locales', {method: 'GET'}).then((response) => {
      return (response.ok) ? response.json() : Promise.reject(response);
    }).then((data) => {
      locales = data.outputData.locales;

      let footerLocalesListContainerElement = document.querySelector('[role="footer-locales-list"]');
      
      if (footerLocalesListContainerElement != null) {
        let footerLocalesListElement = document.createElement('ul');
        footerLocalesListElement.classList.add('locales-list');

        locales.forEach((element, elementIndex) => {
          let footerLocalesListItemElement = document.createElement('li');
          let localeImageElement = document.createElement('img');

          footerLocalesListItemElement.classList.add('locales-list__item');
          footerLocalesListItemElement.classList.add('item');
          localeImageElement.classList.add('item__image');

          localeImageElement.setAttribute('src', element.iconURL);
          localeImageElement.setAttribute('alt', element.title);
          
          footerLocalesListItemElement.addEventListener('click', (event) => {
            event.preventDefault();

            document.cookie = `locale=${element.name}; max-age=max-age-in-seconds; path=/`;
            window.location.reload();
          });

          footerLocalesListItemElement.append(localeImageElement);
          footerLocalesListElement.append(footerLocalesListItemElement);
        });

        footerLocalesListContainerElement.append(footerLocalesListElement);
      }

      return window.CMSCore.locales.base.getData();
    }, (rejectionReason) => {
      let interactiveNotification = new Interactive('notification');
      interactiveNotification.target.isPopup = true;
      interactiveNotification.target.setStatusCode(0);
      interactiveNotification.target.setContent(rejectionReason);
      interactiveNotification.target.assembly();

      interactiveNotification.target.show();
    }).then((localeData) => {
      let profileNavigationItemExitElement = document.querySelector('[role="profileNavigationExit"]');
      if (profileNavigationItemExitElement != null) {
        profileNavigationItemExitElement.addEventListener('click', (event) => {
          event.preventDefault();

          let request = new Interactive('request', {
            method: 'POST',
            url: '/handler/client/session-end?level=1'
          });

          request.target.send().then((data) => {
            if (data.statusCode == 1 && data.outputData.hasOwnProperty('result')) {
              let result = data.outputData.result;

              if (result == true) {
                window.location.reload();
              }
            }
          });
        });
      }

      let systemGlobalElements = document.querySelectorAll('[id^=SYSTEM_GE_]');
      systemGlobalElements.forEach((element, elementIndex) => {
        if (element.id.includes('IMC_00000001')) {
          element.addEventListener('click', (event) => {
            event.preventDefault();

            /** @type {Interactive} */
            let authForm = new Interactive('form');
            authForm.target.init({
              method: 'POST',
              action: '/handler/utils/authorization?method=base&localeMessage=' + window.CMSCore.locales.base.name
            });

            /** @type {ElementInput} */
            let authFormInputLogin = authForm.target.createElementInput();
            
            authFormInputLogin.init({
              name: 'user_login',
              type: 'text',
              required: true
            });

            authFormInputLogin.element.placeholder = localeData.MODAL_AUTHORIZATION_INPUT_LOGIN_PLACEHOLDER;

            /** @type {ElementInput} */
            let authFormInputPassword = authForm.target.createElementInput();
            authFormInputPassword.init({
              name: 'user_password',
              type: 'password',
              required: true
            });
            authFormInputPassword.element.placeholder = localeData.MODAL_AUTHORIZATION_INPUT_PASSWORD_PLACEHOLDER;

            /** @type {ElementInput} */
            let authFormInputCheckbox = authForm.target.createElementInput();
            authFormInputCheckbox.init({
              name: 'user_remember_me',
              type: 'checkbox'
            });

            authForm.target.element.firstChild.append(authFormInputLogin.element);
            authForm.target.element.firstChild.append(authFormInputPassword.element);

            let rememberContainerElement = document.createElement('div');
            rememberContainerElement.classList.add('form__input-container');
            rememberContainerElement.classList.add('input-container');
            rememberContainerElement.classList.add('input-container_flex-checkbox');

            rememberContainerElement.append(authFormInputCheckbox.element);

            let rememberLabelContainerElement = document.createElement('div');
            rememberLabelContainerElement.classList.add('input-container__label');
            rememberLabelContainerElement.classList.add('label');
            rememberLabelContainerElement.innerHTML = localeData.DEFAULT_TEXT_USER_AUTHORIZATION_REMEMBER_ME;

            rememberContainerElement.append(rememberLabelContainerElement);

            authForm.target.element.firstChild.append(rememberContainerElement);

            /** Модальное окно для создания запроса на авторизацию
             * @type {Interactive}
             */
            let interactiveModal = new Interactive('modal', {
              title: localeData.MODAL_AUTHORIZATION_IN_SYSTEM_TITLE,
              content: localeData.MODAL_AUTHORIZATION_IN_SYSTEM_DESCRIPTION,
              width: 300
            });

            interactiveModal.target.addButton(localeData.BUTTON_AUTHORIZATION_LABEL, () => {
              if (authForm.target.checkRequiredFields()) {
                authForm.target.send();
              }
            });

            // Добавление кнопки "Восстановление пароля/Забыл пароль"
            interactiveModal.target.addButton(localeData.BUTTON_RECOVERY_LABEL, () => {
              /** Форма для создания запроса на восстановление пароля 
               * @type {Interactive}
               */
              let requestForm = new Interactive('form');
              requestForm.target.init({
                method: 'POST',
                action: '/handler/user/reset?localeMessage=' + window.CMSCore.locales.base.name
              });
              
              /** Модальное окно для создания запроса на восстановление пароля
               * @type {Interactive}
               */
              let interactiveSubModal = new Interactive('modal', {
                title: localeData.MODAL_AUTHORIZATION_RECOVERY_TITLE,
                content: localeData.MODAL_AUTHORIZATION_RECOVERY_DESCRIPTION,
                width: 300
              });

              interactiveSubModal.target.addButton(localeData.BUTTON_SEND_LABEL, () => {
                // Отправка формы запроса на восстановление пароля
                requestForm.target.send();
                // Закрытие текущего модального окна
                interactiveSubModal.target.close();
                // Восстановление родительского модального окна
                interactiveModal.target.show();
              });

              // Открытие родительского модального окна при закрытии текущего
              interactiveSubModal.target.onCloseCallbackFunction = () => {
                interactiveModal.target.show();
              };
              
              requestForm.target.successCallback = (data) => {};

              /** @type {ElementInput} */
              let requestFormInput = requestForm.target.createElementInput();
              requestFormInput.init({
                name: 'user_login_or_email',
                type: 'text'
              });

              requestFormInput.element.placeholder = localeData.MODAL_AUTHORIZATION_INPUT_PASSWORD_OR_LOGIN_PLACEHOLDER;
              requestForm.target.element.firstChild.append(requestFormInput.element);

              interactiveModal.target.hide();

              interactiveSubModal.assembly();
              let interactiveSubModalBody = interactiveSubModal.target.element.querySelector('.modal__body-container');
              interactiveSubModalBody.append(requestForm.target.element);

              document.body.appendChild(interactiveSubModal.target.element);
              interactiveSubModal.target.show();
            });

            interactiveModal.assembly();
            let interactiveModalBody = interactiveModal.target.element.querySelector('.modal__body-container');
            interactiveModalBody.append(authForm.target.element);

            document.body.appendChild(interactiveModal.target.element);
            interactiveModal.target.show();
          });
        }
      });
    }, (rejectionReason) => {
      let interactiveNotification = new Interactive('notification');
      interactiveNotification.target.isPopup = true;
      interactiveNotification.target.setStatusCode(0);
      interactiveNotification.target.setContent(rejectionReason);
      interactiveNotification.target.assembly();

      interactiveNotification.target.show();
    });
  }
}