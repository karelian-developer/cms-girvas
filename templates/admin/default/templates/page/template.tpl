<article class="main__page page page_{ADMIN_PANEL_PAGE_NAME}">
  <nav id="SYSTEM_AP_SUBNAVIGATION" class="page__navigation navigation"></nav>
  <div class="page__title-container">
    <h1 class="page__title">{LANG:PAGE_TEMPLATE_TITLE}: {TEMPLATE_TITLE}</h1>
    <div id="E8548530785" class="page__interactive-container"></div>
  </div>
  <div class="page__content">
    <div class="template" data-template-name="{TEMPLATE_NAME}" data-template-dowloaded-status="{TEMPLATE_DOWNLOADED_STATUS}" data-template-installed-status="{TEMPLATE_INSTALLED_STATUS}">
      <div class="template__left-block">
        <div class="template__gallary-container" role="gallery">
          <div class="gallery__controllers-list" role="controller">
            <button class="gallery__controller gallery__controller_left" role="controller-left">
              <svg class="gallery__controller-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve">
                <polygon points="35,0 16,32 35,64 48,64 29.3,32 48,0 "/>
              </svg>
            </button>
            <button class="gallery__controller gallery__controller_right" role="controller-right">
              <svg class="gallery__controller-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve">
                <polygon points="26,0 45,32 26,64 13,64 31.7,32 13,0 "/>
              </svg>
            </button>
          </div>
          {TEMPLATE_GALLARY_LIST}
        </div>
        <div class="template__metadata-container">
          <h2 class="template__metadata-label">{LANG:PAGE_TEMPLATE_METADATA_TITLE}</h2>
          {TEMPLATE_METADATA_LIST}
        </div>
      </div>
      <div class="template__right-block">
        <div class="template__description-container description-container">
          <h2 class="template__description-label description-label">{LANG:PAGE_TEMPLATE_DESCRIPTION_TITLE}</h2>
          <div class="template__description description">{TEMPLATE_DESCRIPTION}</div>
        </div>
        <div class="template__properties-containe properties-container" data-role="theme-properties-editor">
          <h2 class="template__properties-label properties-label">{LANG:PAGE_TEMPLATE_PROPERTIES_TITLE}</h2>
          <div id="THEME_PROPERTIES" class="template__properties properties">{TEMPLATE_PROPERTIES}</div>
        </div>
      </div>
    </div>
  </div>
</article>
<aside class="main__page-aside page-aside">
  <article class="page-aside__block">
    <h2 class="page-aside__block-title">{LANG:PAGE_TEMPLATE_SIDEBAR_BLOCK_ABOUT_TITLE}</h2>
    <div class="page-aside__block-content block-content">
      <div class="note-block note-block_blue">
        <p class="block-content__phar">{LANG:PAGE_TEMPLATE_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_1}</p>
        <p class="block-content__phar">{LANG:PAGE_TEMPLATE_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_2}</p>
      </div>
    </div>
  </article>
</aside>