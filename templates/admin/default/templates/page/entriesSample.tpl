<article class="page main__page main__page_entries-sample">
  <nav id="SYSTEM_AP_SUBNAVIGATION" class="page__navigation navigation"></nav>
  <div class="page__title-container">
    <h1 class="page__title">
      {LANG:PAGE_ENTRIES_SAMPLE_TITLE}
    </h1>
    <div class="page__interactive-container" data-element="header-interactive"></div>
  </div>
  <div class="page__content">
    <form class="form page__form" action="/handler/entries/sample" data-element="main-form">
      <input name="entries_sample_id" type="hidden" value="{ENTRIES_SAMPLE_ID}">
      <div class="grid-table page__grid-table">
        <!-- Поле: Техническое наименование -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_ENTRIES_SAMPLE_NAME_LABEL}
          </div>
          <div class="cell__description">
            {LANG:PAGE_ENTRIES_SAMPLE_NAME_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <input name="entries_sample_name" type="text" class="input form__input form__input_text" value="{ENTRIES_SAMPLE_NAME}" placeholder="my-first-sample-for-entries" data-element="input-name" required>
        </div>
        <!-- Поле: Заголовок -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_ENTRIES_SAMPLE_TITLE_LABEL}
          </div>
          <div class="cell__description">
            {LANG:PAGE_ENTRIES_SAMPLE_TITLE_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <input name="entries_sample_title_rus" type="text" class="input form__input form__input_text" value="{ENTRIES_SAMPLE_TITLE}" placeholder="{LANG:PAGE_ENTRIES_SAMPLE_INPUT_TITLE_PLACEHOLDER}" data-element="input-title" required>
        </div>
        <!-- Поле: Описание -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_ENTRIES_SAMPLE_DESCRIPTION_LABEL}
          </div>
          <div class="cell__description">
            {LANG:PAGE_ENTRIES_SAMPLE_DESCRIPTION_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <textarea name="entries_sample_description_rus" class="textarea form__textarea" placeholder="{LANG:PAGE_ENTRIES_SAMPLE_INPUT_DESCRIPTION_PLACEHOLDER}" data-element="input-description" required>{ENTRIES_CATEGORY_DESCRIPTION}</textarea>
        </div>
        <!-- Поле: Количество записей -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_ENTRIES_SAMPLE_LIMIT_COUNT_LABEL}
          </div>
          <div class="cell__description">
            {LANG:PAGE_ENTRIES_SAMPLE_LIMIT_COUNT_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <input name="entries_sample_limit_count" type="number" class="input form__input form__input_text" value="{ENTRIES_SAMPLE_LIMIT_COUNT}" placeholder="6" data-element="input-limit">
        </div>
        <!-- Поле: Категории записей -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_ENTRIES_SAMPLE_CATEGORIES_LABEL}
          </div>
          <div class="cell__description">
            {LANG:PAGE_ENTRIES_SAMPLE_CATEGORIES_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <div data-element="choice" data-choice="categories"></div>
        </div>
        <!-- Поле: Способ сортировки -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_ENTRIES_SAMPLE_SORT_TYPE_LABEL}
          </div>
          <div class="cell__description">
            {LANG:PAGE_ENTRIES_SAMPLE_SORT_TYPE_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <div data-element="choice" data-choice="sort-method"></div>
        </div>
        <!-- Панель формы -->
        <div class="cell grid-table__cell grid-table__cell_panel" data-element="panel"></div>
      </div>
    </form>
  </div>
</article>
<aside class="main__page-aside page-aside">
  <div class="page-aside__block">
    <h2 class="page-aside__block-title">{LANG:PAGE_ENTRIES_SAMPLES_SIDEBAR_BLOCK_ABOUT_TITLE}</h2>
    <div class="page-aside__block-content block-content">
      <div class="note-block note-block_blue">
        <p class="block-content__phar">{LANG:MD:PAGE_ENTRIES_SAMPLES_SIDEBAR_BLOCK_ABOUT_DESCRIPTION}</p>
      </div>
    </div>
  </div>
</aside>