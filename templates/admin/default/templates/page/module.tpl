<article class="main__page page page_{ADMIN_PANEL_PAGE_NAME}">
  <nav id="SYSTEM_AP_SUBNAVIGATION" class="page__navigation navigation"></nav>
  <div class="page__title-container">
    <h1 class="page__title">{LANG:PAGE_MODULE_TITLE}: {MODULE_TITLE}</h1>
    <div id="E8548530785" class="page__interactive-container"></div>
  </div>
  <div class="page__content">
    <div class="module" data-name="{MODULE_NAME}" data-enabled-status="{MODULE_ENABLED_STATUS}" data-installed-status="{MODULE_INSTALLED_STATUS}">
      <div class="module__left-block">
        <div class="module__gallery-container">
          {MODULE_GALLERY_LIST}
        </div>
        <div class="module__metadata-container">
          <h2 class="module__metadata-label">{LANG:PAGE_MODULE_METADATA_BLOCK_TITLE}</h2>
          {MODULE_METADATA_LIST}
        </div>
      </div>
      <div class="module__right-block">
        <div class="module__description-container">
          <h2 class="module__description-label">{LANG:PAGE_MODULE_DESCRIPTION_BLOCK_TITLE}</h2>
          <div class="module__description">{MODULE_DESCRIPTION}</div>
        </div>
      </div>
    </div>
  </div>
</article>
<aside class="main__page-aside page-aside">
  <div class="page-aside__block">
    <h2 class="page-aside__block-title">{LANG:PAGE_MODULE_SIDEBAR_BLOCK_ABOUT_TITLE}</h2>
    <div class="page-aside__block-content block-content">
      <div class="note-block note-block_blue">
        <p class="block-content__phar">{LANG:PAGE_MODULE_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_1}</p>
        <p class="block-content__phar">{LANG:PAGE_MODULE_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_2}</p>
      </div>
    </div>
  </div>
</aside>