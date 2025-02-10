/**
 * @file The build process always expects an index.js file. Anything exported
 * here will be recognized by CKEditor 5 as an available plugin. Multiple
 * plugins can be exported in this one file.
 *
 * I.e. this file's purpose is to make plugin(s) discoverable.
 */
// cSpell:ignore PostlightParser

import { Plugin } from 'ckeditor5/src/core';
import UrlParserUI from "./UrlParserUI";
import UrlParserEditing from "./UrlParserEditing";

export default class PostlightParser extends Plugin {
  /**
   * @inheritdoc
   */
  static get requires() {
    return [UrlParserEditing, UrlParserUI];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'urlParser';
  }
}
