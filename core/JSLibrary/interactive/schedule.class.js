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

import {DataDot} from './schedule/dataDot.class.js';
import {Legend} from './schedule/legend.class.js';
import {Linear as ScheduleLinear} from './schedule/types/linear.class.js';

export class Schedule {
  /**
   * @param {Object} interactiveObject - Объект Interactive
   * @param {HTMLCanvasElement} canvas - Элемент canvas
   * @param {string} type - Тип графика ('linear')
   * @param {Object} options - Опции
   * @param {boolean} options.zoomable - Включить зум
   * @param {number} options.minZoom - Минимальный зум
   * @param {number} options.maxZoom - Максимальный зум
   * @param {number} options.zoomStep - Шаг зума
   * @param {boolean} options.showNavigator - Показывать навигатор
   * @param {Object} options.padding - Отступы {top, right, bottom, left}
   * @param {number|string} options.height - Высота canvas (число в px, 'auto', или соотношение '16:9')
   * @param {number} options.minHeight - Минимальная высота (px)
   * @param {number} options.maxHeight - Максимальная высота (px)
   */
  constructor(interactiveObject, canvas, type = 'linear', options = {}) {
    this.interactiveObject = interactiveObject;
    this.canvas = canvas;
    this.type = type;

    // Опции с дефолтами
    this.options = {
      zoomable: options.zoomable || false,
      minZoom: options.minZoom || 0.5,
      maxZoom: options.maxZoom || 5,
      zoomStep: options.zoomStep || 0.1,
      showNavigator: options.showNavigator !== undefined ? options.showNavigator : true,
      padding: options.padding || { top: 30, right: 30, bottom: 40, left: 50 },
      height: options.height || 'auto', // 'auto', число в px, или '16:9', '4:3'
      minHeight: options.minHeight || 200,
      maxHeight: options.maxHeight || 600,
      aspectRatio: options.aspectRatio || null // '16:9' или '4:3'
    };

    // Состояние
    this.dataCollision = { x: 0, y: 0 };
    this.mouseCollision = false;
    this.mouse = { x: 0, y: 0 };
    this.dotsRenderInterval = null;
    this._isInited = false;
    this._resizeObserver = null;

    // Рамка графика
    this.frame = {
      position: { x: 25, y: 25 },
      size: { width: 600, height: 200 }
    };

    // Контекст
    this.context = typeof canvas === 'object' ? canvas.getContext('2d') : null;
    
    // Устанавливаем начальные размеры
    this.updateCanvasSize();

    // Данные
    this.dataDots = [];
    this.types = [];
    this.legend = new Legend();

    // Zoom
    this.zoom = {
      level: 1,
      viewStart: 0,
      viewEnd: 1,
      isDragging: false,
      dragStartX: 0,
      dragStartViewStart: 0,
      dragStartViewEnd: 0,
      navCanvas: null,
      navContext: null,
      navContainer: null,
      isNavVisible: false
    };

    // Если зум включён — инициализируем навигатор
    if (this.options.zoomable && this.options.showNavigator) {
      this.initNavigator();
    }

    // Следим за изменением размера контейнера
    this.initResizeObserver();
  }

  // ============================================================
  //  УПРАВЛЕНИЕ РАЗМЕРАМИ CANVAS
  // ============================================================

  /**
   * Обновить размер canvas в зависимости от опций
   */
  updateCanvasSize() {
    if (!this.canvas) return;

    const parent = this.canvas.parentElement;
    if (!parent) return;

    const parentWidth = parent.clientWidth || 800;
    let height = this.calculateHeight(parentWidth);

    // Применяем ограничения
    height = Math.max(this.options.minHeight, Math.min(this.options.maxHeight, height));

    // Устанавливаем размеры canvas (атрибуты для рисования)
    this.canvas.width = parentWidth;
    this.canvas.height = height;

    // Устанавливаем CSS-размеры
    this.canvas.style.width = '100%';
    this.canvas.style.height = height + 'px';

    // Обновляем frame size если уже есть данные
    if (this._isInited) {
      const padding = this.options.padding;
      const frameWidth = parentWidth - padding.left - padding.right;
      const frameHeight = height - padding.top - padding.bottom;
      
      this.setFrameSize(frameWidth, frameHeight);
      this.setFramePosition(padding.left, padding.top);
      
      if (this.context) {
        this.render();
      }
    }

    // Обновляем навигатор
    if (this.zoom.navCanvas) {
      const navParent = this.zoom.navCanvas.parentElement;
      if (navParent) {
        const navWidth = navParent.clientWidth - 120; // минус кнопки
        if (navWidth > 100) {
          this.zoom.navCanvas.width = navWidth;
          this.zoom.navCanvas.style.width = '100%';
          if (this.zoom.navContext) {
            this.renderNavigator();
          }
        }
      }
    }
  }

