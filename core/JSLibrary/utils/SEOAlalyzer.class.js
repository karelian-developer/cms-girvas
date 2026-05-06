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

export class SEOAnalyzer {
  constructor(localeData) {
    this.localeData = localeData;
    this.config = {
      titleLength: { min: 30, max: 60, recommended: 55 },
      descriptionLength: { min: 120, max: 160, recommended: 155 },
      keywordsCount: { min: 5, max: 15 },
      contentLength: { min: 300, max: 3000, recommended: 2000 },
      headingsHierarchy: true,
      imageAltRequired: true,
      linkTitleRequired: true
    };
  }

  analyze(SEOData) {
    const results = {
      title: this.analyzeTitle(SEOData.title, SEOData.SEOTitle),
      description: this.analyzeDescription(SEOData.description, SEOData.SEODescription),
      keywords: this.analyzeKeywords(SEOData.keywords),
      content: this.analyzeContent(SEOData.content, SEOData.title, SEOData.SEOTitle),
      url: this.analyzeUrl(SEOData.name),
      overallScore: 0
    };

    results.overallScore = this.calculateOverallScore(results);
    return results;
  }

  analyzeTitle(title, SEOTitle) {
    const issues = [];
    const warnings = [];
    const success = [];
    const primaryTitle = SEOTitle || title;
    
    // Проверка длины
    const length = primaryTitle.length;
    if (length === 0) {
      issues.push('Заголовок отсутствует');
    } else if (length < this.config.titleLength.min) {
      issues.push(`Заголовок слишком короткий (${length}/${this.config.titleLength.min} символов)`);
    } else if (length > this.config.titleLength.max) {
      warnings.push(`Заголовок слишком длинный (${length}/${this.config.titleLength.max} символов)`);
    } else {
      success.push(`Оптимальная длина заголовка (${length} символов)`);
    }

    // Проверка на наличие ключевых слов в начале
    if (primaryTitle && primaryTitle.length > 0) {
      const firstWord = primaryTitle.split(' ')[0].toLowerCase();
      const stopWords = ['как', 'что', 'где', 'когда', 'почему', 'a', 'the', 'in', 'on', 'at', 'to', 'for'];
      
      if (stopWords.includes(firstWord)) {
        warnings.push('Заголовок начинается со стоп-слова');
      } else {
        success.push('Заголовок начинается с сильного слова');
      }
    }

    // Проверка уникальности SEO title
    if (SEOTitle && title && SEOTitle === title) {
      warnings.push('SEO title совпадает с заголовком страницы');
    } else if (SEOTitle && title) {
      success.push('SEO title отличается от заголовка страницы');
    }

    // Проверка на наличие разделителей
    if (primaryTitle && (primaryTitle.includes('|') || primaryTitle.includes('-') || primaryTitle.includes('–'))) {
      success.push('Заголовок содержит разделители');
    }

    return {
      score: this.calculateSectionScore(issues.length, warnings.length, success.length),
      issues,
      warnings,
      success,
      value: primaryTitle || ''
    };
  }

  analyzeDescription(description, SEODescription) {
    const issues = [];
    const warnings = [];
    const success = [];
    const primaryDescription = SEODescription || description;
    
    const length = primaryDescription ? primaryDescription.length : 0;
    
    if (length === 0) {
      issues.push('Meta description отсутствует');
    } else if (length < this.config.descriptionLength.min) {
      issues.push(`Описание слишком короткое (${length}/${this.config.descriptionLength.min} символов)`);
    } else if (length > this.config.descriptionLength.max) {
      warnings.push(`Описание слишком длинное (${length}/${this.config.descriptionLength.max} символов)`);
    } else {
      success.push(`Оптимальная длина описания (${length} символов)`);
    }

    // Проверка на CTA (призыв к действию)
    if (primaryDescription) {
      const ctaWords = ['узнать', 'заказать', 'купить', 'получить', 'скачать', 'читать', 'подробнее', 'learn', 'buy', 'get', 'download', 'read'];
      const hasCTA = ctaWords.some(word => primaryDescription.toLowerCase().includes(word));
      
      if (hasCTA) {
        success.push('Описание содержит призыв к действию');
      } else {
        warnings.push('Добавьте призыв к действию в описание');
      }
    }

    return {
      score: this.calculateSectionScore(issues.length, warnings.length, success.length),
      issues,
      warnings,
      success,
      value: primaryDescription || ''
    };
  }

