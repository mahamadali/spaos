<template>
    <form @submit="formSubmit">
      <div class="offcanvas offcanvas-end" tabindex="-1" id="form-offcanvas" aria-labelledby="form-offcanvasLabel">
        <FormHeader :currentId="currentId" :editTitle="editTitle" :createTitle="createTitle"></FormHeader>
        <div class="offcanvas-body">
          <InputField class="col-md-12" type="text" :is-required="true" :label="$t('service.lbl_name')" placeholder="" v-model="name" :error-message="errors['name']" ></InputField>
          <div class="form-group">
            <label class="form-label" for="type"> {{ $t('custom_feild.lbl_type') }} <span class="text-danger">*</span></label>
            <Multiselect v-model="type" :value="type" :placeholder="$t('product.select_type')" v-bind="type_data" id="type" autocomplete="off"></Multiselect>
            <span v-if="errorMessages['type']">
              <ul class="text-danger">
                <li v-for="err in errorMessages['type']" :key="err">{{ err }}</li>
              </ul>
            </span>
            <span class="text-danger">{{ errors.type }}</span>
          </div>
          <div class="form-group" v-if="type">
            <div v-for="(input, index) in variationValues" :key="input.key" class="d-flex gap-3 align-items-end mb-3">
              <div v-if="type === 'color'" class="flex-grow-1">
                <label class="form-label" for="label">{{ $t('service.lbl_colour') }}</label>
                <input 
                  type="color" 
                  class="form-control form-control-color w-100" 
                  v-model="input.value.value"
                  :class="{'is-invalid': errors[`values.${index}.value`]}"
                />
                <div v-if="errors[`values.${index}.value`]" class="invalid-feedback d-block">
                  {{ errors[`values.${index}.value`] }}
                </div>
              </div>
              <div v-else-if="type === 'text'" class="flex-grow-1">
                <label class="form-label" for="label">{{ $t('service.lbl_value') }}</label>
                <input 
                  type="text" 
                  class="form-control" 
                  v-model="input.value.value"
                  :class="{'is-invalid': errors[`values.${index}.value`]}"
                />
                <div v-if="errors[`values.${index}.value`]" class="invalid-feedback d-block">
                  {{ errors[`values.${index}.value`] }}
                </div>
              </div>
              <div class="flex-grow-1">
                <label class="form-label" for="label">{{ $t('service.lbl_name') }}</label>
                <input 
                  type="text" 
                  class="form-control" 
                  v-model="input.value.name"
                  :class="{'is-invalid': errors[`values.${index}.name`]}"
                />
                <div v-if="errors[`values.${index}.name`]" class="invalid-feedback d-block">
                  {{ errors[`values.${index}.name`] }}
                </div>
              </div>
              <div class="d-flex align-items-end">
                <button v-if="variationValues.length > 1" type="button" class="btn btn-danger" @click="remove(index)">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </div>

               <div v-if="errors.values" class="text-danger mt-2">
                {{ errors.values }}
              </div>
            <div class="my-3">
              <button 
                class="btn btn-secondary w-100" 
                type="button" 
                @click="push({ value: '', name: '' })"
                :disabled="!type"
              >
                <i class="fa fa-plus-circle" aria-hidden="true"></i> {{ $t('service.add_values') }}
              </button>
           
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="category-status">{{ $t('service.lbl_status') }}</label>
            <div class="d-flex justify-content-between align-items-center form-control">
              <label class="form-label mb-0" for="category-status">{{ $t('service.lbl_status') }}</label>
              <div class="form-check form-switch">
                <input class="form-check-input" :value="status" :checked="status" name="status" id="category-status" type="checkbox" v-model="status" />
              </div>
            </div>
          </div>
        </div>
      <FormFooter :IS_SUBMITED="IS_SUBMITED"></FormFooter>
      </div>
    </form>
  </template>

  <script setup>
  import { ref, onMounted, reactive,watch} from 'vue'
  import { EDIT_URL, STORE_URL, UPDATE_URL } from '../constant/variation'
  import { useField, useForm, useFieldArray } from 'vee-validate'
  import InputField from '@/vue/components/form-elements/InputField.vue'

  import { useModuleId, useRequest, useOnOffcanvasHide } from '@/helpers/hooks/useCrudOpration'
  import * as yup from 'yup'
  import FormHeader from '@/vue/components/form-elements/FormHeader.vue'
  import FormFooter from '@/vue/components/form-elements/FormFooter.vue'

  // props
  defineProps({
    createTitle: { type: String, default: '' },
    editTitle: { type: String, default: '' }
  })

  const { getRequest, storeRequest, updateRequest, listingRequest } = useRequest()

  // Edit Form Or Create Form
  const currentId = useModuleId(() => {
    if (currentId.value > 0) {
      getRequest({ url: EDIT_URL, id: currentId.value }).then((res) => {
        if (res.status) {
          setFormData(res.data)
        }
      })
    } else {
      setFormData(defaultData())
    }
  })
  useOnOffcanvasHide('form-offcanvas', () => setFormData(defaultData()))

  const type_data = ref({
  searchable: true,
  options: [
    { label: 'Text', value: 'text' },
    { label: 'Color', value: 'color' }
  ],
  closeOnSelect: true
})

  /*
   * Form Data & Validation & Handeling
   */
  // Default FORM DATA
  const defaultData = () => {
    errorMessages.value = {}
    return {
      name: '',
      type: '', // Set to empty string so no value is selected on create
      status: true,
      values: []
    }
  }

  //  Reset Form
  const setFormData = (data) => {
    resetForm({
      values: {
        name: data.name,
        type: data.type || '', // Only set type if present (edit mode)
        status: data.status,
        values: data.values
      }
    })
  }

  // Reload Datatable, SnackBar Message, Alert, Offcanvas Close
  const reset_datatable_close_offcanvas = (res) => {
    IS_SUBMITED.value = false  // Reset loading state immediately
    if (res.status) {
      window.successSnackbar(res.message)
      renderedDataTable.ajax.reload(null, false)
      bootstrap.Offcanvas.getInstance('#form-offcanvas').hide()
      setFormData(defaultData())
      removeImage()
    } else {
      window.errorSnackbar(res.message || 'An error occurred')
      errorMessages.value = res.all_message || {}
    }
  }

  // Validations
  const validationSchema = yup.object({
    name: yup.string().required('Name is a required field'),
    type: yup.string().required('Type is required'),
    values: yup.array()
      .of(
        yup.object().shape({
          value: yup.string().required('Value is required'),
          name: yup.string().required('Name is required')
        })
      )
      .min(1, 'At least one variation value is required')
      .when('type', {
        is: 'color',
        then: (schema) => schema.test(
          'valid-color',
          'Please enter a valid color',
          (values) => {
            if (!values) return false;
            return values.every(item => /^#([0-9A-F]{3}){1,2}$/i.test(item.value));
          }
        )
      })
  })


  const { handleSubmit, errors, resetForm, setFieldValue, values } = useForm({
    validationSchema,
    initialValues: {
      name: '',
      type: '',
      values: [{ value: '', name: '' }],
      status: 1
    }
  })
  const { value: name, errorMessage: nameError } = useField('name')
  const { value: type, errorMessage: typeError } = useField('type')
  const { value: status } = useField('status')
  const { fields: variationValues, push, remove } = useFieldArray('values')

  // Watch for type changes to reset values if needed
  watch(() => type.value, (newType, oldType) => {
    if (newType !== oldType) {
      // Clear existing values when type changes
      setFieldValue('values', [{ value: '', name: '' }])
    }
  });
  push({value: '', name: ''})

  const errorMessages = ref({})

  onMounted(() => {
    setFormData(defaultData())
  })
  const IS_SUBMITED = ref(false)
  const formSubmit = handleSubmit((values) => {
    if (IS_SUBMITED.value) return false


    // Custom validation: at least one variation value required
    if (variationValues.value.length < 1) {
      errorMessages.value = { ...errorMessages.value, values: [ 'Please add at least one value.'] }
      return false
    } else {
      // Clear previous error if any
      if (errorMessages.value.values) delete errorMessages.value.values
    }

    IS_SUBMITED.value = true
    if (currentId.value > 0) {
      updateRequest({ url: UPDATE_URL, id: currentId.value, body: values })
        .then((res) => reset_datatable_close_offcanvas(res));
    } else {
      storeRequest({ url: STORE_URL, body: values })
        .then((res) => reset_datatable_close_offcanvas(res));
    }
  });


  </script>
<style scoped>
[type="color"] {
  width: 100%;
  height: 2.5rem;
  border-radius: var(--bs-border-radius);
}
</style>
