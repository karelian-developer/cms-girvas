<article class="page main__page main__page_form">
  <nav id="SYSTEM_AP_SUBNAVIGATION" class="page__navigation navigation"></nav>
  <div class="page__title-container">
    <h1 class="page__title">
      {LANG:PAGE_FORM_TITLE}
    </h1>
    <div class="page__interactive-container" data-element="header-interactive"></div>
  </div>
  <div class="page__content">
    <form class="form page__form" action="/handler/form" data-element="main-form">
      <input name="form_id" type="hidden" value="{FORM_ID}">
      <div class="grid-table page__grid-table">
        <!-- Поле: Техническое наименование -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="grid-table__cell-title">
            {LANG:PAGE_FORM_TECHNICAL_NAME_TITLE}
          </div>
          <div class="grid-table__cell-description">
            {LANG:PAGE_FORM_TECHNICAL_NAME_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <input name="form_name" type="text" class="input form__input form__input_text" value="{FORM_NAME}" placeholder="my-first-form" data-element="input-name" required>
        </div>
        <!-- Поле: Заголовок -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="grid-table__cell-title">
            {LANG:PAGE_FORM_TITLE_TITLE}
          </div>
          <div class="grid-table__cell-description">
            {LANG:PAGE_FORM_TITLE_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <input name="form_title_rus" type="text" class="input form__input form__input_text" value="{FORM_TITLE}" placeholder="{LANG:PAGE_FORM_TITLE_PLACEHOLDER}" data-element="input-title" required>
        </div>
        <!-- Поле: Описание -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="grid-table__cell-title">
            {LANG:PAGE_FORM_DESCRIPTION_TITLE}
          </div>
          <div class="grid-table__cell-description">
            {LANG:PAGE_FORM_DESCRIPTION_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <textarea name="form_description_rus" class="textarea form__textarea" placeholder="{LANG:PAGE_FORM_DESCRIPTION_PLACEHOLDER}" data-element="input-description" required>{FORM_DESCRIPTION}</textarea>
        </div>
        <!-- Поле: Ссылка обработки -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="grid-table__cell-title">
            {LANG:PAGE_FORM_ACTION_TITLE}
          </div>
          <div class="grid-table__cell-description">
            {LANG:PAGE_FORM_ACTION_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <input name="form_action" type="text" class="input form__input form__input_text" value="{FORM_ACTION}" placeholder="{LANG:PAGE_FORM_ACTION_PLACEHOLDER}" data-element="input-action" required>
        </div>
        <!-- Поле: Спецификация -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="grid-table__cell-title">
            {LANG:PAGE_FORM_METHOD_TITLE}
          </div>
          <div class="grid-table__cell-description">
            {LANG:PAGE_FORM_METHOD_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <div data-element="choice" data-choice="method"></div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_header">
          {LANG:PAGE_FORM_NOTIFICATIONS_TITLE}
        </div>
        <!-- Поле: Уведомления (чаты Telegram) -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="grid-table__cell-title">
            {LANG:PAGE_FORM_NOTIFICATIONS_TELEGRAM_CHATS_TITLE}
          </div>
          <div class="grid-table__cell-description">
            {LANG:PAGE_FORM_NOTIFICATIONS_TELEGRAM_CHATS_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <input name="form_notification_telegram_chats_ids" type="text" class="input form__input form__input_text" value="{FORM_NOTIFICATION_TELEGRAM_CHATS_IDS}" placeholder="123456789, 123456790..." data-element="input-notifications-telegram-chats-ids">
        </div>
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="grid-table__cell-title">
            {LANG:PAGE_FORM_NOTIFICATIONS_MAX_CHATS_TITLE}
          </div>
          <div class="grid-table__cell-description">
            {LANG:PAGE_FORM_NOTIFICATIONS_MAX_CHATS_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <input name="form_notification_max_chats_ids" type="text" class="input form__input form__input_text" value="{FORM_NOTIFICATION_MAX_CHATS_IDS}" placeholder="123456789, 123456790..." data-element="input-notifications-max-chats-ids">
        </div>
        <!-- Раздел: Элементы формы -->
        <div class="cell grid-table__cell grid-table__cell_header" data-element="form-elements-section-header">
          {LANG:PAGE_FORM_ELEMENTS_TITLE}
        </div>
        <!-- Панель формы -->
        <div class="cell grid-table__cell grid-table__cell_panel" data-element="panel"></div>
      </div>
    </form>
  </div>
</article>
<aside class="main__page-aside page-aside">
  <div class="page-aside__block">
    <h2 class="page-aside__block-title">{LANG:PAGE_FORMS_SIDEBAR_BLOCK_ABOUT_TITLE}</h2>
    <div class="page-aside__block-content block-content">
      <div class="note-block note-block_blue">
        {LANG:MD:PAGE_FORMS_SIDEBAR_BLOCK_ABOUT_DESCRIPTION}
      </div>
    </div>
  </div>
</aside>