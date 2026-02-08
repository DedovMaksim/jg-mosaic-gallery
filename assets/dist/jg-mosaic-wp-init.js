(function () {
  function debounce(fn, wait) {
    let t = null;
    return function () {
      clearTimeout(t);
      t = setTimeout(() => fn.apply(this, arguments), wait);
    };
  }

  function parseJSONAttr(el, attr) {
    const raw = el.getAttribute(attr);
    if (!raw) return null;
    try {
      return JSON.parse(raw);
    } catch (e) {
      // eslint-disable-next-line no-console
      console.warn('[JG Mosaic] JSON parse failed', e);
      return null;
    }
  }

  function initOne(el) {
    if (!el || el.dataset.jgInited === "1") return null;

    const payload = parseJSONAttr(el, 'data-jg');
    if (!payload || !payload.items) return null;

    // Поддерживаем 2 варианта:
    // 1) core экспортирует window.JGMosaic.create(...)
    // 2) или твой старый entrypoint window.jgMosaicLayout(el, items, config)
    let instance = null;

    if (window.JGMosaic && typeof window.JGMosaic.create === 'function') {
      instance = window.JGMosaic.create(el, payload);
    } else if (typeof window.jgMosaicLayout === 'function') {
      window.jgMosaicLayout(el, payload.items, payload.config || {});
      instance = { layout: function() { window.jgMosaicLayout(el, payload.items, payload.config || {}); } };
    } else {
      // eslint-disable-next-line no-console
      console.warn('[JG Mosaic] Core not found. Provide window.JGMosaic.create or window.jgMosaicLayout');
      return null;
    }

    el.dataset.jgInited = "1";
    el.__jgInstance = instance;
    return instance;
  }

  function initAll(root) {
    const ctx = root || document;
    const list = ctx.querySelectorAll('.jg[data-jg]');
    list.forEach(initOne);
  }

  const relayoutAll = debounce(function () {
    document.querySelectorAll('.jg[data-jg]').forEach((el) => {
      const inst = el.__jgInstance;
      if (inst && typeof inst.layout === 'function') inst.layout();
    });
  }, 150);

  document.addEventListener('DOMContentLoaded', function () {
    initAll(document);
    window.addEventListener('load', relayoutAll);
  });

  // Если у тебя галереи могут появляться динамически (AJAX/filters) — включи наблюдатель
  const mo = new MutationObserver(debounce(function (mutations) {
    for (const m of mutations) {
      if (m.addedNodes && m.addedNodes.length) {
        initAll(document);
        break;
      }
    }
  }, 200));

  document.addEventListener('DOMContentLoaded', function () {
    mo.observe(document.body, { childList: true, subtree: true });
  });

})();