  /**
   * Вычислить высоту canvas
   * @param {number} width - Ширина родителя
   * @returns {number} Высота в px
   */
  calculateHeight(width) {
    const heightOption = this.options.height;
    const aspectRatio = this.options.aspectRatio;

    // Если задано соотношение сторон
    if (aspectRatio) {
      const ratio = this.parseAspectRatio(aspectRatio);
      if (ratio) {
        return width / ratio;
      }
    }

    // Если задана строка с соотношением
    if (typeof heightOption === 'string' && heightOption.includes(':')) {
      const ratio = this.parseAspectRatio(heightOption);
      if (ratio) {
        return width / ratio;
      }
    }

    // Если 'auto' — рассчитываем динамически
    if (heightOption === 'auto') {
      // Базовая высота: 60% от ширины, но не меньше 200 и не больше 600
      let autoHeight = width * 0.6;
      return Math.max(this.options.minHeight, Math.min(this.options.maxHeight, autoHeight));
    }

    // Если число — используем его
    if (typeof heightOption === 'number') {
      return heightOption;
    }

    // Если строка с px
    if (typeof heightOption === 'string' && heightOption.endsWith('px')) {
      const parsed = parseInt(heightOption, 10);
      if (!isNaN(parsed)) {
        return parsed;
      }
    }

    // Fallback
    return Math.max(this.options.minHeight, Math.min(this.options.maxHeight, width * 0.6));
  }

  /**
   * Разобрать соотношение сторон из строки
   * @param {string} ratio - '16:9' или '4:3'
   * @returns {number|null} Ширина/высота
   */
  parseAspectRatio(ratio) {
    if (typeof ratio !== 'string') return null;
    const parts = ratio.split(':');
    if (parts.length !== 2) return null;
    const w = parseFloat(parts[0]);
    const h = parseFloat(parts[1]);
    if (isNaN(w) || isNaN(h) || h === 0) return null;
    return w / h;
  }

  /**
   * Наблюдатель за изменением размера контейнера
   */
  initResizeObserver() {
    if (typeof ResizeObserver === 'undefined') {
      // Fallback: обновляем при ресайзе окна
      window.addEventListener('resize', () => {
        this.updateCanvasSize();
      });
      return;
    }

    const parent = this.canvas?.parentElement;
    if (!parent) return;

    this._resizeObserver = new ResizeObserver(() => {
      this.updateCanvasSize();
    });
    this._resizeObserver.observe(parent);
  }

  // ============================================================
  //  НАСТРОЙКА РАМКИ
  // ============================================================

  setFramePosition(x, y) {
    this.frame.position.x = x;
    this.frame.position.y = y;
  }

  setFrameSize(width, height) {
    this.frame.size.width = width;
    this.frame.size.height = height;
  }

  getFramePosition() {
    return { x: this.frame.position.x, y: this.frame.position.y };
  }

  getFrameSize() {
    return { width: this.frame.size.width, height: this.frame.size.height };
  }

  // ============================================================
  //  РАБОТА С ДАННЫМИ
  // ============================================================

  addGroup(label) {
    let type;
    switch (this.type) {
      default:
        type = new ScheduleLinear(this);
    }
    type.setLabel(label);
    this.types.push(type);
    return type;
  }

  addData(groupIndex, x, y) {
    if (this.types[groupIndex]) {
      this.types[groupIndex].addData(new DataDot(x, y));
    }
  }

  buildData() {
    if (this.types.length > 0) {
      this.types.forEach((element) => {
        element.buildData(this.getDaysCountInCurrentMonth());
      });
    }

    // Устанавливаем frame size если ещё не установлен
    if (!this._isInited) {
      const padding = this.options.padding;
      const width = this.canvas.width - padding.left - padding.right;
      const height = this.canvas.height - padding.top - padding.bottom;
      this.setFrameSize(width, height);
      this.setFramePosition(padding.left, padding.top);
    }

    // Обновляем навигатор
    if (this.options.zoomable && this.options.showNavigator && this.zoom.navContext) {
      setTimeout(() => this.renderNavigator(), 50);
    }
  }

  getMaxYData() {
    let maxY = 0;
    for (let groupIndex = 0; groupIndex < this.types.length; groupIndex++) {
      for (let data of this.types[groupIndex].data) {
        if (data && data.y > maxY) maxY = data.y;
      }
    }
    return maxY || 1;
  }

