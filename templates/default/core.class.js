'use strict';

import {Client} from "../../core/JSLibrary/core/client.class.js";
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

    const localeURL = window.CMSCore.searchParams.getParam('locale');

    // Если в URL нет параметра locale — нечего предлагать
    if (localeURL === null || localeURL === undefined || localeURL === '') {
      return;
    }

    // Если пользователь ранее нажал «не спрашивать» — не показываем
    if (Client.getCookie('ignoreLanguageChanged')) {
      return;
    }

    // Текущая локаль пользователя: cookie > base_locale
    const cookieLocale = Client.getCookie('locale');
    const currentLocale = cookieLocale
      ? cookieLocale
      : (window.CMSCore.locales.base?.name ?? null);

    // Если URL-локаль совпадает с текущей — ничего не предлагаем
    if (localeURL === currentLocale) {
      return;
    }

    // Пришли с другой локалью через URL — предлагаем зафиксировать
    showLanguageModal(localeURL);
  });
});

function showLanguageModal(targetLocaleName) {
  const locales = window.CMSCore.locales.list;
  const baseLocale = window.CMSCore.locales.base;

  baseLocale.getData().then((localeData) => {
    const modalBodyContent = document.createElement('div');
    modalBodyContent.classList.add('locale-manager');

    const descriptionElement = document.createElement('div');
    descriptionElement.innerHTML = localeData.MODAL_LOCALE_CHANGE_DESCRIPTION;
    modalBodyContent.appendChild(descriptionElement);

    const interactiveLocaleChoices = new Interactive('choices');

    locales.forEach((locale) => {
      const localeIconImageElement = document.createElement('img');
      localeIconImageElement.setAttribute('src', locale.iconURL);
      localeIconImageElement.setAttribute('alt', locale.title);

      const localeLabelElement = document.createElement('span');
      localeLabelElement.innerText = locale.title;

      const localeTemplate = document.createElement('template');
      localeTemplate.innerHTML += localeIconImageElement.outerHTML;
      localeTemplate.innerHTML += localeLabelElement.outerHTML;

      interactiveLocaleChoices.target.addItem(localeTemplate.innerHTML, locale.name);
    });

    // Предвыбираем локаль из URL
    locales.forEach((locale, index) => {
      if (locale.name === targetLocaleName) {
        interactiveLocaleChoices.target.setItemSelectedIndex(index);
      }
    });

    interactiveLocaleChoices.assembly();
    modalBodyContent.appendChild(interactiveLocaleChoices.target.element);

    const interactiveLanguageModal = new Interactive('modal', {
      title: localeData.MODAL_LOCALE_CHANGE_TITLE,
      content: modalBodyContent
    });

    interactiveLanguageModal.target.addButton(localeData.BUTTON_SUBMIT_LABEL, () => {
      const localeSelected = interactiveLocaleChoices.target.getValue();

      // Ставим cookie на год
      Client.setCookie('locale', localeSelected, 366);

      // Убираем мусорный параметр из URL и перезагружаем
      const url = new URL(window.location.href);
      url.searchParams.delete('locale');
      window.location.href = url.toString();
    });

    interactiveLanguageModal.target.addButton(localeData.BUTTON_DONT_ASK_AGAIN_LABEL, () => {
      Client.setCookie('ignoreLanguageChanged', true, 366);
      interactiveLanguageModal.target.close();
    });

    interactiveLanguageModal.assembly();
    document.body.appendChild(interactiveLanguageModal.target.element);
    interactiveLanguageModal.target.show();
  });
}