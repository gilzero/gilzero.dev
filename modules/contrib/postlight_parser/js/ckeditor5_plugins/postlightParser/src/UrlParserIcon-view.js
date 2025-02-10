import { View, LabeledFieldView, createLabeledInputText, ButtonView, submitHandler} from 'ckeditor5/src/ui';
import {icons} from 'ckeditor5/src/core';

/**
 * A class rendering the information required from user input.
 *
 * @extends module:ui/view~View
 *
 * @internal
 */
export default class UrlParserIconView extends View {

  /**
   * @inheritdoc
   */
  constructor(editor) {
    const locale = editor.locale;
    super(locale);

    this.urlInputView = this._createInput(editor.t('Url to get content'));

    // Create the save and cancel buttons.
    this.saveButtonView = this._createButton(
      editor.t('Save'), icons.check, 'ck-button-save'
    );
    this.saveButtonView.type = 'submit';

    this.cancelButtonView = this._createButton(
      editor.t('Cancel'), icons.cancel, 'ck-button-cancel'
    );
    // Delegate ButtonView#execute to FormView#cancel.
    this.cancelButtonView.delegate('execute').to(this, 'cancel');

    this.childViews = this.createCollection([
      this.urlInputView,
      this.saveButtonView,
      this.cancelButtonView
    ]);

    this.setTemplate({
      tag: 'form',
      attributes: {
        class: ['ck', 'ck-responsive-form', 'ck-link-form'],
        tabindex: '-1'
      },
      children: this.childViews
    });
  }

  /**
   * @inheritdoc
   */
  render() {
    super.render();
    // Submit the form when the user clicked the save button or
    // pressed enter the input.
    submitHandler({
      view: this
    });
  }

  /**
   * @inheritdoc
   */
  focus() {
    this.childViews.first.focus();
  }

  // Create a generic input field.
  _createInput(label) {
    const labeledInput = new LabeledFieldView(this.locale, createLabeledInputText);
    labeledInput.label = label;
    labeledInput.inputMode = 'url';
    return labeledInput;
  }

  // Create a generic button.
  _createButton(label, icon, className) {
    const button = new ButtonView();

    button.set({
      label,
      icon,
      tooltip: true,
      class: className,
    });

    return button;
  }

}
