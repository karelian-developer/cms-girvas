<article class="page main__page main__page_entry">
  <nav id="SYSTEM_AP_SUBNAVIGATION" class="page__navigation navigation"></nav>
  <div class="page__title-container">
    <h1 class="page__title">
      {LANG:PAGE_FEED_TITLE}
    </h1>
    <div class="page__interactive-container" data-element="header-interactive"></div>
  </div>
  <div class="page__content">
    <form class="form page__form" action="/handler/feed" data-element="main-form">
      <input name="feed_id" type="hidden" value="{FEED_ID}">
      <div class="grid-table page__grid-table">
        <!-- Поле: Техническое наименование -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_FEED_TECHNICAL_NAME_TITLE}
          </div>
          <div class="cell__description">
            {LANG:PAGE_FEED_TECHNICAL_NAME_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <input name="feed_name" type="text" class="input form__input form__input_text" value="{FEED_NAME}" placeholder="my-first-feed" data-element="input-url" required>
        </div>
        <!-- Поле: Заголовок -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_FEED_TITLE_TITLE}
          </div>
          <div class="cell__description">
            {LANG:PAGE_FEED_TITLE_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <input name="feed_title_rus" type="text" class="input form__input form__input_text" value="{FEED_TITLE}" placeholder="{LANG:PAGE_FEED_TITLE_PLACEHOLDER}" data-element="input-title" required>
        </div>
        <!-- Поле: Описание -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_FEED_DESCRIPTION_TITLE}
          </div>
          <div class="cell__description">
            {LANG:PAGE_FEED_DESCRIPTION_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <textarea name="feed_description_rus" class="textarea form__textarea" placeholder="{LANG:PAGE_FEED_DESCRIPTION_PLACEHOLDER}" data-element="input-description" required>{FEED_DESCRIPTION}</textarea>
        </div>
        <!-- Поле: Спецификация -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_FEED_SPECIFICATION_TITLE}
          </div>
          <div class="cell__description">
            {LANG:PAGE_FEED_SPECIFICATION_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <div data-element="choice" data-choice="specification"></div>
        </div>
        <!-- Поле: Категория записей -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_FEED_ENTRIES_CATEGORY_TITLE}
          </div>
          <div class="cell__description">
            {LANG:PAGE_FEED_ENTRIES_CATEGORY_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <div data-element="choice" data-choice="category"></div>
        </div>
        <!-- Панель формы -->
        <div class="cell grid-table__cell grid-table__cell_panel" data-element="panel"></div>
      </div>
    </form>
  </div>
</article>
<aside class="main__page-aside page-aside">
  <div class="page-aside__block">
    <h2 class="page-aside__block-title">{LANG:PAGE_FEED_SIDEBAR_BLOCK_ABOUT_TITLE}</h2>
    <div class="page-aside__block-content block-content">
      <div class="note-block note-block_blue">
        <p class="block-content__phar">{LANG:PAGE_FEED_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_1}</p>
        <p class="block-content__phar">{LANG:PAGE_FEED_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_2}</p>
        <p class="block-content__phar">{LANG:PAGE_FEED_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_3}</p>
      </div>
    </div>
  </div>
</aside>