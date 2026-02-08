(function () {
  // Если Fancybox уже есть — ничего не делаем (он сам обработает data-fancybox)
  // (Проверки могут различаться по версиям, делаем мягко)
  const hasFancybox =
    (window.Fancybox && typeof window.Fancybox.bind === "function") ||
    (window.jQuery && typeof window.jQuery.fn?.fancybox === "function");

  if (hasFancybox) return;

  let state = {
    open: false,
    list: [],
    index: 0,
  };

  function q(sel, root) { return (root || document).querySelector(sel); }

  function buildUI() {
    if (q(".jg-lb")) return;

    const wrap = document.createElement("div");
    wrap.className = "jg-lb";
    wrap.innerHTML = `
      <div class="jg-lb__backdrop" data-jg-lb-close></div>
      <div class="jg-lb__dialog" role="dialog" aria-modal="true">
        <button class="jg-lb__btn jg-lb__close" aria-label="Close" data-jg-lb-close>×</button>
        <button class="jg-lb__btn jg-lb__prev" aria-label="Previous">‹</button>
        <button class="jg-lb__btn jg-lb__next" aria-label="Next">›</button>
        <div class="jg-lb__stage">
          <img class="jg-lb__img" alt="" />
        </div>
        <div class="jg-lb__counter"></div>
      </div>
    `;
    document.body.appendChild(wrap);

    wrap.addEventListener("click", (e) => {
      if (e.target && e.target.hasAttribute("data-jg-lb-close")) {
        e.preventDefault();
        close();
      }
    });

    q(".jg-lb__prev", wrap).addEventListener("click", (e) => { e.preventDefault(); prev(); });
    q(".jg-lb__next", wrap).addEventListener("click", (e) => { e.preventDefault(); next(); });

    document.addEventListener("keydown", (e) => {
      if (!state.open) return;
      if (e.key === "Escape") close();
      if (e.key === "ArrowLeft") prev();
      if (e.key === "ArrowRight") next();
    });
  }

  function open(list, index) {
    buildUI();
    state.open = true;
    state.list = list || [];
    state.index = index || 0;

    const root = q(".jg-lb");
    root.classList.add("is-open");
    document.documentElement.classList.add("jg-lb-open");

    render();
  }

  function close() {
    const root = q(".jg-lb");
    if (!root) return;
    state.open = false;
    root.classList.remove("is-open");
    document.documentElement.classList.remove("jg-lb-open");
  }

  function render() {
    const root = q(".jg-lb");
    if (!root) return;

    const img = q(".jg-lb__img", root);
    const counter = q(".jg-lb__counter", root);

    const item = state.list[state.index];
    if (!item) return;

    img.src = item.full;
    img.alt = item.alt || "";
    counter.textContent = `${state.index + 1} / ${state.list.length}`;
  }

  function prev() {
    if (!state.list.length) return;
    state.index = (state.index - 1 + state.list.length) % state.list.length;
    render();
  }

  function next() {
    if (!state.list.length) return;
    state.index = (state.index + 1) % state.list.length;
    render();
  }

  function findGalleryContainer(el) {
    return el.closest(".jg");
  }

  function collectItemsFromContainer(container) {
    try {
      const raw = container.getAttribute("data-jg");
      const payload = JSON.parse(raw);
      const items = (payload.items || []).map((it) => ({
        full: it.full || it.href || it.src,
        alt: it.alt || "",
      }));
      return items;
    } catch {
      return [];
    }
  }

  // Делегируем клики по ссылкам внутри .jg
  document.addEventListener("click", (e) => {
    const a = e.target && e.target.closest ? e.target.closest(".jg a[data-jg-full]") : null;
    if (!a) return;

    const container = findGalleryContainer(a);
    if (!container) return;

    // Если пользователь выбрал lightbox=link/none — не вмешиваемся
    // (link откроется сам, none мы предотвращаем в cell)
    // Тут мы работаем только для default/fancybox fallback
    e.preventDefault();

    const items = collectItemsFromContainer(container);
    if (!items.length) return;

    // индекс: ищем по совпадению full
    const full = a.dataset.jgFull;
    let idx = items.findIndex((x) => x.full === full);
    if (idx < 0) idx = 0;

    open(items, idx);
  });
})();
