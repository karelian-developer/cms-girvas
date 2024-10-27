/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';

import {Interactive} from "../../../interactive.class.js";
import {URLParser} from "../../../urlParser.class.js";

export class PageTemplate {
  constructor(page, params = {}) {
    this.page = page;
  }

  init() {
    let searchParams = new URLParser(), locales;
    let buttons = {install: null, download: null, delete: null};

    let templateBlock = document.querySelector('.template');
    let templateName = templateBlock.getAttribute('data-template-name');
    let templateDownloadedStatus = templateBlock.getAttribute('data-template-dowloaded-status');
    let templateInstalledStatus = templateBlock.getAttribute('data-template-installed-status');
    let interactiveContainerElement = document.querySelector('#E8548530785');
    
    fetch('/handler/locales', {method: 'GET'}).then((response) => {
      return (response.ok) ? response.json() : Promise.reject(response);
    }).then((data) => {
      locales = data.outputData.locales;
      return window.CMSCore.locales.admin.getData();
    }, (rejectionReason) => {
      let interactiveNotification = new Interactive('notification');
      interactiveNotification.target.isPopup = true;
      interactiveNotification.target.setStatusCode(0);
      interactiveNotification.target.setContent(rejectionReason);
      interactiveNotification.target.assembly();

      interactiveNotification.target.show();
    }).then((localeData) => {
      if (searchParams.getPathPart(2) != null) {
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
            if (data.statusCode == 1) {
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
          formData.append('template_category', 'default');

          let request = new Interactive('request', {
            method: 'DELETE',
            url: '/handler/template?localeMessage=' + window.CMSCore.locales.admin.name
          });

          request.target.data = formData;

          request.target.send().then((data) => {
            if (data.statusCode == 1) {
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
            if (data.statusCode == 1) {
              buttons.download.target.element.style.display = 'none';
              buttons.delete.target.element.style.display = 'flex';
              buttons.install.target.element.style.display = 'flex';

              templateBlock.setAttribute('data-template-installed-status', 'not-installed');
              templateBlock.setAttribute('data-template-dowloaded-status', 'dowloaded');
            }
          });
        });

        buttons.delete.assembly();
        buttons.install.assembly();
        buttons.download.assembly();
    
        if (templateInstalledStatus == 'installed') {
          buttons.download.target.element.style.display = 'none';
          buttons.delete.target.element.style.display = 'none';
          buttons.install.target.element.style.display = 'none';
        } else {
          buttons.download.target.element.style.display = (templateDownloadedStatus == 'downloaded') ? 'none' : 'flex';
          buttons.delete.target.element.style.display = (templateDownloadedStatus == 'downloaded') ? 'flex' : 'none';
          buttons.install.target.element.style.display = (templateDownloadedStatus == 'downloaded') ? 'flex' : 'none';
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