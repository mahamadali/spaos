<template>
  <form @submit="formSubmit">
    <div class="offcanvas offcanvas-end" tabindex="-1" id="form-offcanvas" aria-labelledby="form-offcanvasLabel">
      <FormHeader :currentId="currentId" :editTitle="editTitle" :createTitle="'Create Promotion'"></FormHeader>
      <div class="offcanvas-body">

        <InputField
          class="col-md-12"
          type="text"
          :is-required="true"
          :label="$t('promotion.lbl_name')"
          :placeholder="$t('service.enter_name')"
          v-model="name"
          :error-message="errors['name']"
        ></InputField>
        <InputField class="col-md-12" type="textarea" :is-required="true" :label="$t('promotion.description')" :placeholder="$t('messages.placeholder_description')" v-model="description" :error-message="errors['description']" :error-messages="errorMessages['description']"></InputField>

        <div class="form-group">
          <div class="col-md-12">
            <label class="form-label" for="start_date">{{ $t('promotion.start_datetime') }}</label>
            <div class="w-100">
              <flat-pickr id="start_date" class="form-control" :config="config" v-model="start_date_time" :value="start_date_time" placeholder=""></flat-pickr>
              <span class="text-danger">{{ errors['start_date_time'] }}</span>
            </div>
          </div>
        </div>
        <div class="form-group">
          <div class="col-md-12">
            <label class="form-label" for="end_date">{{ $t('promotion.end_datetime') }}</label>
            <div class="w-100">
              <flat-pickr id="end_date" class="form-control" :config="config" v-model="end_date_time" :value="end_date_time" placeholder=""></flat-pickr>
              <span class="text-danger">{{ errors['end_date_time'] }}</span>
            </div>
          </div>
        </div>
     
        <div class="form-group col-md-12" v-if="coupon_type">
          <div v-if="coupon_type == 'custom'">
            <InputField class="col-md-12" type="text" :is-required="true" :label="$t('promotion.coupon_code')" :placeholder="$t('messages.placeholder_coupon_code')" v-model="coupon_code" :error-message="errors['coupon_code']" :error-messages="errorMessages['coupon_code']" :is-read-only="ISREADONLY"></InputField>
          </div>
         
        </div> 

        <div class="form-group">
          <div class="col-md-12">
            <label class="form-label">{{ $t('promotion.percent_or_fixed') }}</label>
            <Multiselect v-model="discount_type" :value="discount_type" v-bind="singleSelectOption" :options="typeOptions" id="type" autocomplete="off"></Multiselect>
          </div>
        </div>
        <div class="col-md-12" v-if="discount_type">
          <div v-if="discount_type == 'percent'">
            <InputField type="number" step="any" :is-required="true" :label="$t('promotion.discount_percentage')" placeholder="{{ __('messages.placeholder_discount_percentage') }}" v-model="discount_percentage" :error-message="errors['discount_percentage']"></InputField>
          </div>
          <div v-else-if="discount_type == 'fixed'">
            <InputField type="number" :is-required="true" :label="$t('promotion.discount_amount')" :placeholder="$t('messages.placeholder_discount_amount')"  v-model="discount_amount" :error-message="errors['discount_amount']"></InputField>
          </div>
        </div>

        <div class="form-group col-md-12" v-if="isSuperAdmin">
          <label class="form-label" for="plan">{{ $t('messages.select_plan') }} <span class="text-danger">*</span></label>
          <Multiselect id="plan_id" v-model="plan_id" :placeholder="$t('messages.select_plan')" :value="plan_id" v-bind="multiselectOption" :options="plan.options" class="form-group"></Multiselect>
          <span class="text-danger">{{ errors['Select_Plan'] }}</span>
         </div>

         <div v-if="isAdmin">
          <InputField class="col-md-12" type="number" :is-required="true" :label="$t('promotion.use_limit')" placeholder="" v-model="use_limit" :error-message="errors['use_limit']" :error-messages="errorMessages['use_limit']" :is-read-only="coupon_type == 'bulk' || ISREADONLY"></InputField>
        </div>


        <div class="form-group">
          <label class="form-label" for="category-status">{{ $t('service.lbl_status') }}</label>
          <div class="d-flex justify-content-between align-items-center form-control">
            <label class="form-label" for="category-status">{{ $t('service.lbl_status') }}</label>
            <div class="form-check form-switch">
              <input class="form-check-input" :value="status" :true-value="1" :false-value="0" :checked="status" name="status" id="category-status" type="checkbox" v-model="status" />
            </div>
          </div>
        </div>
       
      </div>
      <FormFooter :IS_SUBMITED="IS_SUBMITED"></FormFooter>
    </div>
  </form>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { readFile } from '@/helpers/utilities'
