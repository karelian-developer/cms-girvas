<article class="page main__page main__page_entries-category">
  <nav id="SYSTEM_AP_SUBNAVIGATION" class="page__navigation navigation"></nav>
  <div class="page__title-container">
    <h1 class="page__title">
      {LANG:PAGE_ENTRIES_CATEGORY_TITLE}
    </h1>
    <div class="page__interactive-container" data-element="header-interactive"></div>
  </div>
  <div class="page__content">
    <form class="form page__form" action="/handler/entry/category" data-element="main-form">
      <input name="entries_category_id" type="hidden" value="{ENTRIES_CATEGORY_ID}">
      <div class="grid-table page__grid-table">
        <!-- Поле: Техническое наименование -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_ENTRIES_CATEGORY_INPUT_TECH_NAME_LABEL}
          </div>
          <div class="cell__description">
            {LANG:PAGE_ENTRIES_CATEGORY_INPUT_TECH_NAME_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <input name="entries_category_name" type="text" class="input form__input form__input_text" value="{ENTRIES_CATEGORY_NAME}" placeholder="my-first-category-for-entries" data-element="input-url" required>
        </div>
        <!-- Поле: Заголовок -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_ENTRIES_CATEGORY_INPUT_TITLE_LABEL}
          </div>
          <div class="cell__description">
            {LANG:PAGE_ENTRIES_CATEGORY_INPUT_TITLE_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <input name="entries_category_title_rus" type="text" class="input form__input form__input_text" value="{ENTRIES_CATEGORY_TITLE}" placeholder="{LANG:PAGE_ENTRIES_CATEGORY_INPUT_TITLE_PLACEHOLDER}" data-element="input-title" required>
        </div>
        <!-- Поле: SEO-заголовок -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_ENTRIES_CATEGORY_INPUT_SEO_TITLE_LABEL}
          </div>
          <div class="cell__description">
            {LANG:PAGE_ENTRIES_CATEGORY_INPUT_SEO_TITLE_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <input name="entries_category_seo_title_rus" type="text" class="input form__input form__input_text" value="{ENTRIES_CATEGORY_SEO_TITLE}" placeholder="{LANG:PAGE_ENTRIES_CATEGORY_INPUT_SEO_TITLE_PLACEHOLDER}" data-element="input-seo-title">
        </div>
        <!-- Поле: Описание -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_ENTRIES_CATEGORY_INPUT_DESCRIPTION_LABEL}
          </div>
          <div class="cell__description">
            {LANG:PAGE_ENTRIES_CATEGORY_INPUT_DESCRIPTION_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <textarea name="entries_category_description_rus" class="textarea form__textarea" placeholder="{LANG:PAGE_ENTRIES_CATEGORY_INPUT_DESCRIPTION_PLACEHOLDER}" data-element="input-description" required>{ENTRIES_CATEGORY_DESCRIPTION}</textarea>
        </div>
        <!-- Поле: SEO-описание -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_ENTRIES_CATEGORY_INPUT_SEO_DESCRIPTION_LABEL}
          </div>
          <div class="cell__description">
            {LANG:PAGE_ENTRIES_CATEGORY_INPUT_SEO_DESCRIPTION_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <textarea name="entries_category_seo_description_rus" class="textarea form__textarea" placeholder="{LANG:PAGE_ENTRIES_CATEGORY_INPUT_SEO_DESCRIPTION_PLACEHOLDER}" data-element="input-seo-description">{ENTRIES_CATEGORY_SEO_DESCRIPTION}</textarea>
        </div>
        <!-- Поле: Ключевые фразы/слова -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_ENTRIES_CATEGORY_INPUT_KEYWORDS_LABEL}
          </div>
          <div class="cell__description">
            {LANG:PAGE_ENTRIES_CATEGORY_INPUT_KEYWORDS_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <textarea name="entries_category_keywords_rus" class="textarea form__textarea" placeholder="{LANG:PAGE_ENTRIES_CATEGORY_INPUT_KEYWORDS_PLACEHOLDER}" data-element="input-keywords">{ENTRIES_CATEGORY_KEYWORDS}</textarea>
        </div>
        <!-- Поле: Категория записи -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_ENTRIES_CATEGORY_SELECT_PARENT_CATEGORY_LABEL}
          </div>
          <div class="cell__description">
            {LANG:PAGE_ENTRIES_CATEGORY_SELECT_PARENT_CATEGORY_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <div data-element="choice" data-choice="parent-category"></div>
        </div>
        <!-- Панель формы -->
        <div class="cell grid-table__cell grid-table__cell_panel" data-element="panel"></div>
      </div>
    </form>
  </div>
</article>
<aside class="main__page-aside page-aside">
  <div class="page-aside__block">
    <h2 class="page-aside__block-title">{LANG:PAGE_ENTRY_CATEGORIES_SIDEBAR_BLOCK_ABOUT_TITLE}</h2>
    <div class="page-aside__block-content block-content">
      <div class="note-block note-block_blue">
        <p class="block-content__phar">{LANG:PAGE_ENTRY_CATEGORIES_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_1}</p>
        <p class="block-content__phar">{LANG:PAGE_ENTRY_CATEGORIES_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_2}</p>
        <p class="block-content__phar">{LANG:PAGE_ENTRY_CATEGORIES_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_3}</p>
      </div>
    </div>
  </div>
</aside>