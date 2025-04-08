<article class="main__page page page_entries-sample">
  <nav id="SYSTEM_AP_SUBNAVIGATION" class="page__navigation navigation"></nav>
  <div class="page__title-container">
    <h1 class="page__title">{LANG:PAGE_ENTRIES_SAMPLE_TITLE}</h1>
    <div id="E8548530785" class="page__interactive-container"></div>
  </div>
  <div class="page__content">
    <form class="form form_entries-sample page__entries-sample-form entries-sample-form" action="/handler/entries/sample">
      <input name="entries_sample_id" type="hidden" value="{ENTRIES_SAMPLE_ID}">
      <table class="table table_entries-sample">
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_ENTRIES_SAMPLE_NAME_LABEL}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_ENTRIES_SAMPLE_NAME_DESCRIPTION}</div>
            </div>
          </td>
          <td class="table__cell cell">
            <div class="page__phar-block">
              <input name="entries_sample_name" type="text" class="form__input entries-sample-form__input" value="{ENTRIES_SAMPLE_NAME}" placeholder="news" role="entriesSampleName">
            </div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_ENTRIES_SAMPLE_TITLE_LABEL}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_ENTRIES_SAMPLE_TITLE_DESCRIPTION}</div>
            </div>
          </td>
          <td class="table__cell cell">
            <div class="page__phar-block">
              <input name="entries_sample_title_rus" type="text" class="form__input entries-sample-form__input" role="entriesSampleTitle" value="{ENTRIES_SAMPLE_TITLE}" placeholder="{LANG:PAGE_ENTRIES_SAMPLE_INPUT_TITLE_LABEL}">
            </div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_ENTRIES_SAMPLE_DESCRIPTION_LABEL}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_ENTRIES_SAMPLE_DESCRIPTION_DESCRIPTION}</div>
            </div>
          </td>
          <td class="table__cell cell">
            <div class="page__phar-block">
              <textarea name="entries_sample_description_rus" class="form__textarea entries-sample-form__textarea" role="entriesSampleDescription" placeholder="{LANG:PAGE_ENTRIES_SAMPLE_INPUT_DESCRIPTION_LABEL}">{ENTRIES_SAMPLE_DESCRIPTION}</textarea>
            </div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_ENTRIES_SAMPLE_LIMIT_COUNT_LABEL}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_ENTRIES_SAMPLE_LIMIT_COUNT_DESCRIPTION}</div>
            </div>
          </td>
          <td class="table__cell cell">
            <div class="page__phar-block">
              <input name="entries_sample_limit_count" type="number" class="form__input entries-sample-form__input" value="{ENTRIES_SAMPLE_LIMIT_COUNT}" placeholder="6">
            </div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_ENTRIES_SAMPLE_CATEGORIES_LABEL}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_ENTRIES_SAMPLE_CATEGORIES_DESCRIPTION}</div>
            </div>
          </td>
          <td class="table__cell cell">
            <div id="TC6474389602" class="page__phar-block"></div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_ENTRIES_SAMPLE_SORT_TYPE_LABEL}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_ENTRIES_SAMPLE_SORT_TYPE_DESCRIPTION}</div>
            </div>
          </td>
          <td class="table__cell cell">
            <div id="TC6474389603" class="page__phar-block"></div>
          </td>
        </tr>
      </table>
      <div class="form__bottom-panel" id="SYSTEM_E3724126170"></div>
    </form>
  </div>
</article>
<aside class="main__page-aside page-aside">
  <article class="page-aside__block">
    <h2 class="page-aside__block-title">{LANG:PAGE_ENTRIES_SAMPLES_SIDEBAR_BLOCK_ABOUT_TITLE}</h2>
    <div class="page-aside__block-content block-content">
      <div class="note-block note-block_blue">
        <p class="block-content__phar">{LANG:MD:PAGE_ENTRIES_SAMPLES_SIDEBAR_BLOCK_ABOUT_DESCRIPTION}</p>
      </div>
    </div>
  </article>
</aside>