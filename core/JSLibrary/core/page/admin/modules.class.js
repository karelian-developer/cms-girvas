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

export class PageModules {
  constructor(page, params = {}) {
    this.page = page;
  }

  init() {
    let searchParams = new URLParser(), locales;

    fetch('/handler/locales', {method: 'GET'}).then((response) => {
      return (response.ok) ? response.json() : Promise.reject(response);
    }).then((data) => {
      locales = data.outputData.locales;
      return window.CMSCore.locales.admin.getData();
    }, (rejectionReason) => {
      this.page.showPopupNotification(rejectionReason, 0);
    }).then((localeData) => {

      let listItems = document.querySelectorAll('.modules-list .list__item');
      for (let listItem of listItems) {
        let buttons = {delete: null, install: null, enable: null, disable: null, more: null};

        let moduleName = listItem.getAttribute('data-name');
        let moduleInstalledStatus = listItem.hasAttribute('data-installed-status')
          ? listItem.getAttribute('data-module-installed-status')
          : 'not-installed';
        let moduleEnabledStatus = listItem.hasAttribute('data-enabled-status')
          ? listItem.getAttribute('data-module-enabled-status')
          : 'disabled';
        let itemFooterContainer = listItem.querySelector('[data-element="item-footer-panel"]');

        // Добавление интерактивных элементов
        // Кнопка "Подробнее"
        buttons.more = new Interactive('button');
        buttons.more.target.setLabel(localeData.BUTTON_MORE_DETAILS_LABEL);
        buttons.more.target.setCallback(() => {
          switch (searchParams.getPathPart(3)) {
            case 'repository': window.location.href = `/admin/modules/repository/${moduleName}`; break;
            default: window.location.href = `/admin/module/${moduleName}`;
          }
        });
        buttons.more.assembly();
        buttons.more.target.element.classList.add('interactive_button-more');

        // Кнопка "Удалить"
        buttons.delete = new Interactive('button');
        buttons.delete.target.setLabel(localeData.BUTTON_DELETE_LABEL);
        buttons.delete.target.setCallback(() => {
          let interactiveModal = new Interactive('modal', {
            title: localeData.MODAL_MODULE_DELETE_TITLE,
            content: localeData.MODAL_MODULE_DELETE_DESCRIPTION
          });
          
          interactiveModal.target.addButton(localeData.BUTTON_DELETE_LABEL, () => {
            let formData = new FormData();
            formData.append('module_name', moduleName);

            let request = new Interactive('request', {
              method: 'DELETE',
              url: '/handler/module?localeMessage=' + window.CMSCore.locales.admin.name
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

        // Кнопка "Установить"
        buttons.install = new Interactive('button');
        buttons.install.target.setLabel(localeData.BUTTON_INSTALL_LABEL);
        buttons.install.target.setCallback(() => {
          let formData = new FormData();
          formData.append('module_name', moduleName);

          let request = new Interactive('request', {
            method: 'POST',
            url: '/handler/module/install?localeMessage=' + window.CMSCore.locales.admin.name
          });

          request.target.data = formData;

          request.target.send().then((data) => {
            if (data.statusCode === 1) {
              buttons.install.target.element.style.display = 'none';
              buttons.delete.target.element.style.display = 'flex';
            }
          });
        });

        // Кнопка "Активировать"
        buttons.enable = new Interactive('button');
        buttons.enable.target.setLabel(localeData.BUTTON_ACTIVATION_LABEL);
        buttons.enable.target.setCallback(() => {
          let formData = new FormData();
          formData.append('module_name', moduleName);
          formData.append('module_event', 'enable');

          let request = new Interactive('request', {
            method: 'PATCH',
            url: '/handler/module?localeMessage=' + window.CMSCore.locales.admin.name
          });

          request.target.data = formData;

          request.target.send().then((data) => {
            if (data.statusCode === 1) {
              buttons.enable.target.element.style.display = 'none';
              buttons.disable.target.element.style.display = 'flex';
            }
          });
        });

        // Кнопка "Деактивировать"
        buttons.disable = new Interactive('button');
        buttons.disable.target.setLabel(localeData.BUTTON_DEACTIVATION_LABEL);
        buttons.disable.target.setCallback(() => {
          let formData = new FormData();
          formData.append('module_name', moduleName);
          formData.append('module_event', 'disable');

          let request = new Interactive('request', {
            method: 'PATCH',
            url: '/handler/module?localeMessage=' + window.CMSCore.locales.admin.name
          });

          request.target.data = formData;

          request.target.send().then((data) => {
            if (data.statusCode === 1) {
              buttons.enable.target.element.style.display = 'flex';
              buttons.disable.target.element.style.display = 'none';
            }
          });
        });
        
        buttons.enable.assembly();
        buttons.disable.assembly();
        buttons.install.assembly();
        buttons.delete.assembly();

        buttons.enable.target.element.classList.add('interactive_button-activation');
        buttons.disable.target.element.classList.add('interactive_button-activation');
        buttons.install.target.element.classList.add('interactive_button-install');
        buttons.delete.target.element.classList.add('interactive_button-delete');

        if (itemFooterContainer !== null) {
          itemFooterContainer.appendChild(buttons.more.target.element);
          itemFooterContainer.appendChild(buttons.install.target.element);
          itemFooterContainer.appendChild(buttons.delete.target.element);
          itemFooterContainer.appendChild(buttons.enable.target.element);
          itemFooterContainer.appendChild(buttons.disable.target.element);
        }

        buttons.install.target.element.style.display = moduleInstalledStatus === 'installed'
          ? 'none'
          : 'flex';
        buttons.delete.target.element.style.display = moduleInstalledStatus === 'installed'
          ? 'flex'
          : 'none';

        if (
          moduleInstalledStatus === 'installed'
          && (
            searchParams.getPathPart(3) === 'local'
            || searchParams.getPathPart(3) === null
          )
        ) {
          buttons.enable.target.element.style.display = (moduleEnabledStatus === 'enabled') ? 'none' : 'flex';
          buttons.disable.target.element.style.display = (moduleEnabledStatus === 'enabled') ? 'flex' : 'none';
        } else {
          buttons.enable.target.element.style.display = 'none';
          buttons.disable.target.element.style.display = 'none';
        }
      }
    }, (rejectionReason) => {
      this.page.showPopupNotification(rejectionReason, 0);
    });
  }
}