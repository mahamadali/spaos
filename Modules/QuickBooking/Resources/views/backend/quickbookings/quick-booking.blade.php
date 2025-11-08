<div class="booking-wizard">
  <div class="container-fluid p-0">
      <div class="modal-content-wrapper">
      <div class="widget-layout">
      <div class="non-printable">
        <div class="iq-card iq-card-sm bg-primary widget-tabs">
          <ul class="tab-list" id="qb-tab-list">
            @php
              $steps = [
                ['id'=>1,'title'=>'quick_booking.lbl_select_branch','type'=>'select-branch','detail'=>'quick_booking.lbl_brach_description','is_visible'=>true,'next'=>2,'prev'=>null],
                ['id'=>2,'title'=>'quick_booking.lbl_select_service','type'=>'select-service','detail'=>'quick_booking.lbl_service_description','is_visible'=>true,'next'=>3,'prev'=>1],
                ['id'=>3,'title'=>'quick_booking.lbl_select_select_staff','type'=>'select-employee','detail'=>'quick_booking.lbl_staff_description','is_visible'=>true,'next'=>4,'prev'=>2],
                ['id'=>4,'title'=>'quick_booking.lbl_select_date_time','type'=>'select-date-time','detail'=>'quick_booking.lbl_sate_time_description','is_visible'=>true,'next'=>5,'prev'=>3],
                ['id'=>5,'title'=>'quick_booking.lbl_customer_detail','type'=>'customer-details','detail'=>'quick_booking.lbl_customer_description','is_visible'=>true,'next'=>6,'prev'=>4],
                ['id'=>6,'title'=>'quick_booking.lbl_confrimation','type'=>'select-confirm','detail'=>'quick_booking.lbl_confrim_msg','is_visible'=>true,'next'=>null,'prev'=>5],
              ];
              $currentIndex = 1;
            @endphp
            @foreach ($steps as $step)
              @if($step['is_visible'])
                <li class="tab-item {{ $currentIndex === $step['id'] ? 'active' : '' }}" data-id="{{ $step['id'] }}" data-next="{{ $step['next'] }}" data-prev="{{ $step['prev'] }}">
                  <a class="tab-link" href="#{{ $step['type'] }}" id="{{ $step['type'] }}-tab">
                    <div class="step-content">
                      <div class="step-title">{{ __($step['title']) }}</div>
                    @if($step['detail'])
                        <div class="step-description">{{ __($step['detail']) }}</div>
                    @endif
                    </div>
                  </a>
                </li>
              @endif
            @endforeach
          </ul>
        </div>
      </div>
      <div class="widget-pannel">
        <div id="wizard-tab" class="iq-card iq-card-sm tab-content">
          {{-- Step 1: Branch --}}
          <div id="select-branch" class="iq-fade iq-tab-pannel active" data-id="1" data-next="2" data-prev="">
            <div class="p-4">
              <h4 class="mb-3">{{ __('quick_booking.lbl_select_branch') }}</h4>
              <hr>
              <div id="qb-branch-list" class="row g-4"></div>
              <div class="qb-step-footer d-flex justify-content-end gap-2">
                <button id="qb-branch-next" class="btn btn-primary" type="button"  disabled="" onclick="qbNext()">{{ __('quick_booking.lbl_next') }}</button>
              </div>
            </div>
          </div>

          <script>
          (function(){
            var isBound = false;
            function bindCustomerForm(){
              if (isBound) return;
              var form = document.getElementById('qb-customer-form');
              if(!form) return;
              isBound = true;
              form.addEventListener('submit', function(e){
                e.preventDefault();
                // collect values
                var firstName = document.getElementById('qb-first-name') ? document.getElementById('qb-first-name').value.trim() : '';
                var lastName = document.getElementById('qb-last-name') ? document.getElementById('qb-last-name').value.trim() : '';
                var email = document.getElementById('qb-email') ? document.getElementById('qb-email').value.trim() : '';
                var countryCodeSel = document.getElementById('qb-country-code');
                var countryCode = countryCodeSel ? countryCodeSel.value : '';
                var phone = document.getElementById('qb-phone') ? document.getElementById('qb-phone').value.trim() : '';
                var genderEl = document.querySelector('input[name="qb-gender"]:checked');
                var gender = genderEl ? genderEl.value : '';

                window.qbState = window.qbState || {};
                window.qbState.customer = {
                  first_name: firstName,
                  last_name: lastName,
                  email: email,
                  country_code: countryCode,
                  phone: phone,
                  gender: gender
                };

                // proceed to next step (confirmation)
                if (typeof qbNext === 'function') { qbNext(); }
              });
            }
            // Bind when the customer step becomes active (capture event at document level)
            document.addEventListener('qb:step:activated', function(ev){
              var target = ev.target || ev.srcElement;
              if (target && target.id === 'customer-details') {
                // give the browser a tick to paint the panel, then bind
                setTimeout(bindCustomerForm, 0);
              }
            }, true);
          })();
    // Year spinner functions - prevent going to previous years
    function incrementYear() {
      const yearInput = document.getElementById('current-year');
      const currentYear = parseInt(yearInput.value) || 2025;
      yearInput.value = currentYear + 1;
      updateCalendar();
    }

    function decrementYear() {
      const yearInput = document.getElementById('current-year');
      const currentYear = parseInt(yearInput.value) || 2025;
      const actualCurrentYear = new Date().getFullYear();
      
      // Prevent going to previous years - stop at current year
      if (currentYear > actualCurrentYear) {
        yearInput.value = currentYear - 1;
        updateCalendar();
      }
    }

    // Show current year on hover
    document.getElementById('current-year').addEventListener('mouseenter', function() {
      const actualCurrentYear = new Date().getFullYear();
      this.title = `Current year: ${actualCurrentYear}`;
    });
  </script>

          {{-- Step 2: Service --}}
          <div id="select-service" class="iq-fade iq-tab-pannel" data-id="2" data-next="3" data-prev="1">
            <div class="p-4">
              <h4 class="mb-3">{{ __('quick_booking.lbl_select_service') }}</h4>
              <hr>
              <div id="qb-service-list" class="row g-4"></div>
              <div class="qb-step-footer d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" onclick="qbPrev()">{{ __('quick_booking.lbl_back') }}</button>
                <button id="qb-service-next" type="button" class="btn btn-primary" disabled onclick="qbNext()">{{ __('quick_booking.lbl_next') }}</button>
              </div>
            </div>
          </div>

          {{-- Step 3: Employee --}}
          <div id="select-employee" class="iq-fade iq-tab-pannel" data-id="3" data-next="4" data-prev="2">
            <div class="p-4">
              <h4 class="mb-3">{{ __('quick_booking.lbl_select_select_staff') }}</h4>
              <hr>
              <div id="qb-employee-list" class="row g-4"></div>
              <div class="qb-step-footer d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" onclick="qbPrev()">{{ __('quick_booking.lbl_back') }}</button>
                <button id="qb-employee-next" type="button" class="btn btn-primary" disabled onclick="qbNext()">{{ __('quick_booking.lbl_next') }}</button>
              </div>
            </div>
          </div>

          {{-- Step 4: Date & Time --}}
          <div id="select-date-time" class="iq-fade iq-tab-pannel" data-id="4" data-next="5" data-prev="3">
            <div class="select-date-time-container">
              <h4 class="card-title">Select Date & Time</h4>
              <div class="main-content">
                <!-- Date Selection Section (Left Side) -->
                <div>
                  <div class="calendar-container">
                    <div id="qb-calendar" class="calendar-widget">
                      <div class="calendar-header">
                        <div class="calendar-title">
                          <div class="month-selector">
                            <span id="current-month">August</span>
                            <i class="fas fa-chevron-down">⌄</i>
                          </div>
                          <div class="year-selector">
                            <input type="text" id="current-year" class="year-input" value="2025" readonly>
                            <div class="year-spinner">
                              <button type="button" class="spinner-btn" onclick="incrementYear()">▲</button>
                              <button type="button" class="spinner-btn" onclick="decrementYear()">▼</button>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="calendar-weekdays">
                        <div>Sun</div>
                        <div>Mon</div>
                        <div>Tue</div>
                        <div>Wed</div>
                        <div>Thu</div>
                        <div>Fri</div>
                        <div>Sat</div>
                      </div>
                      <div id="calendar-days" class="calendar-days"></div>
                    </div>
                  </div>
                </div>

                <!-- Time Selection Section (Right Side) -->
                <div>
                  <div id="qb-times" class="time-slots-container"></div>
                </div>
              </div>

              <!-- Navigation Buttons -->
              <div class="qb-step-footer d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" onclick="qbPrev()">{{ __('quick_booking.lbl_back') }}</button>
                <button id="qb-datetime-next" type="button" class="btn btn-primary" disabled onclick="qbNext()">{{ __('quick_booking.lbl_next') }}</button>
              </div>
            </div>
          </div>

          {{-- Step 5: Customer Details --}}
          <div id="customer-details" class="iq-fade iq-tab-pannel" data-id="5" data-next="6" data-prev="4">
            <div class="p-4">
              <h4 class="mb-3">Customer Detail</h4>
              <hr>

              <form id="qb-customer-form" class="customer-form">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="qb-first-name" placeholder="Enter first name" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="qb-last-name" placeholder="Enter last name" required>
                  </div>

                  <div class="col-12">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="qb-email" placeholder="Enter email address" required>
                  </div>

                  <div class="col-12">
                    <label class="form-label">Phone Number<span class="text-danger">*</span></label>
                    <div class="input-group">
                      <select class="form-select" id="qb-country-code" style="max-width: 110px">
                        <option value="+1">🇺🇸 +1</option>
                        <option value="+44">🇬🇧 +44</option>
                        <option value="+91" selected>🇮🇳 +91</option>
                        <option value="+61">🇦🇺 +61</option>
                        <option value="+971">🇦🇪 +971</option>
                      </select>
                      <input type="tel" class="form-control" id="qb-phone" placeholder="Enter a phone number" required>
                    </div>
                  </div>

                  <div class="col-12">
                    <label class="form-label d-block">Gender</label>
                    <div class="d-flex align-items-center gap-4">
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="qb-gender" id="qb-gender-male" value="male">
                        <label class="form-check-label" for="qb-gender-male">Male</label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="qb-gender" id="qb-gender-female" value="female">
                        <label class="form-check-label" for="qb-gender-female">Female</label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="qb-gender" id="qb-gender-other" value="other">
                        <label class="form-check-label" for="qb-gender-other">Other</label>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="qb-step-footer d-flex justify-content-end gap-2 mt-4">
                  <button type="button" class="btn btn-secondary" onclick="qbPrev()">Back</button>
                  <button type="submit" class="btn btn-primary">Submit</button>
                </div>
              </form>
            </div>
          </div>
          <div id="select-confirm" class="iq-fade iq-tab-pannel" data-id="6" data-next="" data-prev="5">
            <div class="p-4">
              <h4 class="mb-3">Confirmation</h4>
              <hr>
              <div id="qb-confirm-summary" class="text-center py-5">
                <!-- summary will render here -->
              </div>
              <div class="qb-step-footer d-flex justify-content-center gap-2">
                <button type="button" class="btn btn-primary-subtle" id="qb-book-more">
                  <i class="fa fa-plus me-1"></i> Book More Appointments
                </button>
                <button type="button" class="btn btn-primary" id="qb-view-detail">View Detail</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    </div>
  </div>
