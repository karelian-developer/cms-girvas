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

export class PageUsersConsents {
  constructor(page, params = {}) {
    this.page = page;
    this.localeData = null;
    this.searchInput = null;
  }

  init() {
    this.page.core.locales.admin.getData().then((localeData) => {
      this.localeData = localeData;

      const pageElement = document.querySelector('[data-element="users-consents-page"]');
      const container = document.querySelector('#E8548530785');

      if (container === null) return;

      const currentSort = pageElement?.getAttribute('data-sort-value') || 'by_consentedat_decrease';
      const currentSearch = pageElement?.getAttribute('data-search-value') || '';

      const searchInput = new Interactive('input');
      searchInput.target.setType('search');
      searchInput.target.setPlaceholder(
        localeData.PAGE_USERS_CONSENTS_SEARCH_PLACEHOLDER || 'Поиск по ID пользователя'
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

      const searchButton = new Interactive('button');
      searchButton.target.setLabel(localeData.BUTTON_SEARCH_LABEL || 'Найти');
      searchButton.target.setStyle('default');
      searchButton.target.setCallback((event) => {
        event.preventDefault();
        this.applySearch();
      });
      searchButton.assembly();

      const sortChoices = new Interactive('choices');
      sortChoices.target.setWidth('280px');
      sortChoices.target.addItem(localeData.SORT_BY_CONSENTEDAT_INCREASE,   'by_consentedat_increase');
      sortChoices.target.addItem(localeData.SORT_BY_CONSENTEDAT_DECREASE,   'by_consentedat_decrease');
      sortChoices.target.addItem(localeData.SORT_BY_STATUS_ACTIVE_FIRST,    'by_status_active_first');
      sortChoices.target.addItem(localeData.SORT_BY_STATUS_REVOKED_FIRST,   'by_status_revoked_first');
      sortChoices.target.addItem(localeData.SORT_BY_SOURCE_INCREASE,        'by_source_increase');
      sortChoices.target.addItem(localeData.SORT_BY_SOURCE_DECREASE,        'by_source_decrease');

      const sortIndexMap = {
        'by_consentedat_increase': 0,
        'by_consentedat_decrease': 1,
        'by_status_active_first':  2,
        'by_status_revoked_first': 3,
        'by_source_increase':      4,
        'by_source_decrease':      5,
      };
      sortChoices.target.setItemSelectedIndex(sortIndexMap[currentSort] ?? 1);
      sortChoices.assembly();

      sortChoices.target.elementSelect.addEventListener('change', () => {
        const url = new URL(window.location.href);
        url.searchParams.set('sort', sortChoices.target.getValue());
        url.searchParams.delete('pageNumber');
        window.location.href = url.toString();
      });

      const exportContainer = container.querySelector('[data-element="export-container"]')
        || document.querySelector('[data-element="export-container"]');

      if (exportContainer !== null) {
        const exportButton = new Interactive('button');
        exportButton.target.setLabel(localeData.PAGE_USERS_CONSENTS_BUTTON_EXPORT || 'Экспорт CSV');
        exportButton.target.setStyle('default');
        exportButton.target.setCallback((event) => {
          event.preventDefault();
          this.handleExport();
        });
        exportButton.assembly();
      }

      // ----- Сборка панели -----
      container.append(sortChoices.target.element);
      container.append(searchInput.target.element);
      container.append(searchButton.target.element);

      if (exportContainer !== null) {
        exportContainer.append(exportButton.target.element);
      }

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

      // ----- Кнопки «Отозвать» в таблице -----
      const consentPanels = document.querySelectorAll('[data-element="consent"] [data-role="revoke-panel"]');
      consentPanels.forEach((panel) => {
        const consentItem = panel.closest('[data-element="consent"]');
        const consentID = consentItem ? consentItem.getAttribute('data-id') : null;

        if (!consentID) return;

        const isRevoked = consentItem.getAttribute('data-revoked') === '1';
        const canRevoke = consentItem.getAttribute('data-can-revoke') === '1';
        if (isRevoked || !canRevoke) return;

        const revokeButton = new Interactive('button');
        revokeButton.target.setLabel(localeData.PAGE_USERS_CONSENTS_BUTTON_REVOKE || 'Отозвать');
        revokeButton.target.setStyle('red');
        revokeButton.target.setCallback((event) => {
          event.preventDefault();
          this.handleRevoke(consentID, revokeButton);
        });
        revokeButton.assembly();
        panel.append(revokeButton.target.element);
      });
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
    const currentSort = document.querySelector('[data-element="users-consents-page"]')
      ?.getAttribute('data-sort-value') || 'by_consentedat_decrease';

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

  handleRevoke(consentID, revokeButton) {
    if (!consentID) return;

    const modal = new Interactive('modal', {
      title: this.localeData.MODAL_CONSENT_REVOKE_BY_ADMIN_TITLE,
      content: this.localeData.MODAL_CONSENT_REVOKE_BY_ADMIN_DESCRIPTION
    });

    const reasonTextarea = document.createElement('textarea');
    reasonTextarea.classList.add('form__textarea');
    reasonTextarea.setAttribute('placeholder', this.localeData.PAGE_USERS_CONSENTS_REVOKE_REASON_PLACEHOLDER);
    reasonTextarea.setAttribute('required', 'required');

    modal.target.content = reasonTextarea;

    modal.target.addButton(this.localeData.BUTTON_CONSENT_REVOKE_SUBMIT, () => {
      const reason = reasonTextarea.value.trim();

      if (reason === '') {
        this.page.showPopupNotification(this.localeData.API_CONSENT_REVOKE_ERROR_REASON_REQUIRED, 0);
        return;
      }

      const formData = new FormData();
      formData.append('consent_id', consentID);
      formData.append('revoke_reason', reason);

      const request = new Interactive('request', {
        method: 'PATCH',
        url: '/handler/user/consent/' + consentID + '?localeMessage=' + window.CMSCore.locales.admin.name
      });

      request.target.data = formData;

      request.target.send().then((data) => {
        if (data.statusCode === 1) {
          window.location.reload();
        }
        modal.target.close();
      });
    });

    modal.target.addButton(this.localeData.BUTTON_CANCEL_LABEL, () => modal.target.close());

    modal.assembly();
    document.body.appendChild(modal.target.element);
    modal.target.show();
  }

  async handleExport() {
    const formData = new FormData();
    formData.append('export', 'csv');
    // Защита от кэша — как в Request::send()
    formData.append('_grv_' + Math.random().toString(36).slice(2), Math.random().toString(36).slice(2));

    const headers = {};
    if (window.CMSCore && window.CMSCore.client && window.CMSCore.client.getCSRFToken() !== '') {
      headers['X-CSRF-Token'] = window.CMSCore.client.getCSRFToken();
    }

    try {
      const response = await fetch(
        '/handler/user/consent/export?localeMessage=' + window.CMSCore.locales.admin.name,
        {
          method: 'POST',
          body: formData,
          headers: headers,
          credentials: 'same-origin'
        }
      );

      if (!response.ok) {
        try {
          const errorData = await response.json();
          this.page.showPopupNotification(errorData.message || 'Ошибка экспорта', 0);
        } catch (e) {
          this.page.showPopupNotification('Ошибка экспорта: ' + response.status, 0);
        }
        return;
      }

      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);

      const a = document.createElement('a');
      a.href = url;
      a.download = 'consents_' + new Date().toISOString().slice(0, 19).replace(/[:T]/g, '-') + '.csv';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      window.URL.revokeObjectURL(url);
    } catch (error) {
      this.page.showPopupNotification('Ошибка сети: ' + error, 0);
    }
  }
}