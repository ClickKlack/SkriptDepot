// ==UserScript==
// @name         Beispiel-Skript
// @namespace    skriptdepot
// @version      {{VERSION}}
// @description  Demonstriert das Platzhalter-Schema des Portals.
// @match        https://example.com/*
// @run-at       document-idle
// @updateURL    {{BASE}}/s/{{TOKEN}}/{{SLUG}}.meta.js
// @downloadURL  {{BASE}}/s/{{TOKEN}}/{{SLUG}}.user.js
// ==/UserScript==
/* build: {{BUILD}} */
(function () {
  'use strict';
  const BUILD = '{{BUILD}}';
  const log = (...args) => console.debug(`[${BUILD}]`, ...args);
  log('Skript geladen');
})();
