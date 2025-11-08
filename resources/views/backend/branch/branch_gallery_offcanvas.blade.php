<div class="offcanvas offcanvas-end" tabindex="-1" id="branch-gallery-form" aria-labelledby="branch-gallery-formLabel" v-pre>
	<div class="offcanvas-header border-bottom">
		<h6 class="m-0 h5">
			{{ __('branch.singular_title') }}: <span id="branch-gallery-title-name">-</span>
		</h6>
		<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
	</div>
	<div class="d-flex flex-column border-bottom p-3">
		<div class="">
			<label class="form-label btn btn-secondary d-block my-0" for="branch_gallery_file_input">{{ __('messages.upload_images') }}</label>
			<input type="file" class="form-control d-none" id="branch_gallery_file_input" accept=".jpeg, .jpg, .png, .gif" multiple />
		</div>
	</div>
	<div class="offcanvas-body">
		<div id="branch-gallery-empty" class="text-center mb-0 d-none">{{ __('messages.data_not_available') }}</div>
		<div id="branch-gallery-grid" class="gallery-images"></div>
	</div>
	<div class="offcanvas-footer">
		<p class="text-center mb-0"><small>{{ __('messages.gallery_for_branch') }}</small></p>
		<div class="d-grid gap-3 p-3">
			<button class="btn btn-primary d-block" id="branch-gallery-save"><i class="fa-solid fa-floppy-disk mx-2"></i>{{ __('messages.update') }}</button>
			<button class="btn btn-outline-primary d-block" type="button" data-bs-dismiss="offcanvas"><i class="fa-solid fa-angles-left"></i>{{ __('messages.close') }}</button>
		</div>
	</div>
</div>

@push('after-scripts')
<script>
(function(){
	const ADMIN = '{{ url('app') }}';
	const api = {
		get: (id) => `${ADMIN}/branch/gallery-images/${id}`,
		post: (id) => `${ADMIN}/branch/gallery-images/${id}`,
	};

	let els = {};
	let branchId = null;
	let images = [];
	let eventListenersInitialized = false;
	let isSaving = false;

	function initializeElements() {
		els = {
			file: document.getElementById('branch_gallery_file_input'),
			grid: document.getElementById('branch-gallery-grid'),
			empty: document.getElementById('branch-gallery-empty'),
			save: document.getElementById('branch-gallery-save'),
			titleName: document.getElementById('branch-gallery-title-name'),
			offcanvas: document.getElementById('branch-gallery-form'),
		};
	}

	function renderGrid(){
		if (!els.grid) return;
		els.grid.innerHTML = '';
		if(!images.length){
			if (els.empty) els.empty.classList.remove('d-none');
			return;
		}
		if (els.empty) els.empty.classList.add('d-none');
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
		try {
			const res = await fetch(url, {headers:{'X-Requested-With':'XMLHttpRequest'}});
			return await res.json();
		} catch (error) {
			console.error('Fetch error:', error);
			return { status: false, message: 'Network error' };
		}
	}

	function loadBranchGallery(id){
		branchId = id;
		images = [];
		if (els.titleName) els.titleName.textContent = '-';
		fetchJSON(api.get(id)).then(json => {
			if(json && json.status){
				if (els.titleName) els.titleName.textContent = json.branch?.name || '-';
				images = (json.data || []).map(it => ({ id: it.id, full_url: it.full_url || it.get_first_media_url || '', file: null }));
				renderGrid();
			}
		}).catch(error => {
			console.error('Load gallery error:', error);
		});
	}

	function setupEventListeners() {
		// Prevent duplicate event listener registration
		if (eventListenersInitialized) return;
		eventListenersInitialized = true;

		if (els.file) {
			els.file.addEventListener('change', (e) => {
				const files = Array.from(e.target.files || []);
				files.forEach(file => {
					const reader = new FileReader();
					reader.onload = () => {
						// Check if this file is already in the images array
						const isDuplicate = images.some(img => 
							img.file && img.file.name === file.name && img.file.size === file.size
						);
						
						if (!isDuplicate) {
							images.push({ id: 'null', full_url: reader.result, file });
							renderGrid();
						}
						els.file.value = '';
					};
					reader.readAsDataURL(file);
				});
			});
		}

		if (els.save) {
			els.save.addEventListener('click', async () => {
				if (!branchId || isSaving) return;
				isSaving = true;
				try { els.save.disabled = true; } catch(e) {}
				let formData = new FormData();
				images.forEach((img, index) => {
					formData.append(`gallery[${index}][id]`, String(img.id));
					if(img.file){
						formData.append(`gallery[${index}][file]`, img.file);
					}
				});
				try {
					const res = await fetch(api.post(branchId), { 
						method:'POST', 
						headers:{ 
							'X-Requested-With':'XMLHttpRequest', 
							'X-CSRF-TOKEN':'{{ csrf_token() }}' 
						}, 
						body: formData 
					});
					const json = await res.json();
					if(json.status){
						if(window.successSnackbar) window.successSnackbar('Branch gallery updated successfully');
						if (els.offcanvas) {
							const instance = bootstrap.Offcanvas.getInstance(els.offcanvas);
							if (instance) instance.hide();
						}
					} else {
						if(window.errorSnackbar) window.errorSnackbar(json.message || 'Error');
					}
				} catch (error) {
					console.error('Save error:', error);
					if(window.errorSnackbar) window.errorSnackbar('Network error');
				} finally {
					isSaving = false;
					try { els.save.disabled = false; } catch(e) {}
				}
			});
		}

		if (els.offcanvas) {
			els.offcanvas.addEventListener('hidden.bs.offcanvas', () => {
				branchId = null;
				images = [];
				isSaving = false;
				renderGrid();
			});
		}
	}

	// Wire up launcher event
	document.addEventListener('branch_gallery', (e) => {
		const id = e?.detail?.form_id;
		if(!id) return;
		
		// Initialize elements if not already done
		initializeElements();
		setupEventListeners();
		
		loadBranchGallery(id);
		
		if (els.offcanvas) {
			try {
				const instance = bootstrap.Offcanvas.getOrCreateInstance(els.offcanvas);
				instance.show();
			} catch (error) {
				console.error('Offcanvas error:', error);
			}
		}
	});

	// Initialize on DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', () => {
			initializeElements();
			setupEventListeners();
		});
	} else {
		initializeElements();
		setupEventListeners();
	}
})();
</script>
@endpush

