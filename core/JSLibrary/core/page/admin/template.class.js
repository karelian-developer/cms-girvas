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
    let buttons = {install: null, download: null, delete: null, saveProperties: null};

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
      if (searchParams.getPathPart(2) !== null) {
        let pageGalleryElement = templateBlock.querySelector('[role="gallery"]');
        if (pageGalleryElement != null) {
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

        if (templateInstalledStatus === 'installed') {
          const themePropertiesContainerElement = document.querySelector('#THEME_PROPERTIES');
          const propertiesFilesValues = themePropertiesContainerElement.querySelectorAll('input[type="file"]');
          propertiesFilesValues.forEach((property) => {
            let buttonTrigger = new Interactive('button');
            buttonTrigger.target.setLabel(localeData.BUTTON_DOWNLOAD_LABEL);
            buttonTrigger.target.setCallback(() => {
              property.click();
            });

            buttonTrigger.assembly();

            property.after(buttonTrigger.target.element);

            property.addEventListener('change', (event) => {
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
                property.removeAttribute('disabled');
                property.setAttribute('data-file', fileReader.result);
              };

              fileReader.onerror = (event) => {
                console.error(fileReader.result);
              };

              fileReader.readAsDataURL(file);
            });
          });

          buttons.saveProperties = new Interactive('button');
          buttons.saveProperties.target.setLabel(localeData.BUTTON_SAVE_LABEL);
          buttons.saveProperties.target.setCallback(() => {
            if (themePropertiesContainerElement !== null) {
              const formData = new FormData();

              formData.append('template_name', templateName);
              formData.append('template_category', 'base');

              const propertiesValues = themePropertiesContainerElement.querySelectorAll('input, select');
              propertiesValues.forEach((property) => {
                if (!property.hasAttribute('disabled')) {
                  const propertyName = property.name;
                  const propertyValue = (!property.hasAttribute('data-file')) ? property.value : property.getAttribute('data-file');

                  formData.append(propertyName, propertyValue);
                }
              });

              const request = new Interactive('request', {
                method: 'PATCH',
                url: '/handler/template?localeMessage=' + window.CMSCore.locales.admin.name
              });

              request.target.data = formData;

              request.target.send().then((data) => {
                console.log(data);
              });
            }
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