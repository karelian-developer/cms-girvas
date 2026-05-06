<article class="page main__page main__page_entry">
  <nav id="SYSTEM_AP_SUBNAVIGATION" class="page__navigation navigation"></nav>
  <div class="page__title-container">
    <h1 class="page__title">
      {LANG:PAGE_ENTRY_TITLE}
    </h1>
    <div class="page__interactive-container" data-element="header-interactive"></div>
  </div>
  <div class="page__content">
    <form class="form page__form" action="/handler/entry" data-element="main-form">
      <input name="entry_id" type="hidden" value="{ENTRY_ID}">
      <div class="grid-table page__grid-table">
        <!-- Поле: Техническое наименование записи -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_ENTRY_INPUT_TECH_NAME_LABEL}
          </div>
          <div class="cell__description">
            {LANG:PAGE_ENTRY_INPUT_TECH_NAME_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <input name="entry_name" type="text" class="input form__input form__input_text" value="{ENTRY_NAME}" placeholder="my-first-entry" data-element="input-url" required>
        </div>
        <!-- Поле: Заголовок записи -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_ENTRY_INPUT_TITLE_LABEL}
          </div>
          <div class="cell__description">
            {LANG:PAGE_ENTRY_INPUT_TITLE_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <input name="entry_title_rus" type="text" class="input form__input form__input_text" value="{ENTRY_TITLE}" placeholder="{LANG:PAGE_ENTRY_INPUT_TITLE_PLACEHOLDER}" data-element="input-title" required>
        </div>
        <!-- Поле: SEO-заголовок записи -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_ENTRY_INPUT_SEO_TITLE_LABEL}
          </div>
          <div class="cell__description">
            {LANG:PAGE_ENTRY_INPUT_SEO_TITLE_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <input name="entry_seo_title_rus" type="text" class="input form__input form__input_text" value="{ENTRY_SEO_TITLE}" placeholder="{LANG:PAGE_ENTRY_INPUT_SEO_TITLE_PLACEHOLDER}" data-element="input-seo-title">
        </div>
        <!-- Поле: Описание записи -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_ENTRY_INPUT_DESCRIPTION_LABEL}
          </div>
          <div class="cell__description">
            {LANG:PAGE_ENTRY_INPUT_DESCRIPTION_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <textarea name="entry_description_rus" class="textarea form__textarea" placeholder="{LANG:PAGE_ENTRY_INPUT_DESCRIPTION_PLACEHOLDER}" data-element="input-description" required>{ENTRY_DESCRIPTION}</textarea>
        </div>
        <!-- Поле: SEO-описание записи -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_ENTRY_INPUT_SEO_DESCRIPTION_LABEL}
          </div>
          <div class="cell__description">
            {LANG:PAGE_ENTRY_INPUT_SEO_DESCRIPTION_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <textarea name="entry_seo_description_rus" class="textarea form__textarea" placeholder="{LANG:PAGE_ENTRY_INPUT_SEO_DESCRIPTION_PLACEHOLDER}" data-element="input-seo-description">{ENTRY_SEO_DESCRIPTION}</textarea>
        </div>
        <!-- Поле: Ключевые фразы/слова записи -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_ENTRY_INPUT_KEYWORDS_LABEL}
          </div>
          <div class="cell__description">
            {LANG:PAGE_ENTRY_INPUT_KEYWORDS_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <textarea name="entry_keywords_rus" class="textarea form__textarea" placeholder="{LANG:PAGE_ENTRY_INPUT_KEYWORDS_PLACEHOLDER}" data-element="input-keywords">{ENTRY_KEYWORDS}</textarea>
        </div>
        <!-- Поле: Категория записи -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_ENTRY_SELECT_CATEGORY_LABEL}
          </div>
          <div class="cell__description">
            {LANG:PAGE_ENTRY_SELECT_CATEGORY_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <div data-element="choice" data-choice="category"></div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_editor" data-element="editor">
          {ENTRY_EDITOR}
        </div>
        <!-- Панель формы -->
        <div class="cell grid-table__cell grid-table__cell_panel" data-element="panel"></div>
      </div>
    </form>
  </div>
</article>
<aside class="main__page-aside page-aside">
  <div class="page-aside__block" data-element="aside-block-cover">
    <h2 class="page-aside__block-title">
      {LANG:PAGE_ENTRY_SIDEBAR_BLOCK_COVER_TITLE}
    </h2>
    <div class="page-aside__block-content block-content"></div>
  </div>
  <div class="page-aside__block" data-element="aside-block-seo-analyzer">
    <h2 class="page-aside__block-title">
      {LANG:PAGE_ENTRY_SIDEBAR_SEO_ANALYZER_TITLE}
    </h2>
    <div class="page-aside__block-content block-content"></div>
  </div>
  <div class="page-aside__block block">
    <h2 class="page-aside__block-title">
      {LANG:PAGE_ENTRY_SIDEBAR_BLOCK_SCHEDULER_TITLE}
    </h2>
    <div class="page-aside__block-content block-content">
      <div class="block__input-container input-container">
        {LANG:MD:PAGE_ENTRY_SIDEBAR_BLOCK_SCHEDULER_TIP}
        <input class="block__input input input_date" type="datetime-local" name="entry_published_timestamp" value="{ENTRY_PUBLISHED_TIMESTAMP}" data-element="published-date-input">
      </div>
    </div>
  </div>
  <div class="page-aside__block" data-element="aside-block-additional-fields">
    <h2 class="page-aside__block-title">
      {LANG:PAGE_ENTRY_SIDEBAR_BLOCK_ADDITIONAL_FIELDS_TITLE}
    </h2>
    <div class="page-aside__block-content block-content">
      {LANG:MD:PAGE_ENTRY_SIDEBAR_BLOCK_ADDITIONAL_FIELDS_TIP}
      <div class="additional-data" data-element="additional-data">
        {ENTRY_ADDITIONAL_FIELDS}
      </div>
    </div>
  </div>
  <div class="page-aside__block">
    <h2 class="page-aside__block-title">
      {LANG:PAGE_ENTRY_SIDEBAR_BLOCK_ABOUT_TITLE}
    </h2>
    <div class="page-aside__block-content block-content">
      <div class="note-block note-block_blue">
        <p class="block-content__phar">{LANG:PAGE_ENTRY_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_1}</p>
        <p class="block-content__phar">{LANG:PAGE_ENTRY_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_2}</p>
        <p class="block-content__phar">{LANG:PAGE_ENTRY_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_3}</p>
      </div>
    </div>
  </div>
</aside>