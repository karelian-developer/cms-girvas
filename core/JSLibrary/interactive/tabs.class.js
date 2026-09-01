// core/JSLibrary/interactive/tabs.class.js

/**
 * CMS «ГИРВАС»
 * 
 * Включена в Реестр российского программного обеспечения Минцифры РФ.
 * Реестровый номер: №25012 от 27.11.2024
 * 
 * @copyright Copyright (c) 2021 - 2026, ИП Шестаков А.Р., «Карельский разработчик».
 *             Все права защищены.
 * @license   https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 * @see       https://gitflic.ru/project/garbalo/cms-girvas Репозиторий продукта
 * @see       https://cms-girvas.ru Сайт продукта
 * @author    Андрей Шестаков <andrey.shestakov@karelian-developer.ru>
 * @support   support@karelian-developer.ru
 */

'use strict';

/**
 * Класс интерактивного элемента "Переключатели"
 * 
 * Универсальный компонент для создания переключателей (табов/пилюль)
 * Может использоваться для:
 * - Переключения категорий
 * - Навигации по вкладкам
 * - Фильтрации контента
 * - Любых других сценариев с переключением
 */
export class Tabs {
  constructor(interactiveObject, options = {}) {
    this.interactiveObject = interactiveObject;
    
    // Основные свойства
    this.element = null;
    this.name = null;
    this.items = [];
    this.itemSelectedIndex = 0;
    this.assembled = null;
    
    // Опции
    this.type = options.type || 'pills';
    this.orientation = options.orientation || 'horizontal';
    this.isMultiple = options.isMultiple || false;
    this.selectedIndexes = new Set();
    this.width = options.width || 'auto';
    
    // Колбэки
    this.onChangeCallback = null;
    this.onItemClickCallback = null;
  }
  
  /**
   * Установить имя элемента
   * 
   * @param {String} value
   * @returns {Tabs}
   */
  setName(value) {
    this.name = value;
    return this;
  }
  
  /**
   * Установить тип переключателей
   * 
   * @param {String} type - 'pills' | 'tabs' | 'buttons' | 'segmented'
   * @returns {Tabs}
   */
  setType(type) {
    const allowedTypes = ['pills', 'tabs', 'buttons', 'segmented'];
    if (allowedTypes.includes(type)) {
      this.type = type;
    }
    return this;
  }
  
  /**
   * Установить ориентацию
   * 
   * @param {String} orientation - 'horizontal' | 'vertical'
   * @returns {Tabs}
   */
  setOrientation(orientation) {
    if (['horizontal', 'vertical'].includes(orientation)) {
      this.orientation = orientation;
    }
    return this;
  }
  
  /**
   * Установить множественный выбор
   * 
   * @param {Boolean} isMultiple
   * @returns {Tabs}
   */
  setMultiple(isMultiple) {
    this.isMultiple = isMultiple;
    if (isMultiple) {
      this.selectedIndexes = new Set([this.itemSelectedIndex]);
    }
    return this;
  }
  
  /**
   * Установить ширину
   * 
   * @param {String|Number} value
   * @returns {Tabs}
   */
  setWidth(value) {
    this.width = value;
    return this;
  }
  
  /**
   * Добавить элемент
   * 
   * @param {String} label - Текст элемента
   * @param {any} value - Значение элемента
   * @param {Object} options - Дополнительные опции {icon, badge, disabled, title}
   * @returns {Tabs}
   */
  addItem(label, value, options = {}) {
    this.items.push({
      label: label,
      value: value,
      icon: options.icon || null,
      badge: options.badge || null,
      disabled: options.disabled || false,
      title: options.title || label
    });
    return this;
  }
  
  /**
   * Установить индекс выбранного элемента
   * 
   * @param {Number} index
   * @returns {Tabs}
   */
  setItemSelectedIndex(index) {
    if (this.isMultiple) {
      this.selectedIndexes.add(index);
    } else {
      this.itemSelectedIndex = index;
    }
    return this;
  }
  
  /**
   * Получить индекс выбранного элемента
   * 
   * @returns {Number}
   */
  getItemSelectedIndex() {
    return this.itemSelectedIndex;
  }
  
  /**
   * Получить значение выбранного элемента
   * 
   * @returns {any|Array}
   */
  getValue() {
    if (this.isMultiple) {
      return Array.from(this.selectedIndexes).map(index => this.items[index]?.value);
    }
    return this.items[this.itemSelectedIndex]?.value;
  }
  
  /**
   * Получить массив элементов
   * 
   * @returns {Array}
   */
  getItems() {
    return this.items;
  }
  
  /**
   * Установить колбэк при изменении
   * 
   * @param {Function} callback
   * @returns {Tabs}
   */
  onChange(callback) {
    this.onChangeCallback = callback;
    return this;
  }
  
  /**
   * Установить колбэк при клике на элемент
   * 
   * @param {Function} callback
   * @returns {Tabs}
   */
  onItemClick(callback) {
    this.onItemClickCallback = callback;
    return this;
  }
  
