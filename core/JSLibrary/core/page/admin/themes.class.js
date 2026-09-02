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

import {Interactive} from "../../../interactive.class.js";
import {URLParser} from "../../../urlParser.class.js";

export class PageThemes {
  constructor(page, params = {}) {
    this.page = page;
  }

  init() {
    let searchParams = new URLParser(), locales;

    this.page.core.locales.admin.getData().then((localeData) => {
      let listItems = document.querySelectorAll('.themes-list .list__item');
    
      for (let listItem of listItems) {
        let buttons = {more: null, delete: null, install: null};

        buttons.more = new Interactive('button');
        buttons.delete = new Interactive('button');
        buttons.install = new Interactive('button');

        buttons.more.target.setLabel(localeData.BUTTON_MORE_DETAILS_LABEL);
        buttons.delete.target.setLabel(localeData.BUTTON_DELETE_LABEL);
        buttons.install.target.setLabel(localeData.BUTTON_INSTALL_LABEL)

        let themeName = listItem.getAttribute('data-name');
        let themeCategory = listItem.getAttribute('data-category');
        let themeInstalledStatus = listItem.getAttribute('data-installed-status');
        let itemFooterContainer = listItem.querySelector('[data-element="item-footer-panel"]');

        buttons.more.target.setCallback((event) => {
          switch (searchParams.getPathPart(3)) {
            case 'repository': window.location.href = `/admin/templates/repository/${themeName}`; break;
            default: window.location.href = `/admin/template/${themeName}`;
          }
        });

        buttons.delete.target.setCallback((event) => {
          let interactiveModal = new Interactive('modal', {
            title: localeData.MODAL_TEMPLATE_DELETE_TITLE,
            content: localeData.MODAL_TEMPLATE_DELETE_DESCRIPTION
          });
          
          interactiveModal.target.addButton(localeData.BUTTON_DELETE_LABEL, () => {
            let formData = new FormData();
            formData.append('template_name', themeName);
            formData.append('template_category', themeCategory);

            let request = new Interactive('request', {
              method: 'DELETE',
              url: '/handler/template?localeMessage=' + window.CMSCore.locales.admin.name
            });
  
            request.target.data = formData;
  
            request.target.send().then((data) => {
              interactiveModal.target.close();
              
              if (data.statusCode === 1) {
                if (searchParams.getPathPart(3) != 'repository') {
                  listItem.remove();
                } else {
                  buttons.install.target.element.style.display = 'flex';
                  buttons.delete.target.element.style.display = 'none';
                }
              }
            });
          });
          interactiveModal.assembly();
          document.body.appendChild(interactiveModal.target.element);
          interactiveModal.target.show();
        });

        buttons.install.target.setCallback((event) => {
          let formData = new FormData();
          formData.append('template_name', themeName);
          formData.append('template_category', themeCategory);

          let request = new Interactive('request', {
            method: 'POST',
            url: '/handler/template/install?localeMessage=' + window.CMSCore.locales.admin.name
          });

          request.target.data = formData;

          request.target.send().then((data) => {
            if (data.statusCode === 1) {
              buttons.install.target.element.style.display = 'none';
              buttons.delete.target.element.style.display = 'flex';
            }
          });
        });

        buttons.more.assembly();
        buttons.delete.assembly();
        buttons.install.assembly();
        
        buttons.more.target.element.classList.add('interactive_button-more');
        buttons.delete.target.element.classList.add('interactive_button-activation');
        buttons.install.target.element.classList.add('interactive_button-activation');

        if (itemFooterContainer !== null) {
          itemFooterContainer.appendChild(buttons.more.target.element);
          itemFooterContainer.appendChild(buttons.delete.target.element);
          itemFooterContainer.appendChild(buttons.install.target.element);
        }

        buttons.install.target.element.style.display = themeInstalledStatus === 'installed'
          ? 'none'
          : 'flex';

        buttons.delete.target.element.style.display = themeInstalledStatus === 'installed'
          ? 'flex'
          : 'none';
      }
    }, (rejectionReason) => {
      this.page.showPopupNotification(rejectionReason, 0);
    });
  }
}