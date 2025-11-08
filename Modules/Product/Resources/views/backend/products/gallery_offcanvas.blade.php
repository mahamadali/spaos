<div class="offcanvas offcanvas-end" tabindex="-1" id="product-gallery-form" aria-labelledby="product-gallery-formLabel" v-pre>
	<div class="offcanvas-header border-bottom">
		<h6 class="m-0 h5">
			{{ __('product.singular_title') }}: <span id="product-gallery-title-name">-</span>
		</h6>
		<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
	</div>
	<div class="d-flex flex-column border-bottom p-3">
		<div class="">
			<label class="form-label btn btn-secondary d-block my-0" for="product_gallery_file_input">{{ __('messages.upload_images') }}</label>
			<input type="file" class="form-control d-none" id="product_gallery_file_input" accept=".jpeg, .jpg, .png, .gif" multiple />
		</div>
	</div>
	<div class="offcanvas-body">
		<div id="product-gallery-empty" class="text-center mb-0 d-none">{{ __('messages.data_not_available') }}</div>
		<div id="product-gallery-grid" class="gallery-images"></div>
	</div>
	<div class="offcanvas-footer">
		<p class="text-center mb-0"><small>{{ __('messages.gallery_for_product') }}</small></p>
		<div class="d-grid gap-3 p-3">
			<button class="btn btn-primary d-block" id="product-gallery-save"><i class="fa-solid fa-floppy-disk mx-2"></i>{{ __('messages.update') }}</button>
			<button class="btn btn-outline-primary d-block" type="button" data-bs-dismiss="offcanvas"><i class="fa-solid fa-angles-left"></i>{{ __('messages.close') }}</button>
		</div>
	</div>
</div>

@push('after-scripts')
<script>
(function(){
	const ADMIN = '{{ url('app') }}';
	const api = {
		get: (id) => `${ADMIN}/products/gallery-images/${id}`,
		post: (id) => `${ADMIN}/products/gallery-images/${id}`,
	};

	const els = {
		file: document.getElementById('product_gallery_file_input'),
		grid: document.getElementById('product-gallery-grid'),
		empty: document.getElementById('product-gallery-empty'),
		save: document.getElementById('product-gallery-save'),
		titleName: document.getElementById('product-gallery-title-name'),
		offcanvas: document.getElementById('product-gallery-form'),
	};

	let productId = null;
	let images = [];

	function renderGrid(){
		els.grid.innerHTML = '';
		if(!images.length){
			els.empty.classList.remove('d-none');
			return;
		}
		els.empty.classList.add('d-none');
		images.forEach((img, index) => {
			const wrap = document.createElement('div');
			wrap.className = 'image-container col';
			wrap.innerHTML = `
				<button class="delete-button" type="button" data-index="${index}"><i class="fa-solid fa-xmark"></i></button>
				<img src="${img.full_url || ''}" alt="Selected Image" class="img-fluid selected-image" />
			`;
			wrap.querySelector('.delete-button').addEventListener('click', () => {
				images.splice(index, 1);
				renderGrid();
			});
			els.grid.appendChild(wrap);
		});
	}

	async function fetchJSON(url){
		const res = await fetch(url, {headers:{'X-Requested-With':'XMLHttpRequest'}});
		return await res.json();
	}

	function loadProductGallery(id){
		productId = id;
		images = [];
		els.titleName.textContent = '-';
		fetchJSON(api.get(id)).then(json => {
			if(json && json.status){
				els.titleName.textContent = json.product?.name || '-';
				images = (json.data || []).map(it => ({ id: it.id, full_url: it.full_url || it.get_first_media_url || '', file: null }));
				renderGrid();
			}
		});
	}

	els.file?.addEventListener('change', (e) => {
		const files = Array.from(e.target.files || []);
		files.forEach(file => {
			const reader = new FileReader();
			reader.onload = () => {
				images.push({ id: 'null', full_url: reader.result, file });
				renderGrid();
				els.file.value = '';
			};
			reader.readAsDataURL(file);
		});
	});

	els.save?.addEventListener('click', async () => {
		if(!productId) return;
		let formData = new FormData();
		images.forEach((img, index) => {
			formData.append(`gallery[${index}][id]`, String(img.id));
			if(img.file){
				formData.append(`gallery[${index}][file]`, img.file);
			}
		});
		const res = await fetch(api.post(productId), { method:'POST', headers:{ 'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN':'{{ csrf_token() }}' }, body: formData });
		const json = await res.json();
		if(json.status){
			if(window.successSnackbar) window.successSnackbar(json.message);
			const instance = bootstrap.Offcanvas.getInstance(els.offcanvas);
			instance?.hide();
		} else {
			if(window.errorSnackbar) window.errorSnackbar(json.message || 'Error');
		}
	});

	// Wire up launcher event
	document.addEventListener('product_gallery', (e) => {
		const id = e?.detail?.form_id;
		if(!id) return;
		loadProductGallery(id);
	});

	// Reset when offcanvas hidden
	els.offcanvas?.addEventListener('hidden.bs.offcanvas', () => {
		productId = null;
		images = [];
		renderGrid();
	});
})();
</script>
@endpush