  /**
   * Выбор элемента
   * 
   * @param {Number} index - Индекс элемента
   * @param {Event} event - Событие клика
   * @returns {Tabs}
   */
  selectItem(index, event = null) {
    if (index < 0 || index >= this.items.length) return this;
    
    const item = this.items[index];
    if (item.disabled) return this;
    
    if (this.isMultiple) {
      // Множественный выбор
      if (event && (event.ctrlKey || event.metaKey)) {
        // Ctrl+Click - переключение
        if (this.selectedIndexes.has(index)) {
          this.selectedIndexes.delete(index);
        } else {
          this.selectedIndexes.add(index);
        }
      } else if (event && event.shiftKey) {
        // Shift+Click - диапазон
        const lastIndex = Array.from(this.selectedIndexes).pop() || 0;
        const minIndex = Math.min(lastIndex, index);
        const maxIndex = Math.max(lastIndex, index);
        
        for (let i = minIndex; i <= maxIndex; i++) {
          this.selectedIndexes.add(i);
        }
      } else {
        // Обычный клик - только этот элемент
        this.selectedIndexes = new Set([index]);
      }
    } else {
      // Одиночный выбор
      this.itemSelectedIndex = index;
    }
    
    // Обновляем отображение
    this.updateVisualState();
    
    // Вызываем колбэки
    if (this.onItemClickCallback) {
      this.onItemClickCallback(item, index, event);
    }
    
    if (this.onChangeCallback) {
      const value = this.isMultiple ? this.getValue() : item.value;
      this.onChangeCallback(value, item, index);
    }
    
    return this;
  }
  
  /**
   * Обновление визуального состояния
   */
  updateVisualState() {
    if (!this.element) return;
    
    const itemElements = this.element.querySelectorAll('.tabs__item');
    
    itemElements.forEach((itemElement, index) => {
      const isSelected = this.isMultiple 
        ? this.selectedIndexes.has(index)
        : index === this.itemSelectedIndex;
      
      if (isSelected) {
        itemElement.classList.add('tabs__item_is-selected');
        itemElement.setAttribute('aria-selected', 'true');
      } else {
        itemElement.classList.remove('tabs__item_is-selected');
        itemElement.setAttribute('aria-selected', 'false');
      }
    });
  }
  
  /**
   * Создание элемента переключателя
   * 
   * @param {Object} item - Данные элемента
   * @param {Number} index - Индекс элемента
   * @returns {HTMLButtonElement}
   */
  createItemElement(item, index) {
    const itemElement = document.createElement('button');
    itemElement.setAttribute('type', 'button');
    itemElement.classList.add('tabs__item');
    itemElement.setAttribute('role', 'tab');
    itemElement.setAttribute('aria-selected', 'false');
    itemElement.setAttribute('title', item.title);
    
    // Добавляем модификатор типа
    itemElement.classList.add(`tabs__item_type-${this.type}`);
    
    if (item.disabled) {
      itemElement.classList.add('tabs__item_disabled');
      itemElement.setAttribute('disabled', 'disabled');
    }
    
    if (this.isMultiple) {
      itemElement.setAttribute('aria-multiselectable', 'true');
    }
    
    // Иконка
    if (item.icon) {
      const iconElement = document.createElement('span');
      iconElement.classList.add('tabs__item-icon');
      
      if (typeof item.icon === 'string' && item.icon.startsWith('<')) {
        // SVG или HTML строка
        iconElement.innerHTML = item.icon;
      } else if (typeof item.icon === 'string') {
        // Emoji или текст
        iconElement.textContent = item.icon;
      } else if (item.icon instanceof HTMLElement) {
        // DOM элемент
        iconElement.appendChild(item.icon);
      }
      
      itemElement.appendChild(iconElement);
    }
    
    // Текст
    const labelElement = document.createElement('span');
    labelElement.classList.add('tabs__item-label');
    labelElement.textContent = item.label;
    itemElement.appendChild(labelElement);
    
    // Бейдж
    if (item.badge !== null && item.badge !== undefined) {
      const badgeElement = document.createElement('span');
      badgeElement.classList.add('tabs__item-badge');
      badgeElement.textContent = item.badge;
      itemElement.appendChild(badgeElement);
    }
    
    // Обработчик клика
    itemElement.addEventListener('click', (event) => {
      event.preventDefault();
      this.selectItem(index, event);
    });
    
    return itemElement;
  }
  
  /**
   * Сборка контейнера
   * 
   * @returns {HTMLDivElement}
   */
  assemblyContainer() {
    const container = document.createElement('div');
    container.classList.add('interactive__tabs');
    container.classList.add(`tabs_type-${this.type}`);
    container.classList.add(`tabs_orientation-${this.orientation}`);
    
    if (this.width !== 'auto') {
      container.style.width = typeof this.width === 'number' ? this.width + 'px' : this.width;
    }
    
    // ARIA атрибуты
    container.setAttribute('role', 'tablist');
    if (this.orientation === 'vertical') {
      container.setAttribute('aria-orientation', 'vertical');
    }
    
    return container;
  }
  
  /**
   * Итоговая сборка интерактивного элемента
   * 
   * @returns {HTMLDivElement}
   */
  assembly() {
    const container = this.assemblyContainer();
    
    // Создаем элементы
    this.items.forEach((item, index) => {
      const itemElement = this.createItemElement(item, index);
      container.appendChild(itemElement);
    });
    
    // Устанавливаем начальное состояние
    this.element = container;
    this.updateVisualState();
    
    // Скрытый input для форм
    if (this.name) {
      const hiddenInput = document.createElement('input');
      hiddenInput.setAttribute('type', 'hidden');
      hiddenInput.setAttribute('name', this.name);
      hiddenInput.value = this.getValue() || '';
      container.appendChild(hiddenInput);
      
      // Обновляем значение при изменении
      const originalOnChange = this.onChangeCallback;
      this.onChangeCallback = (value, item, index) => {
        hiddenInput.value = Array.isArray(value) ? value.join(',') : value;
        if (originalOnChange) {
          originalOnChange(value, item, index);
        }
      };
    }
    
    return container;
  }
}