</div>

@push('after-scripts')
<script>

  (function(){
    var current = 1;
    window.qbState = window.qbState || { user_id: @json($user_id), branch_id: null, service_id: null, employee_id: null, date: null, start_date_time: null };
    // Function to check if a step is completed
    function isStepCompleted(stepId) {
      var state = window.qbState || {};
      switch(stepId) {
        case 1: // Branch selection
          return state.branch_id !== null && state.branch_id !== undefined;
        case 2: // Service selection
          return state.service_id !== null && state.service_id !== undefined;
        case 3: // Employee selection
          return state.employee_id !== null && state.employee_id !== undefined;
        case 4: // Date & Time selection
          return state.date !== null && state.date !== undefined && state.start_date_time !== null && state.start_date_time !== undefined;
        case 5: // Customer details
          return state.customer && state.customer.first_name && state.customer.email && state.customer.phone;
        case 6: // Confirmation
          return isStepCompleted(1) && isStepCompleted(2) && isStepCompleted(3) && isStepCompleted(4) && isStepCompleted(5);
        case 7: // Confirmation detail
          return isStepCompleted(6);
        default:
          return false;
      }
    }

    function setActive(id){
      // Check if the step is accessible
      if (id > 1 && !isStepCompleted(id - 1)) {
        return; // Silently prevent navigation
      }
      
      current = id;
      document.querySelectorAll('#qb-tab-list .tab-item').forEach(function(li){
        li.classList.toggle('active', Number(li.dataset.id) === current);
      });
      document.querySelectorAll('#wizard-tab .iq-tab-pannel').forEach(function(p){
        var isActive = Number(p.dataset.id) === current;
        var wasActive = p.classList.contains('active');
        p.classList.toggle('active', isActive);
        if (isActive && !wasActive) {
          p.dispatchEvent(new CustomEvent('qb:step:activated', { bubbles: true }));
        }
      });
    }
    document.querySelectorAll('#qb-tab-list .tab-item .tab-link').forEach(function(a){
      a.addEventListener('click', function(e){
        e.preventDefault();
        var parent = a.closest('.tab-item');
        var stepId = Number(parent.dataset.id);
        
        // Check if step is accessible
        if (stepId > 1 && !isStepCompleted(stepId - 1)) {
          return; // Silently prevent navigation
        }
        
        setActive(stepId);
      });
    });
    window.qbNext = function(){
      var panel = document.querySelector('#wizard-tab .iq-tab-pannel.active');
      var next = Number(panel.dataset.next);
      if(next){ setActive(next); }
    };
    window.qbPrev = function(){
      var panel = document.querySelector('#wizard-tab .iq-tab-pannel.active');
      var prev = Number(panel.dataset.prev);
      if(prev){ setActive(prev); }
    };
  })();
