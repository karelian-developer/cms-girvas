<div id="E3473967486" class="page__entry-editor"></div>
<div id="E3473967486_CONTENT">{ENTRY_CONTENT}</div>
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
        {'name': 'quote', 'type': 'button'},
        {'name': 'link', 'type': 'button'},
        {'name': 'image', 'type': 'button'},
        {'name': 'preview', 'type': 'button'},
        {'name': 'source', 'type': 'button'},
      ]
    });
    nadvoTE.init();
    nadvoTE.textarea.element.classList.add('textarea');
    nadvoTE.textarea.element.classList.add('form__textarea');
    nadvoTE.textarea.element.value = editorContent.innerHTML;
    nadvoTE.textarea.element.setAttribute('name', 'entry_content_rus');
    nadvoTE.textarea.element.setAttribute('data-element', 'input-content');

    editorContent.remove();
  }
</script>