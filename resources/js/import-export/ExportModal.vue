<template>
  <BModal @hide="onHide" :title="$t('export.title')" v-model="modal" centered size="lg" :cancel-title="$t('messages.cancel')">
    <template v-slot:ok>
      <div class="d-grid d-md-block setting-footer">
        <button @click="onSubmit" :disabled="IS_SUBMITED || !columns || !columns.length" class="btn btn-primary"  name="submit">
          <template v-if="IS_SUBMITED">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            {{ $t('messages.loading') }}...
          </template>
          <template v-else> <i class="fa-solid fa-file-arrow-down"></i> {{ $t('export.download') }}</template>
        </button>
      </div>
    </template>
    <div class="form-group">
      <label class="form-label" for="date-range">{{ $t('export.lbl_date') }}</label>
      <flat-pickr v-model="date_range" :value="date_range" :config="config" id="date-range" class="form-control" />
    </div>

    <div class="form-group">
      <p>{{ $t('export.lbl_select_file_type') }}</p>
      <BFormRadioGroup
          v-model="file_type"
          :options="buttonsOptions"
          button-variant="outline-primary"
          name="radios-btn-default"
          buttons
          class="flex-wrap"
        >
      </BFormRadioGroup>
    </div>
    <div class="form-group">
      <p>{{ $t('export.lbl_select_columns') }}</p>
      <BFormCheckboxGroup
          v-model="columns"
          :options="MODULE_COLUMNS"
          button-variant="outline-secondary"
          name="columns"
          stacked>
        </BFormCheckboxGroup>
    </div>
    <span class="text-danger">{{ errors.columns }}</span>
  </BModal>
</template>
<script setup>
import { ref, onMounted,computed} from 'vue'
import { useField, useForm } from 'vee-validate'
import { JSON_REQUEST_HEADER } from '@/helpers/utilities'
import flatPickr from 'vue-flatpickr-component';
import { useModel } from '@/helpers/hooks/bootstrap-components'
import * as yup from 'yup'
import * as moment from 'moment'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  exportUrl: { type: String },
  moduleName: { type: String },
  moduleColumnProp: { type: Array, default: () => [] },
})
const MODULE_COLUMNS = ref(props.moduleColumnProp)

const IS_SUBMITED = ref(false)
// Get the current date
const currentDate = moment();
// Calculate the date for 3 months ago
const threeMonthsAgo = currentDate.clone().subtract(3, 'months');
const config = ref({
    mode: "range",
    dateFormat: 'Y-m-d'
});
const { t } = useI18n();
// Validations
const validationSchema = yup.object({
  file_type: yup.string()
  .required(() => t('messages.file_type_required')),
  date_range: yup.string()
  .required(() => t('messages.data_range_required')),
  columns: yup.array()
    .min(1, () => t('messages.at_least_one_selected'))
})

const { handleSubmit, errors, resetForm } = useForm({
  validationSchema
})

const { value: file_type } = useField('file_type')
const { value: date_range } = useField('date_range')
const { value: columns } = useField('columns')
date_range.value = []

//  Reset Form
const setFormData = (data) => {
  resetForm({
    values: {
      date_range: data.date_range,
      file_type: data.file_type,
      columns: data.columns,
    }
  })
}
const defaultDate = () => {
  return threeMonthsAgo.format('YYYY-MM-DD')+' to '+currentDate.format('YYYY-MM-DD')
}
const defaultData = () => {
  return {
    date_range: defaultDate(),
    file_type: 'csv',
    columns: MODULE_COLUMNS.value.map(({ value }) => value) || [],
  }

}


const modal = useModel(() => {}, 'export_modal')
const buttonsOptions = [
  {text: 'XLSX', value: 'xlsx'},
  {text: 'XLS', value: 'xls'},
  {text: 'ODS', value: 'ods'},
  {text: 'CSV', value: 'csv'},
  {text: 'PDF', value: 'pdf'},
  {text: 'HTML', value: 'html'},
]

const onSubmit = handleSubmit((values) => {
  IS_SUBMITED.value = true;

  const queryParams = new URLSearchParams(Object.entries(values)).toString();
  const urlWithParams = `${props.exportUrl}?${queryParams}`;

  fetch(urlWithParams, { headers: JSON_REQUEST_HEADER })
    .then(async (res) => {
      if (res.status === 200) {
        const blob = await res.blob();
        const url = window.URL.createObjectURL(blob);

        // Open the file in a new tab instead of auto-downloading
        window.open(url, '_blank');

        // Clean up the URL object after some time
        setTimeout(() => window.URL.revokeObjectURL(url), 1000);
      }
      IS_SUBMITED.value = false;
    })
    .catch(() => {
      IS_SUBMITED.value = false;
    });
});


onMounted(() => {
  setFormData(defaultData())
})

</script>
