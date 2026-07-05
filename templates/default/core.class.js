/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';

import {Interactive} from "../../core/JSLibrary/interactive.class.js";

export class Core {
  constructor(CMSCore) {
    this.CMSCore = CMSCore;
  }

  init() {
    // ...
  }
}

document.addEventListener('DOMContentLoaded', () => {
  window.CMSCore.addEventListener('ready', () => {
    window.CMSCore.templateCore = new Core(window.CMSCore);
    window.CMSCore.templateCore.init();

    const localeBase = window.CMSCore.locales.base;
    const localeLocation = window.CMSCore.searchParams.getParam('locale');
    
    let localeIsQual;

    if (localeLocation !== null && localeLocation !== undefined && localeLocation !== "") {
      // localeLocation задан
      const cookieLocale = window.CMSCore.client.getCookie('locale');
      
      if (localeLocation !== localeBase) {
        // Параметр отличается от базовой локали
        if (!cookieLocale) {
          // Cookie не задан → предлагаем выбрать язык
          localeIsQual = false;
        } else {
          // Cookie задан → сравниваем
          localeIsQual = localeLocation === cookieLocale;
        }
      } else {
        // Параметр равен базовой локали → всё ок
        localeIsQual = true;
      }
    } else {
      // localeLocation НЕ задан
      const cookieLocale = window.CMSCore.client.getCookie('locale');
      
      if (cookieLocale) {
        // Cookie задан → используем его, ничего не предлагаем
        localeIsQual = true;
      } else {
        // Cookie не задан → используем localeBase, ничего не предлагаем
        localeIsQual = true;  // ← ВОТ ЗДЕСЬ БЫЛА ОШИБКА!
      }
    }

    if (!localeLocation) {
      fetch('/handler/locales', {method: 'GET'}).then((response) => {
        return (response.ok) ? response.json() : Promise.reject(response);
      }).then((data) => {
        const locales = data.outputData.locales;

        const modalBodyContent = document.createElement('div');
        modalBodyContent.classList.add('locale-manager');

        const interactiveLocaleChoices = new Interactive('choices');
        locales.forEach((locale, localeIndex) => {
          let localeTitle = locale.title;
          let localeIconURL = locale.iconURL;
          let localeName = locale.name;
          let localeISO639_2 = locale.iso639_2;

          let localeIconImageElement = document.createElement('img');
          localeIconImageElement.setAttribute('src', localeIconURL);
          localeIconImageElement.setAttribute('alt', localeTitle);

          let localeLabelElement = document.createElement('span');
          localeLabelElement.innerText = localeTitle;

          let localeTemplate = document.createElement('template');
          localeTemplate.innerHTML += localeIconImageElement.outerHTML;
          localeTemplate.innerHTML += localeLabelElement.outerHTML;

          interactiveLocaleChoices.target.addItem(localeTemplate.innerHTML, localeName);
        });

        locales.forEach((locale, localeIndex) => {
          if (locale.name === window.CMSCore.locales.base.name) {
            interactiveLocaleChoices.target.setItemSelectedIndex(localeIndex);
          }
        });

        interactiveLocaleChoices.assembly();

        modalBodyContent.appendChild(interactiveLocaleChoices.target.element);

        const interactiveLanguageModal = new Interactive('modal', {
          title: window.CMSCore.localeData.MODAL_LOCALE_CHANGE_TITLE,
          content: modalBodyContent
        });

        interactiveLanguageModal.target.addButton(window.CMSCore.localeData.BUTTON_SUBMIT_LABEL, () => {
          const localeSelected = interactiveLocaleChoices.target.getValue();
          document.cookie = `locale=${localeSelected}; max-age=max-age-in-seconds; path=/`;
          window.location.reload();
        });

        interactiveLanguageModal.target.addButton(window.CMSCore.localeData.BUTTON_DONT_ASK_AGAIN_LABEL, () => {
          Client.setCookie('ignoreLanguageChanged', true, 366);
          interactiveLanguageModal.target.close();
        });

        interactiveLanguageModal.target.onClose(() => {
          interactiveLanguageModal.target.close();
        });

        interactiveLanguageModal.assembly();
        document.body.appendChild(interactiveLanguageModal.target.element);

        interactiveLanguageModal.target.show();
      });
    }
  });
});