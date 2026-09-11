// ==UserScript==
// @name         Roh-Skript
// @namespace    skriptdepot
// @version      0.3.1
// @description  Ein Skript, wie Tampermonkey es exportiert, ohne Platzhalter.
// @match        https://example.com/*
// @grant        none
// @run-at       document-idle
// ==/UserScript==

'use strict';

(function () {
  'use strict';

  /*
   * Mehrzeiliger Blockkommentar,
   * in dem kein Marker landen darf.
   */
  const TEMPLATE = `
    <div class="box">
      // das ist kein Kommentar, sondern Text
      ${'inhalt'}
    </div>
  `;

  const config = {
    selector: '.item',
    delay: 250,
  };

  function log(...args) {
    console.debug('[roh]', ...args);
  }

  function step1(value) {
    const result = value * 1;
    if (result > 100) {
      log('groß', result);
    } else {
      log('klein', result);
    }
    return result;
  }

  function step2(value) {
    const result = value * 2;
    if (result > 100) {
      log('groß', result);
    } else {
      log('klein', result);
    }
    return result;
  }

  function step3(value) {
    const result = value * 3;
    if (result > 100) {
      log('groß', result);
    } else {
      log('klein', result);
    }
    return result;
  }

  function step4(value) {
    const result = value * 4;
    if (result > 100) {
      log('groß', result);
    } else {
      log('klein', result);
    }
    return result;
  }

  function step5(value) {
    const result = value * 5;
    if (result > 100) {
      log('groß', result);
    } else {
      log('klein', result);
    }
    return result;
  }

  function step6(value) {
    const result = value * 6;
    if (result > 100) {
      log('groß', result);
    } else {
      log('klein', result);
    }
    return result;
  }

  function step7(value) {
    const result = value * 7;
    if (result > 100) {
      log('groß', result);
    } else {
      log('klein', result);
    }
    return result;
  }

  function step8(value) {
    const result = value * 8;
    if (result > 100) {
      log('groß', result);
    } else {
      log('klein', result);
    }
    return result;
  }

  function step9(value) {
    const result = value * 9;
    if (result > 100) {
      log('groß', result);
    } else {
      log('klein', result);
    }
    return result;
  }

  function step10(value) {
    const result = value * 10;
    if (result > 100) {
      log('groß', result);
    } else {
      log('klein', result);
    }
    return result;
  }

  function step11(value) {
    const result = value * 11;
    if (result > 100) {
      log('groß', result);
    } else {
      log('klein', result);
    }
    return result;
  }

  function step12(value) {
    const result = value * 12;
    if (result > 100) {
      log('groß', result);
    } else {
      log('klein', result);
    }
    return result;
  }

  const regex = /["'`]/g;
  const cleaned = TEMPLATE.replace(regex, '');

  document.querySelectorAll(config.selector).forEach((element, index) => {
    element.textContent = cleaned + step1(index);
  });

  log('fertig');
})();