  getMaxXData() {
    let maxX = 0;
    for (let groupIndex = 0; groupIndex < this.types.length; groupIndex++) {
      for (let data of this.types[groupIndex].data) {
        if (data && data.x > maxX) maxX = data.x;
      }
    }
    return maxX;
  }

  getDaysCountInCurrentMonth() {
    const date = new Date();
    return new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
  }

  // ============================================================
  //  РАЗМЕРЫ CANVAS
  // ============================================================

  getCanvasWidth() {
    return this.canvas?.width || 800;
  }

  getCanvasHeight() {
    return this.canvas?.height || 400;
  }

  // ============================================================
  //  ОТРИСОВКА
  // ============================================================

  drawFrame() {
    if (!this.context) return;
    this.context.strokeStyle = '#CECECE';
    this.context.beginPath();
    this.context.rect(
      this.getFramePosition().x,
      this.getFramePosition().y,
      this.getFrameSize().width,
      this.getFrameSize().height
    );
    this.context.stroke();
  }

  drawGrid() {
    if (!this.context) return;

    const maxX = this.getDaysCountInCurrentMonth();
    const maxY = this.getMaxYData();
    const frameWidth = this.getFrameSize().width;
    const frameHeight = this.getFrameSize().height;
    const frameX = this.getFramePosition().x;
    const frameY = this.getFramePosition().y;

    const lineXStep = frameWidth / maxX;
    const lineYStep = maxY > 0 ? frameHeight / maxY : 10;

    this.context.strokeStyle = '#EAEAEA';
    this.context.font = '11px sans-serif';
    this.context.textBaseline = 'top';
    this.context.textAlign = 'center';

    // Вертикальные линии
    for (let i = 0; i <= maxX; i++) {
      const x = frameX + lineXStep * i;
      this.context.beginPath();
      this.context.moveTo(x, frameY);
      this.context.lineTo(x, frameY + frameHeight);
      this.context.stroke();

      if (i % 2 === 0 || i === maxX) {
        this.context.fillText(`${i + 1}`, x, frameY + frameHeight + 6);
      }
    }

    // Горизонтальные линии
    const gridLines = 5;
    for (let i = 0; i <= gridLines; i++) {
      const y = frameY + (frameHeight / gridLines) * i;
      this.context.beginPath();
      this.context.moveTo(frameX, y);
      this.context.lineTo(frameX + frameWidth, y);
      this.context.stroke();

      const val = Math.round(maxY - (maxY / gridLines) * i);
      this.context.textAlign = 'right';
      this.context.textBaseline = 'middle';
      this.context.fillText(val.toString(), frameX - 8, y);
    }
  }

  render() {
    if (!this.canvas || !this.context) return;

    this.drawGrid();
    this.drawFrame();

    // Рендерим линии
    if (this.types.length > 0) {
      this.types.forEach((element) => {
        element.render(this);
      });
    }

    // Легенда
    this.legend.render(
      this.context,
      this.getFramePosition().x,
      this.getFramePosition().y + this.getFrameSize().height + 10
    );

    // Обработка коллизий для тултипа
    let collisionDetected = false;
    for (const type of this.types) {
      if (collisionDetected) break;
      for (const data of type.data) {
        if (data && data.collision) {
          this.mouseCollision = true;
          this.dataCollision = data;
          collisionDetected = true;
          break;
        }
      }
    }

    if (!collisionDetected) {
      this.mouseCollision = false;
    }

    // Рисуем тултип
    if (this.mouseCollision) {
      const tooltipX = this.mouse.x + 10;
      const tooltipY = this.mouse.y + 10;

      this.context.strokeStyle = '#232323';
      this.context.fillStyle = '#FFFFFF';
      this.context.beginPath();
      this.context.rect(tooltipX, tooltipY, 60, 20);
      this.context.fill();
      this.context.stroke();

      this.context.textAlign = 'left';
      this.context.textBaseline = 'top';
      this.context.font = '12px serif';
      this.context.fillStyle = '#232323';
      this.context.fillText(
        `x: ${this.dataCollision.x}, y: ${this.dataCollision.y}`,
        tooltipX + 4,
        tooltipY + 4
      );
    }
  }

  // ============================================================
  //  ИНИЦИАЛИЗАЦИЯ
  // ============================================================

