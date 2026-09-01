// core/JSLibrary/nadvoTE/toolbar/toolEmoji.class.js

'use strict';

import {Tool} from './tool.class.js';
import {Interactive} from '../../interactive.class.js';

export class ToolEmoji extends Tool {
  // Категории для отображения
  static CATEGORIES = [
    { id: 'all', name: 'Все', icon: '🌟' },
    { id: 'emotions', name: 'Эмоции', icon: '😊' },
    { id: 'gestures', name: 'Жесты', icon: '👍' },
    { id: 'hearts', name: 'Сердца', icon: '❤️' },
    { id: 'animals', name: 'Животные', icon: '🐱' },
    { id: 'food', name: 'Еда', icon: '🍕' },
    { id: 'activities', name: 'Активности', icon: '🎉' },
    { id: 'symbols', name: 'Символы', icon: '✅' },
    { id: 'flags', name: 'Флаги', icon: '🇷🇺' },
  ];
  
  // Стандартный набор эмодзи
  static EMOJIS = [
    // Улыбки и эмоции
    { code: ':smile:', emoji: '😊', name: 'Улыбка', category: 'emotions' },
    { code: ':grin:', emoji: '😁', name: 'Широкая улыбка', category: 'emotions' },
    { code: ':joy:', emoji: '😂', name: 'Смех до слез', category: 'emotions' },
    { code: ':rofl:', emoji: '🤣', name: 'Катаюсь от смеха', category: 'emotions' },
    { code: ':wink:', emoji: '😉', name: 'Подмигивание', category: 'emotions' },
    { code: ':blush:', emoji: '😊', name: 'Смущение', category: 'emotions' },
    { code: ':heart_eyes:', emoji: '😍', name: 'Влюбленные глаза', category: 'emotions' },
    { code: ':kissing_heart:', emoji: '😘', name: 'Воздушный поцелуй', category: 'emotions' },
    { code: ':thinking:', emoji: '🤔', name: 'Задумчивость', category: 'emotions' },
    { code: ':neutral_face:', emoji: '😐', name: 'Нейтральное лицо', category: 'emotions' },
    { code: ':expressionless:', emoji: '😑', name: 'Без эмоций', category: 'emotions' },
    { code: ':smirk:', emoji: '😏', name: 'Ухмылка', category: 'emotions' },
    { code: ':unamused:', emoji: '😒', name: 'Недовольство', category: 'emotions' },
    { code: ':roll_eyes:', emoji: '🙄', name: 'Закатывание глаз', category: 'emotions' },
    { code: ':relieved:', emoji: '😌', name: 'Облегчение', category: 'emotions' },
    { code: ':pensive:', emoji: '😔', name: 'Задумчивость', category: 'emotions' },
    { code: ':sleepy:', emoji: '😪', name: 'Сонливость', category: 'emotions' },
    { code: ':sleeping:', emoji: '😴', name: 'Сон', category: 'emotions' },
    { code: ':mask:', emoji: '😷', name: 'Маска', category: 'emotions' },
    
    // Жесты
    { code: ':thumbsup:', emoji: '👍', name: 'Палец вверх', category: 'gestures' },
    { code: ':thumbsdown:', emoji: '👎', name: 'Палец вниз', category: 'gestures' },
    { code: ':clap:', emoji: '👏', name: 'Аплодисменты', category: 'gestures' },
    { code: ':wave:', emoji: '👋', name: 'Приветствие', category: 'gestures' },
    { code: ':ok_hand:', emoji: '👌', name: 'ОК', category: 'gestures' },
    { code: ':pray:', emoji: '🙏', name: 'Молитва', category: 'gestures' },
    { code: ':muscle:', emoji: '💪', name: 'Мускулы', category: 'gestures' },
    { code: ':point_up:', emoji: '☝️', name: 'Палец вверх', category: 'gestures' },
    { code: ':point_down:', emoji: '👇', name: 'Палец вниз', category: 'gestures' },
    { code: ':point_left:', emoji: '👈', name: 'Палец влево', category: 'gestures' },
    { code: ':point_right:', emoji: '👉', name: 'Палец вправо', category: 'gestures' },
    
    // Сердца и чувства
    { code: ':heart:', emoji: '❤️', name: 'Сердце', category: 'hearts' },
    { code: ':orange_heart:', emoji: '🧡', name: 'Оранжевое сердце', category: 'hearts' },
    { code: ':yellow_heart:', emoji: '💛', name: 'Желтое сердце', category: 'hearts' },
    { code: ':green_heart:', emoji: '💚', name: 'Зеленое сердце', category: 'hearts' },
    { code: ':blue_heart:', emoji: '💙', name: 'Синее сердце', category: 'hearts' },
    { code: ':purple_heart:', emoji: '💜', name: 'Фиолетовое сердце', category: 'hearts' },
    { code: ':broken_heart:', emoji: '💔', name: 'Разбитое сердце', category: 'hearts' },
    { code: ':sparkling_heart:', emoji: '💖', name: 'Сверкающее сердце', category: 'hearts' },
    { code: ':two_hearts:', emoji: '💕', name: 'Два сердца', category: 'hearts' },
    { code: ':heartbeat:', emoji: '💓', name: 'Биение сердца', category: 'hearts' },
    { code: ':heartpulse:', emoji: '💗', name: 'Пульс сердца', category: 'hearts' },
    
    // Животные
    { code: ':cat:', emoji: '🐱', name: 'Кошка', category: 'animals' },
    { code: ':dog:', emoji: '🐶', name: 'Собака', category: 'animals' },
    { code: ':mouse:', emoji: '🐭', name: 'Мышь', category: 'animals' },
    { code: ':hamster:', emoji: '🐹', name: 'Хомяк', category: 'animals' },
    { code: ':rabbit:', emoji: '🐰', name: 'Кролик', category: 'animals' },
    { code: ':fox:', emoji: '🦊', name: 'Лиса', category: 'animals' },
    { code: ':bear:', emoji: '🐻', name: 'Медведь', category: 'animals' },
    { code: ':panda:', emoji: '🐼', name: 'Панда', category: 'animals' },
    { code: ':koala:', emoji: '🐨', name: 'Коала', category: 'animals' },
    { code: ':tiger:', emoji: '🐯', name: 'Тигр', category: 'animals' },
    { code: ':lion:', emoji: '🦁', name: 'Лев', category: 'animals' },
    { code: ':unicorn:', emoji: '🦄', name: 'Единорог', category: 'animals' },
    
    // Еда и напитки
    { code: ':apple:', emoji: '🍎', name: 'Яблоко', category: 'food' },
    { code: ':pizza:', emoji: '🍕', name: 'Пицца', category: 'food' },
    { code: ':hamburger:', emoji: '🍔', name: 'Гамбургер', category: 'food' },
    { code: ':fries:', emoji: '🍟', name: 'Картофель фри', category: 'food' },
    { code: ':coffee:', emoji: '☕', name: 'Кофе', category: 'food' },
    { code: ':tea:', emoji: '🍵', name: 'Чай', category: 'food' },
    { code: ':beer:', emoji: '🍺', name: 'Пиво', category: 'food' },
    { code: ':wine:', emoji: '🍷', name: 'Вино', category: 'food' },
    { code: ':cake:', emoji: '🍰', name: 'Торт', category: 'food' },
    { code: ':icecream:', emoji: '🍦', name: 'Мороженое', category: 'food' },
    { code: ':cookie:', emoji: '🍪', name: 'Печенье', category: 'food' },
    { code: ':chocolate:', emoji: '🍫', name: 'Шоколад', category: 'food' },
    
    // Активности и праздники
    { code: ':tada:', emoji: '🎉', name: 'Праздник', category: 'activities' },
    { code: ':confetti:', emoji: '🎊', name: 'Конфетти', category: 'activities' },
    { code: ':balloon:', emoji: '🎈', name: 'Шарик', category: 'activities' },
    { code: ':gift:', emoji: '🎁', name: 'Подарок', category: 'activities' },
    { code: ':star:', emoji: '⭐', name: 'Звезда', category: 'activities' },
    { code: ':sparkles:', emoji: '✨', name: 'Искры', category: 'activities' },
    { code: ':fire:', emoji: '🔥', name: 'Огонь', category: 'activities' },
    { code: ':zap:', emoji: '⚡', name: 'Молния', category: 'activities' },
    { code: ':rainbow:', emoji: '🌈', name: 'Радуга', category: 'activities' },
    { code: ':sunny:', emoji: '☀️', name: 'Солнце', category: 'activities' },
    { code: ':moon:', emoji: '🌙', name: 'Луна', category: 'activities' },
    { code: ':cloud:', emoji: '☁️', name: 'Облако', category: 'activities' },
    
    // Символы
    { code: ':check:', emoji: '✅', name: 'Галочка', category: 'symbols' },
    { code: ':x:', emoji: '❌', name: 'Крестик', category: 'symbols' },
    { code: ':warning:', emoji: '⚠️', name: 'Предупреждение', category: 'symbols' },
    { code: ':question:', emoji: '❓', name: 'Вопрос', category: 'symbols' },
    { code: ':exclamation:', emoji: '❗', name: 'Восклицание', category: 'symbols' },
    { code: ':100:', emoji: '💯', name: 'Сто баллов', category: 'symbols' },
    { code: ':copyright:', emoji: '©️', name: 'Копирайт', category: 'symbols' },
    { code: ':registered:', emoji: '®️', name: 'Зарегистрировано', category: 'symbols' },
    { code: ':tm:', emoji: '™️', name: 'Торговая марка', category: 'symbols' },
    
    // Флаги стран
    { code: ':flag_ru:', emoji: '🇷🇺', name: 'Россия', category: 'flags' },
    { code: ':flag_by:', emoji: '🇧🇾', name: 'Беларусь', category: 'flags' },
    { code: ':flag_kz:', emoji: '🇰🇿', name: 'Казахстан', category: 'flags' },
    { code: ':flag_am:', emoji: '🇦🇲', name: 'Армения', category: 'flags' },
    { code: ':flag_az:', emoji: '🇦🇿', name: 'Азербайджан', category: 'flags' },
    { code: ':flag_ge:', emoji: '🇬🇪', name: 'Грузия', category: 'flags' },
    { code: ':flag_ua:', emoji: '🇺🇦', name: 'Украина', category: 'flags' },
    { code: ':flag_uz:', emoji: '🇺🇿', name: 'Узбекистан', category: 'flags' },
    { code: ':flag_kg:', emoji: '🇰🇬', name: 'Киргизия', category: 'flags' },
    { code: ':flag_tj:', emoji: '🇹🇯', name: 'Таджикистан', category: 'flags' },
    { code: ':flag_tm:', emoji: '🇹🇲', name: 'Туркменистан', category: 'flags' },
    { code: ':flag_md:', emoji: '🇲🇩', name: 'Молдова', category: 'flags' },
    { code: ':flag_lt:', emoji: '🇱🇹', name: 'Литва', category: 'flags' },
    { code: ':flag_lv:', emoji: '🇱🇻', name: 'Латвия', category: 'flags' },
    { code: ':flag_ee:', emoji: '🇪🇪', name: 'Эстония', category: 'flags' },
    { code: ':flag_gb:', emoji: '🇬🇧', name: 'Великобритания', category: 'flags' },
    { code: ':flag_de:', emoji: '🇩🇪', name: 'Германия', category: 'flags' },
    { code: ':flag_fr:', emoji: '🇫🇷', name: 'Франция', category: 'flags' },
    { code: ':flag_it:', emoji: '🇮🇹', name: 'Италия', category: 'flags' },
    { code: ':flag_es:', emoji: '🇪🇸', name: 'Испания', category: 'flags' },
    { code: ':flag_pt:', emoji: '🇵🇹', name: 'Португалия', category: 'flags' },
    { code: ':flag_nl:', emoji: '🇳🇱', name: 'Нидерланды', category: 'flags' },
    { code: ':flag_be:', emoji: '🇧🇪', name: 'Бельгия', category: 'flags' },
    { code: ':flag_ch:', emoji: '🇨🇭', name: 'Швейцария', category: 'flags' },
    { code: ':flag_at:', emoji: '🇦🇹', name: 'Австрия', category: 'flags' },
    { code: ':flag_pl:', emoji: '🇵🇱', name: 'Польша', category: 'flags' },
    { code: ':flag_cz:', emoji: '🇨🇿', name: 'Чехия', category: 'flags' },
    { code: ':flag_sk:', emoji: '🇸🇰', name: 'Словакия', category: 'flags' },
    { code: ':flag_hu:', emoji: '🇭🇺', name: 'Венгрия', category: 'flags' },
    { code: ':flag_ro:', emoji: '🇷🇴', name: 'Румыния', category: 'flags' },
    { code: ':flag_bg:', emoji: '🇧🇬', name: 'Болгария', category: 'flags' },
    { code: ':flag_gr:', emoji: '🇬🇷', name: 'Греция', category: 'flags' },
    { code: ':flag_se:', emoji: '🇸🇪', name: 'Швеция', category: 'flags' },
    { code: ':flag_no:', emoji: '🇳🇴', name: 'Норвегия', category: 'flags' },
    { code: ':flag_dk:', emoji: '🇩🇰', name: 'Дания', category: 'flags' },
    { code: ':flag_fi:', emoji: '🇫🇮', name: 'Финляндия', category: 'flags' },
    { code: ':flag_ie:', emoji: '🇮🇪', name: 'Ирландия', category: 'flags' },
    { code: ':flag_cn:', emoji: '🇨🇳', name: 'Китай', category: 'flags' },
    { code: ':flag_jp:', emoji: '🇯🇵', name: 'Япония', category: 'flags' },
    { code: ':flag_kr:', emoji: '🇰🇷', name: 'Южная Корея', category: 'flags' },
    { code: ':flag_kp:', emoji: '🇰🇵', name: 'Северная Корея', category: 'flags' },
    { code: ':flag_in:', emoji: '🇮🇳', name: 'Индия', category: 'flags' },
    { code: ':flag_vn:', emoji: '🇻🇳', name: 'Вьетнам', category: 'flags' },
    { code: ':flag_th:', emoji: '🇹🇭', name: 'Таиланд', category: 'flags' },
    { code: ':flag_id:', emoji: '🇮🇩', name: 'Индонезия', category: 'flags' },
    { code: ':flag_my:', emoji: '🇲🇾', name: 'Малайзия', category: 'flags' },
    { code: ':flag_ph:', emoji: '🇵🇭', name: 'Филиппины', category: 'flags' },
    { code: ':flag_sg:', emoji: '🇸🇬', name: 'Сингапур', category: 'flags' },
    { code: ':flag_mn:', emoji: '🇲🇳', name: 'Монголия', category: 'flags' },
    { code: ':flag_us:', emoji: '🇺🇸', name: 'США', category: 'flags' },
    { code: ':flag_ca:', emoji: '🇨🇦', name: 'Канада', category: 'flags' },
    { code: ':flag_mx:', emoji: '🇲🇽', name: 'Мексика', category: 'flags' },
    { code: ':flag_br:', emoji: '🇧🇷', name: 'Бразилия', category: 'flags' },
    { code: ':flag_ar:', emoji: '🇦🇷', name: 'Аргентина', category: 'flags' },
    { code: ':flag_cl:', emoji: '🇨🇱', name: 'Чили', category: 'flags' },
    { code: ':flag_co:', emoji: '🇨🇴', name: 'Колумбия', category: 'flags' },
    { code: ':flag_pe:', emoji: '🇵🇪', name: 'Перу', category: 'flags' },
    { code: ':flag_cu:', emoji: '🇨🇺', name: 'Куба', category: 'flags' },
    { code: ':flag_eg:', emoji: '🇪🇬', name: 'Египет', category: 'flags' },
    { code: ':flag_za:', emoji: '🇿🇦', name: 'ЮАР', category: 'flags' },
    { code: ':flag_tr:', emoji: '🇹🇷', name: 'Турция', category: 'flags' },
    { code: ':flag_il:', emoji: '🇮🇱', name: 'Израиль', category: 'flags' },
    { code: ':flag_sa:', emoji: '🇸🇦', name: 'Саудовская Аравия', category: 'flags' },
    { code: ':flag_ae:', emoji: '🇦🇪', name: 'ОАЭ', category: 'flags' },
    { code: ':flag_ir:', emoji: '🇮🇷', name: 'Иран', category: 'flags' },
    { code: ':flag_iq:', emoji: '🇮🇶', name: 'Ирак', category: 'flags' },
    { code: ':flag_au:', emoji: '🇦🇺', name: 'Австралия', category: 'flags' },
    { code: ':flag_nz:', emoji: '🇳🇿', name: 'Новая Зеландия', category: 'flags' },
    { code: ':flag_eu:', emoji: '🇪🇺', name: 'Евросоюз', category: 'flags' },
    { code: ':flag_un:', emoji: '🇺🇳', name: 'ООН', category: 'flags' },
    { code: ':rainbow_flag:', emoji: '🏳️‍🌈', name: 'Радужный флаг', category: 'flags' },
    { code: ':white_flag:', emoji: '🏳️', name: 'Белый флаг', category: 'flags' },
    { code: ':black_flag:', emoji: '🏴', name: 'Черный флаг', category: 'flags' },
    { code: ':pirate_flag:', emoji: '🏴‍☠️', name: 'Пиратский флаг', category: 'flags' },
    { code: ':checkered_flag:', emoji: '🏁', name: 'Клетчатый флаг', category: 'flags' },
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
    
    // Создаем поиск
    const searchInput = document.createElement('input');
    searchInput.setAttribute('type', 'text');
    searchInput.setAttribute('placeholder', 'Поиск эмодзи...');
    searchInput.classList.add('form__input');
    searchInput.classList.add('nadvo-te__emoji-search');
    
    // Создаем переключатели категорий через компонент Tabs
    const interactiveTabs = new Interactive('tabs', {
      type: 'pills',
      orientation: 'horizontal'
    });
    
    // Добавляем категории
    ToolEmoji.CATEGORIES.forEach((category) => {
      interactiveTabs.target.addItem(
        `${category.name}`,
        category.id,
        {
          icon: category.icon,
          title: category.name
        }
      );
    });
    
    // Устанавливаем обработчик изменения категории
    let currentCategory = 'all';
    
    interactiveTabs.target.onChange((value) => {
      currentCategory = value;
      displayEmojis(currentCategory, searchInput.value);
    });
    
    // Собираем переключатели
    interactiveTabs.assembly();
    
    // Функция для отображения эмодзи
    const displayEmojis = (category = 'all', searchTerm = '') => {
      emojiGrid.innerHTML = '';
      
      const filteredEmojis = ToolEmoji.EMOJIS.filter(emojiData => {
        const matchesCategory = category === 'all' || emojiData.category === category;
        const matchesSearch = searchTerm === '' || 
          emojiData.name.toLowerCase().includes(searchTerm.toLowerCase()) || 
          emojiData.code.toLowerCase().includes(searchTerm.toLowerCase());
        
        return matchesCategory && matchesSearch;
      });
      
      filteredEmojis.forEach((emojiData) => {
        const emojiButton = this.createEmojiButton(emojiData);
        emojiGrid.appendChild(emojiButton);
      });
    };
    
    // Обработка поиска
    searchInput.addEventListener('input', () => {
      displayEmojis(currentCategory, searchInput.value);
    });
    
    // Начальное отображение
    displayEmojis('all');
    
    // Собираем содержимое модального окна
    const contentContainer = document.createElement('div');
    contentContainer.classList.add('nadvo-te__emoji-container');
    contentContainer.appendChild(searchInput);
    contentContainer.appendChild(interactiveTabs.target.element);
    contentContainer.appendChild(emojiGrid);
    
    // Создаем модальное окно
    const modalTitle = locale.NTE_TOOL_EMOJI_TITLE || 'Выберите эмодзи';
    const interactiveModal = new Interactive('modal', {
      title: modalTitle,
      content: contentContainer,
      width: 550
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
   * Создание кнопки эмодзи
   */
  createEmojiButton(emojiData) {
    const emojiButton = document.createElement('button');
    emojiButton.setAttribute('type', 'button');
    emojiButton.setAttribute('title', `${emojiData.name} (${emojiData.code})`);
    emojiButton.classList.add('nadvo-te__emoji-button');
    
    emojiButton.textContent = emojiData.emoji;
    
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
    
    return emojiButton;
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