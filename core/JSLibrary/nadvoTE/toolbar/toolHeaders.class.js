export class ToolHeaders extends Tool {
  constructor(editor, element) {
    super(editor, {
      name: 'headers',
      type: 'choices',
      iconPath: '',
      element: element
    });
  }

  initClickEvent() {
    super.addChangeEvent(() => {
      console.log(`[NADVO TE] Tool ${this.name} selected!`);
      const selectElement = this.element.querySelector('select');
      
      // Возвращаем фокус в textarea
      this.editor.textarea.element.focus();
      
      const selectedText = this.editor.getSelectionString();
      
      if (selectedText) {
        this.editor.textarea.replaceStringSelection(
          '#'.repeat(selectElement.value) + ' ' + selectedText
        );
      }
      
      // Сбрасываем select
      selectElement.selectedIndex = 0;
    });
  }
}