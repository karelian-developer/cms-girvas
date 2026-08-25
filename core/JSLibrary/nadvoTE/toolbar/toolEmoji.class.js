'use strict';

import {Tool} from './tool.class.js';
import {Interactive} from '../../interactive.class.js';

export class ToolEmoji extends Tool {
  // Стандартный набор эмодзи
  static EMOJIS = [
    // Улыбки и эмоции
    { code: ':smile:', emoji: '😊', name: 'Улыбка' },
    { code: ':grin:', emoji: '😁', name: 'Широкая улыбка' },
    { code: ':joy:', emoji: '😂', name: 'Смех до слез' },
    { code: ':rofl:', emoji: '🤣', name: 'Катаюсь от смеха' },
    { code: ':wink:', emoji: '😉', name: 'Подмигивание' },
    { code: ':blush:', emoji: '😊', name: 'Смущение' },
    { code: ':heart_eyes:', emoji: '😍', name: 'Влюбленные глаза' },
    { code: ':kissing_heart:', emoji: '😘', name: 'Воздушный поцелуй' },
    { code: ':thinking:', emoji: '🤔', name: 'Задумчивость' },
    { code: ':neutral_face:', emoji: '😐', name: 'Нейтральное лицо' },
    { code: ':expressionless:', emoji: '😑', name: 'Без эмоций' },
    { code: ':smirk:', emoji: '😏', name: 'Ухмылка' },
    { code: ':unamused:', emoji: '😒', name: 'Недовольство' },
    { code: ':roll_eyes:', emoji: '🙄', name: 'Закатывание глаз' },
    { code: ':relieved:', emoji: '😌', name: 'Облегчение' },
    { code: ':pensive:', emoji: '😔', name: 'Задумчивость' },
    { code: ':sleepy:', emoji: '😪', name: 'Сонливость' },
    { code: ':sleeping:', emoji: '😴', name: 'Сон' },
    { code: ':mask:', emoji: '😷', name: 'Маска' },
    
    // Жесты
    { code: ':thumbsup:', emoji: '👍', name: 'Палец вверх' },
    { code: ':thumbsdown:', emoji: '👎', name: 'Палец вниз' },
    { code: ':clap:', emoji: '👏', name: 'Аплодисменты' },
    { code: ':wave:', emoji: '👋', name: 'Приветствие' },
    { code: ':ok_hand:', emoji: '👌', name: 'ОК' },
    { code: ':pray:', emoji: '🙏', name: 'Молитва' },
    { code: ':muscle:', emoji: '💪', name: 'Мускулы' },
    { code: ':point_up:', emoji: '☝️', name: 'Палец вверх' },
    { code: ':point_down:', emoji: '👇', name: 'Палец вниз' },
    { code: ':point_left:', emoji: '👈', name: 'Палец влево' },
    { code: ':point_right:', emoji: '👉', name: 'Палец вправо' },
    
    // Сердца и чувства
    { code: ':heart:', emoji: '❤️', name: 'Сердце' },
    { code: ':orange_heart:', emoji: '🧡', name: 'Оранжевое сердце' },
    { code: ':yellow_heart:', emoji: '💛', name: 'Желтое сердце' },
    { code: ':green_heart:', emoji: '💚', name: 'Зеленое сердце' },
    { code: ':blue_heart:', emoji: '💙', name: 'Синее сердце' },
    { code: ':purple_heart:', emoji: '💜', name: 'Фиолетовое сердце' },
    { code: ':broken_heart:', emoji: '💔', name: 'Разбитое сердце' },
    { code: ':sparkling_heart:', emoji: '💖', name: 'Сверкающее сердце' },
    { code: ':two_hearts:', emoji: '💕', name: 'Два сердца' },
    { code: ':heartbeat:', emoji: '💓', name: 'Биение сердца' },
    { code: ':heartpulse:', emoji: '💗', name: 'Пульс сердца' },
    
    // Животные
    { code: ':cat:', emoji: '🐱', name: 'Кошка' },
    { code: ':dog:', emoji: '🐶', name: 'Собака' },
    { code: ':mouse:', emoji: '🐭', name: 'Мышь' },
    { code: ':hamster:', emoji: '🐹', name: 'Хомяк' },
    { code: ':rabbit:', emoji: '🐰', name: 'Кролик' },
    { code: ':fox:', emoji: '🦊', name: 'Лиса' },
    { code: ':bear:', emoji: '🐻', name: 'Медведь' },
    { code: ':panda:', emoji: '🐼', name: 'Панда' },
    { code: ':koala:', emoji: '🐨', name: 'Коала' },
    { code: ':tiger:', emoji: '🐯', name: 'Тигр' },
    { code: ':lion:', emoji: '🦁', name: 'Лев' },
    { code: ':unicorn:', emoji: '🦄', name: 'Единорог' },
    
    // Еда и напитки
    { code: ':apple:', emoji: '🍎', name: 'Яблоко' },
    { code: ':pizza:', emoji: '🍕', name: 'Пицца' },
    { code: ':hamburger:', emoji: '🍔', name: 'Гамбургер' },
    { code: ':fries:', emoji: '🍟', name: 'Картофель фри' },
    { code: ':coffee:', emoji: '☕', name: 'Кофе' },
    { code: ':tea:', emoji: '🍵', name: 'Чай' },
    { code: ':beer:', emoji: '🍺', name: 'Пиво' },
    { code: ':wine:', emoji: '🍷', name: 'Вино' },
    { code: ':cake:', emoji: '🍰', name: 'Торт' },
    { code: ':icecream:', emoji: '🍦', name: 'Мороженое' },
    { code: ':cookie:', emoji: '🍪', name: 'Печенье' },
    { code: ':chocolate:', emoji: '🍫', name: 'Шоколад' },
    
    // Активности и праздники
    { code: ':tada:', emoji: '🎉', name: 'Праздник' },
    { code: ':confetti:', emoji: '🎊', name: 'Конфетти' },
    { code: ':balloon:', emoji: '🎈', name: 'Шарик' },
    { code: ':gift:', emoji: '🎁', name: 'Подарок' },
    { code: ':star:', emoji: '⭐', name: 'Звезда' },
    { code: ':sparkles:', emoji: '✨', name: 'Искры' },
    { code: ':fire:', emoji: '🔥', name: 'Огонь' },
    { code: ':zap:', emoji: '⚡', name: 'Молния' },
    { code: ':rainbow:', emoji: '🌈', name: 'Радуга' },
    { code: ':sunny:', emoji: '☀️', name: 'Солнце' },
    { code: ':moon:', emoji: '🌙', name: 'Луна' },
    { code: ':cloud:', emoji: '☁️', name: 'Облако' },
    
    // Символы
    { code: ':check:', emoji: '✅', name: 'Галочка' },
    { code: ':x:', emoji: '❌', name: 'Крестик' },
    { code: ':warning:', emoji: '⚠️', name: 'Предупреждение' },
    { code: ':question:', emoji: '❓', name: 'Вопрос' },
    { code: ':exclamation:', emoji: '❗', name: 'Восклицание' },
    { code: ':100:', emoji: '💯', name: 'Сто баллов' },
    { code: ':copyright:', emoji: '©️', name: 'Копирайт' },
    { code: ':registered:', emoji: '®️', name: 'Зарегистрировано' },
    { code: ':tm:', emoji: '™️', name: 'Торговая марка' }
  ];
  
