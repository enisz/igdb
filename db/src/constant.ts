export const IMAGE_REGEXP = new RegExp('!\\[(.*?)\\]\\((.*?)\\)', 'gi');
export const LINK_REGEXP = new RegExp('\\[(.*?)\\]\\((.*?)\\)', 'gi');
export const HEADING_REGEXP = new RegExp('^(\#{1,6})(.*)$');
export const TABSET_REGEXP = new RegExp('^\#{1,6}(.*)(\{.tabset\}|\{-\})$', 'm');
