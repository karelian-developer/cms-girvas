<tr class="table__row" data-consent-id="{CONSENT_ID}">
  <th class="table__cell cell">
    <div class="cell__title table__cell_header">{CONSENT_DOCUMENT_TITLE}</div>
  </th>
  <td class="table__cell cell">
    <div class="consents__meta">
      <span class="consents__meta-item">{LANG:PROFILE_CONSENTS_VERSION_LABEL}: {CONSENT_VERSION}</span>
      <span class="consents__meta-item">{LANG:PROFILE_CONSENTS_DATE_LABEL}: {CONSENT_DATE}</span>
      <span class="consents__meta-item">{LANG:PROFILE_CONSENTS_SOURCE_LABEL}: {CONSENT_SOURCE_LABEL}</span>
    </div>
    <button type="button" class="button button_red consents__revoke-button" data-consent-action="revoke" data-consent-id="{CONSENT_ID}">
      {LANG:PROFILE_CONSENTS_REVOKE_BUTTON}
    </button>
  </td>
</tr>