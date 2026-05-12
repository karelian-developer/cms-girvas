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
      imageAltRequired: true
    };
  }

  /**
   * Локализация с поддержкой %s, %d, %.Nf
   * @param {string} key - ключ строки
   * @param {...*} args - аргументы для подстановки
   * @returns {string}
   */
  _t(key, ...args) {
    let text = this.localeData[key] || key;
    
    if (args.length === 0) return text;
    
    // Имитация sprintf для %s, %d, %.Nf
    let argIndex = 0;
    return text.replace(/%([.0-9]*)([sdf])/g, (match, precision, type) => {
      if (argIndex >= args.length) return match;
      
      const value = args[argIndex++];
      
      switch (type) {
        case 's':
          return String(value);
        case 'd':
          return parseInt(value, 10);
        case 'f':
          if (precision && precision.startsWith('.')) {
            const decimals = parseInt(precision.slice(1), 10);
            return parseFloat(value).toFixed(decimals);
          }
          return parseFloat(value);
        default:
          return match;
      }
    });
  }

  analyze(SEOData) {
    const results = {
      title: this.analyzeTitle(SEOData.title, SEOData.SEOTitle, SEOData.keywords),
      description: this.analyzeDescription(SEOData.description, SEOData.SEODescription, SEOData.keywords),
      keywords: this.analyzeKeywords(SEOData.keywords),
      content: this.analyzeContent(SEOData.content, SEOData.keywords),
      url: this.analyzeURL(SEOData.name),
      overallScore: 0
    };

    results.overallScore = this.calculateOverallScore(results);
    return results;
  }

  analyzeTitle(title, SEOTitle, keywords) {
    const issues = [];
    const warnings = [];
    const success = [];
    const primaryTitle = SEOTitle || title;
    
    const length = primaryTitle.length;
    if (length === 0) {
      issues.push(this._t('SEO_ANALYZER_TITLE_ABSENT'));
    } else if (length < this.config.titleLength.min) {
      issues.push(this._t('SEO_ANALYZER_TITLE_TOO_SHORT', length, this.config.titleLength.min));
    } else if (length > this.config.titleLength.max) {
      warnings.push(this._t('SEO_ANALYZER_TITLE_TOO_LONG', length, this.config.titleLength.max));
    } else {
      success.push(this._t('SEO_ANALYZER_TITLE_OPTIMAL', length));
    }

    if (primaryTitle && primaryTitle.length > 0) {
      const firstWord = primaryTitle.split(' ')[0].toLowerCase();
      const stopWords = ['как', 'что', 'где', 'когда', 'почему', 'a', 'the', 'in', 'on', 'at', 'to', 'for'];
      
      if (stopWords.includes(firstWord)) {
        warnings.push(this._t('SEO_ANALYZER_TITLE_STOP_WORD'));
      } else {
        success.push(this._t('SEO_ANALYZER_TITLE_STRONG_WORD'));
      }
    }

    if (SEOTitle && title && SEOTitle === title) {
      warnings.push(this._t('SEO_ANALYZER_TITLE_SEO_DUPLICATE'));
    } else if (SEOTitle && title) {
      success.push(this._t('SEO_ANALYZER_TITLE_SEO_DIFFERENT'));
    } else if (!SEOTitle && title) {
      warnings.push(this._t('SEO_ANALYZER_TITLE_SEO_EMPTY'));
    }

    if (primaryTitle && (primaryTitle.includes('|') || primaryTitle.includes('-') || primaryTitle.includes('–'))) {
      success.push(this._t('SEO_ANALYZER_TITLE_HAS_SEPARATOR'));
    }

    if (keywords && keywords.trim() !== '' && primaryTitle) {
      const keywordList = keywords.split(',').map(k => k.trim().toLowerCase()).filter(k => k.length > 0);
      const titleLower = primaryTitle.toLowerCase();
      
      const foundKeywords = keywordList.filter(kw => titleLower.includes(kw));
      
      if (foundKeywords.length > 0) {
        success.push(this._t('SEO_ANALYZER_TITLE_HAS_KEYWORDS', foundKeywords.length, keywordList.length));
      } else {
        warnings.push(this._t('SEO_ANALYZER_TITLE_NO_KEYWORDS'));
      }
    }

    return {
      score: this.calculateSectionScore(issues.length, warnings.length, success.length),
      issues,
      warnings,
      success,
      value: primaryTitle || ''
    };
  }

  analyzeDescription(description, SEODescription, keywords) {
    const issues = [];
    const warnings = [];
    const success = [];
    const primaryDescription = SEODescription || description;
    
    const length = primaryDescription ? primaryDescription.length : 0;
    
    if (length === 0) {
      issues.push(this._t('SEO_ANALYZER_DESCRIPTION_ABSENT'));
    } else if (length < this.config.descriptionLength.min) {
      issues.push(this._t('SEO_ANALYZER_DESCRIPTION_TOO_SHORT', length, this.config.descriptionLength.min));
    } else if (length > this.config.descriptionLength.max) {
      warnings.push(this._t('SEO_ANALYZER_DESCRIPTION_TOO_LONG', length, this.config.descriptionLength.max));
    } else {
      success.push(this._t('SEO_ANALYZER_DESCRIPTION_OPTIMAL', length));
    }

    if (primaryDescription) {
      const ctaWords = ['узнать', 'заказать', 'купить', 'получить', 'скачать', 'читать', 'подробнее', 'learn', 'buy', 'get', 'download', 'read'];
      const hasCTA = ctaWords.some(word => primaryDescription.toLowerCase().includes(word));
      
      if (hasCTA) {
        success.push(this._t('SEO_ANALYZER_DESCRIPTION_HAS_CTA'));
      } else {
        warnings.push(this._t('SEO_ANALYZER_DESCRIPTION_NO_CTA'));
      }
    }

    if (SEODescription && description && SEODescription === description) {
      warnings.push(this._t('SEO_ANALYZER_DESCRIPTION_SEO_DUPLICATE'));
    } else if (SEODescription && description) {
      success.push(this._t('SEO_ANALYZER_DESCRIPTION_SEO_DIFFERENT'));
    } else if (!SEODescription && description) {
      warnings.push(this._t('SEO_ANALYZER_DESCRIPTION_SEO_EMPTY'));
    }

    if (keywords && keywords.trim() !== '' && primaryDescription) {
      const keywordList = keywords.split(',').map(k => k.trim().toLowerCase()).filter(k => k.length > 0);
      const descriptionLower = primaryDescription.toLowerCase();
      
      const foundKeywords = keywordList.filter(kw => descriptionLower.includes(kw));
      
      if (foundKeywords.length > 0) {
        success.push(this._t('SEO_ANALYZER_DESCRIPTION_HAS_KEYWORDS', foundKeywords.length, keywordList.length));
      } else {
        warnings.push(this._t('SEO_ANALYZER_DESCRIPTION_NO_KEYWORDS'));
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
      issues.push(this._t('SEO_ANALYZER_KEYWORDS_ABSENT'));
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
      warnings.push(this._t('SEO_ANALYZER_KEYWORDS_TOO_FEW', keywordList.length, this.config.keywordsCount.min));
    } else if (keywordList.length > this.config.keywordsCount.max) {
      warnings.push(this._t('SEO_ANALYZER_KEYWORDS_TOO_MANY', keywordList.length, this.config.keywordsCount.max));
    } else {
      success.push(this._t('SEO_ANALYZER_KEYWORDS_OPTIMAL', keywordList.length));
    }

    const longKeywords = keywordList.filter(k => k.length > 60);
    if (longKeywords.length > 0) {
      issues.push(this._t('SEO_ANALYZER_KEYWORDS_TOO_LONG', longKeywords.length));
    }

    const duplicates = keywordList.filter((item, index) => keywordList.indexOf(item) !== index);
    if (duplicates.length > 0) {
      warnings.push(this._t('SEO_ANALYZER_KEYWORDS_DUPLICATE'));
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

  analyzeContent(content, keywords) {
    const issues = [];
    const warnings = [];
    const success = [];
    
    if (!content || content.trim() === '') {
      issues.push(this._t('SEO_ANALYZER_CONTENT_ABSENT'));
      return {
        score: 0,
        issues,
        warnings,
        success,
        markdownAnalysis: {}
      };
    }

    const markdownAnalysis = this.analyzeMarkdown(content);
    
    const plainText = this.stripMarkdown(content);
    const length = plainText.length;
    
    if (length < this.config.contentLength.min) {
      issues.push(this._t('SEO_ANALYZER_CONTENT_TOO_SHORT', length, this.config.contentLength.min));
    } else if (length > this.config.contentLength.max) {
      warnings.push(this._t('SEO_ANALYZER_CONTENT_TOO_LONG', length));
    } else {
      success.push(this._t('SEO_ANALYZER_CONTENT_OPTIMAL', length));
    }

    const contentHeadings = markdownAnalysis.headings.filter(h => h.level >= 2);
    
    if (contentHeadings.length === 0) {
      warnings.push(this._t('SEO_ANALYZER_CONTENT_NO_HEADINGS'));
    } else {
      success.push(this._t('SEO_ANALYZER_CONTENT_HEADINGS_FOUND', contentHeadings.length));
      
      if (this.config.headingsHierarchy) {
        const hierarchyIssue = this.checkHeadingHierarchy(contentHeadings);
        if (hierarchyIssue) {
          warnings.push(this._t('SEO_ANALYZER_CONTENT_HEADINGS_HIERARCHY', ...hierarchyIssue));
        }
      }

      if (keywords && keywords.trim() !== '') {
        const keywordList = keywords.split(',').map(k => k.trim().toLowerCase()).filter(k => k.length > 0);
        
        const headingsWithKeywords = contentHeadings.filter(h => {
          const headingLower = h.text.toLowerCase();
          return keywordList.some(kw => headingLower.includes(kw));
        });
        
        if (headingsWithKeywords.length === contentHeadings.length) {
          success.push(this._t('SEO_ANALYZER_CONTENT_HEADINGS_ALL_KEYWORDS'));
        } else if (headingsWithKeywords.length > 0) {
          warnings.push(
            this._t('SEO_ANALYZER_CONTENT_HEADINGS_SOME_KEYWORDS', headingsWithKeywords.length, contentHeadings.length)
          );
        } else {
          warnings.push(this._t('SEO_ANALYZER_CONTENT_HEADINGS_NO_KEYWORDS'));
        }
      }
    }

    if (markdownAnalysis.images.length === 0) {
      success.push(this._t('SEO_ANALYZER_CONTENT_NO_IMAGES'));
    } else {
      success.push(this._t('SEO_ANALYZER_CONTENT_IMAGES_FOUND', markdownAnalysis.images.length));
      
      const imagesWithoutAlt = markdownAnalysis.images.filter(img => !img.alt || img.alt.trim() === '');
      if (imagesWithoutAlt.length > 0) {
        warnings.push(this._t('SEO_ANALYZER_CONTENT_IMAGES_NO_ALT', imagesWithoutAlt.length));
      }
      
      const imagesWithAlt = markdownAnalysis.images.filter(img => img.alt && img.alt.length > 0);
      if (imagesWithAlt.length > 0) {
        success.push(this._t('SEO_ANALYZER_CONTENT_IMAGES_HAS_ALT'));
      }
    }

    if (keywords && keywords.trim() !== '') {
      const keywordList = keywords.split(',').map(k => k.trim().toLowerCase()).filter(k => k.length > 0);
      const plainTextLower = plainText.toLowerCase();
      
      const foundKeywords = keywordList.filter(kw => plainTextLower.includes(kw));
      const notFoundKeywords = keywordList.filter(kw => !plainTextLower.includes(kw));
      
      if (foundKeywords.length === keywordList.length) {
        success.push(this._t('SEO_ANALYZER_CONTENT_ALL_KEYWORDS'));
      } else if (foundKeywords.length > 0) {
        warnings.push(
          this._t('SEO_ANALYZER_CONTENT_SOME_KEYWORDS', foundKeywords.length, keywordList.length, notFoundKeywords.join(', '))
        );
      } else {
        warnings.push(this._t('SEO_ANALYZER_CONTENT_NO_KEYWORDS'));
      }
      
      const firstParagraph = plainTextLower.split('\n\n')[0];
      const keywordsInIntro = keywordList.filter(kw => firstParagraph.includes(kw));
      
      if (keywordsInIntro.length > 0) {
        success.push(this._t('SEO_ANALYZER_CONTENT_KEYWORDS_IN_INTRO'));
      } else {
        warnings.push(this._t('SEO_ANALYZER_CONTENT_NO_KEYWORDS_IN_INTRO'));
      }
    } else {
      warnings.push(this._t('SEO_ANALYZER_CONTENT_KEYWORDS_NOT_SET'));
    }

    if (markdownAnalysis.links.length === 0) {
      warnings.push(this._t('SEO_ANALYZER_CONTENT_NO_LINKS'));
    } else {
      const bareLinks = markdownAnalysis.links.filter(l => !l.isMarkdown);
      
      success.push(this._t('SEO_ANALYZER_CONTENT_LINKS_FOUND', markdownAnalysis.links.length));
      
      if (bareLinks.length > 0) {
        warnings.push(this._t('SEO_ANALYZER_CONTENT_BARE_URLS', bareLinks.length));
      }
      
      const externalLinks = markdownAnalysis.links.filter(link => 
        link.url.startsWith('http') || link.url.startsWith('www')
      );
      
      if (externalLinks.length > 0) {
        const nofollowLinks = externalLinks.filter(link => 
          link.rel && link.rel.includes('nofollow')
        );
        const missingNofollow = externalLinks.length - nofollowLinks.length;
        
        if (missingNofollow > 0) {
          warnings.push(
            this._t('SEO_ANALYZER_CONTENT_NOFOLLOW_MISSING', missingNofollow, externalLinks.length)
          );
        } else {
          success.push(this._t('SEO_ANALYZER_CONTENT_NOFOLLOW_OK'));
        }
      }
    }

    const linkTextLength = markdownAnalysis.links.reduce((sum, link) => sum + link.text.length, 0);
    const linkDensity = length > 0 ? (linkTextLength / length) * 100 : 0;
    if (linkDensity > 10) {
      warnings.push(this._t('SEO_ANALYZER_CONTENT_LINK_DENSITY', linkDensity));
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
    const imageRegex = /!\[([^\]]*)\]\(([^)\s]+)\)(?:\{([^}]*)\})?/g;
    while ((match = imageRegex.exec(content)) !== null) {
      let attrs = {};
      if (match[3]) {
        try {
          attrs = JSON.parse(`{${match[3]}}`);
        } catch (e) {}
      }
      
      result.images.push({
        alt: match[1],
        url: match[2],
        title: attrs.title || null
      });
    }

    // Поиск ссылок (исключая изображения)
    const linkRegex = /(?<!\!)\[([^\]]+)\]\(([^)\s]+)\)(?:\{([^}]*)\})?/g;
    while ((match = linkRegex.exec(content)) !== null) {
      let attrs = {};
      if (match[3]) {
        try {
          attrs = JSON.parse(`{${match[3]}}`);
        } catch (e) {}
      }
      
      result.links.push({
        text: match[1],
        url: match[2],
        title: attrs.title || null,
        rel: attrs.rel || null,
        isMarkdown: true
      });
    }

    // Поиск голых URL
    const usedUrls = new Set([
      ...result.links.map(l => l.url),
      ...result.images.map(i => i.url)
    ]);
    
    const bareUrlRegex = /(?<!\]\(\s*|!\[[^\]]*\]\(\s*)(https?:\/\/[^\s<>"')\]]+)/gi;
    while ((match = bareUrlRegex.exec(content)) !== null) {
      const url = match[1].replace(/[.,;:!?]+$/, '');
      if (!usedUrls.has(url)) {
        result.links.push({
          text: url,
          url: url,
          title: null,
          rel: null,
          isMarkdown: false
        });
        usedUrls.add(url);
      }
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
    // Удаляем HTML комментарии
    .replace(/<!--[\s\S]*?-->/g, '')
    // Удаляем блоки кода
    .replace(/```[\s\S]*?```/g, '')
    // Удаляем изображения (оставляем alt)
    .replace(/!\[([^\]]*)\]\([^)]*\)(?:\{[^}]*\})?/g, '$1')
    // Удаляем ссылки, оставляя текст
    .replace(/\[([^\]]+)\]\([^)]*\)(?:\{[^}]*\})?/g, '$1')
    // Удаляем заголовки (с пробелом и без)
    .replace(/^#{1,6}\s*/gm, '')
    // Удаляем маркеры списков
    .replace(/^[\s]*[-*+]\s/gm, '')
    // Удаляем нумерованные списки
    .replace(/^\d+\.\s/gm, '')
    // Удаляем символы форматирования
    .replace(/[*_~`>|]/g, '')
    // Нормализуем пробелы
    .replace(/\n{3,}/g, '\n\n')
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

  analyzeURL(url) {
    const issues = [];
    const warnings = [];
    const success = [];
    
    if (!url || url.trim() === '') {
      issues.push(this._t('SEO_ANALYZER_URL_ABSENT'));
      return { score: 0, issues, warnings, success, value: '' };
    }

    if (url.length > 75) {
      warnings.push(this._t('SEO_ANALYZER_URL_TOO_LONG'));
    } else {
      success.push(this._t('SEO_ANALYZER_URL_OPTIMAL'));
    }

    if (/[а-яА-ЯёЁ]/.test(url)) {
      issues.push(this._t('SEO_ANALYZER_URL_CYRILLIC'));
    } else {
      success.push(this._t('SEO_ANALYZER_URL_LATIN'));
    }

    if (/[^\w\-/]/.test(url)) {
      warnings.push(this._t('SEO_ANALYZER_URL_SPECIAL_CHARS'));
    }

    if (url.includes('_')) {
      warnings.push(this._t('SEO_ANALYZER_URL_UNDERSCORES'));
    }

    if (/[A-Z]/.test(url)) {
      issues.push(this._t('SEO_ANALYZER_URL_UPPERCASE'));
    } else {
      success.push(this._t('SEO_ANALYZER_URL_LOWERCASE'));
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
    if (score >= 90) return { level: this._t('SEO_ANALYZER_RATING_EXCELLENT'), color: '#28a745' };
    if (score >= 75) return { level: this._t('SEO_ANALYZER_RATING_GOOD'), color: '#17a2b8' };
    if (score >= 60) return { level: this._t('SEO_ANALYZER_RATING_SATISFACTORY'), color: '#ffc107' };
    return { level: this._t('SEO_ANALYZER_RATING_NEEDS_IMPROVEMENT'), color: '#dc3545' };
  }

  generateSummary(analysis) {
    const sections = ['title', 'description', 'keywords', 'content', 'url'];
    let totalIssues = 0;
    let totalWarnings = 0;

    for (const section of sections) {
      totalIssues += analysis[section]?.issues?.length || 0;
      totalWarnings += analysis[section]?.warnings?.length || 0;
    }

    return this._t('SEO_ANALYZER_SUMMARY', totalIssues, totalWarnings, analysis.overallScore);
  }

  generateRecommendations(analysis) {
    const recommendations = [];
    
    if (analysis.title.score < 70) {
      recommendations.push(this._t('SEO_ANALYZER_RECOMMENDATION_TITLE'));
    }
    if (analysis.description.score < 70) {
      recommendations.push(this._t('SEO_ANALYZER_RECOMMENDATION_DESCRIPTION'));
    }
    if (analysis.keywords.score < 70) {
      recommendations.push(this._t('SEO_ANALYZER_RECOMMENDATION_KEYWORDS'));
    }
    if (analysis.content.score < 70) {
      recommendations.push(this._t('SEO_ANALYZER_RECOMMENDATION_CONTENT'));
    }
    if (analysis.url.score < 70) {
      recommendations.push(this._t('SEO_ANALYZER_RECOMMENDATION_URL'));
    }

    return recommendations;
  }
}