<div id="E3473967486" class="page__page-static-editor"></div>
<div id="E3473967486_CONTENT">{PAGE_STATIC_CONTENT}</div>
<script type="module">
  import {NadvoTE} from '/core/JSLibrary/nadvoTE.class.js';

  // Проверяем, не инициализировано ли уже ядро
  if (window.CMSCore?.templateCore) {
    initNadvoTE();
  } else {
    window.CMSCore?.addEventListener('ready', initNadvoTE);
  }

  function initNadvoTE() {
    if (window._nadvoTEInitialized) return;
    window._nadvoTEInitialized = true;

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', createEditor);
    } else {
      createEditor();
    }
  }

  function createEditor() {
    const editorContent = document.querySelector('#E3473967486_CONTENT');
    const editorLocale = window.CMSCore?.locales.nadvoTE;
    if (!editorContent) return;

    const nadvoTE = new NadvoTE(document.querySelector('#E3473967486'), {
      'locale': editorLocale,
      'handler': '/handler/utils/nadvoparse',
      'toolbar': [
        {'name': 'bold', 'type': 'button'},
        {'name': 'italic', 'type': 'button'},
        {'name': 'underline', 'type': 'button'},
        {'name': 'headers', 'type': 'choices'},
        {'name': 'link', 'type': 'button'},
        {'name': 'image', 'type': 'button'},
        {'name': 'quote', 'type': 'button'},
        {'name': 'code', 'type': 'button'},
        {'name': 'preview', 'type': 'button'},
        {'name': 'source', 'type': 'button'},
      ]
    });
    nadvoTE.init();
    nadvoTE.textarea.element.classList.add('textarea');
    nadvoTE.textarea.element.classList.add('form__textarea');
    nadvoTE.textarea.element.value = editorContent.innerHTML;
    nadvoTE.textarea.element.setAttribute('name', 'page_static_content_rus');
    nadvoTE.textarea.element.setAttribute('data-element', 'input-content');

    editorContent.remove();
  }
</script>