</script>

<script>
  // Step 1: Branch list
  (function(){
    var listEl = document.getElementById('qb-branch-list');
    var nextBtn = document.getElementById('qb-branch-next');
    if(!listEl) return;
    function selectCard(card, branch){
      document.querySelectorAll('#qb-branch-list .qb-card').forEach(function(c){ c.classList.remove('active'); });
      card.classList.add('active');
      window.qbState.branch_id = branch.id;
      window.qbState.branch = branch; // Store the full branch object
      nextBtn.disabled = false;
      // reset downstream selections
      window.qbState.service_id = null; window.qbState.employee_id = null;
      window.qbState.service = null; window.qbState.employee = null;
    }
    function renderCard(parent, b){
      var col = document.createElement('div'); col.className = 'col-md-6';
      var card = document.createElement('div'); card.className = 'card iq-branch-box qb-card'; card.style.cursor='pointer';
      var body = document.createElement('div'); body.className = 'card-body';

      // header: image + name + address line
      var head = document.createElement('div'); head.className = 'text-center mb-3';
      var imgWrap = document.createElement('div'); imgWrap.className = 'branch-image';
      var img = document.createElement('img'); img.className = 'avatar-70 rounded-circle'; img.loading='lazy'; img.alt='feature-image'; img.src = b.feature_image || '{{ asset('images/blank.png') }}';
      imgWrap.appendChild(img); head.appendChild(imgWrap);

      var title = document.createElement('h4'); title.className='mb-1 mt-3'; title.textContent = b.name || 'No Branch';
      head.appendChild(title);

      var addr = document.createElement('p'); addr.className='m-0 text-muted';
      var a = b.address || {}; var city = (a.city_data && a.city_data.name) || '-'; var state = (a.state_data && a.state_data.name) || '-'; var country = (a.country_data && a.country_data.name) || '-'; var zip = a.postal_code || '000000';
      addr.textContent = [city, state, country].join(' , ') + ' , ' + zip;
      head.appendChild(addr);
      body.appendChild(head);

      var hr = document.createElement('hr'); body.appendChild(hr);

      var ul = document.createElement('ul'); ul.className='iq-contact-detail list-unstyled p-0 m-0';
      var liPhone = document.createElement('li'); liPhone.className='d-flex align-items-center mb-2';
      var phoneIcon = document.createElement('span'); 
      phoneIcon.className='contact-icon me-2';
      phoneIcon.innerHTML = '📞';
      phoneIcon.style.color = '#000000';
      phoneIcon.style.fontSize = '1rem';
      var phoneP = document.createElement('span'); phoneP.className='contact-text'; phoneP.textContent = b.contact_number || '-';
      liPhone.appendChild(phoneIcon); liPhone.appendChild(phoneP); ul.appendChild(liPhone);

      var liMail = document.createElement('li'); liMail.className='d-flex align-items-center';
      var mailIcon = document.createElement('span'); 
      mailIcon.className='contact-icon me-2';
      mailIcon.innerHTML = '✉';
      mailIcon.style.color = '#000000';
      mailIcon.style.fontSize = '1rem';
      var mailP = document.createElement('span'); mailP.className='contact-text'; mailP.textContent = b.contact_email || '-';
      liMail.appendChild(mailIcon); liMail.appendChild(mailP); ul.appendChild(liMail);

      body.appendChild(ul);
      card.appendChild(body); col.appendChild(card); parent.appendChild(col);
      return {col, card};
    }
    function fetchBranches(){
      var url='{{ url('api/quick-booking/branch-list') }}?user_id='+encodeURIComponent(window.qbState.user_id);
      fetch(url,{headers:{'Accept':'application/json'}}).then(r=>r.json()).then(function(res){
        listEl.innerHTML='';
        (res.data||[]).forEach(function(branch){ var n=renderCard(listEl, branch); n.card.addEventListener('click', function(){ selectCard(n.card, branch); }); });
      });
    }
    
    // Reset button state when branch step is activated
    document.querySelector("#select-branch").addEventListener('qb:step:activated', function(){
      if (window.qbState.branch_id) {
        nextBtn.disabled = false;
      } else {
        nextBtn.disabled = true;
      }
    });
    
    fetchBranches();
  })();

  // Step 2: Services for selected branch
  (function(){
    var listEl = document.getElementById('qb-service-list');
    var nextBtn = document.getElementById('qb-service-next');
    if(!listEl) return;
    function selectCard(card, service){
      document.querySelectorAll('#qb-service-list .qb-card').forEach(function(c){ c.classList.remove('active'); });
      card.classList.add('active');
      window.qbState.service_id = service.id || service.service_id || service;
      window.qbState.service = service; // Store the full service object
      nextBtn.disabled = false;
    }
    function renderService(service){
      var col = document.createElement('div'); col.className='col-md-4';
      var card = document.createElement('div'); card.className='card iq-branch-box qb-card text-center'; card.style.cursor='pointer';
      var body = document.createElement('div'); body.className='card-body';

      var title = document.createElement('h6'); title.className='mb-2'; title.textContent = service.service_name || service.name;
      var duration = document.createElement('div'); duration.className='text-muted small mb-2'; duration.textContent = (service.duration_min || 0) + ' min';

      var priceWrap = document.createElement('div'); priceWrap.className = 'qb-price-pill m-auto';
      var branchPrice = 0;
      if (service.branches && service.branches.length > 0) {
        var b0 = service.branches[0];
        branchPrice = b0.service_price || (b0.pivot ? b0.pivot.service_price : 0) || 0;
      }
      var finalPrice = branchPrice || service.default_price || service.service_price || service.price || 0;
      priceWrap.textContent = (window.currencyFormat ? window.currencyFormat(finalPrice) : finalPrice);

      body.appendChild(title);
      body.appendChild(duration);
      body.appendChild(priceWrap);
      card.appendChild(body); col.appendChild(card);
      card.addEventListener('click', function(){ selectCard(card, service); });
      return col;
    }
    function loadServices(){
      nextBtn.disabled = true; listEl.innerHTML='';
      if(!window.qbState.branch_id) return;
      var url='{{ url('api/quick-booking/services-list') }}?branch_id='+window.qbState.branch_id;
      fetch(url,{headers:{'Accept':'application/json'}}).then(r=>r.json()).then(function(res){
        (res.data||[]).forEach(function(s){ listEl.appendChild(renderService(s)); });
      });
    }
    // whenever branch changes and we enter step 2
    document.querySelector("#select-service").addEventListener('qb:step:activated', loadServices);
  })();

  // Step 3: Employees for branch
  (function(){
    var listEl = document.getElementById('qb-employee-list');
    var nextBtn = document.getElementById('qb-employee-next');
    if(!listEl) return;
    function selectCard(card, employee){
      document.querySelectorAll('#qb-employee-list .qb-card').forEach(function(c){ c.classList.remove('active'); });
      card.classList.add('active');
      window.qbState.employee_id = employee.id;
      window.qbState.employee = employee; // Store the full employee object
      nextBtn.disabled = false;
    }
    function renderEmployee(emp){
      var col=document.createElement('div'); col.className='col-md-4';
      var card=document.createElement('div'); card.className='card iq-branch-box qb-card text-center'; card.style.cursor='pointer';
      var body=document.createElement('div'); body.className='card-body';
      var avatarWrap = document.createElement('div'); avatarWrap.className='d-flex justify-content-center align-items-center mb-3';
      var img=document.createElement('img'); img.className='avatar-90 rounded-circle'; img.alt='staff'; img.loading='lazy'; img.src = emp.profile_image || emp.avatar || '{{ asset('images/blank.png') }}';
      avatarWrap.appendChild(img); body.appendChild(avatarWrap);
      var h6=document.createElement('h6'); h6.textContent = emp.full_name || emp.name || ('#'+emp.id);
      body.appendChild(h6); card.appendChild(body); col.appendChild(card);
      card.addEventListener('click', function(){ selectCard(card, emp); });
      return col;
    }
    function loadEmployees(){
      nextBtn.disabled=true; listEl.innerHTML='';
      if(!window.qbState.branch_id || !window.qbState.service_id) return;
      var url='{{ url('api/quick-booking/employee-list') }}?branch_id='+window.qbState.branch_id+'&service_id='+window.qbState.service_id;
      fetch(url,{headers:{'Accept':'application/json'}}).then(r=>r.json()).then(function(res){
        (res.data||[]).forEach(function(e){ listEl.appendChild(renderEmployee(e)); });
      });
    }
    document.querySelector("#select-employee").addEventListener('qb:step:activated', loadEmployees);
  })();

  // Step 4: Slot date/time
  (function(){
    var calendarEl = document.getElementById('qb-calendar');
    var timesEl = document.getElementById('qb-times');
    var nextBtn = document.getElementById('qb-datetime-next');
    var currentMonthEl = document.getElementById('current-month');
    var currentYearEl = document.getElementById('current-year');
    var calendarDaysEl = document.getElementById('calendar-days');
    
    if(!calendarEl) return;
    
    var currentDate = new Date();
    var selectedDate = new Date();
    var currentMonth = currentDate.getMonth();
    var currentYear = currentDate.getFullYear();
    
    // Initialize calendar
    function initCalendar() {
      updateCalendarHeader();
      renderCalendar();
    }
    
    function updateCalendarHeader() {
      var monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                       'July', 'August', 'September', 'October', 'November', 'December'];
      currentMonthEl.textContent = monthNames[currentMonth];
      currentYearEl.textContent = currentYear;
    }
    
    function renderCalendar() {
      calendarDaysEl.innerHTML = '';
      
      var firstDay = new Date(currentYear, currentMonth, 1);
      var lastDay = new Date(currentYear, currentMonth + 1, 0);
      var startDate = new Date(firstDay);
      startDate.setDate(startDate.getDate() - firstDay.getDay());
      
      for (var i = 0; i < 42; i++) {
        var dayDate = new Date(startDate);
        dayDate.setDate(startDate.getDate() + i);
        
        var dayEl = document.createElement('div');
        dayEl.className = 'calendar-day';
        dayEl.textContent = dayDate.getDate();
        
        // Check if it's today
        if (dayDate.toDateString() === new Date().toDateString()) {
          dayEl.classList.add('today');
        }
        
        // Check if it's selected
        if (dayDate.toDateString() === selectedDate.toDateString()) {
          dayEl.classList.add('selected');
        }
        
        // Check if it's from other month
        if (dayDate.getMonth() !== currentMonth) {
          dayEl.classList.add('other-month');
        }
        
        // Check if it's disabled (past dates)
        if (dayDate < new Date().setHours(0, 0, 0, 0)) {
          dayEl.classList.add('disabled');
        } else {
          dayEl.addEventListener('click', function() {
            selectDate(dayDate);
          });
        }
        
        calendarDaysEl.appendChild(dayEl);
      }
    }
    
    function selectDate(date) {
      selectedDate = date;
      window.qbState.date = date.toISOString().slice(0, 10);
      renderCalendar();
      fetchSlots();
    }
    
    // Month selector
    document.querySelector('.month-selector').addEventListener('click', function() {
      var monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                       'July', 'August', 'September', 'October', 'November', 'December'];
      
      // Get current date for comparison
      var currentDate = new Date();
      var currentYearValue = currentDate.getFullYear();
      var currentMonthValue = currentDate.getMonth();
      
      // Create a simple dropdown effect
      var monthDropdown = document.createElement('div');
      monthDropdown.className = 'month-dropdown';
      monthDropdown.style.cssText = `
        position: absolute;
        top: 100%;
        left: 0;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        z-index: 1000;
        max-height: 200px;
        overflow-y: auto;
      `;
      
      monthNames.forEach(function(month, index) {
        // Check if month should be shown based on selected year
        var shouldShowMonth = true;
        
        if (currentYear === currentYearValue) {
          // If current year is selected, only show current month and future months
          if (index < currentMonthValue) {
            shouldShowMonth = false;
          }
        }
        // If future year is selected, show all months
        
        if (shouldShowMonth) {
          var monthOption = document.createElement('div');
          monthOption.className = 'month-option';
          monthOption.textContent = month;
          monthOption.style.cssText = `
            padding: 0.5rem 1rem;
            cursor: pointer;
            transition: background 0.2s ease;
          `;
          
          if (index === currentMonth) {
            monthOption.style.background = 'var(--bs-primary)';
            monthOption.style.color = 'white';
          }
          
          monthOption.addEventListener('click', function() {
            currentMonth = index;
            updateCalendarHeader();
            renderCalendar();
            document.body.removeChild(monthDropdown);
          });
          
          monthOption.addEventListener('mouseenter', function() {
            if (index !== currentMonth) {
              this.style.background = 'rgba(var(--bs-primary-rgb), 0.1)';
            }
          });
          
          monthOption.addEventListener('mouseleave', function() {
            if (index !== currentMonth) {
              this.style.background = 'transparent';
            }
          });
          
          monthDropdown.appendChild(monthOption);
        }
      });
      
      // Position the dropdown
      var monthSelector = document.querySelector('.month-selector');
      monthSelector.style.position = 'relative';
      monthSelector.appendChild(monthDropdown);
      
      // Close dropdown when clicking outside
      setTimeout(function() {
        document.addEventListener('click', function closeDropdown(e) {
          if (!monthSelector.contains(e.target)) {
            if (monthDropdown.parentNode) {
              monthDropdown.parentNode.removeChild(monthDropdown);
            }
            document.removeEventListener('click', closeDropdown);
          }
        });
      }, 0);
    });
    
    
    function renderTimes(slots){
      timesEl.innerHTML=''; 
      nextBtn.disabled = true;
      
      if(!slots || slots.length === 0) {
        timesEl.innerHTML = '<div class="empty-message"><p class="empty-text">No time slots available for this date.</p></div>';
        return;
      }
      
      (slots||[]).forEach(function(slot){
        if(!slot.value) return;
        
        var timeSlotWrapper = document.createElement('div');
        timeSlotWrapper.className = 'time-slot-wrapper';
        
        var input = document.createElement('input');
        input.type = 'radio';
        input.id = 'time-' + slot.value;
        input.name = 'timeSlot';
        input.className = 'time-slot-input';
        input.dataset.value = slot.value;
        
        var label = document.createElement('label');
        label.htmlFor = 'time-' + slot.value;
        label.className = 'time-slot-button';
        
        // Check if slot is available (you can modify this logic based on your data structure)
        var isAvailable = slot.is_available !== false && slot.value !== '';
        if (!isAvailable) {
          label.className = 'time-slot-button disabled';
          input.disabled = true;
          label.textContent = slot.label + ' (XT)';
        } else {
          label.textContent = slot.label;
        }
        
        input.addEventListener('change', function(){
          if (input.disabled) return;
          
          document.querySelectorAll('#qb-times .time-slot-input').forEach(function(b){ 
            b.checked = false; 
          });
          input.checked = true;
          window.qbState.start_date_time = slot.value; 
          nextBtn.disabled = false;
        });
        
        timeSlotWrapper.appendChild(input);
        timeSlotWrapper.appendChild(label);
        timesEl.appendChild(timeSlotWrapper);
      });
    }
    
    function fetchSlots(){
      if(!window.qbState.branch_id || !window.qbState.employee_id || !window.qbState.service_id || !window.qbState.date){ 
        timesEl.innerHTML = '<div class="empty-message"><p class="empty-text">Please select a date to view available time slots.</p></div>';
        return; 
      }
      
      timesEl.innerHTML = '<div class="loading-skeleton"><div class="time-slot-skeleton"></div><div class="time-slot-skeleton"></div><div class="time-slot-skeleton"></div><div class="time-slot-skeleton"></div><div class="time-slot-skeleton"></div><div class="time-slot-skeleton"></div></div>';
      
      var url='{{ url('api/quick-booking/slot-time-list') }}?branch_id='+window.qbState.branch_id+'&employee_id='+window.qbState.employee_id+'&service_id='+window.qbState.service_id+'&date='+window.qbState.date;
      fetch(url,{headers:{'Accept':'application/json'}}).then(r=>r.json()).then(function(res){ 
        setTimeout(function() {
          renderTimes(res.data||res); 
        }, 500);
      }).catch(function() {
        timesEl.innerHTML = '<div class="empty-message"><p class="empty-text">Error loading time slots. Please try again.</p></div>';
      });
    }
    
    // Initialize calendar and set default date
    initCalendar();
    selectDate(new Date());
    document.querySelector("[data-id='4']").addEventListener('transitionstart', function() {
      if (window.qbState.date) {
        fetchSlots();
      }
    }, { once: false });
  })();