import { EDIT_URL, STORE_URL, UPDATE_URL, TIME_ZONE_LIST,PLAN_LIST, UNIQUE_CHECK } from '../constant'
import { useField, useForm } from 'vee-validate'
import InputField from '@/vue/components/form-elements/InputField.vue'
import { useModuleId, useRequest, useOnOffcanvasHide } from '@/helpers/hooks/useCrudOpration'
import { useSelect } from '@/helpers/hooks/useSelect'
import { buildMultiSelectObject } from '@/helpers/utilities'
import * as yup from 'yup'
import FormHeader from '@/vue/components/form-elements/FormHeader.vue'
import FormFooter from '@/vue/components/form-elements/FormFooter.vue'
import FormElement from '@/helpers/custom-field/FormElement.vue'
import FlatPickr from 'vue-flatpickr-component'
import Multiselect from '@vueform/multiselect';
import { useI18n } from 'vue-i18n';

// props
const props = defineProps({
  createTitle: { type: String, default: '' },
  editTitle: { type: String, default: '' },
  customefield: { type: Array, default: () => [] },
  defaultImage: { type: String, default: '' }
})
const ROLES = ref(
  JSON.parse(document.querySelector('meta[name="auth_user_roles"]')?.getAttribute('content') || '[]')
);



const plan = ref({ options: [], list: [] });




const getPlanList = () => {

      useSelect({ url: PLAN_LIST }, { value: 'id', label: 'name' }).then((data) => (plan.value = data))

    }

 const multiselectOption = ref({
  mode: 'tags',
  searchable: true
})

const ImageViewer = ref(null)
const profileInputRef = ref(null)
const validationMessage = ref('');

const fileUpload = async (e) => {
  let file = e.target.files[0];
  const maxSizeInMB = 2;
  const maxSizeInBytes = maxSizeInMB * 1024 * 1024;

  if (file) {
    if (file.size > maxSizeInBytes) {
      // File is too large
      validationMessage.value = `File size exceeds ${maxSizeInMB} MB. Please upload a smaller file.`;
      // Clear the file
      profileInputRef.value.value = '';
      return;
    }

    await readFile(file, (fileB64) => {
      ImageViewer.value = fileB64;
      profileInputRef.value.value = '';
      validationMessage.value = '';
    });
    feature_image.value = file;
  } else {
    validationMessage.value = '';
  }
};

// Function to delete Images
const removeImage = ({ imageViewerBS64, changeFile }) => {
  imageViewerBS64.value = null
  changeFile.value = null
}

const removeLogo = () => removeImage({ imageViewerBS64: ImageViewer, changeFile: feature_image })

const { getRequest, storeRequest, updateRequest, listingRequest } = useRequest()

// flatpicker
const config = ref({
  dateFormat: 'Y-m-d',
  static: true,
  minDate: new Date()
})

const singleSelectOption = ref({
  closeOnSelect: true,
  searchable: true
})

// Edit Form Or Create Form
const currentId = useModuleId(() => {
  if (currentId.value > 0) {
    getRequest({ url: EDIT_URL, id: currentId.value }).then((res) => {
      if (res.status) {
        setFormData(res.data)
        ISREADONLY.value = true
      }
    })
  } else {
    resetForm()
    setFormData(defaultData())
    getPlanList();
  }
})

const resetMyForm = () => resetForm()

useOnOffcanvasHide('form-offcanvas', () => {
  setFormData(defaultData())
  resetMyForm()
})

// Default FORM DATA
const defaultData = () => {
  errorMessages.value = {}
  ISREADONLY.value = false
  return {
    name: '',
    description: '',
    start_date_time: new Date().toJSON().slice(0, 10),
    end_date_time: new Date(new Date().setDate(new Date().getDate() + 1)).toJSON().slice(0, 10),
    feature_image: null,
    status: 1,
    plan_id: [],

    coupon: [
      {
        discount_amount: 0,
        discount_percentage: 0,
        discount_type: 'fixed',
        coupon_type: 'bulk',
        number_of_coupon: 0,
        coupon_code: '',
        use_limit: 1
      }
    ]
  }
}

//  Reset Form
const setFormData = (data) => {
  if (data.feature_image === props.defaultImage) {
    ImageViewer.value = null
  } else {
    ImageViewer.value = data.feature_image
  }
  const couponsArray = Array.isArray(data.coupon) ? data.coupon : [data.coupon];
  resetForm({
    values: {
      name: data.name,
      description: data.description,
      start_date_time: data.start_date_time,
      end_date_time: data.end_date_time,
      feature_image: data.feature_image !== props.defaultImage ? data.feature_image : undefined,
      status: data.status,
      discount_amount: couponsArray[0]?.discount_amount,
      discount_percentage: couponsArray[0]?.discount_percentage,
      discount_type: couponsArray[0]?.discount_type || 'fixed',
      coupon_type: couponsArray.length === 1 ? 'custom' : 'bulk',
      number_of_coupon: couponsArray.length ?? 1,
      coupon_code: couponsArray[0]?.coupon_code,
      plan_id: data.plan_ids,
      use_limit: couponsArray[0]?.use_limit
    }
  })
}
// Reload Datatable, SnackBar Message, Alert, Offcanvas Close
const reset_datatable_close_offcanvas = (res) => {
  IS_SUBMITED.value = false
  if (res.status) {
    window.successSnackbar(res.message)
    renderedDataTable.ajax.reload(null, false)
    bootstrap.Offcanvas.getInstance('#form-offcanvas').hide()
    setFormData(defaultData())
  } else {
    window.errorSnackbar(res.message)
    if (res.all_message){  errorMessages.value = res.all_message}
    else { errorMessages.value = res.errors}



  }
}
const resetFormAfterSubmit = () => {
  plan_id.value = [];  // Reset plan_id
  // Other form reset logic
};

