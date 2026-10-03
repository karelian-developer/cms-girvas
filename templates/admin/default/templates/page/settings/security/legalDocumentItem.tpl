<div class="cell grid-table__cell grid-table__cell_text">
  <div class="cell__title">
    {DOCUMENT_TITLE}
  </div>
  <div class="cell__description">
    {DOCUMENT_KEY}
  </div>
</div>
<div class="cell grid-table__cell grid-table__cell_data">
  <div class="form__checkbox-container checkbox-container">
    <input
      type="hidden"
      name="setting_security_legal_documents_{DOCUMENT_ID}_status"
      id="{HIDDEN_INPUT_ID}"
      value="{STATUS_VALUE}">
    <input
      class="checkbox-container__input form__input form__input_checkbox"
      id="{CHECKBOX_INPUT_ID}"
      type="checkbox"
      {CHECKED}
      data-status-block="{HIDDEN_INPUT_ID}">
    <label class="checkbox-container__label form__label" for="{CHECKBOX_INPUT_ID}"></label>
  </div>
</div>