</script>

<script>
// Render confirmation summary when step 6 activates
(function(){
  var confirmPanel = document.getElementById('select-confirm');
  if(!confirmPanel) return;
  
  function createBooking() {
    var s = window.qbState || {};
    var customer = s.customer || {};
    
    if (!customer.first_name || !customer.email || !customer.phone) {
      alert('Please complete customer details first');
      return;
    }
    
    if (!s.branch_id || !s.service_id || !s.employee_id || !s.date || !s.start_date_time) {
      alert('Please complete all booking details first');
      return;
    }
    
    var bookingData = {
      user: {
        first_name: customer.first_name,
        last_name: customer.last_name || '',
        email: customer.email,
        phone: customer.phone,
        country_code: customer.country_code || '+91',
        gender: customer.gender || 'other'
      },
      booking: {
        branch_id: s.branch_id,
        service_id: s.service_id,
        employee_id: s.employee_id,
        date: s.date,
        start_date_time: s.start_date_time,
        services: [{
          service_id: s.service_id,
          employee_id: s.employee_id,
          start_date_time: s.start_date_time,
          service_price: s.service && s.service.branches && s.service.branches.length > 0 ? 
            (s.service.branches[0].service_price || s.service.branches[0].pivot?.service_price || 0) : 0,
          duration_min: s.service ? (s.service.duration_min || 30) : 30
        }]
      }
    };
    
    // Show loading state
    var wrap = document.getElementById('qb-confirm-summary');
    wrap.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-3">Creating your booking...</p></div>';
    
    console.log('Sending booking data:', bookingData);
    
    fetch('{{ url('api/quick-booking/store') }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(bookingData)
    })
    .then(response => {
      console.log('Response status:', response.status);
      return response.json();
    })
    .then(data => {
      console.log('Response data:', data);
      if (data.status) {
        // Store the booking response for detail view
        window.qbState.bookingResponse = data.data;
        renderConfirm(data.data);
      } else {
        alert('Error creating booking: ' + (data.message || 'Unknown error'));
        wrap.innerHTML = '<div class="text-center py-5 text-danger"><p>Error creating booking. Please try again.</p></div>';
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Error creating booking. Please try again.');
      wrap.innerHTML = '<div class="text-center py-5 text-danger"><p>Error creating booking. Please try again.</p></div>';
    });
  }
  
  function renderConfirm(bookingData){
    var wrap = document.getElementById('qb-confirm-summary');
    if(!wrap) return;
    
    var s = window.qbState || {};
    var customer = s.customer || {};
    var serviceName = 'Selected Service';
    var staffName = 'Selected Staff';
    var dateStr = s.date || '-';
    var timeStr = (s.start_date_time || '').split(' ').pop() || '-';
    
    // If we have booking data, use it
    if (bookingData) {
      if (bookingData.services && bookingData.services.length > 0) {
        serviceName = bookingData.services[0].service_name || 'Selected Service';
      }
      if (bookingData.employee) {
        staffName = bookingData.employee.full_name || bookingData.employee.name || 'Selected Staff';
      }
      
      // Store the booking data for the detail view
      window.qbState.bookingResponse = bookingData;
    }

    wrap.innerHTML = ''+
      '<div class="mb-4">'+
      '  <div class="mx-auto mb-3" style="width:96px;height:96px;border:4px solid #22c55e;border-radius:50%;display:flex;align-items:center;justify-content:center;">'+
      '    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>'+
      '  </div>'+
      '  <h5 class="fw-semibold">Your Appointment is Booked Successfully!</h5>'+
      '  <p class="text-muted">Please check your email for verification</p>'+
      '</div>'+
      '<div class="row g-3 justify-content-center text-start" style="max-width:720px;margin:0 auto;">'+
      // '  <div class="col-md-6">'+
      // '    <div class="p-3 rounded" style="background:rgba(var(--bs-primary-rgb), .08);">'+
      // '      <div class="small text-muted mb-2">Customer Info</div>'+
      // '      <div><strong>Name:</strong> '+(customer.first_name||'-')+' '+(customer.last_name||'')+'</div>'+
      // '      <div><strong>Email:</strong> '+(customer.email||'-')+'</div>'+
      // '      <div><strong>Number:</strong> '+(customer.country_code||'')+' '+(customer.phone||'-')+'</div>'+
      // '    </div>'+
      // '  </div>'+
      // '  <div class="col-md-6">'+
      // '    <div class="p-3 rounded" style="background:rgba(var(--bs-primary-rgb), .08);">'+
      // '      <div class="small text-muted mb-2">Appointment Summary</div>'+
      // '      <div><strong>Service:</strong> '+serviceName+'</div>'+
      // '      <div><strong>Staff:</strong> '+staffName+'</div>'+
      // '      <div><strong>Date:</strong> '+dateStr+'</div>'+
      // '      <div><strong>Time:</strong> '+timeStr+'</div>'+
      // '    </div>'+
      // '  </div>'+
      '</div>';
  }
  
  confirmPanel.addEventListener('qb:step:activated', createBooking);
  document.getElementById('qb-book-more')?.addEventListener('click', function(){ 
    // Go to first step instead of reloading
    setActive(1);
  });
  document.getElementById('qb-view-detail')?.addEventListener('click', function(){
    // Show detailed confirmation in the same step
    showConfirmationDetail();
  });
  
  
  // Function to show detailed confirmation in the same step
  function showConfirmationDetail() {
    const state = window.qbState || {};
    const bookingResponse = state.bookingResponse || {};
    
    // Time formatting function
    function formatTime(timeString) {
      if (!timeString || timeString === '-') return 'N/A';
      
      var time = new Date('2000-01-01 ' + timeString);
      if (isNaN(time.getTime())) return 'N/A';
      
      var hours = time.getHours().toString().padStart(2, '0');
      var minutes = time.getMinutes().toString().padStart(2, '0');
      
      return hours + ':' + minutes;
    }
    
    // Debug: Log the current state
    console.log('Current booking state:', state);
    console.log('Booking response:', bookingResponse);
    
    // Get data from booking response first, then fallback to state and form data
    const branchName = bookingResponse.branch?.name || state.branch?.name || state.branch_name || state.branchName || document.querySelector('[name="branch_id"]')?.selectedOptions[0]?.text || 'N/A';
    const serviceName = bookingResponse.services?.[0]?.service_name || state.service?.name || state.service_name || state.serviceName || document.querySelector('[name="service_id"]')?.selectedOptions[0]?.text || 'N/A';
    const staffName = bookingResponse.employee?.name || bookingResponse.employee?.full_name || bookingResponse.employee?.first_name || state.employee?.name || state.employee?.first_name || state.employee_name || state.employeeName || document.querySelector('[name="employee_id"]')?.selectedOptions[0]?.text || 'N/A';
    const dateStr = bookingResponse.date || (state.date ? new Date(state.date).toLocaleDateString() : (state.selectedDate ? new Date(state.selectedDate).toLocaleDateString() : 'N/A'));
    const timeStr = bookingResponse.start_date_time ? formatTime(new Date(bookingResponse.start_date_time).toTimeString().split(' ')[0]) : (state.start_date_time ? formatTime(new Date(state.start_date_time).toTimeString().split(' ')[0]) : (state.selectedTime ? state.selectedTime : 'N/A'));
    const customerName = bookingResponse.user?.first_name || bookingResponse.customer?.first_name || state.customer?.first_name || state.customer_name || state.customerName || document.querySelector('[name="customer_name"]')?.value || 'N/A';
    const customerEmail = bookingResponse.user?.email || bookingResponse.customer?.email || state.customer?.email || state.customer_email || state.customerEmail || document.querySelector('[name="customer_email"]')?.value || 'N/A';
    const customerPhone = bookingResponse.user?.phone || bookingResponse.customer?.phone || state.customer?.phone || state.customer_phone || state.customerPhone || document.querySelector('[name="customer_phone"]')?.value || 'N/A';
    
    // Get branch address object and format it
    const branchAddressObj = bookingResponse.branch?.address || state.branch?.address || state.branch_address || state.branchAddress || null;
    
    // Format the address object into a readable string
    let branchAddress = 'N/A';
    if (branchAddressObj && typeof branchAddressObj === 'object') {
      const parts = [];
      if (branchAddressObj.address_line_1) parts.push(branchAddressObj.address_line_1);
      if (branchAddressObj.address_line_2) parts.push(branchAddressObj.address_line_2);
      if (branchAddressObj.city) parts.push(branchAddressObj.city);
      if (branchAddressObj.state) parts.push(branchAddressObj.state);
      if (branchAddressObj.postal_code) parts.push(branchAddressObj.postal_code);
      branchAddress = parts.join(', ');
    } else if (typeof branchAddressObj === 'string') {
      branchAddress = branchAddressObj;
    } else {
      // Fallback: try to get address from branch object directly
      const branch = bookingResponse.branch || state.branch || {};
      if (branch.address_line_1 || branch.address_line_2 || branch.city || branch.state || branch.postal_code) {
        const parts = [];
        if (branch.address_line_1) parts.push(branch.address_line_1);
        if (branch.address_line_2) parts.push(branch.address_line_2);
        if (branch.city) parts.push(branch.city);
        if (branch.state) parts.push(branch.state);
        if (branch.postal_code) parts.push(branch.postal_code);
        branchAddress = parts.join(', ');
      }
    }
    
    // Debug: Log the extracted values
    console.log('Extracted values:', {
      branchName, serviceName, staffName, dateStr, timeStr, 
      customerName, customerEmail, customerPhone, branchAddress
    });
    
    // Generate detailed confirmation HTML matching the second image design exactly
    const detailHTML = `
      <div class="confirmation-detail-content">
        <div class="row g-4">
          <div class="col-md-6">
            <div class="info-card mb-4">
              <h6 class="info-title">SALON INFO</h6>
              <div class="info-content">
                <p class="mb-2"><strong>${branchName}</strong></p>
                <p class="mb-0 text-muted">${branchAddress}</p>
              </div>
            </div>
            <div class="info-card">
              <h6 class="info-title">CUSTOMER INFO</h6>
              <div class="info-content">
                <p class="mb-1"><strong>Name:</strong> ${customerName}</p>
                <p class="mb-1"><strong>Number:</strong> ${customerPhone}</p>
                <p class="mb-0"><strong>Email:</strong> ${customerEmail}</p>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="info-card">
              <h6 class="info-title">APPOINTMENT SUMMARY</h6>
              <div class="info-content">
                <div class="appointment-details">
                  <div class="detail-row">
                    <span class="detail-label">Staff :</span>
                    <span class="detail-value">${staffName}</span>
                  </div>
                  <div class="detail-row">
                    <span class="detail-label">Date & Time :</span>
                    <span class="detail-value">${dateStr} ${timeStr}</span>
                  </div>
                  <div class="detail-row">
                    <span class="detail-label">Services</span>
                    <span class="detail-value"></span>
                  </div>
                  <div class="detail-row service-item">
                    <span class="detail-label">${serviceName}</span>
                    <span class="detail-value price">$50.00/-</span>
                  </div>
                  <div class="detail-row">
                    <span class="detail-label">Taxes</span>
                    <span class="detail-value"></span>
                  </div>
                  <div class="detail-row total-row">
                    <span class="detail-label">Total Price</span>
                    <span class="detail-value total-price">$50.00/-</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    `;
    
    // Update the confirmation summary with detailed information
    const summaryElement = document.getElementById('qb-confirm-summary');
    if (summaryElement) {
      summaryElement.innerHTML = detailHTML;
    }
    
    // Update the buttons to show Book More and Print PDF
    const buttonContainer = document.querySelector('.qb-step-footer');
    if (buttonContainer) {
      buttonContainer.innerHTML = `
        <div class="d-flex justify-content-center gap-2">
          <button type="button" class="btn btn-primary-subtle" onclick="setActive(1)">
            <i class="fa fa-plus me-1"></i> Book More Appointments
          </button>
          <button type="button" class="btn btn-secondary" onclick="window.print()">
            <i class="fa fa-print me-1"></i> Print PDF
          </button>
        </div>
      `;
    }
  }
  })();
