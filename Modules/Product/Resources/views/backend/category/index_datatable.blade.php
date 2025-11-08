@extends('backend.layouts.app')

@section('title')
 {{ __($module_title) }}
@endsection

@push('after-styles')
    <link rel="stylesheet" href="{{ mix('modules/product/style.css') }}">
@endpush

@section('content')
    <div class="card">
        <div class="card-body">
          <x-backend.section-header>
            <div class="d-flex flex-wrap gap-3">
              <x-backend.quick-action url='{{route("backend.products-categories.bulk_action")}}'>
                <div class="">
                  <select name="action_type" class="form-control select2 col-12" id="quick-action-type" style="width:100%">
                      <option selected disabled value="">{{ __('messages.no_action') }}</option>
                      <option value="change-status">{{ __('messages.status') }}</option>
                      <option value="delete">{{ __('messages.delete') }}</option>
                  </select>
                </div>
                <div class="select-status d-none quick-action-field" id="change-status-action">
                    <select name="status" class="form-control select2" id="status" style="width:100%">
                      <option value="1">{{ __('messages.active') }}</option>
                      <option value="0">{{ __('messages.inactive') }}</option>
                    </select>
                </div>
              </x-backend.quick-action>
            </div>
            <x-slot name="toolbar">
              <div>
                  <div class="datatable-filter">
                      {{$filter['status']}}
                    <select name="column_status" id="column_status" class="select2 form-control" data-filter="select" style="width: 100%">
                      <option value="">{{__('messages.all')}}</option>
                      <option value="1" {{$filter['status'] == '1' ? "selected" : ''}}>{{ __('messages.active') }}</option>
                      <option value="0" {{$filter['status'] == '0' ? "selected" : ''}}>{{ __('messages.inactive') }}</option>
                    </select>
                  </div>
                </div>
              <div class="input-group flex-nowrap top-input-search">
                <span class="input-group-text" id="addon-wrapping"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" class="form-control dt-search" placeholder="{{ __('messages.search') }}..." aria-label="Search" aria-describedby="addon-wrapping">
              </div>
                @hasPermission('add_category')
                  <button type="button" class="btn btn-primary" data-crud-id="{{0}}"><i class="fas fa-plus-circle"></i> {{ __('messages.new') }}  </button>
                @endhasPermission
            </x-slot>
          </x-backend.section-header>
          <table id="datatable" class="table table-striped border table-responsive">
          </table>
        </div>
    </div>

    <div data-render="app">
      <category-form-offcanvas
              default-image="{{default_feature_image()}}"
              create-title="{{ __('messages.new') }} {{ __('category.singular_title') }} " edit-title="{{ __('messages.edit') }} {{ __('category.singular_title') }} "
              create-nested-title="{{ __('messages.new') }} {{ __('category.sub_category')}}" edit-nested-title="{{ __('messages.edit') }} {{ __('category.sub_category') }}"
              :customefield="{{ json_encode($customefield) }}" :is-sub-category="false">
      </category-form-offcanvas>
    </div>

    <!-- Brand Modal -->
    <div class="modal fade" id="brandModal" tabindex="-1" aria-labelledby="brandModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="brandModalLabel">All Brands</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="brandList" class="row">
                        <!-- Brands will be loaded here via JavaScript -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('after-styles')
    <!-- DataTables Core and Extensions -->
    <link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
@endpush

