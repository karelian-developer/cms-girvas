<article class="page main__page main__page_page-static">
  <nav id="SYSTEM_AP_SUBNAVIGATION" class="page__navigation navigation"></nav>
  <div class="page__title-container">
    <h1 class="page__title">
      {LANG:PAGE_STATIC_PAGE_TITLE}
    </h1>
    <div class="page__interactive-container" data-element="header-interactive"></div>
  </div>
  <div class="page__content">
    <form class="form page__form" action="/handler/pageStatic" data-element="main-form">
      <input name="page_static_id" type="hidden" value="{PAGE_STATIC_ID}">
      <div class="grid-table page__grid-table">
        <!-- Поле: Техническое наименование -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_STATIC_PAGE_INPUT_TECH_NAME_LABEL}
          </div>
          <div class="cell__description">
            {LANG:PAGE_STATIC_PAGE_INPUT_TECH_NAME_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <input name="page_static_name" type="text" class="input form__input form__input_text" value="{PAGE_STATIC_NAME}" placeholder="my-first-static-page" data-element="input-url" required>
        </div>
        <!-- Поле: Заголовок -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_STATIC_PAGE_INPUT_TITLE_LABEL}
          </div>
          <div class="cell__description">
            {LANG:PAGE_STATIC_PAGE_INPUT_TITLE_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <input name="page_static_title_rus" type="text" class="input form__input form__input_text" value="{PAGE_STATIC_TITLE}" placeholder="{LANG:PAGE_STATIC_PAGE_INPUT_TITLE_PLACEHOLDER}" data-element="input-title" required>
        </div>
        <!-- Поле: SEO-заголовок -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_STATIC_PAGE_INPUT_SEO_TITLE_LABEL}
          </div>
          <div class="cell__description">
            {LANG:PAGE_STATIC_PAGE_INPUT_SEO_TITLE_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <input name="page_static_seo_title_rus" type="text" class="input form__input form__input_text" value="{PAGE_STATIC_SEO_TITLE}" placeholder="{LANG:PAGE_STATIC_PAGE_INPUT_SEO_TITLE_PLACEHOLDER}" data-element="input-seo-title">
        </div>
        <!-- Поле: Описание -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_STATIC_PAGE_INPUT_DESCRIPTION_LABEL}
          </div>
          <div class="cell__description">
            {LANG:PAGE_STATIC_PAGE_INPUT_DESCRIPTION_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <textarea name="page_static_description_rus" class="textarea form__textarea" placeholder="{LANG:PAGE_STATIC_PAGE_INPUT_DESCRIPTION_PLACEHOLDER}" data-element="input-description" required>{PAGE_STATIC_DESCRIPTION}</textarea>
        </div>
        <!-- Поле: SEO-описание -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_STATIC_PAGE_INPUT_SEO_DESCRIPTION_LABEL}
          </div>
          <div class="cell__description">
            {LANG:PAGE_STATIC_PAGE_INPUT_SEO_DESCRIPTION_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <textarea name="page_static_seo_description_rus" class="textarea form__textarea" placeholder="{LANG:PAGE_STATIC_PAGE_INPUT_SEO_DESCRIPTION_PLACEHOLDER}" data-element="input-seo-description">{PAGE_STATIC_SEO_DESCRIPTION}</textarea>
        </div>
        <!-- Поле: Ключевые фразы/слова -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_STATIC_PAGE_INPUT_KEYWORDS_LABEL}
          </div>
          <div class="cell__description">
            {LANG:PAGE_STATIC_PAGE_INPUT_KEYWORDS_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <textarea name="page_static_keywords_rus" class="textarea form__textarea" placeholder="{LANG:PAGE_STATIC_PAGE_INPUT_KEYWORDS_PLACEHOLDER}" data-element="input-keywords">{PAGE_STATIC_KEYWORDS}</textarea>
        </div>
        <!-- Редактор -->
        <div class="cell grid-table__cell grid-table__cell_editor" data-element="editor">
          {PAGE_STATIC_EDITOR}
        </div>
        <!-- Панель формы -->
        <div class="cell grid-table__cell grid-table__cell_panel" data-element="panel"></div>
      </div>
    </form>
  </div>
</article>
<aside class="main__page-aside page-aside">
  <div class="page-aside__block block" data-element="aside-block-cover">
    <h2 class="page-aside__block-title">
      {LANG:PAGE_STATIC_PAGE_SIDEBAR_BLOCK_COVER_TITLE}
    </h2>
    <div class="page-aside__block-content block-content"></div>
    <div class="page-aside__block-panel block-panel"></div>
  </div>
  <div class="page-aside__block" data-element="aside-block-seo-analyzer">
    <h2 class="page-aside__block-title">
      {LANG:PAGE_PAGE_STATIC_SIDEBAR_SEO_ANALYZER_TITLE}
    </h2>
    <div class="page-aside__block-content block-content">
      <div class="note-block note-block_blue">
        {LANG:MD:PAGE_PAGE_STATIC_SIDEBAR_BLOCK_SEO_ANALYZER_TIP}
      </div>
    </div>
    <div class="page-aside__block-panel block-panel"></div>
  </div>
  <div class="page-aside__block block">
    <h2 class="page-aside__block-title">
      {LANG:PAGE_STATIC_PAGE_SIDEBAR_BLOCK_PERSONAL_TEMPLATE_TITLE}
    </h2>
    <div class="page-aside__block-content block-content">
      <div class="block__input-container input-container">
        {LANG:MD:PAGE_PAGE_STATIC_SIDEBAR_BLOCK_PERSONAL_TEMPLATE_TIP}
        <input class="block__input input input_text" type="text" name="page_static_template_path" pattern="^(?!.*(?:\.\./|\./))[a-zA-Z0-9/]+[a-zA-Z0-9_.-]*\.tpl$" placeholder="page/static/snowka.tpl" value="{PAGE_STATIC_PERSONAL_TEMPLATE_PATH}">
      </div>
    </div>
  </div>
  <div class="page-aside__block block">
    <h2 class="page-aside__block-title">
      {LANG:PAGE_STATIC_PAGE_SIDEBAR_BLOCK_SCHEDULER_TITLE}
    </h2>
    <div class="page-aside__block-content block-content">
      <div class="block__input-container input-container">
        {LANG:MD:PAGE_PAGE_STATIC_SIDEBAR_BLOCK_SCHEDULER_TIP}
        <input class="block__input input input_date" type="datetime-local" name="page_static_published_timestamp" value="{PAGE_STATIC_PUBLISHED_TIMESTAMP}" data-element="published-date-input">
      </div>
    </div>
  </div>
  <div class="page-aside__block block">
    <h2 class="page-aside__block-title">
      {LANG:PAGE_STATIC_PAGE_SIDEBAR_BLOCK_ADDITIONAL_FIELDS_TITLE}
    </h2>
    <div class="page-aside__block-content block-content">
      {LANG:MD:PAGE_PAGE_STATIC_SIDEBAR_BLOCK_ADDITIONAL_FIELDS_TIP}
      <div class="additional-data" data-element="additional-data">
        {PAGE_STATIC_ADDITIONAL_FIELDS}
      </div>
    </div>
  </div>
  <div class="page-aside__block">
    <h2 class="page-aside__block-title">
      {LANG:PAGE_STATIC_PAGE_SIDEBAR_BLOCK_ABOUT_TITLE}
    </h2>
    <div class="page-aside__block-content block-content">
      <div class="note-block note-block_blue">
        <p class="block-content__phar">{LANG:PAGE_STATIC_PAGE_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_1}</p>
        <p class="block-content__phar">{LANG:PAGE_STATIC_PAGE_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_2}</p>
      </div>
    </div>
  </div>
</aside>