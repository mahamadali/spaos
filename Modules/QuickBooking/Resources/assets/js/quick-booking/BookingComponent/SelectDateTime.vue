<template>
  <div class="select-date-time-container">
    <div class="main-content">
      <!-- Date Selection Section (Left Side) -->
      <div class="date-selection-section">
        <div class="section-header">
          <h5 class="section-title">{{ $t('messages.select_date') }}</h5>
        </div>
        <div class="calendar-container">
          <flat-pickr v-model="date" :config="config" :placeholder="$t('messages.lbl_select_date')" @change="dateUpdate" class="modern-calendar" />
        </div>
      </div>

      <!-- Time Selection Section (Right Side) -->
      <div class="time-selection-section">
        <div class="section-header">
          <h5 class="section-title">{{ $t('messages.Please_select_time_slot_for_appointment') }}</h5>
        </div>
        
        <div class="time-slots-container" v-if="timeSlotList.length > 0">
          <div class="time-slots-grid" v-if="!IS_LOADER">
            <template v-for="(item, index) in filteredTimeSlotList" :key="`time-slot-${index}`">
              <div class="time-slot-wrapper">
                <input 
                  type="radio" 
                  :id="item.value" 
                  v-model="start_date_time" 
                  :value="item.value" 
                  name="timeSlot" 
                  class="time-slot-input" 
                  @change="onChange" 
                />
                <label :for="item.value" class="time-slot-button">
                  {{ item.label }}
                </label>
              </div>
            </template>
          </div>
          
          <!-- Loading Skeleton -->
          <div class="time-slots-grid loading" v-else>
            <div v-for="index in 12" :key="`skeleton-${index}`" class="time-slot-skeleton"></div>
          </div>
        </div>
        
        <!-- Empty State -->
        <div class="empty-state" v-else-if="timeSlotList.length == 0 && IS_LOADER && date">
          <div class="loading-skeleton">
            <div v-for="index in 12" :key="`empty-skeleton-${index}`" class="time-slot-skeleton"></div>
          </div>
        </div>
        
        <div class="empty-message" v-else>
          <div class="empty-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" fill="currentColor"/>
            </svg>
          </div>
          <p class="empty-text">
            <template v-if="!date">{{ $t('messages.select_date') }}</template>
            <template v-else-if="timeSlotList[0].value == '' || (filteredTimeSlotList.length == 0 && !IS_LOADER)">
              {{ $t('messages.no_slots_available') }}
            </template>
          </p>
        </div>
      </div>
    </div>

    <!-- Navigation Buttons -->
    <div class="navigation-buttons" v-if="timeSlotList.length > 0">
      <button 
        type="button" 
        class="btn btn-back" 
        v-if="wizardPrev" 
        @click="prevTabChange(wizardPrev)"
      >
        {{ $t('quick_booking.lbl_back') }}
      </button>
      <button 
        type="button" 
        class="btn btn-next" 
        v-if="timeSlotList.length > 0 && wizardNext" 
        :disabled="!start_date_time" 
        @click="nextTabChange(wizardNext)"
      >
        {{ $t('quick_booking.lbl_next') }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRequest } from '@/helpers/hooks/useCrudOpration'
import { useQuickBooking } from '../../store/quick-booking'
import { SLOT_TIME_LIST, HOLIDAY_SLOT_LIST } from '@/vue/constants/quick_booking'
import flatPickr from 'vue-flatpickr-component'
import * as moment from 'moment'

const props = defineProps({
  wizardNext: { default: '', type: [String, Number] },
  wizardPrev: { default: '', type: [String, Number] }
})

const emit = defineEmits(['tab-change'])

const date = ref(null)
const start_date_time = ref(null)
const IS_LOADER = ref(true)
const timeSlotList = ref([])
const holidaySlotList = ref([])
const holidaydate = ref([])
const isHoliday = ref(false)

const store = useQuickBooking()
const { listingRequest } = useRequest()

const config = ref({
  inline: true,
  dateFormat: 'Y-m-d',
  minDate: 'today',
  disable: [],
  // Enhanced calendar configuration
  showMonths: 1,
  static: true,
  monthSelectorType: 'static',
  yearSelectorType: 'static',
  // Custom styling classes
  className: 'modern-flatpickr'
})

const filteredTimeSlotList = computed(() => {
  const currentTime = moment()
  return timeSlotList.value.filter(slot => moment(slot.value).isAfter(currentTime))
})

const getSlots = () => {
  IS_LOADER.value = true
  listingRequest({
    url: SLOT_TIME_LIST,
    data: { branch_id: store.booking.branch_id, date: date.value, employee_id: store.booking.employee_id,  service_id: store.booking.services[0].service_id}
  }).then(res => {
    setTimeout(() => {
      IS_LOADER.value = false
      timeSlotList.value = res.data
    }, 1000)
  })
}

const getHolidaySlots = () => {
  IS_LOADER.value = true
  listingRequest({
    url: HOLIDAY_SLOT_LIST,
    data: { branch_id: store.booking.branch_id, employee_id: store.booking.employee_id, service_id: store.booking.services[0].service_id }
  }).then(res => {
    setTimeout(() => {
      IS_LOADER.value = false
      holidaySlotList.value = res.data
      holidaydate.value = res.holidays
      updateHolidayConfig()
    }, 1000)
  })
}

const updateHolidayConfig = () => {
  const disabledDays = holidaySlotList.value
    .filter(slot => slot.is_holiday === 1)
    .map(slot => slot.day.toLowerCase())
  const holidayDates = holidaydate.value
  config.value.disable = getDisabledDays(disabledDays, holidayDates)
}

const getDisabledDays = (days, holidayDates) => {
  const disabledDates = new Set(holidayDates)
  const currentDate = moment()
  for (let i = 0; i < 365; i++) {
    const date = currentDate.clone().add(i, 'days')
    const dayOfWeek = date.format('dddd').toLowerCase()
    if (days.includes(dayOfWeek)) {
      disabledDates.add(date.format('YYYY-MM-DD'))
    }
  }
  return Array.from(disabledDates)
}

const dateUpdate = () => getSlots()

onMounted(() => {
  if (store.booking.branch_id && store.booking.employee_id && store.booking.service_id) {
    getHolidaySlots()
  }
})

watch(() => store.booking.branch_id, (newBranchId) => {
  if (newBranchId && store.booking.employee_id) {
    getHolidaySlots()
  }
})

watch(() => store.booking.employee_id, (newEmployeeId) => {
  if (store.booking.branch_id && newEmployeeId) {
    getHolidaySlots()
  }
})

watch(() => store.booking.services[0].service_id, (serviceId) => {
  if (store.booking.employee_id && serviceId) {
    getHolidaySlots();
  }
});

watch(() => start_date_time.value, (value) => {
  store.updateBookingValues({ key: 'start_date_time', value })
  store.updateBookingServiceTimeValues(value)
})

const onChange = () => emit('tab-change', props.wizardNext)

const nextTabChange = (val) => emit('tab-change', val)
const prevTabChange = (val) => {
  resetData()
  emit('tab-change', val)
}

const resetData = () => {
  store.updateBookingValues({ key: 'start_date_time', value: null })
  store.updateBookingServiceTimeValues(null)
  start_date_time.value = null
  date.value = null
  timeSlotList.value = []
}
</script>

<style scoped>
/* Component-specific styles that need to be scoped */
</style>
