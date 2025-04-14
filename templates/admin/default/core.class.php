<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace templates\admin\default {
  use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\Entry as Entry;
  use \core\PHPLibrary\Entries as Entries;
  use \core\PHPLibrary\Entries\Database as EntriesDatabase;
  use \core\PHPLibrary\User as User;
  use \core\PHPLibrary\Users as Users;
  use \core\PHPLibrary\UserGroup as UserGroup;
  use \core\PHPLibrary\Client\Session as ClientSession;
  use \DOMDocument as DOMDocument;

  #[\AllowDynamicProperties]
  final class Core implements \core\PHPLibrary\Template\InterfaceCore {
    private \core\PHPLibrary\Template $template;
    public string $assembled = '';
    public DOMDocument|null $source = null;
    public array $navigation_sections_array = [
      'index' => [
        'name' => 'index',
        'iconName' => 'index',
        'link' => '/',
        'permanent' => true,
        'role' => ''
      ],
      'entries' => [
        'name' => 'entries',
        'iconName' => 'entries',
        'link' => '/entries',
        'permanent' => false,
        'role' => ''
      ],
      'static_pages' => [
        'name' => 'pages',
        'iconName' => 'pages',
        'link' => '/pages',
        'permanent' => false,
        'role' => ''
      ],
      'media' => [
        'name' => 'media',
        'iconName' => 'media',
        'link' => '/media',
        'permanent' => false,
        'role' => ''
      ],
      'users' => [
        'name' => 'users',
        'iconName' => 'users',
        'link' => '/users',
        'permanent' => false,
        'role' => ''
      ],
      'feeds' => [
        'name' => 'feeds',
        'iconName' => 'feeds',
        'link' => '/feeds',
        'permanent' => false,
        'role' => ''
      ],
      'modules' => [
        'name' => 'modules',
        'iconName' => 'modules',
        'link' => '/modules',
        'permanent' => false,
        'role' => ''
      ],
      'templates' => [
        'name' => 'templates',
        'iconName' => 'templates',
        'link' => '/templates',
        'permanent' => false,
        'role' => ''
      ],
      'analytics' => [
        'name' => 'analytics',
        'iconName' => 'analytics',
        'link' => '/analytics',
        'permanent' => false,
        'role' => ''
      ],
      'settings' => [
        'name' => 'settings_cms',
        'iconName' => 'settings',
        'link' => '/settings',
        'permanent' => true,
        'role' => ''
      ],
      'about' => [
        'name' => 'about_cms',
        'iconName' => 'about',
        'link' => '/about',
        'permanent' => true,
        'role' => ''
      ],
      'exit' => [
        'name' => 'exit_cms',
        'iconName' => 'exit',
        'link' => '/',
        'permanent' => true,
        'role' => 'mainNavigationExit'
      ]
    ];
    
    /**
     * __construct
     *
     * @param  mixed $template
     * @return void
     */
    public function __construct(\core\PHPLibrary\Template $template) {
      $this->template = $template;
    }

    /**
     * Получить абсолютный путь SVG-файла иконки раздела
     * 
     * @param string $navigation_item_name
     * @return string
     */
    private function get_main_navigation_icon_path(string $navigation_item_name) : string {
      $template_path = $this->template->get_path();
      return sprintf('%s/images/icons/mainNavigation/%s.svg', $template_path, $navigation_item_name);
    }

    /**
     * Инициализация главной навигации
     * 
     * @return void
     */
    public function init_main_navigation() : void {
      if (!is_null($this->source)) {
        $element_system_ap_main_navigation = $this->source->getElementById('SYSTEM_AP_MAIN_NAVIGATION');
        if (!is_null($element_system_ap_main_navigation)) {
          $list_element = $element_system_ap_main_navigation->ownerDocument->createElement('ul');
          $list_element->setAttribute('class', 'navigation__list list list-reset');

          if (count($this->navigation_sections_array) > 0) {
            foreach ($this->navigation_sections_array as $navigation_section_index => $navigation_section_data) {
              $navigation_section_name = $navigation_section_data['name'];
              $navigation_section_link = $navigation_section_data['link'];
              $navigation_section_icon_name = $navigation_section_data['iconName'];
              $navigation_section_permanent_status = $navigation_section_data['permanent'];
              $navigation_section_role = $navigation_section_data['role'];
              
              $section_allowed = false;

              if (!$navigation_section_permanent_status) {
                $method_section_checker_name = sprintf('get_section_%s_status', $navigation_section_index);
                
                if (method_exists($this->template->system_core->configurator, $method_section_checker_name)) {
                  if ($this->template->system_core->configurator->{$method_section_checker_name}(true)) {
                    $section_allowed = true;
                  }
                } else {
                  $section_allowed = true;
                }
              } else {
                $section_allowed = true;
              }

              if ($section_allowed) {
                $item_title = sprintf('{LANG:MAIN_NAVIGATION_%s_LABEL}', strtoupper($navigation_section_name));
                $item_title = TemplateCollector::assembly_locale($item_title, $this->template->system_core->locale);
                
                $item_element = $element_system_ap_main_navigation->ownerDocument->createElement('li');
                $link_element = $element_system_ap_main_navigation->ownerDocument->createElement('a');
                $label_element = $element_system_ap_main_navigation->ownerDocument->createElement('div', $item_title);
                
                $item_element->setAttribute('class', sprintf('list__item item item_%s', $navigation_section_name));

                if ($navigation_section_role != '') {
                  $item_element->setAttribute('role', $navigation_section_role); 
                }

                $link_element->setAttribute('class', 'item__link link');
                $link_element->setAttribute('href', sprintf('/admin%s', $navigation_section_link));
                $link_element->setAttribute('title', $item_title);
                $label_element->setAttribute('class', 'item__label label');
                
                $svg_element = new DOMDocument();
                $svg_element->load($this->get_main_navigation_icon_path($navigation_section_icon_name));
                $svg_imported_element = $this->source->importNode($svg_element->documentElement, true);
                $svg_imported_element->setAttribute('class', 'item__icon icon');

                $link_element->appendChild($svg_imported_element);
                $link_element->appendChild($label_element);
                $item_element->appendChild($link_element);
                $list_element->appendChild($item_element);
              }
            }

            $element_system_ap_main_navigation->appendChild($list_element);
          }
        }
      }
    }
    
    /**
     * Сборка шапки сайта
     *
     * @param  mixed $template_replaces Массив тегами шаблона и их значениями
     * @return string
     */
    public function assembly_header(array $template_replaces = []) : string {
      /** @var User Объект авторизованного пользователя */
      $client_user = $this->template->system_core->client->get_user(2);
      $client_user->init_data(['login', 'metadata']);

      /** @var UserGroup Объект группы пользователя */
      $client_user_group = $client_user->get_group();
      $client_user_group->init_data(['texts']);

      /** @var string Техническое имя локализации шаблона */
      $template_locale_name = $this->template->locale->get_name();

      /** @var string Логин пользователя */
      $template_replaces['CLIENT_USER_LOGIN'] = $client_user->get_login();
      /** @var string Логин пользователя */
      $template_replaces['CLIENT_USER_GROUP_TITLE'] = $client_user_group->get_title($template_locale_name);

      return TemplateCollector::assembly_file_content($this->template, 'templates/header.tpl', $template_replaces);
    }
    
    /**
     * Сборка главной секции сайта
     *
     * @param  mixed $template_replaces Массив тегами шаблона и их значениями
     * @return string
     */
    public function assembly_main(array $template_replaces = []) : string {
      $this->template->system_core->init_page(ltrim($_SERVER['REQUEST_URI'], '/'));
      $site_page = $this->template->system_core->get_inited_page();
      $site_page->assembly();
      
      $template_replaces['ADMIN_PANEL_PAGE_WRAPPER'] = TemplateCollector::assembly_file_content($this->template, 'templates/page.tpl', [
        'ADMIN_PANEL_PAGE' => $site_page->assembled,
      ]);

      return TemplateCollector::assembly_file_content($this->template, 'templates/main.tpl', $template_replaces);
    }
    
    /**
     * Сборка подвала сайта
     *
     * @param  mixed $template_replaces Массив тегами шаблона и их значениями
     * @return string
     */
    public function assembly_footer(array $template_replaces = []) : string {
      return TemplateCollector::assembly_file_content($this->template, 'templates/footer.tpl', $template_replaces);
    }
    
    /**
     * Сборка основной части документа
     *
     * @param  mixed $template_replaces Массив тегами шаблона и их значениями
     * @return string
     */
    public function assembly_document(array $template_replaces = []) : string {
      /** @var string $assembled Содержимое шаблона */
      $assembled;

      if ($this->template->system_core->client->is_logged(2)) {
        $template_content = TemplateCollector::assembly_file_content($this->template, 'templates/documentBase.tpl', $template_replaces);
      } else {
        $template_content = TemplateCollector::assembly_file_content($this->template, 'templates/documentAuth.tpl', $template_replaces);
      }

      return $template_content;
    }

    public function assembly_auth_admin_page(array $template_replaces = []) : string {
      return TemplateCollector::assembly_file_content($this->template, 'templates/page/auth.tpl', $template_replaces);
    }
    
    /**
     * Итоговая сборка шаблона
     *
     * @return void
     */
    public function assembly() : void {
      $this->template->add_style(['href' => 'styles/normalize.css', 'rel' => 'stylesheet']);
      $this->template->add_style(['href' => 'styles/fonts.css', 'rel' => 'stylesheet']);
      $this->template->add_style(['href' => 'styles/colors.css', 'rel' => 'stylesheet']);
      $this->template->add_style(['href' => 'styles/common.css', 'rel' => 'stylesheet']);
      $this->template->add_style(['href' => 'styles/table.css', 'rel' => 'stylesheet']);
      $this->template->add_style(['href' => 'styles/form.css', 'rel' => 'stylesheet']);
      $this->template->add_style(['href' => 'styles/modal.css', 'rel' => 'stylesheet']);
      $this->template->add_style(['href' => 'styles/interactive.css', 'rel' => 'stylesheet']);
      $this->template->add_style(['href' => 'styles/notification.css', 'rel' => 'stylesheet']);
      
      $this->template->add_script(['src' => 'interactive.class.js', 'type' => 'module'], true);
      $this->template->add_script(['src' => 'common.js'], true);
      $this->template->add_script(['src' => 'core.class.js', 'type' => 'module'], true);
      $this->template->add_script(['src' => 'core.class.js', 'type' => 'module']);


      /** @var string $user_ip IP-адрес пользователя */
      $user_ip = $_SERVER['REMOTE_ADDR'];

      if ($this->template->system_core->client->is_logged(2)) {
        $this->template->add_style(['href' => 'styles/header.css', 'rel' => 'stylesheet']);
        $this->template->add_style(['href' => 'styles/main.css', 'rel' => 'stylesheet']);
        $this->template->add_style(['href' => 'styles/footer.css', 'rel' => 'stylesheet']);
        $this->template->add_style(['href' => 'styles/page.css', 'rel' => 'stylesheet']);

        /** @var string $this->assembled Итоговый шаблон в виде строки */
        $this->assembled = TemplateCollector::assembly($this->assembly_document(), [
          'ADMIN_PANEL_HEADER' => $this->assembly_header(),
          'ADMIN_PANEL_MAIN' => $this->assembly_main(),
          'ADMIN_PANEL_FOOTER' => $this->assembly_footer()
        ]);
      } else {
        $this->template->add_style(['href' => 'styles/page/auth.css', 'rel' => 'stylesheet']);

        $this->assembled = TemplateCollector::assembly($this->assembly_document(), [
          'ADMIN_PANEL_HEADER' => '',
          'ADMIN_PANEL_MAIN' => $this->assembly_auth_admin_page(),
          'ADMIN_PANEL_FOOTER' => ''
        ]);
      }
    }

  }

}

?>