  constructor(editor, element) {
    super(editor, {
      name: 'emoji',
      type: 'button',
      iconPath: '/core/JSLibrary/nadvoTE/images/icons/toolbar/emoji.svg',
      element: element,
      title: 'Вставить эмодзи'
    });
    
    this.initClickEvent();
  }
  
  initClickEvent() {
    super.addClickEvent(() => {
      console.log(`[NADVO TE] Tool ${this.name} clicked!`);
      this.showEmojiPicker();
    });
  }
  
  /**
   * Показ окна выбора эмодзи
   */
  showEmojiPicker() {
    const locale = this.editor.localeData;
    
    // Создаем контейнер с плиткой эмодзи
    const emojiGrid = document.createElement('div');
    emojiGrid.classList.add('nadvo-te__emoji-grid');
    emojiGrid.style.cssText = `
      display: grid;
      grid-template-columns: repeat(8, 1fr);
      gap: 8px;
      max-height: 400px;
      overflow-y: auto;
      padding: 10px;
    `;
    
    // Создаем плитки эмодзи
    ToolEmoji.EMOJIS.forEach((emojiData) => {
      const emojiButton = document.createElement('button');
      emojiButton.setAttribute('type', 'button');
      emojiButton.setAttribute('title', `${emojiData.name} (${emojiData.code})`);
      emojiButton.classList.add('nadvo-te__emoji-button');
      emojiButton.style.cssText = `
        font-size: 24px;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background: white;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
      `;
      
      emojiButton.textContent = emojiData.emoji;
      
      // Добавляем hover эффект
      emojiButton.addEventListener('mouseenter', () => {
        emojiButton.style.backgroundColor = '#f0f0f0';
        emojiButton.style.borderColor = '#999';
      });
      
      emojiButton.addEventListener('mouseleave', () => {
        emojiButton.style.backgroundColor = 'white';
        emojiButton.style.borderColor = '#ddd';
      });
      
      // Обработка клика
      emojiButton.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        
        // Вставляем код эмодзи
        this.insertEmoji(emojiData.code);
        
        // Закрываем модальное окно
        if (this.modal) {
          this.modal.target.close();
        }
      });
      
      emojiGrid.appendChild(emojiButton);
    });
    
    // Создаем поиск
    const searchInput = document.createElement('input');
    searchInput.setAttribute('type', 'text');
    searchInput.setAttribute('placeholder', 'Поиск эмодзи...');
    searchInput.classList.add('form__input');
    searchInput.style.cssText = `
      width: 100%;
      margin-bottom: 10px;
      padding: 8px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 14px;
    `;
    
    // Обработка поиска
    searchInput.addEventListener('input', () => {
      const searchTerm = searchInput.value.toLowerCase();
      
      const emojiButtons = emojiGrid.querySelectorAll('.nadvo-te__emoji-button');
      emojiButtons.forEach((button, index) => {
        const emojiData = ToolEmoji.EMOJIS[index];
        
        if (searchTerm === '' || 
            emojiData.name.toLowerCase().includes(searchTerm) || 
            emojiData.code.toLowerCase().includes(searchTerm)) {
          button.style.display = 'flex';
        } else {
          button.style.display = 'none';
        }
      });
    });
    
    // Собираем содержимое модального окна
    const contentContainer = document.createElement('div');
    contentContainer.appendChild(searchInput);
    contentContainer.appendChild(emojiGrid);
    
    // Создаем модальное окно
    const modalTitle = locale.NTE_TOOL_EMOJI_TITLE || 'Выберите эмодзи';
    const interactiveModal = new Interactive('modal', {
      title: modalTitle,
      content: contentContainer,
      width: 500
    });
    
    // Добавляем кнопку отмены
    interactiveModal.target.addButton('Отмена', () => {
      interactiveModal.target.close();
    });
    
    interactiveModal.assembly();
    document.body.appendChild(interactiveModal.target.element);
    interactiveModal.target.show();
    
    // Сохраняем ссылку на модальное окно
    this.modal = interactiveModal;
    
    // Фокус на поиск
    setTimeout(() => {
      searchInput.focus();
    }, 100);
  }
  
  /**
   * Вставка эмодзи в редактор
   */
  insertEmoji(emojiCode) {
    const textarea = this.editor.textarea.element;
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    
    // Вставляем код эмодзи с пробелами
    const insertText = ` ${emojiCode} `;
    
    textarea.value = textarea.value.substring(0, start) + insertText + textarea.value.substring(end);
    
    // Устанавливаем курсор после вставленного эмодзи
    const newCursorPos = start + insertText.length;
    textarea.selectionStart = newCursorPos;
    textarea.selectionEnd = newCursorPos;
    textarea.focus();
    
    // Оповещаем редактор об изменении
    if (this.editor.onContentChange) {
      this.editor.onContentChange();
    }
    
    console.log(`[NADVO TE] Emoji ${emojiCode} inserted`);
  }
}