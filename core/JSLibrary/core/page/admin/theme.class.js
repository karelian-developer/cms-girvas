/**
 * CMS «ГИРВАС»
 *
 * Включена в Реестр российского программного обеспечения Минцифры РФ.
 * Реестровый номер: №25012 от 27.11.2024
 *
 * @copyright Copyright (c) 2021 - 2027, ИП Шестаков А.Р., «Карельский разработчик».
 *             Все права защищены.
 * @license   https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 * @see       https://gitflic.ru/project/garbalo/cms-girvas Репозиторий продукта
 * @see       https://cms-girvas.ru Сайт продукта
 * @author    Андрей Шестаков <andrey.shestakov@karelian-developer.ru>
 * @support   support@karelian-developer.ru
 */

'use strict';

import {Interactive} from "../../../interactive.class.js";
import {URLParser} from "../../../urlParser.class.js";

export class PageTheme {
  constructor(page, params = {}) {
    this.page = page;
  }

  /**
   * Инициализация галереи
   */
  initGallery(galleryContainer) {
    if (galleryContainer.querySelector('.interactive_gallery') !== null) {
      return;
    }

    let imageElements = galleryContainer.querySelectorAll('ul > li > img');

    if (imageElements.length === 0) {
      imageElements = Array.from(galleryContainer.querySelectorAll('img'))
        .filter((img) => !img.closest('[role="controller"], .gallery__controllers-list'));
    }

    const items = Array.from(imageElements).map((img) => ({
      url: img.getAttribute('src') ?? '',
      caption: img.getAttribute('alt') ?? ''
    }));

    if (items.length === 0) return;

    const gallery = new Interactive('gallery');
    items.forEach(({url, caption}) => gallery.target.addItem(url, caption));
    gallery.assembly();

    galleryContainer.replaceChildren(gallery.target.element);
  }

  /**
   * Инициализация селектора языка для текстовых свойств шаблона
   *
   * @param {HTMLElement} containerElement Контейнер #E8548530785
   * @param {HTMLTemplateElement} templateBlock Блок .template
   *
   * @returns {HTMLSelectElement|null} Скрытый select селектора (для чтения активной локали)
   */
  initLocaleSelector(containerElement, templateBlock) {
    const interactiveLocaleChoices = new Interactive('choices');
    const adminLocaleName = this.page.core.locales.admin.name;

    this.page.core.locales.list.forEach((locale, localeIndex) => {
      const icon = document.createElement('img');
      icon.setAttribute('src', locale.iconURL);
      icon.setAttribute('alt', locale.title);

      const label = document.createElement('span');
      label.innerText = locale.title;

      const tpl = document.createElement('template');
      tpl.innerHTML += icon.outerHTML;
      tpl.innerHTML += label.outerHTML;

      interactiveLocaleChoices.target.addItem(tpl.innerHTML, locale.name);

      if (locale.name === adminLocaleName) {
        interactiveLocaleChoices.target.setItemSelectedIndex(localeIndex);
      }
    });

    // Это управляющий элемент, а не поле формы — setName не вызываем.
    interactiveLocaleChoices.assembly();
    containerElement.append(interactiveLocaleChoices.target.element);

    const localeSelectElement = interactiveLocaleChoices.target.element.querySelector('select');
    if (localeSelectElement === null) {
      return null;
    }

    localeSelectElement.addEventListener('change', async (event) => {
      const activeLocale = event.target.value;
      const themeName = templateBlock.getAttribute('data-template-name');

      const url = '/handler/template/properties'
        + '?templateName=' + encodeURIComponent(themeName)
        + '&templateCategory=base'
        + '&locale=' + encodeURIComponent(activeLocale)
        + '&localeMessage=' + encodeURIComponent(this.page.core.locales.admin.name);

      let response;
      try {
        response = await fetch(url, {method: 'GET', credentials: 'same-origin'});
      } catch (error) {
        console.error('[CMSCore] Locale properties request failed:', error);
        return;
      }

      if (!response.ok) {
        console.error('[CMSCore] Locale properties request returned HTTP', response.status);
        return;
      }

      const data = await response.json();
      if (data.statusCode !== 1) {
        console.error('[CMSCore] Locale properties request returned statusCode', data.statusCode, data.message ?? '');
        return;
      }

      const properties = data.outputData.properties ?? {};
      const propertiesContainer = document.querySelector('#THEME_PROPERTIES');
      if (propertiesContainer === null) return;

      propertiesContainer.querySelectorAll('[data-property-name]').forEach((input) => {
        const propertyName = input.getAttribute('data-property-name');
        if (!Object.prototype.hasOwnProperty.call(properties, propertyName)) {
          return;
        }

        // Для file-свойств подменять value нельзя — там путь к файлу,
        // а сам файл выбирается через input[type="file"]. Пропускаем.
        if (input.getAttribute('type') === 'file') {
          return;
        }

        input.value = properties[propertyName];
        input.removeAttribute('data-file');
      });
    });

    return localeSelectElement;
  }

