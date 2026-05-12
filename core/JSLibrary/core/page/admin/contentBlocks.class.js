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

export class PageContentBlocks {
  constructor(page, params = {}) {
    this.page = page;

    this.buttons = {save: null, delete: null};
  }

  init() {
    let locales;

    fetch('/handler/locales', {method: 'GET'}).then((response) => {
      return (response.ok) ? response.json() : Promise.reject(response);
    }).then((data) => {
      locales = data.outputData.locales;
      return window.CMSCore.locales.admin.getData();
    }, (rejectionReason) => {
      this.page.showPopupNotification(rejectionReason, 0);
    }).then((localeData) => {

      const interactiveCreatePageButton = new Interactive('button');
      interactiveCreatePageButton.target.setLabel(localeData.BUTTON_NEW_CONTENT_BLOCK_LABEL);
      interactiveCreatePageButton.target.setCallback(() => {
        window.location.href = `./contentBlock`;
      });

      interactiveCreatePageButton.assembly();
    
      const interactiveContainerElement = document.querySelector('#E8548530785');
      interactiveContainerElement.append(interactiveCreatePageButton.target.element);

      const tableItems = document.querySelectorAll('[data-element="content-block"]');
      for (let tableItem of tableItems) {
        const contentBlockID = tableItem.getAttribute('data-id');
        const panelElement = tableItem.querySelector('[data-element="panel"]');
        const panelEventElements = panelElement.querySelectorAll('[data-event]');
        
        for (let eventElement of panelEventElements) {
          eventElement.addEventListener('click', (event) => {
            event.preventDefault();

            if (eventElement.getAttribute('data-event') === 'remove') {
              const interactiveModal = new Interactive('modal', {
                title: localeData.MODAL_CONTENT_BLOCK_DELETE_TITLE,
                content: localeData.MODAL_CONTENT_BLOCK_DELETE_DESCRIPTION
              });
              
              interactiveModal.target.addButton(localeData.BUTTON_DELETE_LABEL, () => {
                let formData = new FormData();
                formData.append('content_block_id', contentBlockID);

                let request = new Interactive('request', {
                  method: 'DELETE',
                  url: '/handler/contentBlock/' + contentBlockID + '?localeMessage=' + window.CMSCore.locales.admin.name
                });
      
                request.target.data = formData;
      
                request.target.send().then((data) => {
                  if (data.statusCode === 1) {
                    window.location.href = '/admin/contentBlocks';
                  }
                });
              });

              interactiveModal.target.addButton(localeData.BUTTON_CANCEL_LABEL, () => {
                interactiveModal.target.close();
              });

              interactiveModal.assembly();
              document.body.appendChild(interactiveModal.target.element);
              interactiveModal.target.show();
            }
          });
        }
      }
    }, (rejectionReason) => {
      this.page.showPopupNotification(rejectionReason, 0);
    });
  }
}