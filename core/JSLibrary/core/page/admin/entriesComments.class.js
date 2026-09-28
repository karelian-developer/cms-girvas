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

export class PageEntriesComments {
  constructor(page, params = {}) {
    this.page = page;
    this.localeData = null;
    this.searchInput = null;
  }

  init() {
    this.page.core.locales.admin.getData().then((localeData) => {
      this.localeData = localeData;

      const pageElement = document.querySelector('[data-element="entries-comments-page"]');
      const container = document.querySelector('#E8548530785');

      if (container === null) return;

      const currentSort = pageElement?.getAttribute('data-sort-value') || 'by_createdtimestamp_decrease';
      const currentSearch = pageElement?.getAttribute('data-search-value') || '';

      // ----- 1. Сортировка -----
      const sortChoices = new Interactive('choices');
      sortChoices.target.setWidth('280px');
      sortChoices.target.addItem(localeData.SORT_BY_CREATEDTIMESTAMP_INCREASE, 'by_createdtimestamp_increase');
      sortChoices.target.addItem(localeData.SORT_BY_CREATEDTIMESTAMP_DECREASE, 'by_createdtimestamp_decrease');
      sortChoices.target.addItem(localeData.SORT_BY_UPDATEDTIMESTAMP_INCREASE, 'by_updatedtimestamp_increase');
      sortChoices.target.addItem(localeData.SORT_BY_UPDATEDTIMESTAMP_DECREASE, 'by_updatedtimestamp_decrease');

      const sortIndexMap = {
        'by_createdtimestamp_increase': 0,
        'by_createdtimestamp_decrease': 1,
        'by_updatedtimestamp_increase': 2,
        'by_updatedtimestamp_decrease': 3,
      };
      sortChoices.target.setItemSelectedIndex(sortIndexMap[currentSort] ?? 1);
      sortChoices.assembly();

      sortChoices.target.elementSelect.addEventListener('change', () => {
        const url = new URL(window.location.href);
        url.searchParams.set('sort', sortChoices.target.getValue());
        url.searchParams.delete('pageNumber');
        window.location.href = url.toString();
      });

      // ----- 2. Поле поиска -----
      const searchInput = new Interactive('input');
      searchInput.target.setType('search');
      searchInput.target.setPlaceholder(
        localeData.PAGE_ENTRIES_COMMENTS_SEARCH_PLACEHOLDER || 'Поиск по тексту комментария'
      );
      searchInput.target.setValue(currentSearch);
      searchInput.assembly();
      this.searchInput = searchInput;

      const searchInputElement = searchInput.target.element.querySelector('input');
      if (searchInputElement !== null) {
        searchInputElement.addEventListener('keydown', (event) => {
          if (event.key === 'Enter') {
            event.preventDefault();
            this.applySearch();
          }
        });
      }

      // ----- 3. Кнопка поиска -----
      const searchButton = new Interactive('button');
      searchButton.target.setLabel(localeData.BUTTON_SEARCH_LABEL || 'Найти');
      searchButton.target.setStyle('default');
      searchButton.target.setCallback((event) => {
        event.preventDefault();
        this.applySearch();
      });
      searchButton.assembly();

      // ----- Сборка панели -----
      container.append(sortChoices.target.element);
      container.append(searchInput.target.element);
      container.append(searchButton.target.element);

      // ----- Сохранение value/sort в ссылках пагинации -----
      const paginationLinks = document.querySelectorAll('.page__pagination a');
      paginationLinks.forEach((link) => {
        const href = link.getAttribute('href');
        if (!href) return;

        const url = new URL(href, window.location.origin);
        if (currentSearch !== '') {
          url.searchParams.set('value', currentSearch);
        }
        url.searchParams.set('sort', currentSort);
        link.setAttribute('href', url.pathname + url.search);
      });

      // ----- Кнопки скрытия/публикации/удаления комментариев -----
      const tableItems = document.querySelectorAll('[data-element="entry-comment"]');
      for (const tableItem of tableItems) {
        const commentID = tableItem.getAttribute('data-id');
        const panelElement = tableItem.querySelector('[data-element="panel"]');
        if (panelElement === null) continue;

        const panelEventElements = panelElement.querySelectorAll('[data-event]');
        for (const eventElement of panelEventElements) {
          eventElement.addEventListener('click', (event) => {
            event.preventDefault();

            const eventName = eventElement.getAttribute('data-event');

            if (eventName === 'remove') {
              const interactiveModal = new Interactive('modal', {
                title: localeData.MODAL_ENTRY_COMMENT_DELETE_TITLE || 'Удаление комментария',
                content: localeData.MODAL_ENTRY_COMMENT_DELETE_DESCRIPTION || 'Удалить комментарий без возможности восстановления?'
              });

              interactiveModal.target.addButton(localeData.BUTTON_DELETE_LABEL, () => {
                const formData = new FormData();
                formData.append('comment_id', commentID);

                const request = new Interactive('request', {
                  method: 'DELETE',
                  url: '/handler/entry/comment/' + commentID + '?localeMessage=' + window.CMSCore.locales.admin.name
                });

                request.target.data = formData;
                request.target.send().then((data) => {
                  if (data.statusCode === 1) {
                    window.location.reload();
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

            if (eventName === 'hide' || eventName === 'show') {
              const isHide = eventName === 'hide';

              const formData = new FormData();
              formData.append('comment_id', commentID);
              formData.append('is_hidden', isHide ? 1 : 0);
              if (isHide) {
                formData.append('hidden_reason', '');
              }

              const request = new Interactive('request', {
                method: 'PATCH',
                url: '/handler/entry/comment?localeMessage=' + window.CMSCore.locales.admin.name
              });

              request.target.data = formData;
              request.target.send().then((data) => {
                if (data.statusCode === 1) {
                  window.location.reload();
                }
              });
            }
          });
        }
      }
    }, (rejectionReason) => {
      this.page.showPopupNotification(rejectionReason, 0);
    });
  }

  /**
   * Применить поиск: перейти на тот же раздел с ?value=...&sort=...
   */
  applySearch() {
    if (this.searchInput === null) return;

    const value = (this.searchInput.target.getValue() || '').trim();
    const currentSort = document.querySelector('[data-element="entries-comments-page"]')
      ?.getAttribute('data-sort-value') || 'by_createdtimestamp_decrease';

    const url = new URL(window.location.href);
    if (value === '') {
      url.searchParams.delete('value');
    } else {
      url.searchParams.set('value', value);
    }
    url.searchParams.set('sort', currentSort);
    url.searchParams.delete('pageNumber');

    window.location.href = url.toString();
  }
}