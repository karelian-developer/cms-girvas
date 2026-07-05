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

    if (localeBase !== localeLocation) {
      const interactiveLanguageModal = new Interactive('modal', {
        title: window.CMSCore.localeData.MODAL_LOCALE_CHANGE_TITLE,
        content: window.CMSCore.localeData.MODAL_LOCALE_CHANGE_DESCRIPTION
      });

      interactiveLanguageModal.target.addButton(window.CMSCore.localeData.BUTTON_SUBMIT_LABEL, () => {
        document.cookie = `locale=${element.name}; max-age=max-age-in-seconds; path=/`;
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
    }
  });
});