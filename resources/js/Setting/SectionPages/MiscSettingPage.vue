<template>
  <form @submit="formSubmit">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <CardTitle :title="$t('setting_sidebar.lbl_misc_setting')" icon="fa-solid fa-screwdriver-wrench"></CardTitle>
    </div>
    
    <div class="row">
      <div class="col-lg-6">
        <InputField :label="$t('setting_analytics_page.lbl_name')" :placeholder="$t('setting_analytics_page.placeholder')" v-model="google_analytics" :errorMessage="errors.google_analytics"></InputField>
      </div>
   
      <div class="col-lg-6">
          <div class="form-group">
            <label class="form-label">{{ $t('setting_language_page.lbl_language') }}</label>
            <Multiselect id="default_language" v-model="default_language" :value="default_language" v-bind="singleSelectOption" :options="language.options" class="form-group"></Multiselect>
            <span class="text-danger">{{ errors.default_language }}</span>
          </div>
      </div>

      <div class="col-lg-6">
        <div class="form-group">
          <label class="form-label">{{ $t('setting_language_page.lbl_timezone') }} <span class="badge bg-danger">{{ $t('setting_language_page.soon') }}</span></label>
          <Multiselect id="default_time_zone" v-model="default_time_zone" :value="default_time_zone" v-bind="TimeZoneSelectOption" :options="timezone.options" class="form-group"></Multiselect>
          <span class="text-danger">{{ errors.default_time_zone }}</span>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="form-group">
          <label class="form-label">{{ $t('setting_language_page.lbl_data_table_limit') }}</label>
          <Multiselect id="data_table_limit" v-model="data_table_limit" :value="data_table_limit"  v-bind="data_table_limit_data" class="form-group"></Multiselect>
          <span class="text-danger">{{ errors.data_table_limit }}</span>
        </div>
      </div>

      <div class="col-lg-6">
  <div class="form-group">
    <label class="form-label">{{ $t('messages.date_format') }}</label>
    <Multiselect 
      id="date_format" 
      v-model="date_format" 
      :options="dateFormats.options" 
       :value="date_format"
       v-bind="singleSelectOption"
      class="form-group" >
    </Multiselect>
    <span class="text-danger">{{ errors.date_format }}</span>
  </div>
</div>

<div class="col-lg-6">
  <div class="form-group">
    <label class="form-label">{{ $t('messages.time_format') }}</label>
    <Multiselect 
      id="time_format" 
      v-model="time_format" 
      :options="timeFormats.options" 
        v-bind="singleSelectOption"
       :value="time_format"
      class="form-group">
    </Multiselect>
    <span class="text-danger">{{ errors.time_format }}</span>
  </div>
</div>
    
    </div>
   
    <div class="row py-4">
      <SubmitButton :IS_SUBMITED="IS_SUBMITED"></SubmitButton>
    </div>
  </form>
</template>
<script setup>
import CardTitle from '@/Setting/Components/CardTitle.vue'
import InputField from '@/vue/components/form-elements/InputField.vue'
import { onMounted, ref } from 'vue'
import { useField, useForm } from 'vee-validate'
import { STORE_URL, GET_URL, TIME_ZONE_LIST, CURRENCY_LIST ,DATE_TIME_FORMAT_LIST} from '@/vue/constants/setting'
import { useSelect } from '@/helpers/hooks/useSelect'
import { LANGUAGE_LIST, LISTING_URL } from '@/vue/constants/language'
import { createRequest, buildMultiSelectObject } from '@/helpers/utilities'
import { useRequest } from '@/helpers/hooks/useCrudOpration'
import FlatPickr from 'vue-flatpickr-component'
import SubmitButton from './Forms/SubmitButton.vue'
import { confirmSwal } from '@/helpers/utilities'

// flatepicker
const config = ref({
  dateFormat: 'H:i',
  time_24hr: true,
  enableTime: true,
  noCalendar: true,
  defaultHour: '00', // Update default hour to 9
  defaultMinute: '30'
})

const IS_SUBMITED = ref(false)
const { storeRequest, listingRequest } = useRequest()

// options
const TimeZoneSelectOption = ref({
  closeOnSelect: true,
  searchable: true,
  clearable: false
})

const currencyOption = ref({
  closeOnSelect: true,
  searchable: true,
  clearable: false
})

const singleSelectOption = ref({
  closeOnSelect: true,
  searchable: true,
  clearable: false
})

const language = ref([])
const timezone = ref([])
const currency = ref([])
const dateFormats = ref([])
const timeFormats = ref([])

const type = 'time_zone'

const getLanguageList = () => {
  useSelect({ url: LANGUAGE_LIST }, { value: 'id', label: 'name' }).then((data) => (language.value = data))
}
const getTimeZoneList = () => {
  listingRequest({ url: TIME_ZONE_LIST, data: { type: type } }).then((res) => {
    timezone.value.options = buildMultiSelectObject(res.results, {
      value: 'id',
      label: 'text'
    })
  })
}

const getDateTimeFormats = () => {
  listingRequest({ url: DATE_TIME_FORMAT_LIST }).then((res) => {
    dateFormats.value.options = Object.entries(res.date_formats).map(([key, value]) => ({
      value: key,  
      label: value
    }));
    timeFormats.value.options = buildMultiSelectObject(res.time_formats, {
      value: 'format',
      label: 'time'
    })
  });
};

const data_table_limit_data = ref({
  searchable: true,
  options: [
    { label: 5, value: 5 },
    { label: 10, value: 10 },
    { label: 15, value: 15 },
    { label: 20, value: 20 },
    { label: 25, value: 25 },
    { label: 50, value: 50 },
    { label: 100, value: 100 },
    { label: 'All', value: -1 }
  ],
  closeOnSelect: true,
  createOption: true
})

//  Reset Form
const setFormData = (data) => {
  resetForm({
    values: {
      google_analytics: data.google_analytics,
      default_language: data.default_language,
      default_time_zone: data.default_time_zone,
      data_table_limit: data.data_table_limit,
      default_currency: data.default_currency,
      date_format: data.date_format,
      time_format: data.time_format
    }
  })
}
const { handleSubmit, errors, resetForm } = useForm()
const errorMessage = ref(null)
const { value: google_analytics } = useField('google_analytics')
const { value: default_language } = useField('default_language')
const { value: default_time_zone } = useField('default_time_zone')
const { value: data_table_limit } = useField('data_table_limit')
const { value: default_currency } = useField('default_currency')
const { value: date_format } = useField('date_format')
const { value: time_format } = useField('time_format')

const data = 'google_analytics,default_language,default_time_zone,data_table_limit,default_currency,date_format,time_format'
onMounted(() => {
  createRequest(GET_URL(data)).then((response) => {
    setFormData(response)
  })

  getDateTimeFormats()  

 

  getLanguageList()
  getTimeZoneList()
})

// message
const display_submit_message = (res) => {
  IS_SUBMITED.value = false
  if (res.status) {
    window.successSnackbar("Misc Setting updated successfully!")
  } else {
    window.errorSnackbar(res.message)
  }
}
//Form Submit
const formSubmit = handleSubmit((values) => {
  IS_SUBMITED.value = true
  const newValues = {}
  Object.keys(values).forEach((key) => {
    newValues[key] = values[key] || ''
  })

  storeRequest({ url: STORE_URL, body: values }).then((res) => display_submit_message(res))
})
</script>
