<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary {
  use \core\PHPLibrary\Template\Collector as TemplateCollector;
  use \core\PHPLibrary\SystemCore as SystemCore;
  use \DOMDocument as DOMDocument;

  trait TraitPage {
    /**
     * Получить абсолютный путь SVG-файла иконки подраздела
     * 
     * @param SystemCore $system_core
     * @param string $subnavigation_item_name
     * @return string
     */
    private function get_subnavigation_item_icon_path(SystemCore $system_core, string $subnavigation_item_name) : string {
      $template_path = $this->system_core->template->get_path();
      return sprintf('%s/images/icons/subNavigation/%s.svg', $template_path, $subnavigation_item_name);
    }

    public function init_admin_panel_subnavigation(SystemCore $system_core, DOMDocument|null &$source) : void {
      $template_source =& $source;

      if (!is_null($template_source)) {
        $element_system_ap_subnavigation = $template_source->getElementById('SYSTEM_AP_SUBNAVIGATION');
        if (!is_null($element_system_ap_subnavigation)) {
          $list_element = $element_system_ap_subnavigation->ownerDocument->createElement('ul');
          $list_element->setAttribute('class', 'navigation__list list list-reset');

          if (count($this->navigation_subsections_array) > 0) {
            foreach ($this->navigation_subsections_array as $navigation_subsection_index => $navigation_subsection_data) {
              $navigation_subsection_name = $navigation_subsection_data['name'];
              $navigation_subsection_link = $navigation_subsection_data['link'];
              $navigation_subsection_icon_name = $navigation_subsection_data['iconName'];
              $navigation_subsection_permanent_status = $navigation_subsection_data['permanent'];
              $navigation_subsection_is_active_status = $navigation_subsection_data['isActive'];

              // :D
              $section_allowed = true;

              if ($section_allowed) {
                $item_title = sprintf('{LANG:%s}', self::LANG_PAGE_NAVIGATION_LABLE_TEMPLATE);
                $item_title = sprintf($item_title, strtoupper($navigation_subsection_name));
                $item_title = TemplateCollector::assembly_locale($item_title, $system_core->locale);

                $item_element = $element_system_ap_subnavigation->ownerDocument->createElement('li');
                $link_element = $element_system_ap_subnavigation->ownerDocument->createElement('a');
                $label_element = $element_system_ap_subnavigation->ownerDocument->createElement('div', $item_title);

                $icon_path = $this->get_subnavigation_item_icon_path($system_core, $navigation_subsection_icon_name);
                if (file_exists($icon_path)) {
                  $icon_container_element = $element_system_ap_subnavigation->ownerDocument->createElement('div');
                  $icon_container_element->setAttribute('class', sprintf('item__icon-container icon-container', $navigation_subsection_icon_name));

                  $svg_element = new DOMDocument();
                  $svg_element->load($icon_path);
                  $svg_imported_element = $template_source->importNode($svg_element->documentElement, true);
                  $svg_imported_element->setAttribute('class', 'item__icon icon');

                  $icon_container_element->appendChild($svg_imported_element);
                  $link_element->appendChild($icon_container_element);
                }

                if ($navigation_subsection_is_active_status) {
                  $item_element->setAttribute('class', sprintf('list__item item item_%s item_is-active', $navigation_subsection_name));
                } else {
                  $item_element->setAttribute('class', sprintf('list__item item item_%s', $navigation_subsection_name));
                }

                $link_element->setAttribute('href', sprintf('/admin%s', $navigation_subsection_link));
                $link_element->setAttribute('class', 'item__link link');
                $link_element->setAttribute('title', $item_title);
                $label_element->setAttribute('class', 'item__label label');

                $link_element->appendChild($label_element);
                $item_element->appendChild($link_element);
                $list_element->appendChild($item_element);
              }
            }

            $element_system_ap_subnavigation->appendChild($list_element);
          }
        }
      }
    }
  }
}
?>