  init() {
    if (this._isInited) return;

    // Устанавливаем frame size
    const padding = this.options.padding;
    const width = this.canvas.width - padding.left - padding.right;
    const height = this.canvas.height - padding.top - padding.bottom;
    this.setFrameSize(width, height);
    this.setFramePosition(padding.left, padding.top);

    this._isInited = true;

    // Событие движения мыши
    this.canvas.addEventListener('mousemove', (event) => {
      const rect = this.canvas.getBoundingClientRect();
      this.mouse.x = event.clientX - rect.left;
      this.mouse.y = event.clientY - rect.top;

      if (!this.dotsRenderInterval) {
        this.dotsRenderInterval = setInterval(() => {
          if (this.context) {
            this.context.reset();
            this.render();
          }
        }, 10);
      }
    });

    // Уходим с canvas
    this.canvas.addEventListener('mouseleave', () => {
      this.mouse = { x: 0, y: 0 };
      if (this.dotsRenderInterval) {
        clearInterval(this.dotsRenderInterval);
        this.dotsRenderInterval = null;
        if (this.context) {
          this.context.reset();
          this.render();
        }
      }
    });

    // Легенда
    for (let i = 0; i < this.types.length; i++) {
      if (this.types[i].label === '') {
        this.types[i].label = `Data #${i}`;
      }
      this.legend.addType(this.types[i]);
    }

    this.legend.setRenderSize(
      this.getFrameSize().width,
      this.canvas.height - this.getFrameSize().height - 10
    );

    this.render();
  }

  assembly() {
    this.element = this.canvas;
  }

  // ============================================================
  //  ZOOM
  // ============================================================

  initNavigator() {
    if (this.zoom.navContainer) return;

    const navContainer = document.createElement('div');
    navContainer.className = 'schedule-navigator';

    // Кнопки
    const zoomInBtn = document.createElement('button');
    zoomInBtn.innerHTML = '➕';
    zoomInBtn.title = 'Увеличить';
    zoomInBtn.addEventListener('click', () => this.zoomIn());

    const zoomOutBtn = document.createElement('button');
    zoomOutBtn.innerHTML = '➖';
    zoomOutBtn.title = 'Уменьшить';
    zoomOutBtn.addEventListener('click', () => this.zoomOut());

    const resetBtn = document.createElement('button');
    resetBtn.className = 'nav-btn-reset';
    resetBtn.innerHTML = '⟳';
    resetBtn.title = 'Сбросить масштаб';
    resetBtn.addEventListener('click', () => this.resetZoom());

    // Навигационный canvas
    const navCanvas = document.createElement('canvas');
    navCanvas.className = 'schedule-nav-canvas';
    navCanvas.width = 800;
    navCanvas.height = 50;
    navCanvas.style.width = '100%';

    this.zoom.navCanvas = navCanvas;
    this.zoom.navContext = navCanvas.getContext('2d');

    // События навигатора
    navCanvas.addEventListener('mousedown', (e) => this.navStartDrag(e));
    navCanvas.addEventListener('mousemove', (e) => this.navMoveDrag(e));
    navCanvas.addEventListener('mouseup', () => this.navEndDrag());
    navCanvas.addEventListener('mouseleave', () => this.navEndDrag());

    // Собираем
    navContainer.append(zoomInBtn);
    navContainer.append(zoomOutBtn);
    navContainer.append(resetBtn);
    navContainer.append(navCanvas);

    this.canvas.parentNode.insertBefore(navContainer, this.canvas.nextSibling);

    this.zoom.navContainer = navContainer;
    this.zoom.isNavVisible = true;

    // Wheel zoom
    this.canvas.addEventListener('wheel', (e) => {
      e.preventDefault();
      const delta = e.deltaY > 0 ? 1 + this.options.zoomStep : 1 / (1 + this.options.zoomStep);
      this.zoomBy(delta);
    }, { passive: false });
  }

  zoomIn() {
    this.zoomBy(1 / (1 + this.options.zoomStep));
  }

  zoomOut() {
    this.zoomBy(1 + this.options.zoomStep);
  }

  zoomBy(factor) {
    const newLevel = this.zoom.level * factor;
    if (newLevel < this.options.minZoom || newLevel > this.options.maxZoom) return;

    this.zoom.level = newLevel;
    this.updateView();
  }

  resetZoom() {
    this.zoom.level = 1;
    this.zoom.viewStart = 0;
    this.zoom.viewEnd = 1;
    this.updateView();
  }

