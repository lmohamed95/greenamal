/* GreenAmal Admin — interactions */

document.addEventListener('DOMContentLoaded', () => {
  /* Toolbar tabs */
  document.querySelectorAll('.toolbar-tabs').forEach(group => {
    group.querySelectorAll('.toolbar-tab').forEach(tab => {
      tab.addEventListener('click', () => {
        group.querySelectorAll('.toolbar-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
      });
    });
  });

  /* Select-all checkbox */
  document.querySelectorAll('[data-select-all]').forEach(master => {
    const tableId = master.dataset.selectAll;
    const table = document.getElementById(tableId);
    if (!table) return;
    master.addEventListener('change', () => {
      table.querySelectorAll('tbody input[type="checkbox"]').forEach(cb => cb.checked = master.checked);
    });
  });

  /* Mobile sidebar drawer */
  const sidebar = document.querySelector('.sidebar');
  const sidebarToggle = document.getElementById('sidebar-toggle');
  const sidebarBackdrop = document.getElementById('sidebar-backdrop');
  const closeSidebar = () => {
    sidebar?.classList.remove('open');
    sidebarBackdrop?.classList.remove('open');
    document.body.style.overflow = '';
  };
  const openSidebar = () => {
    sidebar?.classList.add('open');
    sidebarBackdrop?.classList.add('open');
    document.body.style.overflow = 'hidden';
  };
  sidebarToggle?.addEventListener('click', () => {
    sidebar?.classList.contains('open') ? closeSidebar() : openSidebar();
  });
  sidebarBackdrop?.addEventListener('click', closeSidebar);
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeSidebar(); });
  document.querySelectorAll('.sidebar-link').forEach(link => {
    link.addEventListener('click', () => {
      if (window.innerWidth <= 900) closeSidebar();
    });
  });

  /* Topbar search keyboard shortcut */
  const search = document.querySelector('.topbar-search input');
  if (search) {
    document.addEventListener('keydown', (e) => {
      if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        search.focus();
      }
    });
  }

  /* ========================================================================
     Image upload widget
     Used on category-edit.php and product-edit.php.
     Markup: <div class="upload-widget" data-target="categories|products"
                  data-name="image_url" data-current="/path/to/img.jpg"></div>
     ======================================================================== */

  /* ========================================================================
     Crop modal — used by upload widget before sending the file
     ======================================================================== */
  const cropAspectFor = (target) => target === 'categories' ? 16 / 9 : 1;

  function openCropModal(file, aspectRatio) {
    return new Promise((resolve, reject) => {
      const fileUrl = URL.createObjectURL(file);

      const modal = document.createElement('div');
      modal.className = 'crop-modal open';
      modal.innerHTML = `
        <div class="crop-modal-content">
          <div class="crop-modal-head">
            <div>
              <h3>Recadrer l'image</h3>
              <div class="head-meta">Glissez les coins du cadre · ratio ${aspectRatio === 1 ? '1:1' : '16:9'}</div>
            </div>
            <button class="drawer-close" data-cancel aria-label="Annuler">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
          <div class="crop-modal-body">
            <img src="${fileUrl}" alt="">
          </div>
          <div class="crop-modal-foot">
            <div class="crop-tools">
              <button type="button" data-rotate="-90" title="Rotation gauche"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg></button>
              <button type="button" data-rotate="90" title="Rotation droite"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg></button>
              <button type="button" data-flip="x" title="Miroir horizontal"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 014-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 01-4 4H3"/></svg></button>
              <button type="button" data-reset title="Réinitialiser"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg></button>
            </div>
            <div class="crop-actions">
              <button type="button" class="btn btn-ghost" data-cancel>Annuler</button>
              <button type="button" class="btn btn-primary" data-confirm>Valider le recadrage</button>
            </div>
          </div>
        </div>
      `;
      document.body.appendChild(modal);
      document.body.style.overflow = 'hidden';

      const cleanup = () => {
        document.body.removeChild(modal);
        document.body.style.overflow = '';
        URL.revokeObjectURL(fileUrl);
        if (cropper) cropper.destroy();
      };

      const img = modal.querySelector('.crop-modal-body img');
      let cropper = null;

      img.onload = () => {
        cropper = new Cropper(img, {
          aspectRatio,
          viewMode: 1,
          autoCropArea: 1,
          movable: true,
          zoomable: true,
          rotatable: true,
          scalable: true,
          background: false,
          modal: true,
          guides: true,
          highlight: false,
          dragMode: 'move',
        });
      };

      modal.querySelectorAll('[data-cancel]').forEach(b => b.addEventListener('click', () => {
        cleanup();
        reject(new Error('cancelled'));
      }));

      modal.querySelector('[data-confirm]').addEventListener('click', () => {
        if (!cropper) return;
        cropper.getCroppedCanvas({
          maxWidth: 2000,
          maxHeight: 2000,
          fillColor: '#fff',
          imageSmoothingQuality: 'high',
        }).toBlob((blob) => {
          cleanup();
          resolve(blob);
        }, 'image/jpeg', 0.92);
      });

      modal.querySelectorAll('[data-rotate]').forEach(b => b.addEventListener('click', () => {
        if (cropper) cropper.rotate(parseFloat(b.dataset.rotate));
      }));
      modal.querySelectorAll('[data-flip]').forEach(b => b.addEventListener('click', () => {
        if (!cropper) return;
        const data = cropper.getData();
        cropper.scaleX(data.scaleX === -1 ? 1 : -1);
      }));
      modal.querySelector('[data-reset]').addEventListener('click', () => {
        if (cropper) cropper.reset();
      });

      // Esc closes
      const onKey = (e) => {
        if (e.key === 'Escape') {
          document.removeEventListener('keydown', onKey);
          cleanup();
          reject(new Error('cancelled'));
        }
      };
      document.addEventListener('keydown', onKey);
    });
  }

  /* ========================================================================
     Product gallery widget
     <div class="gallery-widget" data-product-id="N"></div>
     ======================================================================== */
  document.querySelectorAll('.gallery-widget').forEach(widget => {
    const productId = parseInt(widget.dataset.productId);
    if (!productId) {
      widget.innerHTML = `<div class="gallery-disabled">Enregistrez le produit une première fois pour pouvoir ajouter d'autres images à la galerie.</div>`;
      return;
    }

    const grid = document.createElement('div');
    grid.className = 'gallery-grid';
    widget.appendChild(grid);

    const render = (images) => {
      grid.innerHTML = '';
      images.forEach((img, i) => {
        const item = document.createElement('div');
        item.className = 'gallery-item';
        item.dataset.id = img.id;
        item.innerHTML = `
          <img src="${img.url}" alt="">
          <span class="gallery-item-order">#${i + 1}</span>
          <div class="gallery-item-actions">
            <button type="button" data-remove title="Supprimer">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-2 14a2 2 0 01-2 2H9a2 2 0 01-2-2L5 6"/></svg>
            </button>
          </div>
        `;
        item.querySelector('[data-remove]').addEventListener('click', async () => {
          if (!confirm('Supprimer cette image ?')) return;
          const res = await fetch('api/product-images.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'remove', id: img.id }),
          });
          const data = await res.json();
          if (data.ok) load();
        });
        grid.appendChild(item);
      });

      // Add button
      const addBtn = document.createElement('label');
      addBtn.className = 'gallery-add';
      addBtn.innerHTML = `
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        <span>Ajouter</span>
        <input type="file" accept="image/jpeg,image/png,image/webp,image/gif">
      `;
      const fileInput = addBtn.querySelector('input');
      fileInput.addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (!file) return;
        try {
          const blob = await openCropModal(file, 1);  // products = 1:1
          const fd = new FormData();
          fd.append('image', blob, file.name);
          fd.append('target', 'products');

          addBtn.style.opacity = '0.5';
          const upRes = await fetch('upload.php', { method: 'POST', body: fd });
          const upData = await upRes.json();
          addBtn.style.opacity = '1';
          fileInput.value = '';

          if (!upData.ok) {
            alert('Upload échoué : ' + (upData.message || upData.error));
            return;
          }

          // Attach to product
          const attachRes = await fetch('api/product-images.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'add', product_id: productId, url: upData.url }),
          });
          const attachData = await attachRes.json();
          if (attachData.ok) load();
        } catch (err) {
          // user cancelled crop
        }
      });
      grid.appendChild(addBtn);
    };

    const load = async () => {
      const res = await fetch(`api/product-images.php?product_id=${productId}`);
      const data = await res.json();
      if (data.ok) render(data.images || []);
    };

    load();
  });

  document.querySelectorAll('.upload-widget').forEach(widget => {
    const target = widget.dataset.target || 'products';
    const inputName = widget.dataset.name || 'image_url';
    const currentUrl = widget.dataset.current || '';
    const cropAspect = cropAspectFor(target);

    widget.innerHTML = `
      <div class="upload-preview" ${currentUrl ? '' : 'style="display:none"'}>
        <img src="${currentUrl}" alt="">
        <div class="upload-preview-actions">
          <button type="button" data-replace title="Remplacer">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg>
          </button>
          <button type="button" data-remove title="Supprimer">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
      </div>

      <label class="upload-zone" ${currentUrl ? 'style="display:none"' : ''}>
        <input type="file" accept="image/jpeg,image/png,image/webp,image/gif">
        <div class="upload-zone-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        </div>
        <div class="upload-zone-title">Glissez-déposez ou <strong>parcourir</strong></div>
        <div class="upload-zone-hint">JPG, PNG, WebP — max 5 Mo</div>
      </label>

      <div class="upload-progress" style="display:none">
        <div class="upload-progress-bar"></div>
      </div>

      <div class="upload-error" style="display:none"></div>

      <input type="hidden" name="${inputName}" value="${currentUrl}">

      <div class="upload-url-row" style="display:none">
        <input type="text" readonly>
        <a class="upload-url-toggle" data-toggle-url>masquer</a>
      </div>
      <a class="upload-url-toggle" data-toggle-url style="align-self:flex-start">Coller une URL externe à la place</a>
    `;

    const zone = widget.querySelector('.upload-zone');
    const fileInput = widget.querySelector('input[type="file"]');
    const preview = widget.querySelector('.upload-preview');
    const previewImg = widget.querySelector('.upload-preview img');
    const hidden = widget.querySelector(`input[name="${inputName}"]`);
    const progress = widget.querySelector('.upload-progress');
    const progressBar = widget.querySelector('.upload-progress-bar');
    const errorEl = widget.querySelector('.upload-error');
    const replaceBtn = widget.querySelector('[data-replace]');
    const removeBtn = widget.querySelector('[data-remove]');
    const urlRow = widget.querySelector('.upload-url-row');
    const urlInput = urlRow.querySelector('input');
    const urlToggles = widget.querySelectorAll('[data-toggle-url]');

    const showError = (msg) => {
      errorEl.textContent = msg;
      errorEl.style.display = 'block';
    };
    const clearError = () => { errorEl.style.display = 'none'; };

    const setUrl = (url) => {
      hidden.value = url;
      previewImg.src = url;
      urlInput.value = url;
      preview.style.display = url ? 'block' : 'none';
      zone.style.display = url ? 'none' : 'flex';
    };

    const sendBlob = (blob, originalName) => {
      const fd = new FormData();
      fd.append('image', blob, originalName || 'cropped.jpg');
      fd.append('target', target);

      const xhr = new XMLHttpRequest();
      xhr.open('POST', 'upload.php');
      progress.style.display = 'block';
      progressBar.style.width = '0%';

      xhr.upload.addEventListener('progress', (e) => {
        if (e.lengthComputable) {
          progressBar.style.width = (e.loaded / e.total * 90) + '%';
        }
      });
      xhr.onload = () => {
        progressBar.style.width = '100%';
        setTimeout(() => { progress.style.display = 'none'; }, 400);
        try {
          const res = JSON.parse(xhr.responseText);
          if (res.ok) {
            setUrl(res.url);
          } else {
            showError(res.message || res.error || 'Erreur de téléversement.');
          }
        } catch (err) {
          showError('Réponse serveur invalide.');
        }
      };
      xhr.onerror = () => {
        progress.style.display = 'none';
        showError('Erreur réseau.');
      };
      xhr.send(fd);
    };

    const upload = async (file) => {
      clearError();
      if (!file.type.match(/^image\/(jpeg|png|webp|gif)$/)) {
        showError('Format non supporté. Utilisez JPG, PNG, WebP ou GIF.');
        return;
      }
      if (file.size > 15 * 1024 * 1024) {
        showError(`Fichier trop volumineux (${(file.size / 1024 / 1024).toFixed(1)} Mo). Maximum 15 Mo.`);
        return;
      }

      // Open the crop modal first
      try {
        const blob = await openCropModal(file, cropAspect);
        sendBlob(blob, file.name);
      } catch (e) {
        // User cancelled — do nothing
      }
    };

    fileInput.addEventListener('change', (e) => {
      const file = e.target.files[0];
      if (file) upload(file);
    });

    // Drag and drop
    ['dragenter', 'dragover'].forEach(evt => {
      zone.addEventListener(evt, (e) => { e.preventDefault(); zone.classList.add('is-dragover'); });
    });
    ['dragleave', 'drop'].forEach(evt => {
      zone.addEventListener(evt, (e) => { e.preventDefault(); zone.classList.remove('is-dragover'); });
    });
    zone.addEventListener('drop', (e) => {
      const file = e.dataTransfer?.files?.[0];
      if (file) upload(file);
    });

    replaceBtn?.addEventListener('click', () => {
      preview.style.display = 'none';
      zone.style.display = 'flex';
      fileInput.value = '';
    });
    removeBtn?.addEventListener('click', () => {
      setUrl('');
    });

    // URL fallback toggle
    urlToggles.forEach(toggle => {
      toggle.addEventListener('click', (e) => {
        e.preventDefault();
        const isOpen = urlRow.style.display !== 'none';
        urlRow.style.display = isOpen ? 'none' : 'flex';
        urlInput.removeAttribute('readonly');
        urlInput.focus();
      });
    });
    urlInput.addEventListener('input', (e) => {
      const v = e.target.value.trim();
      if (v) setUrl(v);
    });
  });
});
