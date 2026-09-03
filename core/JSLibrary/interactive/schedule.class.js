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
      isNavVisible: false,
      isResizingLeft: false,
      isResizingRight: false,
      resizeStartX: 0,
      resizeStartViewStart: 0,
      resizeStartViewEnd: 0
    };

    this._renderRequested = false;
    this._lastRenderTime = 0;
    this._minRenderInterval = 16; // ~60 FPS

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

    height = Math.max(this.options.minHeight, Math.min(this.options.maxHeight, height));

    this.canvas.width = parentWidth;
    this.canvas.height = height;

    this.canvas.style.width = '100%';
    this.canvas.style.height = height + 'px';

    if (this._isInited) {
      const padding = this.options.padding;
      const frameWidth = parentWidth - padding.left - padding.right;
      const frameHeight = height - padding.top - padding.bottom;
      
      this.setFrameSize(frameWidth, frameHeight);
      this.setFramePosition(padding.left, padding.top);
      
      if (this.context) {
        // ==========================================
        // ОЧИЩАЕМ И ПЕРЕРИСОВЫВАЕМ
        // ==========================================
        this.context.clearRect(0, 0, this.canvas.width, this.canvas.height);
        this.render();
      }
    }

    // Обновляем навигатор
    if (this.zoom && this.zoom.navCanvas) {
      const navParent = this.zoom.navCanvas.parentElement;
      if (navParent) {
        const navWidth = navParent.clientWidth - 120;
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

  buildData(dataTotalCount) {
    this.dataBuckup = this.data;
    this.data = [];

    // ==========================================
    // ИСПРАВЛЕНИЕ: i < dataTotalCount (не <=)
    // ==========================================
    for (let i = 0; i < dataTotalCount; i++) {
      for (let data of this.dataBuckup) {
        if (data.x == i) {
          this.data[i] = new DataDot(i, data.y);
        }
      }
    }

    for (let i = 0; i < dataTotalCount; i++) {
      if (typeof(this.data[i]) == 'undefined') {
        this.data[i] = new DataDot(i, 0);
      }
    }

    this.dataBuckup = [];
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

    const totalDays = this.getDaysCountInCurrentMonth();
    const maxY = this.getMaxYData();
    const frameWidth = this.getFrameSize().width;
    const frameHeight = this.getFrameSize().height;
    const frameX = this.getFramePosition().x;
    const frameY = this.getFramePosition().y;

    const viewStart = this.zoom.viewStart || 0;
    const viewEnd = this.zoom.viewEnd || 1;
    const visibleDays = Math.max(1, (viewEnd - viewStart) * totalDays);
    
    const lineXStep = frameWidth / visibleDays;
    const lineYStep = maxY > 0 ? frameHeight / maxY : 10;

    this.context.strokeStyle = '#EAEAEA';
    this.context.font = '11px sans-serif';
    this.context.textBaseline = 'top';
    this.context.textAlign = 'center';

    // Вертикальные линии и подписи
    const step = Math.max(1, Math.ceil(visibleDays / 20));
    for (let i = 0; i < visibleDays; i += step) {
      const x = frameX + lineXStep * i;
      const day = Math.round(viewStart * totalDays + i);
      
      this.context.beginPath();
      this.context.moveTo(x, frameY);
      this.context.lineTo(x, frameY + frameHeight);
      this.context.stroke();

      if (day < totalDays) {
        this.context.fillText(`${day + 1}`, x, frameY + frameHeight + 6);
      }
    }

    // ==========================================
    // ДОБАВЛЯЕМ ПОДПИСЬ ПОСЛЕДНЕГО ДНЯ МЕСЯЦА
    // ==========================================
    // Последний день месяца
    const lastDayX = frameX + frameWidth;
    const lastDayNumber = totalDays;
    
    this.context.fillStyle = '#333';
    this.context.font = '11px sans-serif';
    this.context.textAlign = 'center';
    this.context.textBaseline = 'top';
    this.context.fillText(`${lastDayNumber}`, lastDayX, frameY + frameHeight + 6);

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
      this.context.fillStyle = '#666';
      this.context.fillText(val.toString(), frameX - 8, y);
    }
  }

  render() {
  if (!this.canvas || !this.context) return;

  this.context.clearRect(0, 0, this.canvas.width, this.canvas.height);

  this.drawGrid();
  this.drawFrame();

  // ==========================================
  // 1. РИСУЕМ ЛИНИИ И ТОЧКИ
  // ==========================================
  if (this.types.length > 0) {
    this.types.forEach((element) => {
      element.render(this);
    });
  }

  // ==========================================
  // 2. РИСУЕМ ЛЕГЕНДУ
  // ==========================================
  // Сбрасываем прозрачность перед легендой
  this.context.globalAlpha = 1.0;
  
  this.legend.render(
    this.context,
    this.getFramePosition().x,
    this.getFramePosition().y + this.getFrameSize().height + 10
  );

  // ==========================================
  // 3. РИСУЕМ ТУЛТИПЫ (ПОВЕРХ ЛЕГЕНДЫ)
  // ==========================================
  // Тултипы уже рисуются внутри element.render(),
  // но они рисуются до легенды.
  // Поэтому выносим тултипы в отдельный проход.
  this.renderTooltips();
  }

  // ==========================================
  // НОВЫЙ МЕТОД ДЛЯ ТУЛТИПОВ (ПОВЕРХ ЛЕГЕНДЫ)
  // ==========================================
  renderTooltips() {
    // Проходим по всем типам и собираем данные тултипов
    for (const type of this.types) {
      if (type._isHovered && type._hoveredData) {
        // Рисуем тултип для этого типа
        this.drawTooltip(type);
      }
    }
  }

  // ==========================================
  // МЕТОД ДЛЯ ОТРИСОВКИ ОДНОГО ТУЛТИПА
  // ==========================================
  drawTooltip(type) {
    const schedule = this;
    const data = type._hoveredData;
    if (!data) return;

    const totalDays = this.getDaysCountInCurrentMonth();
    const viewStart = this.zoom.viewStart || 0;
    const viewEnd = this.zoom.viewEnd || 1;
    const visibleDays = Math.max(1, (viewEnd - viewStart) * totalDays);
    const maxY = this.getMaxYData() || 1;
    const lineXStep = this.getFrameSize().width / visibleDays;
    const lineYStep = this.getFrameSize().height / maxY;

    const frameX = this.getFramePosition().x;
    const frameY = this.getFramePosition().y;
    const frameHeight = this.getFrameSize().height;

    const relativeX = data.x - viewStart * totalDays;
    const clampedX = Math.max(0, Math.min(visibleDays, relativeX));
    const xPos = clampedX * lineXStep;
    const yPos = data.y * lineYStep;

    const cx = frameX + xPos;
    const cy = frameY + frameHeight - yPos;

    const dayNumber = data.x + 1;
    const monthName = type.getMonthName();

    const typeIndex = this.types.indexOf(type);
    const typeNames = ['Просмотры', 'Визиты', 'Посещения'];
    const metricName = typeNames[typeIndex] || type.label;

    const lines = [
      `${dayNumber} ${monthName}`,
      `${metricName}: ${data.y}`
    ];

    const fontSize = 13;
    const lineHeight = 20;
    const padding = 10;
    const borderRadius = 6;

    this.context.font = `${fontSize}px sans-serif`;
    this.context.textAlign = 'left';
    this.context.textBaseline = 'top';

    let maxWidth = 0;
    for (const line of lines) {
      const metrics = this.context.measureText(line);
      if (metrics.width > maxWidth) maxWidth = metrics.width;
    }

    const tooltipWidth = maxWidth + padding * 2;
    const tooltipHeight = lines.length * lineHeight + padding * 2;

    let tooltipX = cx + 15;
    let tooltipY = cy - tooltipHeight / 2;

    const canvasWidth = this.canvas.width;
    const canvasHeight = this.canvas.height;

    if (tooltipX + tooltipWidth > canvasWidth - 10) {
      tooltipX = cx - tooltipWidth - 15;
    }
    if (tooltipY < 10) {
      tooltipY = 10;
    }
    if (tooltipY + tooltipHeight > canvasHeight - 10) {
      tooltipY = canvasHeight - tooltipHeight - 10;
    }

    // Рисуем фон тултипа (поверх всего)
    this.context.shadowBlur = 15;
    this.context.shadowColor = 'rgba(0, 0, 0, 0.2)';
    this.context.fillStyle = '#FFFFFF';
    this.context.strokeStyle = type.color;
    this.context.lineWidth = 2.5;
    
    const r = borderRadius;
    const x = tooltipX;
    const y = tooltipY;
    const w = tooltipWidth;
    const h = tooltipHeight;

    this.context.beginPath();
    this.context.moveTo(x + r, y);
    this.context.lineTo(x + w - r, y);
    this.context.quadraticCurveTo(x + w, y, x + w, y + r);
    this.context.lineTo(x + w, y + h - r);
    this.context.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
    this.context.lineTo(x + r, y + h);
    this.context.quadraticCurveTo(x, y + h, x, y + h - r);
    this.context.lineTo(x, y + r);
    this.context.quadraticCurveTo(x, y, x + r, y);
    this.context.closePath();
    this.context.fill();
    this.context.stroke();

    this.context.shadowBlur = 0;

    // Текст
    for (let i = 0; i < lines.length; i++) {
      const textY = tooltipY + padding + i * lineHeight;
      const textX = tooltipX + padding;
      
      if (i === 0) {
        this.context.fillStyle = '#333333';
        this.context.font = `bold ${fontSize}px sans-serif`;
      } else {
        this.context.fillStyle = type.color;
        this.context.font = `${fontSize}px sans-serif`;
      }
      
      this.context.fillText(lines[i], textX, textY);
    }

    // Стрелка
    const arrowX = cx > tooltipX + tooltipWidth / 2 ? tooltipX + tooltipWidth : tooltipX;
    const arrowY = tooltipY + tooltipHeight / 2;

    this.context.fillStyle = '#FFFFFF';
    this.context.strokeStyle = type.color;
    this.context.lineWidth = 2;
    this.context.beginPath();
    
    if (cx > tooltipX + tooltipWidth / 2) {
      this.context.moveTo(tooltipX + tooltipWidth, arrowY);
      this.context.lineTo(tooltipX + tooltipWidth + 10, arrowY);
      this.context.lineTo(tooltipX + tooltipWidth, arrowY - 6);
    } else {
      this.context.moveTo(tooltipX, arrowY);
      this.context.lineTo(tooltipX - 10, arrowY);
      this.context.lineTo(tooltipX, arrowY - 6);
    }
    this.context.closePath();
    this.context.fill();
    this.context.stroke();
  }

  getMonthName() {
    const months = [
      'Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня',
      'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря'
    ];
    const now = new Date();
    return months[now.getMonth()];
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

    // События мыши
    navCanvas.addEventListener('mousedown', (e) => this.navMouseDown(e));
    navCanvas.addEventListener('mousemove', (e) => this.navMouseMove(e));
    navCanvas.addEventListener('mouseup', () => this.navMouseUp());
    
    // ==========================================
    // ИСПРАВЛЕНИЕ: mouseleave НЕ сбрасывает drag
    // ==========================================
    navCanvas.addEventListener('mouseleave', () => {
      // Просто меняем курсор, если не в процессе drag
      if (!this.zoom.isDragging && !this.zoom.isResizingLeft && !this.zoom.isResizingRight) {
        this.zoom.navCanvas.style.cursor = 'grab';
      }
    });

    // ==========================================
    // ГЛОБАЛЬНЫЙ mousemove ДЛЯ ОТСЛЕЖИВАНИЯ ЗА ПРЕДЕЛАМИ CANVAS
    // ==========================================
    if (!this._globalMouseMoveAdded) {
      let lastMoveTime = 0;
      const minInterval = 16;
      
      document.addEventListener('mousemove', (e) => {
        if (this.zoom.isDragging || this.zoom.isResizingLeft || this.zoom.isResizingRight) {
          const now = performance.now();
          if (now - lastMoveTime < minInterval) {
            // Пропускаем слишком частые вызовы
            if (!this._renderRequested) {
              this._renderRequested = true;
              requestAnimationFrame(() => {
                this._renderRequested = false;
                lastMoveTime = performance.now();
                const fakeEvent = { clientX: e.clientX };
                this.navMouseMove(fakeEvent);
              });
            }
            return;
          }
          lastMoveTime = now;
          const fakeEvent = { clientX: e.clientX };
          this.navMouseMove(fakeEvent);
        }
      });
      this._globalMouseMoveAdded = true;
    }

    // ==========================================
    // ГЛОБАЛЬНЫЙ ОБРАБОТЧИК mouseup на document
    // ==========================================
    // Один раз добавляем, чтобы не плодить слушатели
    if (!this._globalMouseUpAdded) {
      document.addEventListener('mouseup', () => {
        // Сбрасываем drag при отпускании кнопки где угодно
        this.navMouseUp();
      });
      this._globalMouseUpAdded = true;
    }

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

    // ==========================================
    // ИСПРАВЛЕНИЕ: пересчитываем область просмотра
    // ==========================================
    const center = (this.zoom.viewStart + this.zoom.viewEnd) / 2;
    const viewWidth = this.zoom.viewEnd - this.zoom.viewStart;
    
    // Новая ширина области с учётом зума
    const newViewWidth = viewWidth / factor;
    
    // Ограничиваем, чтобы не выйти за пределы [0, 1]
    let newStart = center - newViewWidth / 2;
    let newEnd = center + newViewWidth / 2;
    
    // Корректируем, если вышли за границы
    if (newStart < 0) {
      newStart = 0;
      newEnd = newViewWidth;
    }
    if (newEnd > 1) {
      newEnd = 1;
      newStart = 1 - newViewWidth;
    }
    
    this.zoom.level = newLevel;
    this.zoom.viewStart = Math.max(0, newStart);
    this.zoom.viewEnd = Math.min(1, newEnd);
    
    this.updateView();
  }

  resetZoom() {
    this.zoom.level = 1;
    this.zoom.viewStart = 0;
    this.zoom.viewEnd = 1;
    this.updateView();
  }

  /**
   * Запрашивает перерисовку с ограничением частоты
   */
  requestRender() {
    if (this._renderRequested) return;
    
    this._renderRequested = true;
    requestAnimationFrame(() => {
      this._renderRequested = false;
      this.render();
    });
  }

  /**
   * Перерисовка с ограничением частоты (для драга)
   */
  renderThrottled() {
    const now = performance.now();
    if (now - this._lastRenderTime < this._minRenderInterval) {
      // Если прошло меньше 16ms — откладываем
      if (!this._renderRequested) {
        this._renderRequested = true;
        requestAnimationFrame(() => {
          this._renderRequested = false;
          this._lastRenderTime = performance.now();
          this.render();
        });
      }
      return;
    }
    
    this._lastRenderTime = now;
    this.render();
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
    
    // Сохраняем начальную позицию для оптимизации
    this._dragLastX = x;
  }

  navMoveDrag(e) {
    if (!this.zoom.isDragging || !this.zoom.navCanvas) return;

    const rect = this.zoom.navCanvas.getBoundingClientRect();
    const x = (e.clientX - rect.left) / rect.width;
    
    // ==========================================
    // ОПТИМИЗАЦИЯ: обновляем только если сдвиг > 0.5%
    // ==========================================
    const delta = x - this._dragLastX;
    if (Math.abs(delta) < 0.005) return; // игнорируем микро-движения
    this._dragLastX = x;
    
    const viewWidth = this.zoom.viewEnd - this.zoom.viewStart;
    const moveDelta = (x - this.zoom.dragStartX) * viewWidth;

    let newStart = this.zoom.dragStartViewStart + moveDelta;
    let newEnd = this.zoom.dragStartViewEnd + moveDelta;

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

    // ==========================================
    // ОПТИМИЗАЦИЯ: обновляем навигатор сразу (лёгкая операция)
    // ==========================================
    if (this.options.zoomable && this.options.showNavigator && this.zoom.navContext) {
      this.renderNavigator();
    }
    
    // ==========================================
    // ОПТИМИЗАЦИЯ: основной график — с ограничением частоты
    // ==========================================
    this.renderThrottled();
  }

  navEndDrag() {
    this.zoom.isDragging = false;
    if (this.zoom.navCanvas) {
      this.zoom.navCanvas.style.cursor = 'grab';
    }
    // Финальная перерисовка для чистоты
    this.render();
  }

  updateView() {
    // Очищаем основной canvas
    if (this.context) {
      this.context.clearRect(0, 0, this.canvas.width, this.canvas.height);
    }
    
    // Обновляем навигатор
    if (this.options.zoomable && this.options.showNavigator && this.zoom.navContext) {
      this.renderNavigator();
    }
    
    // Перерисовываем основной график
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

    this.drawMiniChart(ctx, w, h);

    const left = this.zoom.viewStart * w;
    const right = this.zoom.viewEnd * w;

    // ==========================================
    // ОБЛАСТЬ ПРОСМОТРА
    // ==========================================
    ctx.fillStyle = 'rgba(40, 94, 142, 0.15)';
    ctx.strokeStyle = '#285E8E';
    ctx.lineWidth = 2;
    ctx.fillRect(left, 0, right - left, h);
    ctx.strokeRect(left, 0, right - left, h);

    // ==========================================
    // РУЧКИ ДЛЯ РЕСАЙЗА
    // ==========================================
    const handleSize = 8;
    const handleOffset = handleSize / 2;
    
    // Левая ручка
    ctx.fillStyle = '#285E8E';
    ctx.beginPath();
    ctx.rect(left - handleOffset, h/2 - handleOffset, handleSize, handleSize);
    ctx.fill();
    ctx.strokeStyle = '#1a4a6e';
    ctx.lineWidth = 1;
    ctx.stroke();
    
    // Правая ручка
    ctx.fillStyle = '#285E8E';
    ctx.beginPath();
    ctx.rect(right - handleOffset, h/2 - handleOffset, handleSize, handleSize);
    ctx.fill();
    ctx.stroke();

    // ==========================================
    // ПОДПИСЬ ДИАПАЗОНА
    // ==========================================
    ctx.fillStyle = '#333';
    ctx.font = '10px sans-serif';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'bottom';
    
    const totalDays = this.getDaysCountInCurrentMonth();
    const startDay = Math.round(this.zoom.viewStart * totalDays) + 1;
    const endDay = Math.round(this.zoom.viewEnd * totalDays);
    
    ctx.fillText(`${startDay} — ${endDay}`, (left + right) / 2, h - 4);
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

  // ============================================================
  //  РЕСАЙЗ ОБЛАСТИ В НАВИГАТОРЕ
  // ============================================================

  navMouseDown(e) {
    const rect = this.zoom.navCanvas.getBoundingClientRect();
    let x = (e.clientX - rect.left) / rect.width;
    x = Math.max(0, Math.min(1, x));
    
    const handleSize = 0.02;
    const left = this.zoom.viewStart;
    const right = this.zoom.viewEnd;
    
    if (x >= left && x <= left + handleSize) {
      this.zoom.isResizingLeft = true;
      this.zoom.resizeStartX = x;
      this.zoom.resizeStartViewStart = left;
      this.zoom.resizeStartViewEnd = right;
      this.zoom.navCanvas.style.cursor = 'ew-resize';
      this._dragLastX = x;
      return;
    }
    
    if (x >= right - handleSize && x <= right) {
      this.zoom.isResizingRight = true;
      this.zoom.resizeStartX = x;
      this.zoom.resizeStartViewStart = left;
      this.zoom.resizeStartViewEnd = right;
      this.zoom.navCanvas.style.cursor = 'ew-resize';
      this._dragLastX = x;
      return;
    }
    
    this.zoom.isDragging = true;
    this.zoom.dragStartX = x;
    this.zoom.dragStartViewStart = left;
    this.zoom.dragStartViewEnd = right;
    this.zoom.navCanvas.style.cursor = 'grabbing';
    this._dragLastX = x;
  }

  navMouseMove(e) {
    const rect = this.zoom.navCanvas.getBoundingClientRect();
    const x = (e.clientX - rect.left) / rect.width;
    const clampedX = Math.max(0, Math.min(1, x));
    const handleSize = 0.02;
    
    const left = this.zoom.viewStart;
    const right = this.zoom.viewEnd;
    
    // Меняем курсор при наведении на границы
    if (!this.zoom.isDragging && !this.zoom.isResizingLeft && !this.zoom.isResizingRight) {
      if ((clampedX >= left && clampedX <= left + handleSize) || 
          (clampedX >= right - handleSize && clampedX <= right)) {
        this.zoom.navCanvas.style.cursor = 'ew-resize';
      } else {
        this.zoom.navCanvas.style.cursor = 'grab';
      }
      return;
    }
    
    // Ресайз левой границы
    if (this.zoom.isResizingLeft) {
      let newStart = Math.max(0, Math.min(right - 0.01, clampedX));
      if (right - newStart < 0.01) return;
      this.zoom.viewStart = newStart;
      this.renderNavigator();
      this.renderThrottled();
      return;
    }
    
    // Ресайз правой границы
    if (this.zoom.isResizingRight) {
      let newEnd = Math.min(1, Math.max(left + 0.01, clampedX));
      if (newEnd - left < 0.01) return;
      this.zoom.viewEnd = newEnd;
      this.renderNavigator();
      this.renderThrottled();
      return;
    }
    
    // Drag — используем оптимизированный метод
    this.navMoveDrag(e);
  }

  navMouseUp() {
    this.zoom.isDragging = false;
    this.zoom.isResizingLeft = false;
    this.zoom.isResizingRight = false;
    if (this.zoom.navCanvas) {
      this.zoom.navCanvas.style.cursor = 'grab';
    }
  }
}