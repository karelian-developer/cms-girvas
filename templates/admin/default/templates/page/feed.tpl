<article class="main__page page page_{ADMIN_PANEL_PAGE_NAME}">
  <nav id="SYSTEM_AP_SUBNAVIGATION" class="page__navigation navigation"></nav>
  <div class="page__title-container">
    <h1 class="page__title">{LANG:PAGE_WEB_CHANNEL_TITLE}</h1>
    <div id="E8548530785" class="page__interactive-container"></div>
  </div>
  <div class="page__content">
    <form class="form form_webchannel page__web-channel-editor-form web-channel-editor-form" action="/handler/webChannel">
      <input name="web_channel_id" type="hidden" value="{WEB_CHANNEL_ID}">
      <table class="table table_feed">
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_WEB_CHANNEL_TECHNICAL_NAME_TITLE}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_WEB_CHANNEL_TECHNICAL_NAME_DESCRIPTION}</div>
            </div>
          </td>
          <td class="table__cell cell">
            <div class="page__phar-block"><input name="web_channel_name" typde="text" class="form__input web-channel-editor-form__input" value="{WEB_CHANNEL_NAME}" placeholder="my-channel" role="feedURL" required></div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_WEB_CHANNEL_TITLE_TITLE}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_WEB_CHANNEL_TITLE_DESCRIPTION}</div>
            </div>
          </td>
          <td class="table__cell cell">
            <div class="page__phar-block"><input name="web_channel_title_rus" role="feedTitle" type="text" class="form__input form__input_text" placeholder="Мой канал" value="{WEB_CHANNEL_TITLE}" required></div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_WEB_CHANNEL_DESCRIPTION_TITLE}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_WEB_CHANNEL_DESCRIPTION_DESCRIPTION}</div>
            </div>
          </td>
          <td class="table__cell cell">
            <div class="page__phar-block">
              <textarea class="form__textarea" name="web_channel_description_rus" role="feedDescription" cols="30" rows="10" placeholder="Канал последних новостей" required>{WEB_CHANNEL_DESCRIPTION}</textarea>
            </div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_WEB_CHANNEL_SPECIFICATION_TITLE}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_WEB_CHANNEL_SPECIFICATION_DESCRIPTION}</div>
            </div>
          </td>
          <td id="TC6474387201" class="table__cell cell"></td>
        </tr>
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_WEB_CHANNEL_ENTRIES_CATEGORY_TITLE}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_WEB_CHANNEL_ENTRIES_CATEGORY_DESCRIPTION}</div>
            </div>
          </td>
          <td id="TC6474387200" class="table__cell cell"></td>
        </tr>
      </table>
      <div class="form__bottom-panel" id="SYSTEM_E3724126170"></div>
    </form>
  </div>
</article>
<aside class="main__page-aside page-aside">
  <article class="page-aside__block">
    <h2 class="page-aside__block-title">{LANG:PAGE_FEED_SIDEBAR_BLOCK_ABOUT_TITLE}</h2>
    <div class="page-aside__block-content block-content">
      <div class="note-block note-block_blue">
        <p class="block-content__phar">{LANG:PAGE_FEED_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_1}</p>
        <p class="block-content__phar">{LANG:PAGE_FEED_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_2}</p>
        <p class="block-content__phar">{LANG:PAGE_FEED_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_3}</p>
      </div>
    </div>
  </article>
</aside>