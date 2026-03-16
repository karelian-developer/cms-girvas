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
    let locales;
    const searchParams = new URLParser();
    const buttons = {enable: null, disable: null, install: null, delete: null};

    const moduleBlock = document.querySelector('.module');
    const moduleName = moduleBlock.getAttribute('data-name');
    const moduleEnabledStatus = moduleBlock.getAttribute('data-enabled-status');
    const moduleInstalledStatus = moduleBlock.getAttribute('data-installed-status');
    const interactiveContainerElement = document.querySelector('#E8548530785');

    fetch('/handler/locales', {method: 'GET'}).then((response) => {
      return (response.ok) ? response.json() : Promise.reject(response);
    }).then((data) => {
      locales = data.outputData.locales;
      return window.CMSCore.locales.admin.getData();
    }, (rejectionReason) => {
      this.page.showPopupNotification(rejectionReason, 0);
    }).then((localeData) => {

      if (searchParams.getPathPart(2) !== null) {
        buttons.enable = new Interactive('button');
        buttons.disable = new Interactive('button');
        buttons.install = new Interactive('button');
        buttons.delete = new Interactive('button');

        buttons.enable.target.setLabel(localeData.BUTTON_ENABLE_LABEL);
        buttons.disable.target.setLabel(localeData.BUTTON_DISABLE_LABEL);
        buttons.install.target.setLabel(localeData.BUTTON_INSTALL_LABEL);
        buttons.delete.target.setLabel(localeData.BUTTON_DELETE_LABEL);
        
        buttons.enable.target.setStyle('green');
        buttons.disable.target.setStyle('red');
        buttons.install.target.setStyle('default');
        buttons.delete.target.setStyle('red');

        let pageGalleryElement = moduleBlock.querySelector('[role="gallery"]');
        if (pageGalleryElement !== null) {
          this.initGallery(pageGalleryElement); 
        }

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
    
              moduleBlock.setAttribute('data-enabled-status', 'enabled');
            }
          });
        });

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
    
              moduleBlock.setAttribute('data-enabled-status', 'disabled');
            }
          });
        });

        buttons.install.target.setCallback(() => {
          let formData = new FormData();
          formData.append('module_name', moduleName);
          formData.append('module_event', 'install');

          let request = new Interactive('request', {
            method: 'POST',
            url: '/handler/module/install?localeMessage=' + window.CMSCore.locales.admin.name
          });

          request.target.data = formData;

          request.target.send().then((data) => {
            if (data.statusCode === 1) {
              buttons.install.target.element.style.display = 'none';
              buttons.delete.target.element.style.display = 'flex';
              buttons.enable.target.element.style.display = 'flex';
              buttons.disable.target.element.style.display = 'none';
    
              moduleBlock.setAttribute('data-enabled-status', 'disabled');
              moduleBlock.setAttribute('data-installed-status', 'installed');

              if (searchParams.getPathPart(2) !== 'repository') {
                const currentUrl = window.location.href;
                const newUrl = currentUrl.replace('/repository/', '/');

                if (newUrl !== currentUrl) {
                  window.location.href = newUrl;
                }
              }
            }
          });
        });

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
    
              moduleBlock.setAttribute('data-enabled-status', 'disabled');
              moduleBlock.setAttribute('data-installed-status', 'not-installed');

              if (searchParams.getPathPart(2) !== 'repository') {
                window.location.href = '/admin/modules';
              }
            }
          });
        });

        buttons.enable.assembly();
        buttons.disable.assembly();
        buttons.install.assembly();
        buttons.delete.assembly();
    
        if (moduleEnabledStatus === 'enabled') {
          buttons.enable.target.element.style.display = 'none';
          buttons.disable.target.element.style.display = 'flex';
        }
  
        if (moduleEnabledStatus === 'disabled') {
          buttons.enable.target.element.style.display = moduleInstalledStatus === 'installed'
            ? 'flex'
            : 'none';
          buttons.disable.target.element.style.display = 'none';
        }
        
        if (moduleInstalledStatus === 'installed') {
          buttons.install.target.element.style.display = 'none';
          buttons.delete.target.element.style.display = 'flex';
        }
        
        if (moduleInstalledStatus === 'not-installed') {
          buttons.install.target.element.style.display = 'flex';
          buttons.delete.target.element.style.display = 'none';
        }
    
        interactiveContainerElement.append(buttons.enable.target.element);
        interactiveContainerElement.append(buttons.disable.target.element);
        interactiveContainerElement.append(buttons.install.target.element);
        interactiveContainerElement.append(buttons.delete.target.element);
      }
    }, (rejectionReason) => {
      this.page.showPopupNotification(rejectionReason, 0);
    });
  }
}