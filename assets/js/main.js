/* GreenAmal · interactions (server-side cart via PHP API) */

document.addEventListener('DOMContentLoaded', () => {
  /* ========== Mobile menu ========== */
  const menuToggle = document.querySelector('.menu-toggle');
  const mainNav = document.querySelector('.main-nav');
  const mobileDrawer = document.getElementById('mobile-drawer');
  const mobileDrawerBackdrop = document.getElementById('mobile-drawer-backdrop');
  const mobileDrawerClose = document.querySelector('.mobile-drawer-close');

  const openMobileDrawer = () => {
    if (!mobileDrawer) return;
    mobileDrawer.classList.add('open');
    mobileDrawer.setAttribute('aria-hidden', 'false');
    mobileDrawerBackdrop?.classList.add('open');
    document.body.style.overflow = 'hidden';
  };
  const closeMobileDrawer = () => {
    if (!mobileDrawer) return;
    mobileDrawer.classList.remove('open');
    mobileDrawer.setAttribute('aria-hidden', 'true');
    mobileDrawerBackdrop?.classList.remove('open');
    document.body.style.overflow = '';
  };

  if (menuToggle) {
    menuToggle.addEventListener('click', () => {
      // Prefer the new mobile drawer if it's present (gd-2026 layout);
      // fall back to the legacy .main-nav dropdown.
      if (mobileDrawer) openMobileDrawer();
      else mainNav?.classList.toggle('open');
    });
  }
  mobileDrawerClose?.addEventListener('click', closeMobileDrawer);
  mobileDrawerBackdrop?.addEventListener('click', closeMobileDrawer);
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && mobileDrawer?.classList.contains('open')) closeMobileDrawer();
  });
  // Auto-close the drawer when a link inside is followed
  mobileDrawer?.querySelectorAll('a').forEach(a => a.addEventListener('click', () => setTimeout(closeMobileDrawer, 50)));

  const SHIPPING_THRESHOLD = parseFloat(document.body.dataset.shippingThreshold || '350');

  /* ========== Inject cart drawer ========== */
  const drawerHTML = `
    <div class="drawer-backdrop" id="drawer-backdrop"></div>
    <aside class="cart-drawer" id="cart-drawer" aria-label="Panier" role="dialog" aria-modal="true">
      <div class="cart-drawer-head">
        <h3>Mon panier <span class="count-pill" id="drawer-count">0</span></h3>
        <button class="drawer-close" aria-label="Fermer">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
      <div class="cart-drawer-shipping" id="drawer-shipping">
        <p>Plus que <strong>${SHIPPING_THRESHOLD} DH</strong> pour la livraison gratuite</p>
        <div class="progress-track"><div class="progress-fill" style="width:0%"></div></div>
      </div>
      <div class="cart-drawer-body" id="drawer-body"></div>
      <div class="cart-drawer-foot" id="drawer-foot"></div>
    </aside>
  `;
  document.body.insertAdjacentHTML('beforeend', drawerHTML);

  const drawer = document.getElementById('cart-drawer');
  const backdrop = document.getElementById('drawer-backdrop');
  const drawerBody = document.getElementById('drawer-body');
  const drawerFoot = document.getElementById('drawer-foot');
  const drawerCount = document.getElementById('drawer-count');
  const drawerShipping = document.getElementById('drawer-shipping');

  const openDrawer = () => {
    drawer.classList.add('open');
    backdrop.classList.add('open');
    document.body.style.overflow = 'hidden';
  };
  const closeDrawer = () => {
    drawer.classList.remove('open');
    backdrop.classList.remove('open');
    document.body.style.overflow = '';
  };

  drawer.querySelector('.drawer-close').addEventListener('click', closeDrawer);
  backdrop.addEventListener('click', closeDrawer);
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeDrawer();
  });

  /* ========== API ========== */
  async function api(endpoint, body = null) {
    const opts = body
      ? { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) }
      : { method: 'GET' };
    const res = await fetch('api/' + endpoint, opts);
    return res.json();
  }

  function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, s => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[s]));
  }

  /* ========== Render drawer from cart state ========== */
  function renderDrawerState(state) {
    const items = state.items || [];
    const subtotal = state.subtotal || 0;
    const count = state.count || 0;

    drawerCount.textContent = count;
    document.querySelectorAll('.cart-badge').forEach(el => {
      el.textContent = count;
      el.style.display = count > 0 ? 'grid' : 'none';
    });

    if (items.length === 0) {
      drawerBody.innerHTML = `
        <div class="cart-drawer-empty">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4">
            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <path d="M16 10a4 4 0 01-8 0"/>
          </svg>
          <h4>Votre panier est vide</h4>
          <p>Découvrez nos produits naturels du Maroc.</p>
          <a href="shop.php" class="btn btn-primary">Voir la boutique</a>
        </div>
      `;
      drawerFoot.innerHTML = '';
      drawerShipping.style.display = 'none';
      return;
    }

    drawerShipping.style.display = 'block';
    drawerBody.innerHTML = items.map(item => `
      <div class="drawer-item" data-id="${item.id}">
        <div class="drawer-item-image"><img src="${item.image}" alt="${escapeHtml(item.name)}"></div>
        <div class="drawer-item-info">
          <h4>${escapeHtml(item.name)}</h4>
          <div class="price">${(item.price * item.qty).toFixed(0)} DH</div>
          <div class="qty-mini">
            <button data-qty-minus aria-label="Diminuer">−</button>
            <input type="text" value="${item.qty}" readonly>
            <button data-qty-plus aria-label="Augmenter">+</button>
          </div>
        </div>
        <button class="drawer-item-remove" data-remove aria-label="Supprimer">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-2 14a2 2 0 01-2 2H9a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
        </button>
      </div>
    `).join('');

    drawerBody.querySelectorAll('.drawer-item').forEach(row => {
      const id = parseInt(row.dataset.id);
      row.querySelector('[data-qty-minus]').addEventListener('click', () => updateQty(id, -1, row));
      row.querySelector('[data-qty-plus]').addEventListener('click', () => updateQty(id, 1, row));
      row.querySelector('[data-remove]').addEventListener('click', () => removeItem(id));
    });

    drawerFoot.innerHTML = `
      <div class="total-row">
        <span>Sous-total</span>
        <span>${subtotal.toFixed(0)} DH</span>
      </div>
      <div class="drawer-cta">
        <a href="checkout.php" class="btn btn-primary btn-lg btn-block">
          Commander
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
        <a href="cart.php" class="btn btn-ghost btn-block">Voir le panier complet</a>
      </div>
      <div class="secure-line">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
        Paiement sécurisé · Livraison à domicile
      </div>
    `;

    const fill = drawerShipping.querySelector('.progress-fill');
    const txt = drawerShipping.querySelector('p');
    const pct = Math.min(100, (subtotal / SHIPPING_THRESHOLD) * 100);
    fill.style.width = pct + '%';
    if (subtotal >= SHIPPING_THRESHOLD) {
      txt.innerHTML = `<svg class="icon-inline" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="9 12 12 15 16 10"/></svg><strong>Livraison gratuite débloquée !</strong>`;
    } else {
      const remaining = (SHIPPING_THRESHOLD - subtotal).toFixed(0);
      txt.innerHTML = `Plus que <strong>${remaining} DH</strong> pour la livraison gratuite`;
    }
  }

  async function refreshCart(open = false) {
    const state = await api('cart-state.php');
    renderDrawerState(state);
    if (open) openDrawer();
  }

  async function updateQty(id, delta, row) {
    const input = row.querySelector('input');
    const newQty = parseInt(input.value) + delta;
    const state = await api('cart-update.php', { product_id: id, qty: Math.max(0, newQty) });
    renderDrawerState(state);
  }

  async function removeItem(id) {
    const state = await api('cart-remove.php', { product_id: id });
    renderDrawerState(state);
  }

  // Initial cart state
  refreshCart(false);

  /* ========== Add to cart ========== */
  document.querySelectorAll('[data-add-to-cart]').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      e.preventDefault();
      const card = btn.closest('[data-product-id]') || btn;
      const productId = parseInt(card.dataset.productId);
      if (!productId) return;
      const qtyInput = btn.closest('.pdp-actions, .pdp-info, .pdp')?.querySelector('.qty-selector input');
      const qty = qtyInput ? Math.max(1, parseInt(qtyInput.value) || 1) : 1;

      btn.style.opacity = '0.7';
      const state = await api('cart-add.php', { product_id: productId, qty });
      btn.style.opacity = '1';
      renderDrawerState(state);
      openDrawer();
    });
  });

  /* ========== Cart icon → open drawer ========== */
  document.querySelectorAll('a[href$="cart.php"].icon-btn, a[href="/panier"].icon-btn, a[href$="/panier"].icon-btn').forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      openDrawer();
    });
  });

  /* ========== Toast ========== */
  function showToast(message) {
    let toast = document.querySelector('.toast');
    if (!toast) {
      toast = document.createElement('div');
      toast.className = 'toast';
      toast.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg><span></span>`;
      document.body.appendChild(toast);
    }
    toast.querySelector('span').textContent = message;
    toast.classList.add('show');
    clearTimeout(toast._t);
    toast._t = setTimeout(() => toast.classList.remove('show'), 2800);
  }

  /* ========== Quantity selector (PDP) ========== */
  document.querySelectorAll('.qty-selector').forEach(qty => {
    const input = qty.querySelector('input');
    const [minus, plus] = qty.querySelectorAll('button');
    if (!input) return;
    minus?.addEventListener('click', () => { input.value = Math.max(1, parseInt(input.value) - 1); });
    plus?.addEventListener('click', () => { input.value = parseInt(input.value) + 1; });
  });

  /* ========== Cart page qty (server-driven) ========== */
  document.querySelectorAll('.cart-item .qty-mini').forEach(qty => {
    const row = qty.closest('.cart-item');
    if (!row) return;
    const id = parseInt(row.dataset.productId);
    if (!id) return;
    const input = qty.querySelector('input');
    const [minus, plus] = qty.querySelectorAll('button');
    minus?.addEventListener('click', async () => {
      const newQty = Math.max(0, parseInt(input.value) - 1);
      await api('cart-update.php', { product_id: id, qty: newQty });
      window.location.reload();
    });
    plus?.addEventListener('click', async () => {
      const newQty = parseInt(input.value) + 1;
      await api('cart-update.php', { product_id: id, qty: newQty });
      window.location.reload();
    });
  });

  document.querySelectorAll('.cart-item [data-cart-remove]').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      e.preventDefault();
      const id = parseInt(btn.dataset.cartRemove);
      await api('cart-remove.php', { product_id: id });
      window.location.reload();
    });
  });

  /* ========== PDP gallery ========== */
  const thumbs = document.querySelectorAll('.pdp-thumb');
  const mainContainer = document.querySelector('.pdp-main-image');
  thumbs.forEach(t => {
    t.addEventListener('click', () => {
      thumbs.forEach(x => x.classList.remove('active'));
      t.classList.add('active');
      if (!mainContainer) return;
      // Clone the thumb's <picture> (or <img>) into the main slot.
      // Cloning the full <picture> swaps its <source> srcsets too —
      // simply changing img.src doesn't, because <source> wins.
      const node = t.querySelector('picture, img');
      if (!node) return;
      const fresh = node.cloneNode(true);
      const finalImg = fresh.tagName === 'PICTURE' ? fresh.querySelector('img') : fresh;
      if (finalImg) {
        finalImg.removeAttribute('loading');
        finalImg.setAttribute('fetchpriority', 'high');
        finalImg.setAttribute('decoding', 'async');
      }
      mainContainer.innerHTML = '';
      mainContainer.appendChild(fresh);
    });
  });

  /* ========== PDP option pills ========== */
  document.querySelectorAll('.option-pills').forEach(group => {
    group.querySelectorAll('.option-pill').forEach(pill => {
      pill.addEventListener('click', () => {
        group.querySelectorAll('.option-pill').forEach(p => p.classList.remove('active'));
        pill.classList.add('active');
      });
    });
  });

  /* ========== PDP tabs ========== */
  const tabBtns = document.querySelectorAll('.tab-btn');
  const tabPanes = document.querySelectorAll('.tab-pane');
  tabBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      tabBtns.forEach(b => b.classList.remove('active'));
      tabPanes.forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      const target = document.getElementById(btn.dataset.tab);
      target?.classList.add('active');
    });
  });

  /* ========== Exit-intent newsletter modal ========== */
  const modal = document.getElementById('exit-modal');
  if (modal) {
    let shown = sessionStorage.getItem('exit-modal-shown');
    const open = () => {
      if (shown) return;
      modal.classList.add('open');
      sessionStorage.setItem('exit-modal-shown', '1');
      shown = '1';
    };
    document.addEventListener('mouseleave', (e) => { if (e.clientY < 5) open(); });
    setTimeout(() => { if (!shown) open(); }, 25000);
    modal.querySelector('.modal-close')?.addEventListener('click', () => modal.classList.remove('open'));
    modal.addEventListener('click', (e) => { if (e.target === modal) modal.classList.remove('open'); });
    modal.querySelector('.modal-form')?.addEventListener('submit', async (e) => {
      e.preventDefault();
      const email = e.target.querySelector('input[type=email]').value;
      await api('newsletter.php', { email });
      modal.classList.remove('open');
      showToast('Merci ! Code first25 envoyé par email.');
    });
  }

  document.querySelectorAll('.newsletter-form').forEach(form => {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const email = form.querySelector('input').value;
      await api('newsletter.php', { email });
      form.querySelector('input').value = '';
      showToast('Inscription confirmée. Bienvenue !');
    });
  });

  /* ========== Header scroll shadow ========== */
  /* ========== Sticky header — add .is-scrolled past 10px so the CSS can
                drop a shadow without inline styles. ========== */
  const header = document.querySelector('.site-header');
  if (header) {
    const onScroll = () => {
      header.classList.toggle('is-scrolled', window.scrollY > 10);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll(); // sync on load (in case we land mid-scroll)
  }

  /* ========== Header search dropdown — animated slide-down from header.
                Click toggles, Esc / outside-click / close-button dismisses,
                focus auto-moves into the input on open. ========== */
  const searchToggle = document.getElementById('searchToggle');
  const searchPanel  = document.getElementById('headerSearch');
  const searchClose  = document.getElementById('searchClose');
  const searchInput  = searchPanel?.querySelector('input[name="q"]');

  const openSearch = () => {
    if (!searchPanel) return;
    searchPanel.classList.add('open');
    searchPanel.setAttribute('aria-hidden', 'false');
    searchToggle?.setAttribute('aria-expanded', 'true');
    // Wait for the transition to start before focusing — focusing a
    // visibility:hidden element silently fails on some browsers.
    setTimeout(() => searchInput?.focus(), 60);
  };
  const closeSearch = () => {
    if (!searchPanel) return;
    searchPanel.classList.remove('open');
    searchPanel.setAttribute('aria-hidden', 'true');
    searchToggle?.setAttribute('aria-expanded', 'false');
  };

  searchToggle?.addEventListener('click', (e) => {
    e.preventDefault();
    if (searchPanel?.classList.contains('open')) closeSearch();
    else openSearch();
  });
  searchClose?.addEventListener('click', closeSearch);
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && searchPanel?.classList.contains('open')) closeSearch();
  });
  // Outside click — only when open, and only if the click missed both the
  // panel and the toggle button.
  document.addEventListener('click', (e) => {
    if (!searchPanel?.classList.contains('open')) return;
    if (searchPanel.contains(e.target) || searchToggle?.contains(e.target)) return;
    closeSearch();
  });
});
