// core/JSLibrary/core/page/global.class.js

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

import {Interactive} from "../../interactive.class.js";
import {Client} from "../client.class.js";

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
    
    this.initCodeCopy();
    this.initGalleries();
    this.initCookieBanner();

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

      return this.page.core.locales.base.getData();
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

      // Подмена интерактивной базы «Select» на интерактивный элемент «Choice»
      const intractiveChoiceBases = document.querySelectorAll('[data-interactive-base]');
      intractiveChoiceBases.forEach(selectElement => {
        if (selectElement.tagName === 'SELECT') {
          const selectElementOptionsElements = selectElement.querySelectorAll('option');
          const intractiveChoice = new Interactive('choices');
          
          selectElementOptionsElements.forEach(optionElement => {
            intractiveChoice.target.addItem(optionElement.innerText, optionElement.value);
          });

          intractiveChoice.target.setWidth('100%');
          intractiveChoice.target.setName(selectElement.getAttribute('name'));
          intractiveChoice.assembly();

          selectElement.replaceWith(intractiveChoice.target.element);
        }
      });

      let systemGlobalElements = document.querySelectorAll('[id^=SYSTEM_GE_]');
      systemGlobalElements.forEach((element, elementIndex) => {
        if (element.id.includes('IMC_00000001')) {
          element.addEventListener('click', (event) => {
            event.preventDefault();

            /** @type {Interactive} */
            let authForm = new Interactive('form');
            authForm.target.init({
              method: 'POST',
              action: '/handler/utils/authorization?method=base&localeMessage=' + this.page.core.locales.base.name
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
                action: '/handler/user/reset?localeMessage=' + this.page.core.locales.base.name
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

  initGalleries() {
    console.log('[PageGlobal] initGalleries');
    console.log(document.querySelectorAll('.nadvo-gallery'));

    const galleryElements = document.querySelectorAll('.nadvo-gallery');

    galleryElements.forEach((galleryElement) => {
      const interactiveGallery = new Interactive('gallery');
      console.log(galleryElement.innerHTML);

      const images = galleryElement.querySelectorAll('img');

      console.log(images);

      images.forEach((image) => {
        interactiveGallery.target.addItem(
          image.getAttribute('src'),
          image.getAttribute('alt') || ''
        );
      });

      interactiveGallery.assembly();

      galleryElement.innerHTML = '';
      galleryElement.appendChild(interactiveGallery.target.element);
    });

    console.log('[PageGlobal] content inserted', document.querySelectorAll('.nadvo-gallery'));
  }

  /**
   * Инициализация cookie-баннера (152-ФЗ)
   *
   * Проверяет настройку `security_cookie_banner_status` через API,
   * показывает модалку при первом заходе, фиксирует согласие.
   */
  async initCookieBanner() {
    if (Client.existsCookie('allowCookies')) {
      return;
    }

    const settings = await this.page.core.getSettings([
      'security_cookie_banner_status',
      'security_cookie_banner_document_id'
    ]);

    if (settings.security_cookie_banner_status !== true) {
      return;
    }

    const cookieDocumentID = parseInt(settings.security_cookie_banner_document_id, 10) || 0;

    if (cookieDocumentID <= 0) {
      return;
    }

    let cookieDocument = null;

    try {
      const response = await fetch(
        '/handler/pageStatic/' + cookieDocumentID +
        '?locale=' + this.page.core.locales.base.name +
        '&localeMessage=' + this.page.core.locales.base.name,
        { method: 'GET', credentials: 'same-origin' }
      );

      if (response.ok) {
        const data = await response.json();
        if (data.statusCode === 1 && data.outputData && data.outputData.pageStatic) {
          cookieDocument = data.outputData.pageStatic;
        }
      }
    } catch (e) {
      this.page.core.debugError(1, 'CookieBanner', 'Failed to fetch document: ' + e);
      return;
    }

    if (!cookieDocument || !cookieDocument.id) {
      return;
    }

    const localeData = this.page.core.localeData;
    const contentElement = document.createElement('div');
    contentElement.classList.add('cookie-banner');
    contentElement.innerHTML = localeData.MODAL_COOKIE_SITE_USING_DESCRIPTION || '';

    if (cookieDocument && cookieDocument.name) {
      const linkElement = document.createElement('a');
      linkElement.href = '/page/' + cookieDocument.name;
      linkElement.target = '_blank';
      linkElement.rel = 'noopener';
      linkElement.textContent = localeData.MODAL_COOKIE_SITE_USING_LINK_LABEL || 'Подробнее';
      linkElement.classList.add('cookie-banner__link');

      contentElement.appendChild(document.createElement('br'));
      contentElement.appendChild(linkElement);
    }

    const modal = new Interactive('modal', {
      title: localeData.MODAL_COOKIE_SITE_USING_TITLE || 'Использование cookie',
      content: contentElement
    });

    modal.target.addButton(localeData.BUTTON_SUBMIT_LABEL || 'Принять', () => {
      this.submitCookieConsent(cookieDocument, true);
      modal.target.close();
    });

    modal.target.addButton(localeData.BUTTON_DECLINE_LABEL || 'Отказаться', () => {
      this.submitCookieConsent(cookieDocument, false);
      modal.target.close();
    });

    modal.assembly();
    document.body.appendChild(modal.target.element);
    modal.target.show();
  }

  /**
   * Отправить согласие/отказ на cookie
   *
   * @param {Object|null} cookieDocument — объект документа (id, version)
   * @param {boolean} accepted
   */
  submitCookieConsent(cookieDocument, accepted) {
    Client.setCookie('allowCookies', accepted ? 'true' : 'false', 366);

    if (!accepted) {
      return;
    }

    const documentVersion = cookieDocument ? cookieDocument.currentVersion : '';

    if (!cookieDocument || !cookieDocument.id || !documentVersion) {
      return;
    }

    const formData = new FormData();
    formData.append('pageStaticID', cookieDocument.id);
    formData.append('documentVersion', documentVersion);
    formData.append('locale', this.page.core.locales.base.name);

    const request = new Interactive('request', {
      method: 'POST',
      url: '/handler/client/consent-cookie?localeMessage=' + this.page.core.locales.base.name
    });

    request.target.data = formData;
    request.target.send();
  }

  /**
   * Инициализация копирования кода
   * 
   * Находит все <code> и <pre> на странице и добавляет кнопку копирования
   */
  initCodeCopy() {
    // Обрабатываем <pre> блоки
    const preBlocks = document.querySelectorAll('pre');
    preBlocks.forEach((preBlock) => {
      this.addCopyButtonToPre(preBlock);
    });

    // Обрабатываем <code> элементы, которые не внутри <pre>
    const codeElements = document.querySelectorAll('code');
    codeElements.forEach((codeElement) => {
      // Пропускаем code внутри pre (уже обработаны)
      if (!codeElement.closest('pre')) {
        this.addCopyButtonToCode(codeElement);
      }
    });
  }

  /**
   * Добавление кнопки копирования к <pre> блоку
   */
  addCopyButtonToPre(preBlock) {
    // Пропускаем, если кнопка уже добавлена
    if (preBlock.querySelector('.code-copy-button')) {
      return;
    }

    // Создаем обертку, если нужно
    let wrapper = preBlock.parentElement;
    if (!wrapper.classList.contains('code-block-wrapper')) {
      wrapper = document.createElement('div');
      wrapper.classList.add('code-block-wrapper');
      preBlock.parentNode.insertBefore(wrapper, preBlock);
      wrapper.appendChild(preBlock);
    }

    // Создаем кнопку копирования
    const copyButton = this.createCopyButton();
    
    // Добавляем кнопку в обертку
    wrapper.appendChild(copyButton);

    // Обработчик клика
    copyButton.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();
      
      const codeText = preBlock.textContent;
      this.copyTextToClipboard(codeText, copyButton);
    });

    // Показываем кнопку при наведении
    wrapper.addEventListener('mouseenter', () => {
      copyButton.classList.add('code-copy-button_visible');
    });

    wrapper.addEventListener('mouseleave', () => {
      copyButton.classList.remove('code-copy-button_visible');
    });
  }

  /**
   * Добавление кнопки копирования к <code> элементу (инлайн)
   */
  addCopyButtonToCode(codeElement) {
    // Пропускаем, если кнопка уже добавлена
    if (codeElement.querySelector('.code-copy-button')) {
      return;
    }

    // Создаем обертку
    const wrapper = document.createElement('span');
    wrapper.classList.add('inline-code-wrapper');
    codeElement.parentNode.insertBefore(wrapper, codeElement);
    wrapper.appendChild(codeElement);

    // Создаем кнопку копирования
    const copyButton = this.createCopyButton(true);
    
    // Добавляем кнопку в обертку
    wrapper.appendChild(copyButton);

    // Обработчик клика
    copyButton.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();
      
      const codeText = codeElement.textContent;
      this.copyTextToClipboard(codeText, copyButton);
    });

    // Показываем кнопку при наведении
    wrapper.addEventListener('mouseenter', () => {
      copyButton.classList.add('code-copy-button_visible');
    });

    wrapper.addEventListener('mouseleave', () => {
      copyButton.classList.remove('code-copy-button_visible');
    });
  }

  /**
   * Создание кнопки копирования
   */
  createCopyButton(isInline = false) {
    const button = document.createElement('button');
    button.setAttribute('type', 'button');
    button.setAttribute('aria-label', 'Копировать код');
    button.classList.add('code-copy-button');
    
    if (isInline) {
      button.classList.add('code-copy-button_inline');
    }

    // SVG иконка копирования
    button.innerHTML = `
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
      </svg>
      <span>Копировать</span>
    `;

    return button;
  }

  /**
   * Копирование текста в буфер обмена
   */
  async copyTextToClipboard(text, button) {
    try {
      // Пытаемся использовать современный API
      if (navigator.clipboard && window.isSecureContext) {
        await navigator.clipboard.writeText(text);
        this.showCopySuccess(button);
      } else {
        // Fallback для старых браузеров
        this.copyTextToClipboardFallback(text, button);
      }
    } catch (error) {
      console.error('Failed to copy:', error);
      this.copyTextToClipboardFallback(text, button);
    }
  }

  /**
   * Fallback метод копирования для старых браузеров
   */
  copyTextToClipboardFallback(text, button) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.top = '0';
    textarea.style.left = '0';
    textarea.style.opacity = '0';
    textarea.style.pointerEvents = 'none';

    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();

    try {
      const successful = document.execCommand('copy');
      if (successful) {
        this.showCopySuccess(button);
      } else {
        this.showCopyError(button);
      }
    } catch (error) {
      console.error('Fallback failed:', error);
      this.showCopyError(button);
    } finally {
      document.body.removeChild(textarea);
    }
  }

  /**
   * Показ успешного копирования
   */
  showCopySuccess(button) {
    const label = button.querySelector('span');
    
    if (label) {
      label.textContent = 'Скопировано!';
    }
    
    button.classList.add('code-copy-button_success');
    
    // Меняем иконку на галочку
    const svg = button.querySelector('svg');
    if (svg) {
      svg.innerHTML = '<polyline points="20 6 9 17 4 12"></polyline>';
    }

    // Возвращаем исходное состояние через 2 секунды
    setTimeout(() => {
      if (label) {
        label.textContent = 'Копировать';
      }
      
      button.classList.remove('code-copy-button_success');
      
      if (svg) {
        svg.innerHTML = '<rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>';
      }
    }, 2000);
  }

  /**
   * Показ ошибки копирования
   */
  showCopyError(button) {
    const label = button.querySelector('span');
    
    if (label) {
      label.textContent = 'Ошибка!';
    }
    
    button.classList.add('code-copy-button_error');

    setTimeout(() => {
      if (label) {
        label.textContent = 'Копировать';
      }
      
      button.classList.remove('code-copy-button_error');
    }, 2000);
  }
}