@push('after-scripts')
    <script src="{{ mix('modules/product/script.js') }}"></script>
    <script src="{{ asset('js/form-modal/index.js') }}" defer></script>


    <!-- DataTables Core and Extensions -->
    <script type="text/javascript" src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>

    <script type="text/javascript" defer>

    const columns = [
            {
                name: 'check',
                data: 'check',
                title: '<input type="checkbox" class="form-check-input" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                width: '2%',
                exportable: false,
                orderable: false,
                searchable: false,
            },
            { data: 'name', name: 'name', title: "{{ __('category.lbl_name') }}", width: '15%'},
            { data: 'brand_id', name: 'brand_id', title: "{{ __('category.lbl_brand') }}", width: '15%'},
            { data: 'updated_at', name: 'updated_at',  title: "{{ __('category.lbl_updated_at') }}", width: '15%'},
            { data: 'created_at', name: 'created_at',  title: "{{ __('category.lbl_created_at') }}",width: '15%' },
            { data: 'status', name: 'status', orderable: true,  searchable: true, title: "{{ __('category.lbl_status') }}",width: '5%'},

        ]

        const actionColumn = [
            { data: 'action', name: 'action', orderable: false, searchable: false, title: "{{ __('category.lbl_action') }}", width: '5%'}
        ]

        const customFieldColumns = JSON.parse(@json($columns))

        let finalColumns = [
            ...columns,
            ...customFieldColumns,
            ...actionColumn
        ]

        document.addEventListener('DOMContentLoaded', (event) => {
            initDatatable({
                url: '{{ route("backend.products-categories.index_data") }}',
                finalColumns,
                orderColumn: [[ 3, "desc" ]],
            })
        })

        const formOffcanvas = document.getElementById('form-offcanvas')
        const instance = bootstrap.Offcanvas.getOrCreateInstance(formOffcanvas)

        $(document).on('click', '[data-crud-id]', function() {
            setEditID($(this).attr('data-crud-id'), $(this).attr('data-parent-id'))
        })

        function setEditID(id, parent_id) {
            if (id !== '' || parent_id !== '') {
                const idEvent = new CustomEvent('crud_change_id', {
                    detail: {
                        form_id: id,
                        parent_id: parent_id
                    }
                })
                document.dispatchEvent(idEvent)
            } else {
                removeEditID()
            }
            instance.show()
        }

        function removeEditID() {
            const idEvent = new CustomEvent('crud_change_id', {
                detail: {
                    form_id: 0,
                    parent_id: null
                }
            })
            document.dispatchEvent(idEvent)
        }

        formOffcanvas?.addEventListener('hidden.bs.offcanvas', event => {
            removeEditID()
        })


      function resetQuickAction () {
        const actionValue = $('#quick-action-type').val();
        if (actionValue != '') {
            $('#quick-action-apply').removeAttr('disabled');

            if (actionValue == 'change-status') {
                $('.quick-action-field').addClass('d-none');
                $('#change-status-action').removeClass('d-none');
            } else {
                $('.quick-action-field').addClass('d-none');
            }
        } else {
            $('#quick-action-apply').attr('disabled', true);
            $('.quick-action-field').addClass('d-none');
        }
      }

      $('#quick-action-type').change(function () {
        resetQuickAction()
      });

      // Function to show all brands in modal from button data attributes
      function showAllBrandsFromButton(button) {
          console.log('showAllBrandsFromButton called with button:', button);
          
          try {
              // Get data from button attributes
              const categoryId = button.getAttribute('data-category-id');
              const categoryName = button.getAttribute('data-category-name');
              const brandsJson = button.getAttribute('data-brands');
              
              console.log('Extracted data:', { categoryId, categoryName, brandsJson });
              
              // Parse brands JSON
              let brands = [];
              try {
                  brands = JSON.parse(brandsJson);
                  console.log('Parsed brands:', brands);
              } catch (parseError) {
                  console.error('Error parsing brands JSON:', parseError);
                  console.error('Raw brands JSON:', brandsJson);
                  return;
              }
              
              // Call the main function
              showAllBrands(categoryId, brands, categoryName);z
              
          } catch (error) {
              console.error('Error in showAllBrandsFromButton:', error);
          }
      }

      // Function to show all brands in modal
      function showAllBrands(categoryId, brands, categoryName) {
          console.log('showAllBrands called with categoryId:', categoryId, 'brands:', brands, 'categoryName:', categoryName);
          
          try {
              // Validate inputs
              if (!categoryId || !brands || !categoryName) {
                  console.error('Missing required parameters:', { categoryId, brands, categoryName });
                  return;
              }
              
              // Update modal title
              const modalTitle = document.getElementById('brandModalLabel');
              if (modalTitle) {
                  modalTitle.textContent = 'All Brands for ' + categoryName;
              } else {
                  console.error('Modal title element not found');
              }
              
              const brandList = document.getElementById('brandList');
              console.log('brandList element:', brandList);
              
              if (!brandList) {
                  console.error('brandList element not found');
                  return;
              }
              
              // Clear existing content
              brandList.innerHTML = '';
              
              // Check if brands is an array
              if (!Array.isArray(brands)) {
                  console.error('Brands is not an array:', brands);
                  brandList.innerHTML = '<div class="col-12 text-center"><p class="text-muted">Error loading brands.</p></div>';
                  return;
              }
              
              if (brands.length === 0) {
                  brandList.innerHTML = '<div class="col-12 text-center"><p class="text-muted">No brands found.</p></div>';
                  return;
              }
              
              // Create brand cards
              brands.forEach(function(brand, index) {
                  console.log('Processing brand:', brand, 'at index:', index);
                  
                  const brandCard = document.createElement('div');
                  brandCard.className = 'col-md-4 col-sm-6 mb-3';
                  
                  // Safely get brand name
                  const brandName = brand && brand.name ? brand.name : 'Unknown Brand';
                  
                  brandCard.innerHTML = `
                      <div class="card h-100">
                          <div class="card-body text-center">
                              <h6 class="card-title">${brandName}</h6>
                              <span class="badge bg-primary">Brand</span>
                          </div>
                      </div>
                  `;
                  
                  brandList.appendChild(brandCard);
              });
              
              console.log('Brands loaded successfully. Total brands:', brands.length);
              
          } catch (error) {
              console.error('Error in showAllBrands:', error);
              const brandList = document.getElementById('brandList');
              if (brandList) {
                  brandList.innerHTML = '<div class="col-12 text-center"><p class="text-danger">Error loading brands.</p></div>';
              }
          }
      }

    </script>
@endpush

