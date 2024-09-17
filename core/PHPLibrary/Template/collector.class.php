<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary\Template {
  use \core\PHPLibrary\SystemCore\Locale as SystemCoreLocale;
  use \core\PHPLibrary\Template as Template;
  use \core\PHPLibrary\Template\Locale as TemplateLocale;
  use \core\PHPLibrary\Module\Locale as ModuleLocale;

  final class Collector {
    private const TEMPLATE_TAG_PATTERN = '/\{([a-zA-Z0-9_]+)\}/';
    private const TEMPLATE_TAG_LANG_PATTERN = '/\{LANG\:([a-zA-Z0-9_]+)\}/';
    private const TEMPLATE_LOGIC_IF_PATTERN = '/\{\?IF\:([a-zA-Z0-9_]+)([=<>!]+)([a-zA-Z0-9_]+)\?\}(.*)\{\?ENDIF\?\}/is';
    private const TEMPLATE_LOGIC_IF_ELSE_PATTERN = '/\{\?IF\:([a-zA-Z0-9_]+)([=<>!]+)([a-zA-Z0-9_]+)\?\}(.*){\?ELSE\?\}(.*)\{\?ENDIF\?\}/is';
    private Template $template;
    
    /**
     * __construct
     *
     * @param  mixed $template
     * @return void
     */
    public function __construct(Template $template) {
      $this->template = $template;
    }

    public static function assembly_styles(Template $template, array $styles_array) : string {
      /** @var array $styles_assembled Массив стилей страницы, прошедших сборку */
      $styles_assembled = [];

      foreach ($styles_array as $style) {
        if (array_key_exists('href', $style) && array_key_exists('rel', $style)) {
          $style_is_core = false;
          if (array_key_exists('is_core', $style)) {
            if ($style['is_core'] == true) {
              $style_is_core = true;
              $style_href = sprintf('/core/CSSCore/%s', $style['href']);
            }
          }

          if (!$style_is_core) {
            $style_href = ($template->get_category() != 'default') ? sprintf('/templates/%s/%s/%s', $template->get_category(), $template->get_name(), $style['href']) : sprintf('/templates/%s/%s', $template->get_name(), $style['href']);
          }

          /** @var string $style_assembled Собранный DOM-элемент LINK для добавления стиля */
          $style_assembled = self::assembly('<link href="{STYLE_HREF}" rel="{STYLE_RELATIONSHIP}">', [
            'STYLE_HREF' => $style_href,
            'STYLE_RELATIONSHIP' => $style['rel']
          ]);

          array_push($styles_assembled, $style_assembled);
        }
      }

      return implode($styles_assembled);
    }

    public static function assembly_scripts(Template $template, array $scripts_array) : string {
      /** @var array $styles_assembled Массив стилей страницы, прошедших сборку */
      $scripts_assembled = [];

      foreach ($scripts_array as $script) {
        if ($template->get_category() != 'default') {
          $script_url = (!$script['is_cms_core']) ? sprintf('/templates/%s/%s/%s', $template->get_category(), $template->get_name(), $script['src']) : sprintf('/core/JSLibrary/%s', $script['src']);
        } else {
          $script_url = (!$script['is_cms_core']) ? sprintf('/templates/%s/%s', $template->get_name(), $script['src']) : sprintf('/core/JSLibrary/%s', $script['src']);
        }

        if (array_key_exists('src', $script)) {
          $attributes = [];
          foreach ($script as $script_attribute_name => $script_attribute_value) {
            if ($script_attribute_name != 'is_cms_core' && $script_attribute_name != 'src') {
              array_push($attributes, sprintf('%s="%s"', $script_attribute_name, $script_attribute_value));
            }

            if ($script_attribute_name == 'src') {
              array_push($attributes, sprintf('%s="%s"', $script_attribute_name, $script_url));
            }
          }

          /** @var string $script_assembled Собранный DOM-элемент SCRIPT для добавления скрипта */
          $script_assembled = self::assembly('<script {SCRIPT_ATTRIBUTES}></script>', [
            'SCRIPT_ATTRIBUTES' => implode(' ', $attributes)
          ]);

          array_push($scripts_assembled, $script_assembled);
        }
      }

      return implode($scripts_assembled);
    }

    public static function assembly_locale(string $template_string, SystemCoreLocale|TemplateLocale|ModuleLocale $locale) : string {
      $template_transformed = $template_string;

      $locale_data = $locale->get_data();
      if (!empty($locale_data)) {
        foreach ($locale_data as $string_name => $string_value) {
          if (preg_match(self::TEMPLATE_TAG_LANG_PATTERN, $template_transformed)) {
            $template_transformed = str_replace("{LANG:{$string_name}}", $string_value, $template_transformed);
          }
        }
      }

      return $template_transformed;
    }

    /**
     * Сборка шаблона на основе строки
     *
     * @param  mixed $template_string Содержимое шаблона
     * @param  mixed $template_replaces Массив с тегами шаблона и их значениями
     * @return string
     */
    public static function assembly(string $template_string, array $template_replaces) : string {
      $template_transformed = $template_string;

      foreach($template_replaces as $template_name => $template_value) {
        if (preg_match(self::TEMPLATE_TAG_PATTERN, $template_transformed)) {
          $template_transformed = str_replace("{{$template_name}}", $template_value, $template_transformed);
        }
      }

      return $template_transformed;
    }

    public static function assembly_logic(SystemCore $system_core, string $template_string) : string {
      $template_transformed = $template_string;

      $define_function = function(string $function_name) : mixed {
        switch ($function_name) {
          case 'CLIENT_IS_LOGGED': return $system_core->client->is_logged(1);
        }

        return null;
      };

      //       1  2  3     4            5
      // {?IF:CONDITION?} ... {?ELSE?} ... {?ENDIF?}
      if (preg_match(self::TEMPLATE_LOGIC_IF_ELSE_PATTERN, $template_transformed, $matches)) {
        //
      }

      //       1  2  3     4
      // {?IF:CONDITION?} ... {?ENDIF?}
      if (preg_match(self::TEMPLATE_LOGIC_IF_PATTERN, $template_transformed, $matches)) {
        $define_function_returned = false;
        if ($matches[2] == '==') $define_function_returned = $define_function($matches[1]) == $matches[3];
        if ($matches[2] == '!=') $define_function_returned = $define_function($matches[1]) != $matches[3];
        if ($matches[2] == '>=') $define_function_returned = $define_function($matches[1]) >= $matches[3];
        if ($matches[2] == '<=') $define_function_returned = $define_function($matches[1]) <= $matches[3];
        if ($matches[2] == '>') $define_function_returned = $define_function($matches[1]) > $matches[3];
        if ($matches[2] == '<') $define_function_returned = $define_function($matches[1]) < $matches[3];

        if ($define_function_returned) {
          $template_transformed = str_replace($matches[0], self::assembly_logic($matches[4]), $template_transformed);
        } else {
          $template_transformed = '';
        }
      }

      return $template_transformed;
    }
    
    /**
     * Сборка шаблона на основе содержимого файла
     *
     * @param  mixed $file_path Полный путь до файла
     * @param  mixed $template_replaces Массив с тегами шаблона и их значениями
     * @return string
     */
    public static function assembly_file_content(Template $template, string $file_path, array $template_replaces) : string {
      /** @var string $file_path Полный путь до шаблона */
      $file_path = sprintf('%s/%s', $template->get_path(), $file_path);

      if (file_exists($file_path)) {
        $file_content = file_get_contents($file_path);
        return self::assembly($file_content, $template_replaces);
      }

      return sprintf('{ERROR:FILE_IS_NOT_EXISTS=%s}', $file_path);
    }

  }

}

?>