<article class="page main__page main__page_content-block">
  <nav id="SYSTEM_AP_SUBNAVIGATION" class="page__navigation navigation"></nav>
  <div class="page__title-container">
    <h1 class="page__title">
      {LANG:PAGE_CONTENT_BLOCK_TITLE}
    </h1>
    <div class="page__interactive-container" data-element="header-interactive"></div>
  </div>
  <div class="page__content">
    <form class="form page__form" action="/handler/contentBlock" data-element="main-form">
      <input name="content_block_id" type="hidden" value="{CONTENT_BLOCK_ID}">
      <div class="grid-table page__grid-table">
        <!-- Поле: Техническое наименование контент-блока -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_CONTENT_BLOCK_INPUT_TECH_NAME_LABEL}
          </div>
          <div class="cell__description">
            {LANG:PAGE_CONTENT_BLOCK_INPUT_TECH_NAME_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <input name="content_block_name" type="text" class="input form__input form__input_text" value="{CONTENT_BLOCK_NAME}" placeholder="myFirstContentBlock" data-element="input-name" required>
        </div>
        <!-- Поле: Заголовок контент-блока -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_CONTENT_BLOCK_INPUT_TITLE_LABEL}
          </div>
          <div class="cell__description">
            {LANG:PAGE_CONTENT_BLOCK_INPUT_TITLE_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <input name="content_block_title_rus" type="text" class="input form__input form__input_text" value="{CONTENT_BLOCK_TITLE}" placeholder="{LANG:PAGE_CONTENT_BLOCK_INPUT_TITLE_PLACEHOLDER}" data-element="input-title" required>
        </div>
        <!-- Поле: Описание записи -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_CONTENT_BLOCK_INPUT_DESCRIPTION_LABEL}
          </div>
          <div class="cell__description">
            {LANG:PAGE_CONTENT_BLOCK_INPUT_DESCRIPTION_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <textarea name="content_block_description_rus" class="textarea form__textarea" placeholder="{LANG:PAGE_CONTENT_BLOCK_INPUT_DESCRIPTION_PLACEHOLDER}" data-element="input-description" required>{CONTENT_BLOCK_DESCRIPTION}</textarea>
        </div>
        <!-- Поле: Категория записи -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_CONTENT_BLOCK_SELECT_TYPE_LABEL}
          </div>
          <div class="cell__description">
            {LANG:PAGE_CONTENT_BLOCK_SELECT_TYPE_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <div data-element="choice" data-choice="type"></div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_editor" data-element="editor">
          {CONTENT_BLOCK_EDITOR}
        </div>
        <!-- Панель формы -->
        <div class="cell grid-table__cell grid-table__cell_panel" data-element="panel"></div>
      </div>
    </form>
  </div>
</article>
<aside class="main__page-aside page-aside">
  <article class="page-aside__block">
    <h2 class="page-aside__block-title">{LANG:PAGE_CONTENT_BLOCKS_SIDEBAR_BLOCK_ABOUT_TITLE}</h2>
    <div class="page-aside__block-content block-content">
      <div class="note-block note-block_blue">
        <p class="block-content__phar">{LANG:MD:PAGE_CONTENT_BLOCKS_SIDEBAR_BLOCK_ABOUT_DESCRIPTION}</p>
      </div>
    </div>
  </article>
</aside>