/**
 * @file defines InsertBootstrapIconsCommand, which is executed when the icon
 * toolbar button is pressed.
 */
// cSpell:ignore urlParser

import {Command} from 'ckeditor5/src/core';

export default class InsertUrlCommand extends Command {
  execute(url) {
    const { editor } = this;
    const { model } = editor;
    const config = this.editor.config.get('url_parser');
    url = config['endpoint'] + '?url=' + url;
    if(config['save_image']) {
      url += '&save_image=' + config['save_image'];
    }
    fetch(url).then(function (response) {
      if (!response.ok) {
        throw new Error('Request error: ' + response.status);
      }
      return response.json();
    }).then(function (data) {
      model.change(writer => {
        const content = writer.createElement('urlParser');
        const docFrag = writer.createDocumentFragment();
        const viewFragment = editor.data.processor.toView(data.content);
        const modelFragment = editor.data.toModel(viewFragment);
        writer.append(content, docFrag);
        writer.append(modelFragment, content);
        model.insertContent(docFrag);
      });
    });
  }

  refresh() {
    const {model} = this.editor;
    const {selection} = model.document;

    // Determine if the cursor (selection) is in a position where adding a
    // simpleBox is permitted. This is based on the schema of the model(s)
    // currently containing the cursor.
    const allowedIn = model.schema.findAllowedParent(
      selection.getFirstPosition(),
      'urlParser',
    );

    // If the cursor is not in a location where a simpleBox can be added, return
    // null so the addition doesn't happen.
    this.isEnabled = allowedIn !== null;
  }

}
