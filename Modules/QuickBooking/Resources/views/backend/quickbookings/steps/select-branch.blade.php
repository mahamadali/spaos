<div class="p-4">
  <h4 class="mb-3">{{ __('quick_booking.lbl_select_branch') }}</h4>
  <hr>

  <div id="qb-branch-list" class="row g-4"></div>

  <div class="d-flex justify-content-end mt-4">
    <button id="qb-branch-next" type="button" class="btn btn-primary" disabled onclick="qbNext()">{{ __('quick_booking.lbl_next') }}</button>
  </div>
</div>

@push('after-scripts')
<script>
  window.qbState = window.qbState || { user_id: @json($user_id), branch_id: null };

  (function(){
    var listEl = document.getElementById('qb-branch-list');
    var nextBtn = document.getElementById('qb-branch-next');

    function selectCard(card, branch){
      document.querySelectorAll('#qb-branch-list .qb-branch-card').forEach(function(c){ c.classList.remove('active'); });
      card.classList.add('active');
      window.qbState.branch_id = branch.id;
      nextBtn.disabled = false;
    }

    function renderBranch(branch){
      var col = document.createElement('div');
      col.className = 'col-md-4';

      var card = document.createElement('div');
      card.className = 'card qb-branch-card text-center shadow-sm';
      card.style.cursor = 'pointer';

      var body = document.createElement('div');
      body.className = 'card-body';

      var title = document.createElement('h5');
      title.className = 'card-title mb-2';
      title.textContent = branch.name;

      var address = document.createElement('p');
      address.className = 'text-muted mb-3';
      address.textContent = (branch.address_line_1 || '')
      
      body.appendChild(title);
      body.appendChild(address);
      card.appendChild(body);
      col.appendChild(card);

      card.addEventListener('click', function(){ selectCard(card, branch); });
      return col;
    }

    function fetchBranches(){
      var url = '{{ url('api/quick-booking/branch-list') }}' + '?user_id=' + encodeURIComponent(window.qbState.user_id);
      fetch(url, { headers: { 'Accept': 'application/json' }})
        .then(function(r){ return r.json(); })
        .then(function(res){
          listEl.innerHTML = '';
          if(res && res.status && Array.isArray(res.data)){
            if(res.data.length === 0){
              listEl.innerHTML = '<div class="col"><div class="alert alert-warning mb-0">{{ __('messages.no_data_found') }}</div></div>'
              return;
            }
            res.data.forEach(function(branch){ listEl.appendChild(renderBranch(branch)); });
          } else {
            listEl.innerHTML = '<div class="col"><div class="alert alert-danger mb-0">{{ __('messages.something_went_wrong') }}</div></div>'
          }
        })
        .catch(function(){
          listEl.innerHTML = '<div class="col"><div class="alert alert-danger mb-0">{{ __('messages.something_went_wrong') }}</div></div>'
        });
    }

    fetchBranches();
  })();
</script>
@endpush



