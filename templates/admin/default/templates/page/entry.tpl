<article class="main__page page page_{ADMIN_PANEL_PAGE_NAME}">
  <nav id="SYSTEM_AP_SUBNAVIGATION" class="page__navigation navigation"></nav>
  <div class="page__title-container">
    <h1 class="page__title">{LANG:PAGE_ENTRY_TITLE}</h1>
    <div id="E8548530785" class="page__interactive-container"></div>
  </div>
  <div class="page__content">
    <form id="SYSTEM_F0648538658" class="form form_entry entry-form" action="/handler/entry">
      <input name="entry_id" type="hidden" value="{ENTRY_ID}">
      <table class="table table_entry">
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_ENTRY_INPUT_TECH_NAME_LABEL}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_ENTRY_INPUT_TECH_NAME_DESCRIPTION}</div>
            </div>
          </td>
          <td class="table__cell cell">
            <div class="page__phar-block">
              <input name="entry_name" type="text" class="form__input entry-form__input" value="{ENTRY_NAME}" placeholder="my-first-entry" role="entryURL" required>
            </div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_ENTRY_INPUT_TITLE_LABEL}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_ENTRY_INPUT_TITLE_DESCRIPTION}</div>
            </div>
          </td>
          <td class="table__cell cell">
            <div class="page__phar-block">
              <input name="entry_title_rus" type="text" class="form__input entry-form__input" role="entryTitle" value="{ENTRY_TITLE}" placeholder="{LANG:PAGE_ENTRY_INPUT_TITLE_LABEL}" required>
            </div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_ENTRY_INPUT_SEO_TITLE_LABEL}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_ENTRY_INPUT_SEO_TITLE_DESCRIPTION}</div>
            </div>
          </td>
          <td class="table__cell cell">
            <div class="page__phar-block">
              <input name="entry_seo_title_rus" type="text" class="form__input entry-form__input" role="entryTitle" value="{ENTRY_SEO_TITLE}" placeholder="{LANG:PAGE_ENTRY_INPUT_SEO_TITLE_LABEL}">
            </div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_ENTRY_INPUT_DESCRIPTION_LABEL}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_ENTRY_INPUT_DESCRIPTION_DESCRIPTION}</div>
            </div>
          </td>
          <td class="table__cell cell">
            <div class="page__phar-block">
              <textarea name="entry_description_rus" class="form__textarea entry-form__textarea" role="entryDescription" placeholder="{LANG:PAGE_ENTRY_INPUT_DESCRIPTION_LABEL}" required>{ENTRY_DESCRIPTION}</textarea>
            </div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_ENTRY_INPUT_SEO_DESCRIPTION_LABEL}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_ENTRY_INPUT_SEO_DESCRIPTION_DESCRIPTION}</div>
            </div>
          </td>
          <td class="table__cell cell">
            <div class="page__phar-block">
              <textarea name="entry_seo_description_rus" class="form__textarea entry-form__textarea" role="entryDescription" placeholder="{LANG:PAGE_ENTRY_INPUT_SEO_DESCRIPTION_LABEL}">{ENTRY_SEO_DESCRIPTION}</textarea>
            </div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_ENTRY_INPUT_KEYWORDS_LABEL}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_ENTRY_INPUT_KEYWORDS_DESCRIPTION}</div>
            </div>
          </td>
          <td class="table__cell cell">
            <div class="page__phar-block">
              <textarea name="entry_keywords_rus" class="form__textarea entry-form__textarea" role="entryKeywords" placeholder="{LANG:PAGE_ENTRY_INPUT_KEYWORDS_PLACEHOLDER}">{ENTRY_KEYWORDS}</textarea>
            </div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_ENTRY_SELECT_CATEGORY_LABEL}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_ENTRY_SELECT_CATEGORY_DESCRIPTION}</div>
            </div>
          </td>
          <td class="table__cell cell">
            <div class="page__phar-block" id="TC6474389611"></div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell cell" colspan="2">
            <div class="page__phar-block">
              {ENTRY_EDITOR}
            </div>
          </td>
        </tr>
      </table>
      <div class="form__bottom-panel" id="SYSTEM_E3724126170"></div>
    </form>
  </div>
</article>
<aside class="main__page-aside page-aside">
  <article id="SYSTEM_E3754926184" class="page-aside__block">
    <h2 class="page-aside__block-title">{LANG:PAGE_ENTRY_SIDEBAR_BLOCK_COVER_TITLE}</h2>
    <div class="page-aside__block-content block-content"></div>
  </article>
  <article id="SYSTEM_E3754926185" class="page-aside__block">
    <h2 class="page-aside__block-title">{LANG:PAGE_ENTRY_SIDEBAR_BLOCK_ADDITIONAL_FIELDS_TITLE}</h2>
    <div class="page-aside__block-content block-content">
      {LANG:MD:PAGE_ENTRY_SIDEBAR_BLOCK_ADDITIONAL_FIELDS_TIP}
      <div class="additional-data" role="additional-data">
        {ENTRY_ADDITIONAL_FIELDS}
      </div>
    </div>
  </article>
  <article class="page-aside__block">
    <h2 class="page-aside__block-title">{LANG:PAGE_ENTRY_SIDEBAR_BLOCK_ABOUT_TITLE}</h2>
    <div class="page-aside__block-content block-content">
      <div class="note-block note-block_blue">
        <p class="block-content__phar">{LANG:PAGE_ENTRY_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_1}</p>
        <p class="block-content__phar">{LANG:PAGE_ENTRY_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_2}</p>
        <p class="block-content__phar">{LANG:PAGE_ENTRY_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_3}</p>
      </div>
    </div>
  </article>
</aside>