  init() {
    let searchParams = new URLParser(), locales;
    let buttons = {install: null, download: null, delete: null, saveProperties: null};

    let templateBlock = document.querySelector('.template');
    let templateName = templateBlock.getAttribute('data-template-name');
    let templateDownloadedStatus = templateBlock.getAttribute('data-template-dowloaded-status');
    let templateInstalledStatus = templateBlock.getAttribute('data-template-installed-status');
    let interactiveContainerElement = document.querySelector('#E8548530785');

    this.page.core.locales.admin.getData().then((localeData) => {
      if (searchParams.getPathPart(2) !== null) {
        // Селектор языка — только для установленного шаблона (есть что переключать)
        const localeSelectElement = this.initLocaleSelector(interactiveContainerElement, templateBlock);

        let pageGalleryElement = templateBlock.querySelector('[class*="__gallery-container"]');
        if (pageGalleryElement !== null) {
          this.initGallery(pageGalleryElement);
        }

        buttons.install = new Interactive('button');
        buttons.install.target.setLabel(localeData.BUTTON_INSTALL_LABEL);
        buttons.install.target.setCallback(() => {
          let formData = new FormData();
          formData.append('setting_base_template', templateName);

          let request = new Interactive('request', {
            method: 'POST',
            url: '/handler/settings?localeMessage=' + window.CMSCore.locales.admin.name
          });

          request.target.data = formData;

          request.target.send().then((data) => {
            if (data.statusCode === 1) {
              buttons.install.target.element.style.display = 'none';
              buttons.delete.target.element.style.display = 'none';
              buttons.download.target.element.style.display = 'none';

              templateBlock.setAttribute('data-template-installed-status', 'installed');
            }
          });
        });

        buttons.delete = new Interactive('button');
        buttons.delete.target.setLabel(localeData.BUTTON_DELETE_LABEL);
        buttons.delete.target.setCallback(() => {
          let formData = new FormData();
          formData.append('template_name', templateName);
          formData.append('template_category', 'base');

          let request = new Interactive('request', {
            method: 'DELETE',
            url: '/handler/template?localeMessage=' + window.CMSCore.locales.admin.name
          });

          request.target.data = formData;

          request.target.send().then((data) => {
            if (data.statusCode === 1) {
              buttons.download.target.element.style.display = 'flex';
              buttons.delete.target.element.style.display = 'none';
              buttons.install.target.element.style.display = 'none';

              templateBlock.setAttribute('data-template-installed-status', 'not-installed');
              templateBlock.setAttribute('data-template-dowloaded-status', 'not-dowloaded');
            }
          });
        });

        buttons.download = new Interactive('button');
        buttons.download.target.setLabel(localeData.BUTTON_DOWNLOAD_LABEL);
        buttons.download.target.setCallback(() => {
          let formData = new FormData();
          formData.append('template_name', templateName);

          let request = new Interactive('request', {
            method: 'POST',
            url: '/handler/template?localeMessage=' + window.CMSCore.locales.admin.name
          });

          request.target.data = formData;

          request.target.send().then((data) => {
            if (data.statusCode === 1) {
              buttons.download.target.element.style.display = 'none';
              buttons.delete.target.element.style.display = 'flex';
              buttons.install.target.element.style.display = 'flex';

              templateBlock.setAttribute('data-template-installed-status', 'not-installed');
              templateBlock.setAttribute('data-template-dowloaded-status', 'dowloaded');
            }
          });
        });

        // Контейнер свойств объявляем ДО колбэка saveProperties,
        // иначе первый вызов колбэка ловит ReferenceError (TDZ).
        const themePropertiesContainerElement = document.querySelector('#THEME_PROPERTIES');

        if (templateInstalledStatus === 'installed') {
          buttons.saveProperties = new Interactive('button');
          buttons.saveProperties.target.setLabel(localeData.BUTTON_SAVE_LABEL);
          buttons.saveProperties.target.setCallback(() => {
            if (themePropertiesContainerElement !== null && !buttons.saveProperties.target.isDisabled()) {
              let formData = new FormData();

              formData.append('template_name', templateName);
              formData.append('template_category', 'base');

              // Активная локаль — из селектора языка (если он есть)
              if (localeSelectElement !== null) {
                formData.append('template_locale', localeSelectElement.value);
              }

              let propertiesValues = themePropertiesContainerElement.querySelectorAll('input');
              propertiesValues.forEach((property) => {
                if (property.hasAttribute('disabled')) {
                  return;
                }

                let propertyName = property.name;
                if (propertyName === '') {
                  return;
                }

                let propertyValue = property.hasAttribute('data-file')
                  ? property.getAttribute('data-file')
                  : property.value;

                formData.append(propertyName, propertyValue);
              });

              let request = new Interactive('request', {
                method: 'PATCH',
                url: '/handler/template?localeMessage=' + window.CMSCore.locales.admin.name
              });

              request.target.data = formData;

              request.target.send();
            }
          });

          let propertiesFilesValues = themePropertiesContainerElement.querySelectorAll('input[type="file"]');
          propertiesFilesValues.forEach((property) => {
            let buttonTrigger = new Interactive('button');
            buttonTrigger.target.setLabel(localeData.BUTTON_DOWNLOAD_LABEL);
            buttonTrigger.target.setCallback(() => {
              property.click();
            });

            buttonTrigger.assembly();

            property.after(buttonTrigger.target.element);

            property.addEventListener('change', (event) => {
              buttons.saveProperties.target.disable();

              event.preventDefault();

              property.setAttribute('disabled', 'disabled');

              let file = event.target.files[0], fileReader = new FileReader();

              if (!fileReader) {
                console.error(`[CMSCore] ${localeData.REPORT_JS_CMSCORE_ERROR_FILEREADER_IS_NOT_SUPPORTED}.`);
                return;
              }

              if (event.target.files.length === 0) {
                console.error(`[CMSCore] ${localeData.REPORT_JS_CMSCORE_ERROR_IMAGES_WHERE_NOT_LOADED}.`);
                return;
              }

              fileReader.onload = (event) => {
                buttons.saveProperties.target.enable();
                property.removeAttribute('disabled');
                property.setAttribute('data-file', fileReader.result);
              };

              fileReader.onerror = (event) => {
                buttons.saveProperties.target.enable();
                console.error(fileReader.result);
              };

              fileReader.readAsDataURL(file);
            });
          });

          buttons.saveProperties.assembly();

          buttons.saveProperties.target.element.style.display = templateInstalledStatus === 'installed' ? 'flex' : 'none';
          themePropertiesContainerElement.after(buttons.saveProperties.target.element);
        }

        buttons.delete.assembly();
        buttons.install.assembly();
        buttons.download.assembly();

        if (templateInstalledStatus === 'installed') {
          buttons.download.target.element.style.display = 'none';
          buttons.delete.target.element.style.display = 'none';
          buttons.install.target.element.style.display = 'none';
        } else {
          buttons.download.target.element.style.display = templateDownloadedStatus === 'downloaded' ? 'none' : 'flex';
          buttons.delete.target.element.style.display = templateDownloadedStatus === 'downloaded' ? 'flex' : 'none';
          buttons.install.target.element.style.display = templateDownloadedStatus === 'downloaded' ? 'flex' : 'none';
        }

        interactiveContainerElement.append(buttons.download.target.element);
        interactiveContainerElement.append(buttons.install.target.element);
        interactiveContainerElement.append(buttons.delete.target.element);
      }
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