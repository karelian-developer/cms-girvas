<form class="form page__form" data-element="main-form">
  <div class="grid-table page__grid-table">
    <!-- Поле: Максимальный вес (в КБ) загружаемого файла -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_FILES_UPLOAD_FILE_WEIGHT_MAX_TITLE}
      </div>
      <div class="cell__description">
        {LANG:PAGE_SETTINGS_SETTING_FILES_UPLOAD_FILE_WEIGHT_MAX_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <input name="setting_{SETTINGS_NAME}_upload_file_weight_max" type="number" class="input form__input form__input_number" value="{SETTING_UPLOAD_FILE_WEIGHT_MAX_VALUE}" placeholder="1024" min="0" data-element="input-image-weight-max">
    </div>
    <!-- Поле: Максимальная ширина (в PX) загружаемого изображения -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_FILES_UPLOAD_FILE_IMAGE_WIDTH_MAX_TITLE}
      </div>
      <div class="cell__description">
        {LANG:PAGE_SETTINGS_SETTING_FILES_UPLOAD_FILE_IMAGE_WIDTH_MAX_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <input name="setting_{SETTINGS_NAME}_upload_file_image_width_max" type="number" class="input form__input form__input_number" value="{SETTING_UPLOAD_FILE_IMAGE_WIDTH_MAX_VALUE}" placeholder="128" min="0" data-element="input-image-width-max">
    </div>
    <!-- Поле: Максимальная высота (в PX) загружаемого изображения -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_FILES_UPLOAD_FILE_IMAGE_HEIGHT_MAX_TITLE}
      </div>
      <div class="cell__description">
        {LANG:PAGE_SETTINGS_SETTING_FILES_UPLOAD_FILE_IMAGE_HEIGHT_MAX_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <input name="setting_{SETTINGS_NAME}_upload_file_image_height_max" type="number" class="input form__input form__input_number" value="{SETTING_UPLOAD_FILE_IMAGE_HEIGHT_MAX_VALUE}" placeholder="128" min="0" data-element="input-image-height-max">
    </div>
    <!-- Поле: Максимальный вес (в КБ) загружаемого аватара -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_FILES_UPLOAD_FILE_IMAGE_AVATAR_WEIGHT_MAX_TITLE}
      </div>
      <div class="cell__description">
        {LANG:PAGE_SETTINGS_SETTING_FILES_UPLOAD_FILE_IMAGE_AVATAR_WEIGHT_MAX_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <input name="setting_{SETTINGS_NAME}_upload_file_image_avatar_weight_max" type="number" class="input form__input form__input_number" value="{SETTING_UPLOAD_FILE_IMAGE_AVATAR_WEIGHT_MAX_VALUE}" placeholder="1024" min="0" data-element="input-avatar-weight-max">
    </div>
    <!-- Поле: Максимальная ширина (в PX) загружаемого аватара -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_FILES_UPLOAD_FILE_IMAGE_AVATAR_WIDTH_MAX_TITLE}
      </div>
      <div class="cell__description">
        {LANG:PAGE_SETTINGS_SETTING_FILES_UPLOAD_FILE_IMAGE_AVATAR_WIDTH_MAX_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <input name="setting_{SETTINGS_NAME}_upload_file_image_avatar_width_max" type="number" class="input form__input form__input_number" value="{SETTING_UPLOAD_FILE_IMAGE_AVATAR_WIDTH_MAX_VALUE}" placeholder="128" min="0" data-element="input-avatar-width-max">
    </div>
    <!-- Поле: Максимальная высота (в PX) загружаемого аватара -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_FILES_UPLOAD_FILE_IMAGE_AVATAR_HEIGHT_MAX_TITLE}
      </div>
      <div class="cell__description">
        {LANG:PAGE_SETTINGS_SETTING_FILES_UPLOAD_FILE_IMAGE_AVATAR_HEIGHT_MAX_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <input name="setting_{SETTINGS_NAME}_upload_file_image_avatar_height_max" type="number" class="input form__input form__input_number" value="{SETTING_UPLOAD_FILE_IMAGE_AVATAR_HEIGHT_MAX_VALUE}" placeholder="128" min="0" data-element="input-avatar-height-max">
    </div>
    <!-- Поле: Сжатие загружаемого изображения -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_FILES_UPLOAD_IMAGE_COMPRESSION_TITLE}
      </div>
      <div class="cell__description">
        {LANG:PAGE_SETTINGS_SETTING_FILES_UPLOAD_IMAGE_COMPRESSION_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <input name="setting_{SETTINGS_NAME}_upload_image_compression" type="number" class="input form__input form__input_number" value="{SETTING_UPLOAD_IMAGE_COMPRESSION_VALUE}" placeholder="40" min="0" max="100" data-element="input-image-compression">
    </div>
    <!-- Поле: Автоматическая конвертация загружаемого изображения -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_FILES_AUTO_CONVERT_FILE_IMAGE_TITLE}
      </div>
      <div class="cell__description">
        {LANG:PAGE_SETTINGS_SETTING_FILES_AUTO_CONVERT_FILE_IMAGE_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <div class="form__checkbox-container checkbox-container">
        <input type="hidden" name="setting_{SETTINGS_NAME}_auto_convert_file_image_status" id="I1474308112" value="{SETTING_AUTO_CONVERT_FILE_IMAGE_STATUS_VALUE}">
        <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308802" type="checkbox" {SETTING_AUTO_CONVERT_FILE_IMAGE_CHECKED_VALUE} data-status-block="I1474308112">
        <label class="checkbox-container__label form__label" for="I1474308802"></label>
      </div>
    </div>
    <!-- Поле: Расширение изображения при автоматической конвертации -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_FILES_AUTO_CONVERT_FILE_IMAGE_EXTENSION_TITLE}
      </div>
      <div class="cell__description">
        {LANG:PAGE_SETTINGS_SETTING_FILES_AUTO_CONVERT_FILE_IMAGE_EXTENSION_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <div data-element="choice" data-choice="convert-extension"></div>
    </div>
    <!-- Панель формы -->
    <div class="cell grid-table__cell grid-table__cell_panel" data-element="panel"></div>
  </div>
</form>