<div class="offcanvas offcanvas-end" tabindex="-1" id="form-offcanvas-stock-blade" aria-labelledby="form-offcanvas-stockLabel" v-pre>
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="form-offcanvas-stockLabel">{{ __('product.add_stock') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="stock-form">
            <div class="form-group mb-3">
                <label class="form-label">{{ __('product.brand') }} <span class="text-danger">*</span></label>
                <select id="stock-brand" class="form-select select2" style="width:100%"></select>
                <small class="text-danger d-none" id="stock-brand-error"></small>
            </div>
            <div class="form-group mb-3">
                <label class="form-label" for="stock-category">{{ __('product.categories') }} <span class="text-danger">*</span></label>
                <select id="stock-category" class="form-select select2" multiple style="width:100%"></select>
                <small class="text-danger d-none" id="stock-category-error"></small>
            </div>
            <div class="form-group mb-3">
                <label class="form-label">{{ __('product.title') }}</label>
                <select id="stock-product" class="form-select select2" style="width:100%"></select>
                <small class="text-danger d-none" id="stock-product-error"></small>
            </div>
            <template id="stock-variations-template">
                <table class="table">
                    <thead>
                        <tr>
                            <th><label class="control-label m-0">{{ __('product.variation') }}</label></th>
                            <th><label class="control-label m-0">{{ __('product.stock') }}</label></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </template>
            <div id="stock-variations" class="d-none"></div>
            <div id="stock-simple" class="d-none">
                <label class="form-label">{{ __('product.stock') }}</label>
                <input type="number" id="stock-qty" class="form-control" min="1" value="1" />
            </div>
        </form>
    </div>
    <div class="offcanvas-footer border-top p-3 d-flex justify-content-end align-items-center gap-2">
        <button class="btn btn-outline-primary" type="button" data-bs-dismiss="offcanvas">{{ __('messages.close') }}</button>
        <button class="btn btn-primary" id="stock-save" disabled>{{ __('messages.save') }}</button>
    </div>
</div>

@push('after-scripts')
<script>
(function(){
  const ADMIN = '{{ url('app') }}';
  const api = {
    brand: ADMIN + '/brands/index_list',
    category: (brandId) => ADMIN + '/products-categories/index_list?brand_id=' + encodeURIComponent(brandId || ''),
    product: (categoryId) => ADMIN + '/products/index_list?category_id=' + encodeURIComponent(categoryId || ''),
    getVariations: (locationId, productId) => ADMIN + `/get-variation-stocks?location_id=${locationId}&product_id=${productId}`,
    save: ADMIN + '/stock-add'
  };

  const els = {
    brand: document.getElementById('stock-brand'),
    category: document.getElementById('stock-category'),
    product: document.getElementById('stock-product'),
    variations: document.getElementById('stock-variations'),
    simple: document.getElementById('stock-simple'),
    qty: document.getElementById('stock-qty'),
    save: document.getElementById('stock-save')
  };

  function initSingleSelect2(select){
    if(!select) return;
    if(!(window.$ && $.fn.select2)) return;
    const $el = $(select);
    const $containers = $el.siblings('.select2');
    if ($containers.length > 1) {
      $containers.not(':first').remove();
    }
    if (!$el.hasClass('select2-hidden-accessible')) {
      $el.select2({ width: '100%' });
    }
  }

  async function fetchJSON(url){
    const res = await fetch(url, {headers:{'X-Requested-With': 'XMLHttpRequest'}});
    const text = await res.text();
    let json;
    try {
      json = text ? JSON.parse(text) : {};
    } catch (err) {
      throw err;
    }
    return json;
  }

  async function loadBrands(){
    const data = await fetchJSON(api.brand).catch(err => { });
    fillSelect(els.brand, data);
  }
  async function loadCategories(brandId){
    const url = api.category(brandId);
    const data = await fetchJSON(url).catch(err => { });
    fillSelect(els.category, data);
  }
  async function loadCategoriesAll(){
    const url = ADMIN + '/products-categories/index_list';
    const data = await fetchJSON(url).catch(err => { });
    fillSelect(els.category, data);
  }
  async function loadProducts(categoryId){
    const url = api.product(categoryId);
    const data = await fetchJSON(url).catch(err => { });
    fillSelect(els.product, data);
  }
  async function loadProductsAll(){
    const url = ADMIN + '/products/index_list';
    const data = await fetchJSON(url).catch(err => { });
    fillSelect(els.product, data);
  }

  function fillSelect(select, list){
    if(!select) return;
    // if already select2, destroy before mutation
    if (window.$ && $.fn.select2 && $(select).hasClass('select2-hidden-accessible')) {
      $(select).select2('destroy');
    }
    select.innerHTML = '<option value="">{{ __('messages.select') }}</option>';
    (list || []).forEach(item => {
      const opt = document.createElement('option');
      opt.value = item.id || item.value || item;
      opt.textContent = item.name || item.label || item.text || item;
      select.appendChild(opt);
    });
    // ensure it is visible and enabled
    select.classList.remove('d-none');
    select.style.display = '';
    select.disabled = false;
    // Initialize Select2 safely
    initSingleSelect2(select);
  }

  function hasOption(select, value){
    return Array.from(select.options).some(o => String(o.value) === String(value));
  }

  function setSelectValue(select, value){
    if (!select) return false;
    const isArray = Array.isArray(value);
    const val = isArray ? value.map(v => String(v)) : String(value ?? '');
    if (window.$ && $.fn.select2 && $(select).hasClass('select2-hidden-accessible')) {
      $(select).val(val).trigger('change');
      return true;
    } else {
      if (isArray) {
        // For non-select2, set the first value only
        select.value = val[0] ?? '';
        return true;
      } else {
        select.value = val;
        return select.value === val;
      }
    }
  }

  function ensureOptions(select, ids) {
    if (!select || !Array.isArray(ids) || ids.length === 0) return;
    ids.forEach(id => {
      if (!hasOption(select, id)) {
        const opt = document.createElement('option');
        opt.value = String(id);
        opt.textContent = 'Selected category';
        select.appendChild(opt);
      }
    });
  }

  els.brand?.addEventListener('change', async e => {
    await loadCategories(e.target.value);
    if (els.category.options.length <= 1) {
      // brand returned no categories; show all categories instead
      await loadCategoriesAll();
    }
    // show all products so user can pick any
    await loadProductsAll();
  });
  els.category?.addEventListener('change', async e => {
    const selected = Array.from(e.target.selectedOptions || []).map(o => o.value);
    if (selected.length === 1) {
      await loadProducts(selected[0]);
    } else {
      await loadProductsAll();
    }
  });
  els.product?.addEventListener('change', async e => {
    const id = e.target.value;
    if(!id) return;
    const data = await fetchJSON(api.getVariations(1, id)).catch(err => { });
    renderStockSection(data);
    els.save.disabled = false;
  });

  function renderStockSection(payload){
    els.variations.classList.add('d-none');
    els.simple.classList.add('d-none');
    els.variations.innerHTML='';
    if(payload && payload.has_variation){
      els.variations.classList.remove('d-none');
      const table = document.createElement('table');
      table.className = 'table';
      table.innerHTML = `<thead><tr><th>{{ __('product.variation') }}</th><th>{{ __('product.stock') }}</th></tr></thead><tbody></tbody>`;
      const tbody = table.querySelector('tbody');
      (payload.data || []).forEach(v => {
        const name = (v?.value?.name ?? v?.name ?? v?.label ?? '');
        const stock = (v?.value?.stock ?? v?.stock ?? 1);
        const varId = (v?.value?.product_variation_id ?? v?.product_variation_id ?? '');
        const tr = document.createElement('tr');
        tr.innerHTML = `<td><input class=\"form-control\" value=\"${name}\" readonly></td>
                        <td><input class=\"form-control\" type=\"number\" min=\"1\" value=\"${stock}\" data-variation-id=\"${varId}\"></td>`;
        tbody.appendChild(tr);
      });
      els.variations.appendChild(table);
    } else {
      els.simple.classList.remove('d-none');
      const cur = (payload && payload.data) ? payload.data : {};
      els.qty.value = (cur.stock ?? 1);
      els.qty.dataset.variationId = cur.product_variation_id || '';
    }
  }

  els.save?.addEventListener('click', async () => {
    const productId = els.product.value;
    if(!productId) return;
    // collect brand and categories
    const brandId = els.brand ? els.brand.value : '';
    let categoryIds = [];
    if (window.$ && els.category) {
      categoryIds = $(els.category).val() || [];
    } else if (els.category) {
      categoryIds = Array.from(els.category.selectedOptions || []).map(o => o.value);
    }
    const hasVar = !els.variations.classList.contains('d-none');
    let body;
    if(hasVar){
      const rows = els.variations.querySelectorAll('input[data-variation-id]');
      body = { location_id: 1, product_id: productId, has_variation: 1, variations: Array.from(rows).map(r => ({product_variation_id: r.dataset.variationId, stock: r.value})), brand_id: brandId, category_ids: categoryIds };
    } else {
      body = { location_id: 1, product_id: productId, has_variation: 0, product_variation_id: els.qty.dataset.variationId, stock: els.qty.value, brand_id: brandId, category_ids: categoryIds };
    }
    const res = await fetch(api.save, {method:'POST', headers:{'Content-Type':'application/json', 'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN':'{{ csrf_token() }}'}, body: JSON.stringify(body)});
    const json = await res.json();
    if(json.status){
      if(window.successSnackbar) window.successSnackbar(json.message);
      if(window.renderedDataTable) window.renderedDataTable.ajax.reload(null,false);
      const oc = bootstrap.Offcanvas.getInstance(document.getElementById('form-offcanvas-stock-blade'));
      oc?.hide();
    } else {
      if(window.errorSnackbar) window.errorSnackbar(json.message || 'Error');
    }
  });

  // preload brands immediately (don't rely only on DOMContentLoaded/offcanvas events)
  try { loadBrands(); } catch (e) { }

  // When the offcanvas is shown, ensure selects are populated
  document.getElementById('form-offcanvas-stock-blade')?.addEventListener('shown.bs.offcanvas', () => {
    const needsBrand = !els.brand || els.brand.options.length <= 1;
    if (needsBrand) {
      loadBrands();
    }
    // Always make sure categories and products have something to show
    const needsCategory = !els.category || els.category.options.length <= 1;
    if (needsCategory) {
      loadCategoriesAll();
    }
    const needsProduct = !els.product || els.product.options.length <= 1;
    if (needsProduct) {
      loadProductsAll();
    }
  });

  // Listen for launcher button payload and prefill (brand/category/product)
  document.addEventListener('custom_form', async (e) => {
    const payload = e?.detail?.form_id || {};
    const brandId = payload.brand_id || '';
    const categoryIds = Array.isArray(payload.category_id) ? payload.category_id : (payload.category_id ? [payload.category_id] : []);
    const productId = payload.product_id || '';

    await loadBrands();
    if (brandId) {
      setSelectValue(els.brand, String(brandId));
      await loadCategories(brandId);
      if (els.category.options.length <= 1) {
        // if none for brand, load all so the product's category can be shown
        await loadCategoriesAll();
      }
    } else {
      // no brand in payload; ensure selects are initialized
      await loadCategoriesAll();
    }
    // load full products list first so the selected product is always present
    await loadProductsAll();
    // try to select categories (multi-select)
    let matchedCategory = '';
    for (const cid of categoryIds) {
      await loadProducts(cid);
      if (productId && hasOption(els.product, productId)) {
        matchedCategory = cid;
        break;
      }
    }
    // if not matched, pick first category id and load products
    if (!matchedCategory && categoryIds.length) {
      matchedCategory = categoryIds[0];
      await loadProducts(matchedCategory);
    }
    // If category dropdown came back empty (brand filter mismatch), inject selected category id so UI shows it
    if (els.category && els.category.options.length <= 1 && categoryIds.length) {
      ensureOptions(els.category, categoryIds);
    }
    if (categoryIds.length) setSelectValue(els.category, categoryIds);

    if (productId) {
      // ensure product option exists; if not, just set value (text won't show but section will load)
      if (!hasOption(els.product, productId)) {
        const opt = document.createElement('option');
        opt.value = String(productId);
        opt.textContent = 'Selected product';
        els.product.appendChild(opt);
      }
      setSelectValue(els.product, productId);
      const data = await fetchJSON(api.getVariations(1, productId));
      renderStockSection(data);
      document.getElementById('stock-save').disabled = false;
    }
  });
})();
</script>
@endpush

