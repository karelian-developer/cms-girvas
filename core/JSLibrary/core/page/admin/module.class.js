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

export class PageModule {
  constructor(page, params = {}) {
    this.page = page;
  }

  initGallery(galleryElement) {
    let controllerElement = galleryElement.querySelector('[role="controller"]');
    let slidesListElement = galleryElement.querySelector('ul');
    if (controllerElement != null && slidesListElement != null) {
      let slidesListItemsElements = slidesListElement.querySelectorAll('li');
      let controllerButtonElements = controllerElement.querySelectorAll('button');
      if (controllerButtonElements.length > 0) {
        controllerButtonElements.forEach((element) => {
          element.addEventListener('click', (event) => {
            event.preventDefault();

            let computedStyle = window.getComputedStyle(slidesListItemsElements[0]);
            let computedStyleMarginLeft = Number(computedStyle.getPropertyValue('margin-left').replace(/px/, ''));
            let computedStyleWidth = Number(computedStyle.getPropertyValue('width').replace(/px/, ''));

            if (element.getAttribute('role') === 'controller-left') {
              if (computedStyleMarginLeft < 0) {
                computedStyleMarginLeft += computedStyleWidth;
              } else {
                computedStyleMarginLeft = (computedStyleWidth * (slidesListItemsElements.length - 1)) * -1;
              }

              slidesListItemsElements[0].style.marginLeft = `${computedStyleMarginLeft}px`;
            }

            if (element.getAttribute('role') === 'controller-right') {
              if ((computedStyleMarginLeft * -1) >= computedStyleWidth * (slidesListItemsElements.length - 1)) {
                computedStyleMarginLeft = 0;
              } else {
                computedStyleMarginLeft -= computedStyleWidth;
              }

              slidesListItemsElements[0].style.marginLeft = `${computedStyleMarginLeft}px`;
            }
          });
        });
      }
    }
  }

  init() {
    let searchParams = new URLParser(), locales;
    let buttons = {enable: null, disable: null, install: null, delete: null};

    let moduleBlock = document.querySelector('.module');
    let moduleName = moduleBlock.getAttribute('data-module-name');
    let moduleEnabledStatus = moduleBlock.getAttribute('data-module-enabled-status');
    let moduleInstalledStatus = moduleBlock.getAttribute('data-module-installed-status');
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
        let pageGalleryElement = moduleBlock.querySelector('[role="gallery"]');
        if (pageGalleryElement != null) {
          this.initGallery(pageGalleryElement); 
        }

        buttons.enable = new Interactive('button');
        buttons.enable.target.setLabel(localeData.BUTTON_ENABLE_LABEL);
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
    
              moduleBlock.setAttribute('data-module-enabled-status', 'enabled');
            }
          });
        });
        buttons.enable.assembly();
    
        buttons.disable = new Interactive('button');
        buttons.disable.target.setLabel(localeData.BUTTON_DISABLE_LABEL);
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
    
              moduleBlock.setAttribute('data-module-enabled-status', 'disabled');
            }
          });
        });
        buttons.disable.assembly();
    
        buttons.install = new Interactive('button');
        buttons.install.target.setLabel(localeData.BUTTON_INSTALL_LABEL);
        buttons.install.target.setCallback(() => {
          let formData = new FormData();
          formData.append('module_name', moduleName);
          formData.append('module_event', 'install');

          let request = new Interactive('request', {
            method: 'POST',
            url: '/handler/module?localeMessage=' + window.CMSCore.locales.admin.name
          });

          request.target.data = formData;

          request.target.send().then((data) => {
            if (data.statusCode === 1) {
              buttons.install.target.element.style.display = 'none';
              buttons.delete.target.element.style.display = 'flex';
              buttons.enable.target.element.style.display = 'flex';
              buttons.disable.target.element.style.display = 'none';
    
              moduleBlock.setAttribute('data-module-enabled-status', 'disabled');
              moduleBlock.setAttribute('data-module-installed-status', 'installed');
            }
          });
        });
        buttons.install.assembly();
    
        buttons.delete = new Interactive('button');
        buttons.delete.target.setLabel(localeData.BUTTON_DELETE_LABEL);
        buttons.delete.target.setCallback(() => {
          let formData = new FormData();
          formData.append('module_name', moduleName);

          let request = new Interactive('request', {
            method: 'DELETE',
            url: '/handler/module?localeMessage=' + window.CMSCore.locales.admin.name
          });

          request.target.data = formData;

          request.target.send().then((data) => {
            if (data.statusCode === 1) {
              buttons.install.target.element.style.display = 'flex';
              buttons.delete.target.element.style.display = 'none';
              buttons.enable.target.element.style.display = 'none';
              buttons.disable.target.element.style.display = 'none';
    
              moduleBlock.setAttribute('data-module-enabled-status', 'disabled');
              moduleBlock.setAttribute('data-module-installed-status', 'not-installed');
            }
          });
        });
        buttons.delete.assembly();
    
        if (moduleEnabledStatus === 'enabled') {
          buttons.enable.target.element.style.display = 'none';
          buttons.disable.target.element.style.display = 'flex';
        }
  
        if (moduleEnabledStatus === 'disabled') {
          buttons.enable.target.element.style.display = 'flex';
          buttons.disable.target.element.style.display = 'none';
        }
  
        buttons.install.target.element.style.display = 'none';
        buttons.delete.target.element.style.display = 'flex';
    
        interactiveContainerElement.append(buttons.enable.target.element);
        interactiveContainerElement.append(buttons.disable.target.element);
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