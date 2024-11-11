<main class="main">
  <section class="main__section section">
    <div class="main__container container">
      <div class="main__title-container title-container">
        <div class="title-container__step-container">
          <span class="title-container__step-label">Шаг</span>
          <span class="title-container__step-number">1</span>
        </div>
        <h2 class="main__title">Знакомство</h2>
      </div>
      <div class="main__page-container">
        <div class="main__page page" data-page-index="0">
          <p class="page__phar"><b>Спасибо за то, что выбрали CMS &laquo;GIRVAS&raquo;!</b></p>
          <p class="page__phar"><b>CMS&nbsp;&laquo;GIRVAS&raquo;</b>&nbsp;&dash;&nbsp;многопрофильная бесплатная система управления содержимым с открытым исходным кодом от карельских разработчиков &laquo;Garbalo&raquo;. Направление системы управления содержимым не является конкретным, поскольку при помощи данной CMS возможно практически все!</p>
          <p class="page__phar">Мастер-установщик поможет Вам правильно настроить и установить систему управления содержимым. Только хотим Вас предупредить о том, что CMS&nbsp;&laquo;GIRVAS&raquo; находится на стадии разразработки, поэтому она <b>может быть нестабильна</b>.</p>
        </div>
        <div class="main__page page" data-page-index="1">
          <p class="page__phar">Мастер-установщик проанализировал Вашу систему и вывел результат в таблице. В случае, если обнаружены ошибки, то рекомендуем их исправить для стабильной работы CMS&nbsp;&laquo;GIRVAS&raquo;.</p>
        </div>
        <div class="main__page page" data-page-index="2">
          <p class="page__phar">Мастер-установщик проанализировал корневую директорию, куда помещены файлы CMS и вывел результат в таблице. В случае, если обнаружены ошибки, то рекомендуем их исправить для стабильной работы системы управления содержимым.</p>
        </div>
        <div class="main__page page" data-page-index="3">
          <p class="page__phar">Мастер-установщик проанализировал права на некоторые директории, куда помещены файлы CMS и вывел результат в таблице. В случае, если обнаружены ошибки, то рекомендуем их исправить для стабильной работы системы управления содержимым.</p>
          <p class="page__phar"><b>Общие рекомендации по правам:</b><br>Для директорий (включая вложенных) <b>"./backups"</b>, <b>"./templates"</b>, <b>"./uploads"</b>, <b>"./modules"</b> необходимо установить права 0755, а для файлов, вложенных в директориях <b>"./modules"</b> и <b>"./templates"</b> 0644.</p>
        </div>
        <div class="main__page page" data-page-index="4">
          <p class="page__phar">Мастер-установщик проанализировал установленные СУБД и вывел результат в таблице. Если в таблице указана отрицательная оценка (помечена красным цветом) по отношению к предпочитаемой, то данная СУБД не была идентифицирована на Вашем сервере. Установите необходимую СУБД или выберите в следующем шаге доступную.</p>
        </div>
        <div class="main__page page" data-page-index="5">
          <p class="page__phar">Данная страница позволит правильно настроить работу базой данных и доменом в целом. Заполните все необходимые данные в форме ниже, после чего нажмите на кнопку "Создать конфигурации".</p>
          <p class="page__phar">
            <form class="form" role="form-database">
              <table class="table">
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">Доменное имя</div>
                    <div class="cell__description">
                      <div class="page__phar-block">Укажите доменное имя, с которым будет работать Ваш веб-сайт. Это необходимо для правильной переадресации.</div>
                    </div>
                  </td>
                  <td class="table__cell cell" >
                    <input name="domain" type="text" class="form__input" value="{CONFIGURATION_DATABASE_DOMAIN}" placeholder="example.ru">
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">Доменное имя для Cookie</div>
                    <div class="cell__description">
                      <div class="page__phar-block">Укажите специфическое доменное имя для генерации Cookie. Если доменное имя Вашего веб-сайта совпадает с доменным именем для которого необходимо генерировать Cookie, то укажите здесь доменное имя Вашего веб-сайта.</div>
                    </div>
                  </td>
                  <td class="table__cell cell">
                    <input name="domain_cookies" type="text" class="form__input" value="{CONFIGURATION_DATABASE_DOMAIN}" placeholder="example.ru">
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">Доменное имя для E-Mail</div>
                    <div class="cell__description">
                      <div class="page__phar-block">Укажите доменное имя, с которого будут отправляться системные электронные письма.</div>
                    </div>
                  </td>
                  <td class="table__cell cell">
                    <input name="domain_email" type="text" class="form__input" value="{CONFIGURATION_DATABASE_DOMAIN}" placeholder="example.ru">
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">Использование SSL</div>
                    <div class="cell__description">
                      <div class="page__phar-block">Включите этот параметр, если Ваш домен работает с SSL-сертификатом.</div>
                    </div>
                  </td>
                  <td class="table__cell cell">
                    <div class="page__phar-block">
                      <div class="form__checkbox-container checkbox-container">
                        <input type="hidden" name="domain_ssl" id="I1474308110" value="off">
                        <input class="checkbox-container__input form__input form__input_checkbox" id="I1474301200" name="domain_ssl_status" type="checkbox" data-status-block="I1474308110">
                        <label class="checkbox-container__label form__label" for="I1474301200"></label>
                      </div>
                    </div>
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">Система управления базами данных</div>
                    <div class="cell__description">
                      <div class="page__phar-block">Выберите подходящую для Вас систему управления базами данных (СУБД), с которой будет работать CMS.</div>
                    </div>
                  </td>
                  <td class="table__cell cell">
                    <select name="database_dms">
                      <option value="DMS::PostgreSQL">PostgreSQL (рекомендуется)</option>
                    </select>
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">Префикс базы данных</div>
                    <div class="cell__description">
                      <div class="page__phar-block">Префикс базы данных позволит предотвратить конфликт наименований таблиц в том случае, если в Вашей базе данных уже имеются другие таблицы. Если префикс не нужен, то оставьте поле пустым.</div>
                    </div>
                  </td>
                  <td class="table__cell cell">
                    <input name="database_prefix" type="text" class="form__input" value="{CONFIGURATION_DATABASE_PREFIX}" placeholder="base">
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">Схема базы данных</div>
                    <div class="cell__description">
                      <div class="page__phar-block">Если Вы используете схемы, то укажите здесь ее наименование (обычно это "public"). Если не используете, то оставьте поле пустым.</div>
                    </div>
                  </td>
                  <td class="table__cell cell">
                    <input name="database_scheme" type="text" class="form__input" value="{CONFIGURATION_DATABASE_SCHEME}" placeholder="public">
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">Хост базы данных</div>
                  </td>
                  <td class="table__cell cell">
                    <input name="database_host" type="text" class="form__input" value="{CONFIGURATION_DATABASE_HOST}" placeholder="127.0.0.1">
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">Пользователь базы данных</div>
                  </td>
                  <td class="table__cell cell">
                    <input name="database_user" type="text" class="form__input" value="{CONFIGURATION_DATABASE_USER}" placeholder="db_user">
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">Пароль базы данных</div>
                  </td>
                  <td class="table__cell cell">
                    <input name="database_pass" type="password" class="form__input" value="{CONFIGURATION_DATABASE_PASSWORD}" placeholder="db_pass">
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">Наименование базы данных</div>
                  </td>
                  <td class="table__cell cell">
                    <input name="database_name" type="text" class="form__input" value="{CONFIGURATION_DATABASE_NAME}" placeholder="db_name">
                  </td>
                </tr>
              </table>
            </form>
          </p>
        </div>
        <div class="main__page page" data-page-index="6">
          <p class="page__phar">Выберите доступную СУБД, а мастер-установщик сгенерирует для Вас таблицы для подключенной базы данных. Это не займет много времени!</p>
          <p class="page__phar" role="cms-dms-selector"></p>
        </div>
        <div class="main__page page" data-page-index="7">
          <p class="page__phar">Осталось еще немного! Предлагаем Вам настроить язык и часовой пояс.</p>
          <p class="page__phar">
            <form class="form" role="form-locale">
              <table class="table">
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">Язык сайта</div>
                    <div class="cell__description">
                      <div class="page__phar-block">Выбранный Вами язык будет стандартным для пользователей. Он будет применяться до тех пор, пока пользователи самостоятельно не выберут другой.</div>
                    </div>
                  </td>
                  <td class="table__cell cell">
                    <div id="E85485302311" class="page__interactive-container"></div>
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">Язык административной панели</div>
                    <div class="cell__description">
                      <div class="page__phar-block">Выбранный Вами язык будет стандартным для пользователей административной панели. Он будет применяться до тех пор, пока пользователи самостоятельно не выберут другой.</div>
                    </div>
                  </td>
                  <td class="table__cell cell">
                    <div id="E85485302312" class="page__interactive-container"></div>
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">Часовой пояс</div>
                    <div class="cell__description">
                      <div class="page__phar-block">Выбранный Вами часовой пояс будет применен ко всем пользователям и гостям веб-сайта.</div>
                    </div>
                  </td>
                  <td class="table__cell cell">
                    <div id="E85485302313" class="page__interactive-container"></div>
                  </td>
                </tr>
              </table>
            </form>
          </p>
          <p class="page__phar" role="cms-locale-and-timezone"></p>
        </div>
        <div class="main__page page" data-page-index="8">
          <p class="page__phar">Вы уже почти на финишной прямой! Заполните краткую информацию о Вашем веб-сайте.</p>
          <p class="page__phar">
            <form class="form" role="form-metadata">
              <table class="table">
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">Наименование сайта</div>
                    <div class="cell__description">
                      <div class="page__phar-block">Напишите наименование сайта таким образом, чтобы это отражало суть его наполнения.</div>
                    </div>
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <input name="site_title" type="text" class="form__input" value="{SITE_TITLE_VALUE}" placeholder="Мой сайт">
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">Описание сайта</div>
                    <div class="cell__description">
                      <div class="page__phar-block">Описание сайта должно точно отражать суть его содержимого, а также включать в себя ключевые слова.</div>
                    </div>
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <textarea name="site_description" id="" cols="30" rows="10" class="form__textarea" placeholder="Это мой первый сайт">{SITE_DESCRIPTION_VALUE}</textarea>
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">Ключевые слова</div>
                    <div class="cell__description">
                      <div class="page__phar-block">Ключевые слова должны помогать находить сайт в поисковых системах. Для подбора ключевых слов рекомендуем использовать такие сервисы &laquo;<a href="https://wordstat.yandex.ru/" target="_blank">Yandex&nbsp;Wordstat</a>&raquo; и &laquo;<a href="https://trends.google.com/" target="_blank">Google&nbsp;Trends</a>&raquo;.</div>
                    </div>
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <textarea class="form__textarea" name="site_keywords" id="" cols="30" rows="10" placeholder="Ключевое слово 1, ключевое слово 2, ключевое слово 3">{SITE_KEYWORDS_VALUE}</textarea>
                  </td>
                </tr>
              </table>
            </form>
          </p>
          <p class="page__phar" role="cms-metadata"></p>
        </div>
        <div class="main__page page" data-page-index="9">
          <p class="page__phar">Это предпоследний этап установки CMS &laquo;GIRVAS&raquo;! Сейчас Вам необходимо создать аккаунт администратора - для этого заполните форму ниже и нажмите на кнопку "Создать аккаунт".</p>
          <p class="page__phar">
            <form class="form" role="form-admin-create">
              <table class="table">
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">Логин</div>
                    <div class="cell__description">
                      <div class="page__phar-block">Придумайте логин для Вашего администратора. Старайтесь избегать популярных логинов: admin, user и других.</div>
                    </div>
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <input name="admin_login" type="text" class="form__input" value="" placeholder="ILoveThisCMS_97">
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">E-Mail</div>
                    <div class="cell__description">
                      <div class="page__phar-block">Укажите существующий E-Mail администратора. Туда будут приходить важные уведомления от CMS, а также через него можно будет восстановить доступ к аккаунту.</div>
                    </div>
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <input name="admin_email" type="email" class="form__input" value="" placeholder="admin@domain.com">
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">Пароль</div>
                    <div class="cell__description">
                      <div class="page__phar-block">Придумайте сложный пароль. Хороший пароль должен ключать в себя минимум 8 символов, включать в себя латинские символы, цифры и специальные символы: !@#$%^&*(){}.</div>
                    </div>
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <input name="admin_password" type="password" class="form__input" value="" placeholder="">
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">Повторите пароль</div>
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <input name="admin_password_repeat" type="password" class="form__input" value="" placeholder="">
                  </td>
                </tr>
              </table>
            </form>
          </p>
          <p class="page__phar" role="cms-admin-create"></p>
        </div>
        <div class="main__page page" data-page-index="10">
          <p class="page__phar">Добро пожаловать на заключительный этап! Осталось сгенерировать секретный ключ - для этого нажмите на кнопку "Сгенерировать ключ".</p>
          <p class="page__phar"><b>Внимание!</b> Ключ, который будет отображен перед Вами, необходимо куда-нибудь записать, поскольку без него Вы не сможете авторизоваться в административной панели.</p>
          <p class="page__phar" role="cms-secret-key"></p>
        </div>
        <div class="main__page page" data-page-index="11">
          <p class="page__phar">Поздравляем с завершением установки CMS &laquo;GIRVAS&raquo;! Теперь Вы можете перейти на сам <a href="/" target="_blank">веб-сайт</a> или в <a href="/admin" target="_blank">администативную панель</a>. Для того, чтобы избежать повторной установки, мы сгенерировали в корне ситсемы пустой файл с именем "INSTALLED" - ни в коем случае его не удалайте, иначе станет возможна повторная установка CMS, что может привести к плохим последствиям.</p>
        </div>
        <div class="main__page-panel-container panel-container" role="installation-buttons-panel"></div>
      </div>
    </div>
  </section>
</main>