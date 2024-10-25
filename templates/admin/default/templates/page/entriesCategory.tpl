<article class="main__page page page_{ADMIN_PANEL_PAGE_NAME}">
  {PAGE_NAVIGATION}
  <div class="page__title-container">
    <h1 class="page__title">{LANG:PAGE_ENTRIES_CATEGORY_TITLE}</h1>
    <div id="E8548530785" class="page__interactive-container"></div>
  </div>
  <div class="page__content">
    <form class="form form_entries-category page__entries-category-form entries-category-form" action="/handler/entry/category">
      <input name="entries_category_id" type="hidden" value="{ENTRIES_CATEGORY_ID}">
      <table class="table table_entries-category">
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_ENTRIES_CATEGORY_INPUT_TECH_NAME_LABEL}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_ENTRIES_CATEGORY_INPUT_TECH_NAME_DESCRIPTION}</div>
            </div>
          </td>
          <td class="table__cell cell">
            <div class="page__phar-block">
              <input name="entries_category_name" typde="text" class="form__input entries-category-form__input" value="{ENTRIES_CATEGORY_NAME}" placeholder="news" role="entriesCategoryURL">
            </div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_ENTRIES_CATEGORY_INPUT_TITLE_LABEL}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_ENTRIES_CATEGORY_INPUT_TITLE_DESCRIPTION}</div>
            </div>
          </td>
          <td class="table__cell cell">
            <div class="page__phar-block">
              <input name="entries_category_title_rus" type="text" class="form__input entries-category-form__input" role="entriesCategoryTitle" value="{ENTRIES_CATEGORY_TITLE}" placeholder="{LANG:PAGE_ENTRIES_CATEGORY_INPUT_TITLE_LABEL}">
            </div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_ENTRIES_CATEGORY_INPUT_DESCRIPTION_LABEL}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_ENTRIES_CATEGORY_INPUT_DESCRIPTION_DESCRIPTION}</div>
            </div>
          </td>
          <td class="table__cell cell">
            <div class="page__phar-block">
              <textarea name="entries_category_description_rus" class="form__textarea entries-category-form__textarea" role="entriesCategoryDescription" placeholder="{LANG:PAGE_ENTRIES_CATEGORY_INPUT_DESCRIPTION_LABEL}">{ENTRIES_CATEGORY_DESCRIPTION}</textarea>
            </div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_ENTRIES_CATEGORY_SELECT_PARENT_CATEGORY_LABEL}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_ENTRIES_CATEGORY_SELECT_PARENT_CATEGORY_DESCRIPTION}</div>
            </div>
          </td>
          <td class="table__cell cell">
            <div id="TC6474389602" class="page__phar-block"></div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_ENTRIES_CATEGORY_SHOW_ON_INDEX_PAGE_LABEL}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_ENTRIES_CATEGORY_SHOW_ON_INDEX_PAGE_DESCRIPTION}</div>
            </div>
          </td>
          <td class="table__cell cell">
            <div class="form__checkbox-container checkbox-container">
              <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308970" name="entries_category_show_index" type="checkbox" value="on" {ENTRIES_CATEGORY_SHOW_ON_INDEX_PAGE}>
              <label class="checkbox-container__label form__label" for="I1474308970"></label>
            </div>
          </td>
        </tr>
      </table>
      <div class="form__bottom-panel" id="SYSTEM_E3724126170"></div>
    </form>
  </div>
</article>
<aside class="main__page-aside page-aside">
  <article class="page-aside__block">
    <h2 class="page-aside__block-title">{LANG:PAGE_ENTRY_CATEGORIES_SIDEBAR_BLOCK_ABOUT_TITLE}</h2>
    <div class="page-aside__block-content block-content">
      <div class="note-block note-block_blue">
        <p class="block-content__phar">{LANG:PAGE_ENTRY_CATEGORIES_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_1}</p>
        <p class="block-content__phar">{LANG:PAGE_ENTRY_CATEGORIES_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_2}</p>
        <p class="block-content__phar">{LANG:PAGE_ENTRY_CATEGORIES_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_3}</p>
      </div>
    </div>
  </article>
</aside>