</script>

<script>
  // Render detailed confirmation content when step 7 activates
  (function(){
    var detailPanel = document.getElementById('confirmation-detail');
    if(!detailPanel) return;

    function renderDetail(){
      var detailContent = document.getElementById('qb-confirmation-detail');
      if(!detailContent) return;
      
      // Show loading state initially
      detailContent.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-3">Loading booking details...</p></div>';
      
      // Small delay to show loading state
      setTimeout(function() {
        renderDetailContent();
      }, 500);
    }
    
    function renderDetailContent(){
      var detailContent = document.getElementById('qb-confirmation-detail');
      if(!detailContent) return;

      // Date formatting function
      function formatDate(dateString) {
        if (!dateString || dateString === '-') return 'N/A';
        
        var date = new Date(dateString);
        if (isNaN(date.getTime())) return 'N/A';
        
        var day = date.getDate().toString().padStart(2, '0');
        var month = (date.getMonth() + 1).toString().padStart(2, '0');
        var year = date.getFullYear();
        
        return day + ' - ' + month + ' - ' + year;
      }

      // Time formatting function
      function formatTime(timeString) {
        if (!timeString || timeString === '-') return 'N/A';
        
        var time = new Date('2000-01-01 ' + timeString);
        if (isNaN(time.getTime())) return 'N/A';
        
        var hours = time.getHours().toString().padStart(2, '0');
        var minutes = time.getMinutes().toString().padStart(2, '0');
        
        return hours + ':' + minutes;
      }

      var s = window.qbState || {};
      var bookingData = s.bookingResponse || {};
      var customer = s.customer || {};
      
      // Get data from booking response or fallback to state
      var branch = bookingData.branch || s.branch || {};
      var services = bookingData.services || [];
      var employee = bookingData.employee || s.employee || {};
      var tax = bookingData.tax || [];
      var service = s.service || {};
      var date = s.date || '-';
      var time = (s.start_date_time || '').split(' ').pop() || '-';
      
      // If no services from booking response, use state service
      if (services.length === 0 && service.id) {
        services = [service];
      }
      
      // Calculate pricing
      var subTotal = 0;
      var totalTax = 0;
      var totalPrice = 0;
      
      if (services.length > 0) {
        subTotal = services[0].service_price || services[0].price || 0;
        
        // Calculate taxes
        tax.forEach(function(taxItem) {
          if (taxItem.type === 'percent') {
            totalTax += (subTotal * taxItem.percent / 100);
          } else {
            totalTax += taxItem.tax_amount;
          }
        });
        
        totalPrice = subTotal + totalTax;
      }

      // Format branch address properly
      let branchAddress = 'N/A';
      const branch = bookingData.branch || s.branch || {};
      
      if (branch.address && typeof branch.address === 'object') {
        const parts = [];
        if (branch.address.address_line_1) parts.push(branch.address.address_line_1);
        if (branch.address.address_line_2) parts.push(branch.address.address_line_2);
        if (branch.address.city) parts.push(branch.address.city);
        if (branch.address.state) parts.push(branch.address.state);
        if (branch.address.postal_code) parts.push(branch.address.postal_code);
        branchAddress = parts.join(', ');
      } else if (branch.address_line_1 || branch.address_line_2 || branch.city || branch.state || branch.postal_code) {
        const parts = [];
        if (branch.address_line_1) parts.push(branch.address_line_1);
        if (branch.address_line_2) parts.push(branch.address_line_2);
        if (branch.city) parts.push(branch.city);
        if (branch.state) parts.push(branch.state);
        if (branch.postal_code) parts.push(branch.postal_code);
        branchAddress = parts.join(', ');
      } else if (bookingData.branch_address) {
        branchAddress = bookingData.branch_address;
      }
      
      var detailHTML = `
        <div class="card-list-data">
          <div class="row">
            <div class="col-sm-6">
              <div class="confirmation-info-section mb-5">
                <h6 class="text-primary text-uppercase fw-bold mb-3">SALON INFO</h6>
                <div class="iq-card bg-primary-subtle text-body p-1">
                  <div class="iq-card-body">
                    <div class="salon-details">
                      <div class="detail-line">
                        <span class="salon-name">${branch.name || 'N/A'}</span>
                      </div>
                      <div class="detail-line">
                        <span class="salon-address">${branchAddress}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="confirmation-info-section">
                <h6 class="text-primary text-uppercase fw-bold mb-3">CUSTOMER INFO</h6>
                <div class="iq-card bg-primary-subtle text-body p-1">
                  <div class="iq-card-body">
                    <div class="customer-details">
                      <div class="detail-line">
                        <span class="label">Name:</span>
                        <span class="value">${customer.first_name || ''} ${customer.last_name || ''}</span>
                      </div>
                    
                      <div class="detail-line">
                        <span class="label">Number:</span>
                        <span class="value">${customer.country_code || ''} ${customer.phone || ''}</span>
                      </div>
                      <div class="detail-line">
                        <span class="label">Email:</span>
                        <span class="value">${customer.email || ''}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6">
              <h6 class="text-primary text-uppercase fw-bold mb-3">APPOINTMENT SUMMARY</h6>
              <div class="iq-card iq-card-border p-1">
                <div class="d-flex justify-content-between align-items-center">
                  <p class="m-0">Staff:</p>
                  <h6 class="m-0">${employee.full_name || employee.name || 'N/A'}</h6>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                  <p class="m-0">Date & Time:</p>
                  <h6><span id="dateOfAppointment">${formatDate(date)} ${formatTime(time)}</span></h6>
                </div>
                <div class="iq-card bg-primary-subtle text-body p-1 mt-4 mb-0 shadow-none">
                  <div class="iq-card-body">
                    <div class="services-taxes-total">
                      <div class="section-line">
                        <h6 class="section-title">Services</h6>
                        ${services.map(service => `
                          <div class="detail-line">
                            <span class="service-name">${service.service_name || service.name || 'N/A'}</span>
                            <span class="service-price">$${(service.service_price || service.price || 0).toFixed(2)}/-</span>
                          </div>
                        `).join('')}
                      </div>
                      
                      <div class="section-line">
                        <h6 class="section-title">Taxes</h6>
                        ${tax.map(taxItem => `
                          <div class="detail-line">
                            <span class="tax-name">${taxItem.type === 'percent' ? `${taxItem.name}: ${taxItem.percent}%` : `${taxItem.name}: $${taxItem.tax_amount}`}</span>
                            <span class="tax-amount">+ $${taxItem.type === 'percent' ? (subTotal * taxItem.percent / 100).toFixed(2) : taxItem.tax_amount.toFixed(2)}</span>
                          </div>
                        `).join('')}
                      </div>
                      
                      <div class="section-line total-section">
                        <div class="detail-line total-line">
                          <span class="total-label">Total Price</span>
                          <span class="total-amount">$${totalPrice.toFixed(2)}/-</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer non-printable">
          <div class="d-flex flex-wrap gap-1 justify-content-center">
            <button type="button" class="btn btn-primary d-flex gap-3" onclick="location.reload()">
              <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
              </svg>
              <span>Book More Appointments</span>
            </button>
            <button type="button" class="btn btn-secondary d-flex gap-3" onclick="window.print()">
              <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
              </svg>
              <span>Print to PDF</span>
            </button> 
          </div>
        </div>
      `;

      detailContent.innerHTML = detailHTML;
    }

    detailPanel.addEventListener('qb:step:activated', renderDetail);
  })();
</script>
@endpush


