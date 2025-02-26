<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{SITE_TITLE} (Admin)</title>
  {SITE_STYLES}
  {SITE_SCRIPTS}
</head>
<body class="body body_base admin-panel">
  <div class="admin-panel__wrapper wrapper">
    <!-- ========================================= -->
    <!-- Главная вертикальная навигационная панель -->
    <!-- ========================================= -->
    <nav id="SYSTEM_AP_MAIN_NAVIGATION" class="admin-panel__navigation navigation">
      <div class="navigation__burger burger" role="mainNavigationBurger">
        <svg class="burger__icon icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 51.49" style="enable-background:new 0 0 64 64;">
          <rect width="64" height="11.94"/>
          <rect y="19.78" width="64" height="11.94"/>
          <rect y="39.56" width="64" height="11.94"/>
        </svg>
      </div>
    </nav>
    <div class="admin-panel__basis">
      {ADMIN_PANEL_HEADER}
      {ADMIN_PANEL_MAIN}
      {ADMIN_PANEL_FOOTER}
    </div>
  </div>
</body>
</html>