const isSuperAdmin = ROLES.value.includes('super admin');
const isAdmin = ROLES.value.includes('admin');
// Validations
const { t } = useI18n();

const validationSchema = yup.object({
  name: yup.string().required(t('messages.name_required')),
  start_date_time: yup.string().required(t('messages.start_date_time_required')),
  end_date_time: yup.date().required(t('messages.end_date_time_required')).min(new Date(), t('messages.end_date_time_past')).typeError(t('messages.valid_date')),
  description: yup.string().required(t('messages.description_required')),
  use_limit: yup
  .number()
  .when([], {
    is: () => isAdmin, // Check if the user is an admin
    then: (schema) =>
      schema
        .required(t('messages.use_limit_required'))
        .min(1, t('messages.use_limit_min'))
        .typeError(t('messages.use_limit_number')),
    otherwise: (schema) => schema.notRequired(), // Optional for non-admin users
  }),
  plan_id: yup
    .array()
    .when([], {
      is: () => isSuperAdmin, // Check if the user is a super admin
      then: (schema) => schema.required(t('messages.select_plan_required')),
      otherwise: (schema) => schema.notRequired(), // Optional for non-super admin users
    }),
  coupon_code: yup
  .string()
  .when('coupon_type', {
    is: (value) => value === 'custom', // Condition for custom type or admin role
    then: () =>
      yup
        .string()
        .required(t('messages.coupon_code_required'))
        .test('unique', t('messages.coupon_code_unique'), async function (value) {
          if (ISREADONLY.value) {
            return true;
          }
          const isUnique = await storeRequest({ url: UNIQUE_CHECK, body: value, type: 'file' });
          if (!isUnique.isUnique) {
            return this.createError({ path: 'coupon_code', message: t('messages.coupon_code_unique')  });
          }
          return true;
        }),
    otherwise: (schema) => schema.notRequired(), // Optional if condition isn't met
  }),

number_of_coupon: yup
  .string()
  .when('coupon_type', {
    is: (value) => value === 'bulk', // Condition for bulk type or admin role
    then: (schema) =>
      schema
        .required(t('messages.number_of_coupon_required'))
        .matches(/^[1-9]\d*$/, t('messages.number_of_coupon_valid')),
    otherwise: (schema) => schema.notRequired(), // Optional if condition isn't met
  }),

  discount_amount: yup.string().when('discount_type', {
    is: 'fixed',
    then: () => yup.number().typeError(t('messages.discount_amount_number')).min(1, t('messages.discount_amount_min'))
  }),
  discount_percentage: yup.string().when('discount_type', {
    is: 'percent',
    then: () =>
      yup
        .number()
        .required(t('messages.value_required'))
        .test('is-less-than-100', t('messages.percent_value_max'), (value) => {
          const numValue = parseFloat(value)
          return !isNaN(numValue) && numValue <= 100
        })
  })
})


const { handleSubmit, errors, resetForm } = useForm({
  validationSchema
})

const { value: name } = useField('name')
const { value: description } = useField('description')
const { value: start_date_time } = useField('start_date_time')
const { value: end_date_time } = useField('end_date_time')
const { value: feature_image } = useField('feature_image')
const { value: status } = useField('status')
const { value: discount_amount } = useField('discount_amount')
const { value: discount_percentage } = useField('discount_percentage')
const { value: discount_type } = useField('discount_type')
const { value: coupon_type } = useField('coupon_type')
const { value: number_of_coupon } = useField('number_of_coupon')
const { value: coupon_code } = useField('coupon_code')
const { value: plan_id } = useField('plan_id')
const { value: use_limit } = useField('use_limit')



const errorMessages = ref({})

const typeOptions = [
  { label: 'Percent', value: 'percent' },
  { label: 'Fixed', value: 'fixed' }
]

const couponOptions = [
  { label: 'Custom', value: 'custom' },
  { label: 'bulk', value: 'bulk' }
]

onMounted(() => {
  getPlanList()
  setFormData(defaultData())
})
const ISREADONLY = ref(false)
// Form Submit
const IS_SUBMITED = ref(false)
const formSubmit = handleSubmit((values) => {
  if (IS_SUBMITED.value) return false;
  IS_SUBMITED.value = true;
  values.custom_fields_data = JSON.stringify(values.custom_fields_data);

  if (currentId.value > 0) {
    updateRequest({ url: UPDATE_URL, id: currentId.value, body: values, type: 'file' })
      .then((res) => reset_datatable_close_offcanvas(res));
  } else {
    storeRequest({ url: STORE_URL, body: values, type: 'file' })
      .then((res) => reset_datatable_close_offcanvas(res));
  }
});
</script>
