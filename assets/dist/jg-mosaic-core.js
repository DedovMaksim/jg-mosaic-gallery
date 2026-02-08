(function () {
  const clamp = (v, a, b) => Math.max(a, Math.min(b, v));

  function normalizeItems(items) {
    return (items || []).map((it) => {
      const full = it.full || it.href || it.src;
      return {
        ...it,
        src: it.src,
        full,
        w: it.w || 0,
        h: it.h || 0,
      };
    });
  }

  function ensureSizes(items) {
    return Promise.all(
      items.map((it) => {
        if (it.w && it.h) return Promise.resolve(it);
        return new Promise((res) => {
          const img = new Image();
          img.onload = () => {
            it.w = img.naturalWidth || 1;
            it.h = img.naturalHeight || 1;
            res(it);
          };
          img.onerror = () => {
            it.w = 1;
            it.h = 1;
            res(it);
          };
          img.src = it.src;
        });
      })
    );
  }

  function uniq(arr) {
    return Array.from(new Set(arr));
  }

  function shuffle(arr) {
    for (let i = arr.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1));
      [arr[i], arr[j]] = [arr[j], arr[i]];
    }
    return arr;
  }

  function sanitizePatternKeys(keys) {
    const keyToIndex = { p2: 0, p3l: 1, p3r: 2, p4: 3, p5: 4, p5b: 5 };
    return uniq((keys || []).filter((k) => keyToIndex[k] !== undefined));
  }

  function renderMosaic(trackEl, items, width, gap, targetH, cfg) {
    trackEl.innerHTML = "";
    trackEl.style.setProperty("--gap", gap + "px");

    const H = (mult = 2) => Math.round(targetH * mult);

        const cell = (it) => {
          const a = document.createElement("a");

          const lightbox = (cfg && cfg.lightbox) ? String(cfg.lightbox).toLowerCase() : "fancybox";

          // 1) link: просто ссылка как сейчас
          if (lightbox === "link") {
            a.href = it.full;
            a.target = "_blank";
            a.rel = "noopener";
          }
          // 2) none: отключаем переходы
          else if (lightbox === "none") {
            a.href = "#";
            a.addEventListener("click", (e) => e.preventDefault());
          }
          // 3) fancybox (или default): готовим для лайтбокса
          else {
            a.href = it.full;
            // Fancybox если он уже подключён где-то на сайте
            a.setAttribute("data-fancybox", "jg");
            // Для нашего встроенного лайтбокса:
            a.dataset.jgFull = it.full;
          }

          const img = document.createElement("img");
          img.src = it.src;
          img.alt = it.alt || "";
          img.loading = "lazy";

          a.appendChild(img);
          return a;
        };


    // patterns[] должны совпадать с mapping ниже
    const patterns = [
      // idx 0 => p2: 2 фото (адаптивно)
      (arr) => {
        if (arr.length < 2) return null;

        const group = document.createElement("div");
        group.className = "jg-mosaic-group";

        const isMobile = width <= 560;
        if (isMobile) {
          group.style.gridTemplateColumns = "1fr";
          group.style.gridTemplateRows = "1fr 1fr";
          group.style.height = H(2.6) + "px";
        } else {
          group.style.gridTemplateColumns = "1fr 1fr";
          group.style.gridTemplateRows = "1fr";
          group.style.height = H(2.2) + "px";
        }

        const a1 = cell(arr[0]);
        a1.style.gridColumn = "1";
        a1.style.gridRow = "1";

        const a2 = cell(arr[1]);
        a2.style.gridColumn = isMobile ? "1" : "2";
        a2.style.gridRow = isMobile ? "2" : "1";

        group.append(a1, a2);
        return { group, used: 2 };
      },

      // idx 1 => p3l: 3 фото, большая слева
      (arr) => {
        if (arr.length < 3) return null;
        const group = document.createElement("div");
        group.className = "jg-mosaic-group";
        group.style.gridTemplateColumns = "2fr 1fr";
        group.style.gridTemplateRows = "1fr 1fr";
        group.style.height = H(2.4) + "px";

        const a1 = cell(arr[0]);
        a1.style.gridColumn = "1";
        a1.style.gridRow = "1 / span 2";
        const a2 = cell(arr[1]);
        a2.style.gridColumn = "2";
        a2.style.gridRow = "1";
        const a3 = cell(arr[2]);
        a3.style.gridColumn = "2";
        a3.style.gridRow = "2";

        group.append(a1, a2, a3);
        return { group, used: 3 };
      },

      // idx 2 => p3r: 3 фото, большая справа
      (arr) => {
        if (arr.length < 3) return null;
        const group = document.createElement("div");
        group.className = "jg-mosaic-group";
        group.style.gridTemplateColumns = "1fr 2fr";
        group.style.gridTemplateRows = "1fr 1fr";
        group.style.height = H(2.4) + "px";

        const a1 = cell(arr[0]);
        a1.style.gridColumn = "1";
        a1.style.gridRow = "1";
        const a2 = cell(arr[1]);
        a2.style.gridColumn = "1";
        a2.style.gridRow = "2";
        const a3 = cell(arr[2]);
        a3.style.gridColumn = "2";
        a3.style.gridRow = "1 / span 2";

        group.append(a1, a2, a3);
        return { group, used: 3 };
      },

      // idx 3 => p4: 4 фото
      (arr) => {
        if (arr.length < 4) return null;

        const group = document.createElement("div");
        group.className = "jg-mosaic-group";

        const isMobile = width <= 560;

        if (isMobile) {
          // мобилка: большая сверху, 3 снизу
          group.style.gridTemplateColumns = "1fr 1fr 1fr";
          group.style.gridTemplateRows = "3fr 2fr";
          group.style.height = H(2.4) + "px";

          const a1 = cell(arr[0]);
          a1.style.gridColumn = "1 / span 3";
          a1.style.gridRow = "1";
          const a2 = cell(arr[1]);
          a2.style.gridColumn = "1";
          a2.style.gridRow = "2";
          const a3 = cell(arr[2]);
          a3.style.gridColumn = "2";
          a3.style.gridRow = "2";
          const a4 = cell(arr[3]);
          a4.style.gridColumn = "3";
          a4.style.gridRow = "2";

          group.append(a1, a2, a3, a4);
          return { group, used: 4 };
        }

        // десктоп: большая слева, три справа
        group.style.gridTemplateColumns = "2.4fr 1fr";
        group.style.gridTemplateRows = "1fr 1fr 1fr";
        group.style.height = H(2.4) + "px";

        const a1 = cell(arr[0]);
        a1.style.gridColumn = "1";
        a1.style.gridRow = "1 / span 3";
        const a2 = cell(arr[1]);
        a2.style.gridColumn = "2";
        a2.style.gridRow = "1";
        const a3 = cell(arr[2]);
        a3.style.gridColumn = "2";
        a3.style.gridRow = "2";
        const a4 = cell(arr[3]);
        a4.style.gridColumn = "2";
        a4.style.gridRow = "3";

        group.append(a1, a2, a3, a4);
        return { group, used: 4 };
      },

      // idx 4 => p5: 5 фото “2–1–2”
      (arr) => {
        if (arr.length < 5) return null;
        const group = document.createElement("div");
        group.className = "jg-mosaic-group";
        group.style.gridTemplateColumns = "1fr 2fr 1fr";
        group.style.gridTemplateRows = "1fr 1fr";
        group.style.height = H(2.2) + "px";

        const a1 = cell(arr[0]);
        a1.style.gridColumn = "1";
        a1.style.gridRow = "1";
        const a2 = cell(arr[1]);
        a2.style.gridColumn = "1";
        a2.style.gridRow = "2";
        const a3 = cell(arr[2]);
        a3.style.gridColumn = "2";
        a3.style.gridRow = "1 / span 2";
        const a4 = cell(arr[3]);
        a4.style.gridColumn = "3";
        a4.style.gridRow = "1";
        const a5 = cell(arr[4]);
        a5.style.gridColumn = "3";
        a5.style.gridRow = "2";

        group.append(a1, a2, a3, a4, a5);
        return { group, used: 5 };
      },

      // idx 5 => p5b: 5 фото “2 большие по краям + 3 в центре”
      (arr) => {
        if (arr.length < 5) return null;

        const group = document.createElement("div");
        group.className = "jg-mosaic-group";
        group.style.gridTemplateColumns = "1.2fr 1fr 1.2fr";
        group.style.gridTemplateRows = "1fr 1fr 1fr";
        group.style.height = H(2.6) + "px";

        const a1 = cell(arr[0]);
        a1.style.gridColumn = "1";
        a1.style.gridRow = "1 / span 3";
        const a2 = cell(arr[1]);
        a2.style.gridColumn = "2";
        a2.style.gridRow = "1";
        const a3 = cell(arr[2]);
        a3.style.gridColumn = "2";
        a3.style.gridRow = "2";
        const a4 = cell(arr[3]);
        a4.style.gridColumn = "2";
        a4.style.gridRow = "3";
        const a5 = cell(arr[4]);
        a5.style.gridColumn = "3";
        a5.style.gridRow = "1 / span 3";

        group.append(a1, a2, a3, a4, a5);
        return { group, used: 5 };
      },
    ];

    const keyToIndex = { p2: 0, p3l: 1, p3r: 2, p4: 3, p5: 4, p5b: 5 };

    // ✅ Разрешённые паттерны с учётом mobile
    const defaultKeys = ["p2", "p3l", "p3r", "p4", "p5", "p5b"];

    const desktopAllowed = sanitizePatternKeys(
      Array.isArray(cfg?.patterns) && cfg.patterns.length ? cfg.patterns : defaultKeys
    );

    const mobileEnabled = !!cfg?.mobile?.enabled;
    const mobileBreakpoint = Number(cfg?.mobile?.breakpoint ?? 560);
    const isMobile = mobileEnabled && width <= mobileBreakpoint;

    const mobileAllowed = sanitizePatternKeys(
      Array.isArray(cfg?.mobile?.patterns) && cfg.mobile.patterns.length ? cfg.mobile.patterns : desktopAllowed
    );

    const allowedKeys = isMobile ? mobileAllowed : desktopAllowed;

    const randomOn = cfg?.random !== false; // default true

    let i = 0;
    while (i < items.length) {
      const chunk = items.slice(i);

      // порядок попыток — только из allowed
      let order = allowedKeys.slice();

      if (randomOn) {
        order = shuffle(order);
      }

      let placed = null;
      for (const key of order) {
        const idx = keyToIndex[key];
        placed = patterns[idx](chunk);
        if (placed) break;
      }

      if (!placed) break;

      trackEl.appendChild(placed.group);
      i += placed.used;
    }
  }

  async function layout(containerEl, payload) {
    const trackEl = containerEl.querySelector(".jg__track") || containerEl;

    const cfg = payload && payload.config ? payload.config : {};
    const layoutMode = String(cfg.layout || "mosaic").toLowerCase();

    // mobile rowHeight override
    const mobileEnabled = !!cfg?.mobile?.enabled;
    const mobileBreakpoint = Number(cfg?.mobile?.breakpoint ?? 560);
    const isMobile = mobileEnabled && (trackEl.clientWidth || containerEl.clientWidth) <= mobileBreakpoint;

    const targetH = parseInt(
      String(isMobile && cfg?.mobile?.rowHeight ? cfg.mobile.rowHeight : (cfg.rowHeight ?? 220)),
      10
    );
    const gap = parseInt(String(cfg.gap ?? 8), 10);

    let items = normalizeItems(payload && payload.items ? payload.items : []);
    if (!items.length) return;

    const width = trackEl.clientWidth || containerEl.clientWidth;
    if (!width) {
      requestAnimationFrame(() => layout(containerEl, payload));
      return;
    }

    items = await ensureSizes(items);

    // Сейчас реализована mosaic (паттерны). Остальное — на будущее.
    if (layoutMode === "mosaic" || layoutMode === "justify" || layoutMode === "masonry") {
      renderMosaic(trackEl, items, width, gap, targetH, cfg);
      return;
    }

    renderMosaic(trackEl, items, width, gap, targetH, cfg);
  }

  window.JGMosaic = window.JGMosaic || {};

  window.JGMosaic.create = function (containerEl, payload) {
    layout(containerEl, payload);

    return {
      layout: function () {
        layout(containerEl, payload);
      },
      update: function (next) {
        payload = next || payload;
        layout(containerEl, payload);
      },
      destroy: function () {
        const trackEl = containerEl.querySelector(".jg__track") || containerEl;
        trackEl.innerHTML = "";
      },
    };
  };
})();
