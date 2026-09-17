<div class="input-container input-container_flex-checkbox">
  <input
    type="checkbox"
    class="form__input form__input_checkbox"
    id="legal-doc-{DOCUMENT_ID}"
    name="setting_security_legal_documents[]"
    value="{DOCUMENT_KEY}"
    {DOCUMENT_CHECKED}>
  <label class="input-container__label label" for="legal-doc-{DOCUMENT_ID}">
    <span class="label__title">{DOCUMENT_TITLE}</span>
    <span class="label__subtitle">({DOCUMENT_KEY})</span>
  </label>
</div>