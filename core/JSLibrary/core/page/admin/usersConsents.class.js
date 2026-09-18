'use strict';

import {Interactive} from "../../../interactive.class.js";

export class PageUsersConsents {
  constructor(page, params = {}) {
    this.page = page;
  }

  init() {
    this.page.core.locales.admin.getData().then((localeData) => {
      this.localeData = localeData;

      // Кнопки «Отозвать»
      const revokeButtons = document.querySelectorAll('[data-event="revoke"]');
      revokeButtons.forEach((button) => {
        button.addEventListener('click', (event) => {
          event.preventDefault();
          const consentID = button.getAttribute('data-consent-id');
          this.handleRevoke(consentID, button);
        });
      });

      // Кнопка экспорта
      const exportButton = document.querySelector('[data-action="export-consents"]');
      if (exportButton !== null) {
        exportButton.addEventListener('click', (event) => {
          event.preventDefault();
          this.handleExport();
        });
      }
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

  handleExport() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/handler/user/consent/export?localeMessage=' + window.CMSCore.locales.admin.name;
    form.style.display = 'none';

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'export';
    input.value = 'csv';
    form.appendChild(input);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
  }
}