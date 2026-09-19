'use strict';

import {Interactive} from "../../../interactive.class.js";

export class PageUsersConsents {
  constructor(page, params = {}) {
    this.page = page;
  }

  init() {
    this.page.core.locales.admin.getData().then((localeData) => {
      this.localeData = localeData;

      // Кнопка «Экспорт CSV»
      const exportContainer = document.querySelector('[data-element="export-container"]');
      if (exportContainer !== null) {
        const exportButton = new Interactive('button');
        exportButton.target.setLabel(localeData.PAGE_USERS_CONSENTS_BUTTON_EXPORT || 'Экспорт CSV');
        exportButton.target.setStyle('default');
        exportButton.target.setCallback((event) => {
          event.preventDefault();
          this.handleExport();
        });
        exportButton.assembly();
        exportContainer.append(exportButton.target.element);
      }

      // Кнопки «Отозвать» — оставить как есть (они уже работают)
      const revokeButtons = document.querySelectorAll('[data-event="revoke"]');
      revokeButtons.forEach((button) => {
        button.addEventListener('click', (event) => {
          event.preventDefault();
          const consentID = button.getAttribute('data-consent-id');
          this.handleRevoke(consentID, button);
        });
      });
    }, (rejectionReason) => {
      this.page.showPopupNotification(rejectionReason, 0);
    });
  }

  handleRevoke(consentID, button) {
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
        // Если сервер вернул JSON с ошибкой (CSRF, права)
        try {
          const errorData = await response.json();
          this.page.showPopupNotification(errorData.message || 'Ошибка экспорта', 0);
        } catch (e) {
          this.page.showPopupNotification('Ошибка экспорта: ' + response.status, 0);
        }
        return;
      }

      // Получаем blob
      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);

      // Скачиваем
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