  analyzeKeywords(keywords) {
    const issues = [];
    const warnings = [];
    const success = [];
    
    if (!keywords || keywords.trim() === '') {
      issues.push('Ключевые слова отсутствуют');
      return {
        score: 0,
        issues,
        warnings,
        success,
        value: ''
      };
    }

    const keywordList = keywords.split(',').map(k => k.trim()).filter(k => k.length > 0);
    
    if (keywordList.length < this.config.keywordsCount.min) {
      warnings.push(`Мало ключевых слов (${keywordList.length}/${this.config.keywordsCount.min})`);
    } else if (keywordList.length > this.config.keywordsCount.max) {
      warnings.push(`Слишком много ключевых слов (${keywordList.length}/${this.config.keywordsCount.max})`);
    } else {
      success.push(`Оптимальное количество ключевых слов (${keywordList.length})`);
    }

    // Проверка на длину ключевых слов
    const longKeywords = keywordList.filter(k => k.length > 60);
    if (longKeywords.length > 0) {
      issues.push(`${longKeywords.length} ключевых слов слишком длинные (>60 символов)`);
    }

    // Проверка на повторения
    const duplicates = keywordList.filter((item, index) => keywordList.indexOf(item) !== index);
    if (duplicates.length > 0) {
      warnings.push('Обнаружены повторяющиеся ключевые слова');
    }

    return {
      score: this.calculateSectionScore(issues.length, warnings.length, success.length),
      issues,
      warnings,
      success,
      value: keywords,
      keywordList
    };
  }

  analyzeContent(content, title, SEOTitle) {
    const issues = [];
    const warnings = [];
    const success = [];
    
    if (!content || content.trim() === '') {
      issues.push('Контент отсутствует');
      return {
        score: 0,
        issues,
        warnings,
        success,
        markdownAnalysis: {}
      };
    }

    // Анализ Markdown
    const markdownAnalysis = this.analyzeMarkdown(content);
    
    // Длина контента (без учета Markdown синтаксиса)
    const plainText = this.stripMarkdown(content);
    const length = plainText.length;
    
    if (length < this.config.contentLength.min) {
      issues.push(`Контент слишком короткий (${length}/${this.config.contentLength.min} символов)`);
    } else if (length > this.config.contentLength.max) {
      warnings.push(`Контент очень длинный (${length} символов)`);
    } else {
      success.push(`Оптимальная длина контента (${length} символов)`);
    }

    // Проверка заголовков
    if (markdownAnalysis.headings.length === 0) {
      issues.push('Отсутствуют заголовки в контенте');
    } else {
      success.push(`Найдено ${markdownAnalysis.headings.length} заголовков`);
      
      // Проверка иерархии заголовков
      if (this.config.headingsHierarchy) {
        const hierarchyIssue = this.checkHeadingHierarchy(markdownAnalysis.headings);
        if (hierarchyIssue) {
          warnings.push(hierarchyIssue);
        }
      }
      
      // Проверка наличия H1
      if (!markdownAnalysis.headings.some(h => h.level === 1)) {
        warnings.push('Отсутствует H1 заголовок в контенте');
      }
    }

    // Проверка изображений
    if (markdownAnalysis.images.length === 0) {
      warnings.push('В контенте отсутствуют изображения');
    } else {
      success.push(`Найдено ${markdownAnalysis.images.length} изображений`);
      
      // Проверка alt текста
      const imagesWithoutAlt = markdownAnalysis.images.filter(img => !img.alt || img.alt.trim() === '');
      if (imagesWithoutAlt.length > 0 && this.config.imageAltRequired) {
        issues.push(`${imagesWithoutAlt.length} изображений без alt текста`);
      }
      
      // Проверка ключевых слов в alt
      const imagesWithKeywords = markdownAnalysis.images.filter(img => 
        img.alt && img.alt.length > 0
      );
      if (imagesWithKeywords.length > 0) {
        success.push('Изображения содержат alt текст');
      }
    }

    // Проверка ссылок
    if (markdownAnalysis.links.length === 0) {
      warnings.push('В контенте отсутствуют ссылки');
    } else {
      success.push(`Найдено ${markdownAnalysis.links.length} ссылок`);
      
      // Проверка title атрибутов
      const linksWithoutTitle = markdownAnalysis.links.filter(link => !link.title);
      if (linksWithoutTitle.length > 0 && this.config.linkTitleRequired) {
        warnings.push(`${linksWithoutTitle.length} ссылок без title атрибута`);
      }
      
      // Проверка внешних ссылок с nofollow
      const externalLinks = markdownAnalysis.links.filter(link => 
        link.url.startsWith('http') || link.url.startsWith('www')
      );
      if (externalLinks.length > 0) {
        warnings.push(`${externalLinks.length} внешних ссылок. Рекомендуется добавить rel="nofollow"`);
      }
    }

    // Проверка вхождения ключевых слов в начале контента
    if (title) {
      const firstParagraph = plainText.split('\n\n')[0];
      const titleWords = title.toLowerCase().split(' ').filter(w => w.length > 3);
      const keywordInIntro = titleWords.some(word => firstParagraph.toLowerCase().includes(word));
      
      if (keywordInIntro) {
        success.push('Ключевые слова встречаются в начале контента');
      } else {
        warnings.push('Ключевые слова не найдены в первом абзаце');
      }
    }

    // Проверка плотности текста со ссылками
    const linkTextLength = markdownAnalysis.links.reduce((sum, link) => sum + link.text.length, 0);
    const linkDensity = length > 0 ? (linkTextLength / length) * 100 : 0;
    if (linkDensity > 10) {
      warnings.push(`Высокая плотность ссылок: ${linkDensity.toFixed(1)}%`);
    }

    return {
      score: this.calculateSectionScore(issues.length, warnings.length, success.length),
      issues,
      warnings,
      success,
      markdownAnalysis,
      length
    };
  }