  navStartDrag(e) {
    if (!this.zoom.navCanvas) return;
    this.zoom.isDragging = true;
    const rect = this.zoom.navCanvas.getBoundingClientRect();
    const x = (e.clientX - rect.left) / rect.width;

    this.zoom.dragStartX = x;
    this.zoom.dragStartViewStart = this.zoom.viewStart;
    this.zoom.dragStartViewEnd = this.zoom.viewEnd;

    this.zoom.navCanvas.style.cursor = 'grabbing';
  }

  navMoveDrag(e) {
    if (!this.zoom.isDragging || !this.zoom.navCanvas) return;

    const rect = this.zoom.navCanvas.getBoundingClientRect();
    const x = (e.clientX - rect.left) / rect.width;
    const delta = x - this.zoom.dragStartX;
    const viewWidth = this.zoom.viewEnd - this.zoom.viewStart;

    let newStart = this.zoom.dragStartViewStart + delta * viewWidth;
    let newEnd = this.zoom.dragStartViewEnd + delta * viewWidth;

    if (newStart < 0) {
      newStart = 0;
      newEnd = viewWidth;
    }
    if (newEnd > 1) {
      newEnd = 1;
      newStart = 1 - viewWidth;
    }

    this.zoom.viewStart = Math.max(0, Math.min(1 - viewWidth, newStart));
    this.zoom.viewEnd = this.zoom.viewStart + viewWidth;

    this.updateView();
  }

  navEndDrag() {
    this.zoom.isDragging = false;
    if (this.zoom.navCanvas) {
      this.zoom.navCanvas.style.cursor = 'grab';
    }
  }

  updateView() {
    if (this.options.zoomable && this.options.showNavigator && this.zoom.navContext) {
      this.renderNavigator();
    }
    this.render();
  }

  // ============================================================
  //  НАВИГАТОР (мини-график)
  // ============================================================

  renderNavigator() {
    const ctx = this.zoom.navContext;
    if (!ctx) return;

    const w = this.zoom.navCanvas.width;
    const h = this.zoom.navCanvas.height;

    ctx.clearRect(0, 0, w, h);

    // Рисуем мини-график
    this.drawMiniChart(ctx, w, h);

    // Область просмотра
    const left = this.zoom.viewStart * w;
    const right = this.zoom.viewEnd * w;

    ctx.fillStyle = 'rgba(40, 94, 142, 0.15)';
    ctx.strokeStyle = '#285E8E';
    ctx.lineWidth = 2;
    ctx.fillRect(left, 0, right - left, h);
    ctx.strokeRect(left, 0, right - left, h);
  }

  drawMiniChart(ctx, w, h) {
    const data = this.types[0]?.data || [];
    if (data.length < 2) {
      ctx.fillStyle = '#ccc';
      ctx.font = '12px sans-serif';
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';
      ctx.fillText('Нет данных', w / 2, h / 2 + 4);
      return;
    }

    const maxY = this.getMaxYData() || 1;
    const pad = 4;

    // Рисуем линии для всех типов
    this.types.forEach((type) => {
      const typeData = type.data || [];
      if (typeData.length < 2) return;

      const color = type.color || '#999';
      ctx.strokeStyle = color;
      ctx.lineWidth = 1.5;
      ctx.globalAlpha = 0.7;

      ctx.beginPath();
      typeData.forEach((dot, i) => {
        if (!dot) return;
        const x = (i / (typeData.length - 1 || 1)) * (w - pad * 2) + pad;
        const y = h - pad - (dot.y / maxY) * (h - pad * 2);
        i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
      });
      ctx.stroke();
      ctx.globalAlpha = 1;
    });
  }

  // ============================================================
  //  ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ
  // ============================================================

  /**
   * Получить видимый диапазон данных с учётом зума
   * @returns {Object} { start, end }
   */
  getVisibleDataRange() {
    const data = this.types[0]?.data || [];
    if (data.length === 0) return { start: 0, end: 0 };

    const startIdx = Math.floor(this.zoom.viewStart * data.length);
    const endIdx = Math.ceil(this.zoom.viewEnd * data.length);

    return {
      start: Math.max(0, startIdx),
      end: Math.min(data.length, endIdx)
    };
  }

  /**
   * Уничтожить график и очистить DOM
   */
  destroy() {
    if (this._resizeObserver) {
      this._resizeObserver.disconnect();
      this._resizeObserver = null;
    }
    if (this.zoom.navContainer) {
      this.zoom.navContainer.remove();
      this.zoom.navContainer = null;
    }
    if (this.dotsRenderInterval) {
      clearInterval(this.dotsRenderInterval);
      this.dotsRenderInterval = null;
    }
    this.canvas = null;
    this.context = null;
    this._isInited = false;
  }
}