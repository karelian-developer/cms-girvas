<article class="main__page page page_{ADMIN_PANEL_PAGE_NAME}">
  <nav id="SYSTEM_AP_SUBNAVIGATION" class="page__navigation navigation"></nav>
  <div class="page__title-container">
    <h1 class="page__title">{LANG:PAGE_ANALYTICS_TITLE}</h1>
    <div id="E8548530785" class="page__interactive-container"></div>
  </div>
  <div class="page__content">
    <div id="analytic-app" class="analytic">
      <div class="block analytic__block analytic__block_attendance-schedule">
        <h2 class="block__title">{LANG:PAGE_ANALYTICS_BLOCK_SITE_VISITS_TITLE}</h2>
        <div class="block__content" role="attendance-schedule"></div>
      </div>
      <div class="block analytic__block analytic__block_entries-popular">
        <h2 class="block__title">{LANG:PAGE_ANALYTICS_BLOCK_ENTRIES_VIEWS_TITLE}</h2>
        <div class="block__content">
          {ENTRIES_LIST_ITEMS}
        </div>
      </div>
      <div class="block analytic__block analytic__block_pages-popular">
        <h2 class="block__title">{LANG:PAGE_ANALYTICS_BLOCK_PAGES_VIEWS_TITLE}</h2>
        <div class="block__content">
          {PAGES_LIST_ITEMS}
        </div>
      </div>
    </div>
  </div>
</article>
<aside class="main__page-aside page-aside">
  <div class="page-aside__block">
    <h2 class="page-aside__block-title">{LANG:PAGE_ANALYTICS_SIDEBAR_BLOCK_ABOUT_TITLE}</h2>
    <div class="page-aside__block-content block-content">
      <div class="note-block note-block_blue">
        <p class="block-content__phar">{LANG:PAGE_ANALYTICS_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_1}</p>
        <p class="block-content__phar">{LANG:PAGE_ANALYTICS_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_2}</p>
        <p class="block-content__phar">{LANG:PAGE_ANALYTICS_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_3}</p>
      </div>
    </div>
  </div>
</aside>