  analyzeMarkdown(content) {
    const result = {
      headings: [],
      images: [],
      links: [],
      lists: [],
      codeBlocks: []
    };

    if (!content) return result;

    // Поиск заголовков
    const headingRegex = /^(#{1,6})\s+(.+)$/gm;
    let match;
    while ((match = headingRegex.exec(content)) !== null) {
      result.headings.push({
        level: match[1].length,
        text: match[2].trim()
      });
    }

    // Поиск изображений
    const imageRegex = /!\[([^\]]*)\]\(([^)\s]+)(?:\s+"([^"]*)")?\)/g;
    while ((match = imageRegex.exec(content)) !== null) {
      result.images.push({
        alt: match[1],
        url: match[2],
        title: match[3] || null
      });
    }

    // Поиск ссылок (исключая изображения)
    const linkRegex = /(?<!!)\[([^\]]+)\]\(([^)\s]+)(?:\s+"([^"]*)")?\)/g;
    while ((match = linkRegex.exec(content)) !== null) {
      result.links.push({
        text: match[1],
        url: match[2],
        title: match[3] || null
      });
    }

    // Поиск списков
    const listRegex = /^[\s]*[-*+]\s.+$/gm;
    while ((match = listRegex.exec(content)) !== null) {
      result.lists.push(match[0]);
    }

    // Поиск блоков кода
    const codeBlockRegex = /```[\s\S]*?```/g;
    while ((match = codeBlockRegex.exec(content)) !== null) {
      result.codeBlocks.push(match[0]);
    }

    return result;
  }

  stripMarkdown(content) {
    if (!content) return '';
    
    return content
      // Удаляем изображения
      .replace(/!\[([^\]]*)\]\([^)]+\)/g, '$1')
      // Удаляем ссылки, оставляя текст
      .replace(/\[([^\]]+)\]\([^)]+\)/g, '$1')
      // Удаляем форматирование
      .replace(/[*_~`#]/g, '')
      // Удаляем HTML теги
      .replace(/<[^>]*>/g, '')
      .trim();
  }

  checkHeadingHierarchy(headings) {
    if (headings.length < 2) return null;
    
    for (let i = 0; i < headings.length - 1; i++) {
      const current = headings[i].level;
      const next = headings[i + 1].level;
      
      if (next - current > 1) {
        return `Нарушена иерархия заголовков: H${current} -> H${next}`;
      }
    }
    
    return null;
  }

  analyzeUrl(url) {
    const issues = [];
    const warnings = [];
    const success = [];
    
    if (!url || url.trim() === '') {
      issues.push('URL отсутствует');
      return { score: 0, issues, warnings, success, value: '' };
    }

    // Проверка длины
    if (url.length > 75) {
      warnings.push('URL слишком длинный (>75 символов)');
    } else {
      success.push('Оптимальная длина URL');
    }

    // Проверка на использование кириллицы
    if (/[а-яА-ЯёЁ]/.test(url)) {
      issues.push('URL содержит кириллические символы');
    } else {
      success.push('URL использует латиницу');
    }

    // Проверка на специальные символы
    if (/[^\w\-/]/.test(url)) {
      warnings.push('URL содержит нежелательные символы');
    }

    // Проверка на использование дефисов вместо подчеркиваний
    if (url.includes('_')) {
      warnings.push('Замените подчеркивания на дефисы в URL');
    }

    // Проверка на заглавные буквы
    if (/[A-Z]/.test(url)) {
      issues.push('URL содержит заглавные буквы');
    } else {
      success.push('URL использует строчные буквы');
    }

    return {
      score: this.calculateSectionScore(issues.length, warnings.length, success.length),
      issues,
      warnings,
      success,
      value: url
    };
  }

  calculateSectionScore(issues, warnings, success) {
    const total = issues + warnings + success;
    if (total === 0) return 0;
    
    const score = (success / total) * 100 - issues * 10 - warnings * 5;
    return Math.max(0, Math.min(100, Math.round(score)));
  }

  calculateOverallScore(results) {
    const weights = {
      title: 0.25,
      description: 0.2,
      keywords: 0.1,
      content: 0.35,
      url: 0.1
    };

    let totalScore = 0;
    for (const [key, weight] of Object.entries(weights)) {
      totalScore += results[key].score * weight;
    }

    return Math.round(totalScore);
  }

  generateReport(analysis) {
    return {
      score: analysis.overallScore,
      rating: this.getRating(analysis.overallScore),
      summary: this.generateSummary(analysis),
      details: analysis,
      recommendations: this.generateRecommendations(analysis)
    };
  }

  getRating(score) {
    if (score >= 90) return { level: 'Отлично', color: '#28a745' };
    if (score >= 75) return { level: 'Хорошо', color: '#17a2b8' };
    if (score >= 60) return { level: 'Удовлетворительно', color: '#ffc107' };
    return { level: 'Требует улучшения', color: '#dc3545' };
  }

  generateSummary(analysis) {
    const totalIssues = Object.values(analysis).reduce((sum, section) => {
      return sum + (section.issues ? section.issues.length : 0);
    }, 0);

    const totalWarnings = Object.values(analysis).reduce((sum, section) => {
      return sum + (section.warnings ? section.warnings.length : 0);
    }, 0);

    return `Найдено проблем: ${totalIssues}, предупреждений: ${totalWarnings}. Общая оценка: ${analysis.overallScore}/100`;
  }

  generateRecommendations(analysis) {
    const recommendations = [];
    
    if (analysis.title.score < 70) {
      recommendations.push('Улучшите заголовок: оптимальная длина 30-60 символов');
    }
    if (analysis.description.score < 70) {
      recommendations.push('Улучшите описание: оптимальная длина 120-160 символов');
    }
    if (analysis.keywords.score < 70) {
      recommendations.push('Добавьте 5-15 ключевых слов через запятую');
    }
    if (analysis.content.score < 70) {
      recommendations.push('Увеличьте контент минимум до 300 символов');
    }
    if (analysis.url.score < 70) {
      recommendations.push('Оптимизируйте URL: используйте латиницу, дефисы, строчные буквы');
    }

    return recommendations;
  }
}