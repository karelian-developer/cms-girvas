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

export class PageFeeds {
  constructor(page, params = {}) {
    this.page = page;

    this.buttons = {save: null, delete: null};
  }

  init() {
    this.page.core.locales.admin.getData().then((localeData) => {
      const interactiveCreatePageButton = new Interactive('button');
      interactiveCreatePageButton.target.setLabel(localeData.BUTTON_NEW_FEED_LABEL);
      interactiveCreatePageButton.target.setCallback(() => {
        window.location.href = `./feed`;
      });
      interactiveCreatePageButton.assembly();
    
      const interactiveContainerElement = document.querySelector('#E8548530785');
      interactiveContainerElement.append(interactiveCreatePageButton.target.element);

      const tableItems = document.querySelectorAll('[data-element="feed"]');
      for (let tableItem of tableItems) {
        const feedID = tableItem.getAttribute('data-id');
        const panelElement = tableItem.querySelector('[data-element="panel"]');
        const panelEventElements = panelElement.querySelectorAll('[data-event]');
        
        for (let eventElement of panelEventElements) {
          eventElement.addEventListener('click', (event) => {
            event.preventDefault();

            if (eventElement.getAttribute('data-event') === 'remove') {
              const interactiveModal = new Interactive('modal', {
                title: localeData.MODAL_FEED_DELETE_TITLE,
                content: localeData.MODAL_FEED_DELETE_DESCRIPTION
              });
              
              interactiveModal.target.addButton(localeData.BUTTON_DELETE_LABEL, () => {
                let formData = new FormData();
                formData.append('web_channel_id', feedID);

                let request = new Interactive('request', {
                  method: 'DELETE',
                  url: '/handler/feed/' + feedID + '?localeMessage=' + window.CMSCore.locales.admin.name
                });
      
                request.target.data = formData;
      
                request.target.send().then((data) => {
                  if (data.statusCode === 1) {
                    window.location.href = '/admin/feeds';
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