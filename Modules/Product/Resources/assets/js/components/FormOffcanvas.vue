<template>
  <form @submit="formSubmit">
    <div class="offcanvas offcanvas-end" tabindex="-1" id="form-offcanvas" aria-labelledby="form-offcanvasLabel">
      <FormHeader :currentId="currentId" :editTitle="editTitle" :createTitle="createTitle"></FormHeader>
      <div class="offcanvas-body">
        <fieldset>
          <legend>{{ $t('product.product_information') }}</legend>
          <div class="row">
            <div class="form-group col-md-4">
              <div class="text-center">
                <img :src="ImageViewer || defaultImage" alt="feature-image"
                  class="img-fluid mb-2 product-image-thumbnail" />
                <div v-if="validationMessage" class="text-danger mb-2">{{ validationMessage }}</div>
                <div class="d-flex align-items-center justify-content-center gap-2">
                  <input type="file" ref="profileInputRef" class="form-control d-none" id="feature_image"
                    name="feature_image" @change="fileUpload" accept=".jpeg, .jpg, .png, .gif" />
                  <label class="btn btn-secondary" for="feature_image">{{ $t('messages.upload') }}</label>
                  <input type="button" class="btn btn-danger" name="remove" :value="$t('messages.remove')"
                    @click="removeLogo()" v-if="ImageViewer" />
                </div>
              </div>
            </div>

            <div class="col-md-8">
              <InputField class="" type="text" :is-required="true" :label="$t('product.name')" placeholder=""
                v-model="name" :error-message="errors['name']"></InputField>
              <InputField class="" type="textarea" :textareaRows="5" :label="$t('product.description')" placeholder=""
                v-model="short_description"></InputField>
            </div>

            <div class="col-md-12 form-group editor-container">
              <label class="form-label" for="description">{{ $t('product.long_description') }}</label>
              <!-- Add Quill editor here -->
              <QuillEditor theme="snow" v-model:content="description" contentType="html" />
              <span class="text-danger">{{ errors.description }}</span>
            </div>

            <div class="form-group col-md-6">
              <label class="form-label">{{ $t('product.brand') }} <span class="text-danger">*</span></label>
              <Multiselect id="brand-list" v-model="brand_id" :value="brand_id"
                :placeholder="$t('product.select_brand')" v-bind="singleSelectOption" :options="brands.options"
                @select="selectBrand" class="form-group"></Multiselect>
              <span v-if="errorMessages['brand_id']">
                <ul class="text-danger">
                  <li v-for="err in errorMessages['brand_id']" :key="err">{{ err }}</li>
                </ul>
              </span>
              <span class="text-danger">{{ errors.brand_id }}</span>
            </div>

            <div class="form-group col-md-6">
              <label class="form-label" for="categories">{{ $t('product.categories') }} <span
                  class="text-danger">*</span></label>
              <Multiselect id="categories" v-model="main_category_id" :value="main_category_id"
                :placeholder="$t('product.select_category')" v-bind="singleSelectOption" :options="category.options"
                @select="onSelectMainCategory" class="form-group"></Multiselect>
              <span class="text-danger">{{ errors['main_category_id'] }}</span>
            </div>

            <div class="form-group col-md-6">
              <label class="form-label" for="subcategories">{{ $t('product.subcategories') }}</label>
              <Multiselect id="subcategories" v-model="subcategory_ids" :value="subcategory_ids"
                :placeholder="$t('product.select_subcategory')" v-bind="multiselectOption" :options="subCategory.options"
                class="form-group"></Multiselect>
              <span class="text-danger">{{ errors['subcategory_ids'] }}</span>
            </div>

            <div class="form-group col-md-6">
              <label class="form-label">{{ $t('product.tags') }}</label>
              <Multiselect v-model="tags" :value="tags" :placeholder="$t('product.select_tag')"
                v-bind="multiselectCreateOption" :options="tagsList.options" id="tags-list" autocomplete="off">
              </Multiselect>
            </div>

            <div class="col-md-6 form-group">
              <label class="form-label">{{ $t('product.unit') }}</label>
              <Multiselect id="units-list" v-model="unit_id" :value="unit_id" :placeholder="$t('product.select_unit')"
                v-bind="singleSelectOption" :options="units.options" class="form-group"></Multiselect>
            </div>
          </div>
        </fieldset>
        <fieldset>
          <legend>{{ $t('product.product_price') }}</legend>
          <div class="form-group">
            <div class="d-flex justify-content-end">
              <label class="form-label me-2" for="category-status">{{ $t('product.has_variation') }}</label>
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" :value="has_variation" :true-value="1" :false-value="0"
                  :checked="has_variation" v-model="has_variation" />
              </div>
            </div>
          </div>
          <div class="row" v-if="has_variation == 1">
            <template v-for="(varData, index) in variations" :key="varData.key">
              <div class="col-md-12">
                <div class="d-flex gap-3 align-items-center">
                  <div class="d-flex flex-grow-1 gap-3">
                    <div class="form-group w-50">
                      <label for="">{{ $t('product.variation_type') }}</label>
                      <Multiselect :id="'variations-list-' + varData.key" v-model="varData.value.variation"
                        :placeholder="$t('product.select_type')" v-bind="singleSelectOption"
                        :options="getFilteredVariationOptions(index)" @select="generateCombinations"
                        @deselect="generateCombinations" @change="() => varData.value.variationValue = []"
                        class="form-group" />
                    </div>
                    <div class="form-group w-50">
                      <label for="">{{ $t('product.variation_value') }}</label>
                      <Multiselect :id="'variation-value-list-' + varData.key" v-model="varData.value.variationValue"
                        :placeholder="$t('product.select_value')" @select="generateCombinations"
                        @deselect="generateCombinations" :value="varData.value.variationValue"
                        v-bind="multiselectOption" :options="variationValueCheck(varData.value.variation)"
                        class="form-group"></Multiselect>
                    </div>
                  </div>
                  <div v-if="variations.length > 1">
                    <button class="btn btn-danger btn-icon" @click="variationsSplice(varData.key); generateCombinations()">
                      <i class="fa-solid fa-trash"></i>
                    </button>
                  </div>
                </div>
              </div>
            </template>

            <div class="col-md-6" v-if="variations.length !== variationsData.options.length">
              <div class="form-group">
                <button class="btn btn-secondary" type="button"
                  @click="variationsPush({ variation: '', variationValue: [] })">+ {{
                    $t('product.add_more_variation') }}</button>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th>{{ $t('product.variation') }}</th>
                      <th>{{ $t('product.price_tax') }}</th>
                      <th>{{ $t('product.stock') }}</th>
                      <th>{{ $t('product.sku') }}</th>
                      <th>{{ $t('product.code') }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(comb, index) in combinations.length ? combinations : [defaultCombination]" :key="index">
                      <td>
                        <input class="form-control" v-model="comb.value.variation" :readonly="true" :disabled="true"
                          :required="has_variation === 1" />
                      </td>
                      <td>
                        <input class="form-control" v-model="comb.value.price" type="number" :min="0.01" :step="0.01"
                          :required="has_variation === 1" />
                      </td>
                      <td>
                        <input class="form-control" v-model="comb.value.stock" type="number" :min="1" :step="1"
                          :required="has_variation === 1" />
                      </td>
                      <td>
                        <input class="form-control" v-model="comb.value.sku" :required="has_variation === 1" />
                      </td>
                      <td>
                        <input class="form-control" v-model="comb.value.code" :required="has_variation === 1" />
                      </td>
                    </tr>

                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="row" v-else>
            <InputField class="col-md-3" type="number" :is-required="has_variation === 0"
              :error-messages="showPriceValidation ? errorMessages['price'] : []" :step="0.01" :min="0.01"
              :label="$t('product.price_tax')" placeholder="" v-model="price"
              :error-message="showPriceValidation ? errors['price'] : ''"></InputField>
            <InputField class="col-md-3" type="number" :is-required="has_variation === 0"
              :error-messages="showStockValidation ? errorMessages['stock'] : []" :step="0" :min="1"
              :label="$t('product.stock')" placeholder="" v-model="stock"
              :error-message="showStockValidation ? errors['stock'] : ''"></InputField>
            <InputField class="col-md-3" type="text" :is-required="has_variation === 0" :label="$t('product.sku')"
              placeholder="" v-model="sku" :error-message="errors['sku']"></InputField>
            <InputField class="col-md-3" type="text" :is-required="has_variation === 0" :label="$t('product.code')"
              placeholder="" v-model="code" :error-message="errors['code']"></InputField>
          </div>
        </fieldset>
        <fieldset>
          <legend>{{ $t('product.product_discount') }}</legend>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label class="form-label" for="date">{{ $t('product.date') }}</label>
                <div class="w-100">
                  <flat-pickr id="date" class="form-control" :config="config" v-model="date_range" :value="date_range"
                    @click="scrollToCalendar"></flat-pickr>
                </div>
              </div>
            </div>
            <div  class="col-md-4">

              <InputField type="text" :label="$t('product.amount')" placeholder=""
                          v-model="discount_value" :error-message="errors['discount_value']"></InputField>
            <div v-if="errorMessages['discount_value'] && errorMessages['discount_value'].length" class="text-danger mt-2">
              <ul>
                <li v-for="err in errorMessages['discount_value']" :key="err">{{ err }}</li>
              </ul>
            </div>

            </div>
          
            <div class="col-md-4">
              <label class="form-label">{{ $t('product.percent_or_fixed') }}</label>
              <Multiselect v-model="discount_type" :value="discount_type" v-bind="singleSelectOption"
                :options="typeOptions" id="type" autocomplete="off"></Multiselect>
            </div>
          </div>
        </fieldset>
        <div class="row">
          <div class="col-md-12 px-5">
            <div class="form-group">
              <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                  <input class="form-check-input m-0" :value="is_featured" :checked="is_featured" :true-value="1"
                    :false-value="0" name="is_featured" id="is_featured" type="checkbox" v-model="is_featured" />
                  <label class="form-label m-0" for="is_featured">{{ $t('product.lbl_is_featured') }}</label>
                </div>
                <div class="d-flex align-items-center gap-3">
                  <label class="form-label" for="status">{{ $t('product.lbl_status') }}</label>
                  <div class="form-check form-switch">
                    <input class="form-check-input" :value="status" :checked="status" :true-value="1" :false-value="0"
                      name="status" id="status" type="checkbox" v-model="status" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <FormFooter :IS_SUBMITED="IS_SUBMITED" :disabled="!isVariationValid"></FormFooter>
    </div>
  </form>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { EDIT_URL, STORE_URL, UPDATE_URL } from '../constant/product'
import { CATEGORY_LIST, SUBCATEGORY_LIST, BRAND_LIST, UNITS_LIST, TAGS_LIST, VARIATIONS_LIST } from '../constant/product'
import { useField, useForm, useFieldArray } from 'vee-validate'
import InputField from '@/vue/components/form-elements/InputField.vue'
import FlatPickr from 'vue-flatpickr-component'
import { useModuleId, useRequest, useOnOffcanvasHide } from '@/helpers/hooks/useCrudOpration'
import * as yup from 'yup'
import { readFile } from '@/helpers/utilities'
import { useSelect } from '@/helpers/hooks/useSelect'
import FormHeader from '@/vue/components/form-elements/FormHeader.vue'
import FormFooter from '@/vue/components/form-elements/FormFooter.vue'
import { buildMultiSelectObject } from '@/helpers/utilities'
import { useI18n } from 'vue-i18n';

// ... (rest of the setup code) ...

const descriptionEditorRef = ref(null);
const validationMessage = ref('');

// props
const props = defineProps({
  createTitle: { type: String, default: '' },
  editTitle: { type: String, default: '' },
  defaultImage: { type: String, default: '' }
})

const { getRequest, storeRequest, updateRequest, listingRequest } = useRequest()

// flatpicker
const config = ref({
  dateFormat: 'Y/m/d',
  minDate: 'today',
  static: true,
  mode: 'range'
})

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
    variationsPush({ variation: '', variationValue: [] })
    // generateCombinations()
  }
})

useOnOffcanvasHide('form-offcanvas', () => {
  setFormData(defaultData())
})

const ImageViewer = ref(null)
const profileInputRef = ref(null)
const fileUpload = async (e) => {
  let file = e.target.files[0];
  const maxSizeInMB = 2;
  const maxSizeInBytes = maxSizeInMB * 1024 * 1024;

  if (file) {
    if (file.size > maxSizeInBytes) {
      // File is too large
      validationMessage.value = `File size exceeds ${maxSizeInMB} MB. Please upload a smaller file.`;
      // Clear the file input
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

const defaultCombination = {
  value: {
    variation: '',
    price: '',
    stock: '',
    sku: '',
    code: ''
  }
}


const removeLogo = () => removeImage({ imageViewerBS64: ImageViewer, changeFile: feature_image })

/*
 * Form Data & Validation & Handeling
 */
// Default FORM DATA
const defaultData = () => {
  errorMessages.value = {}

  return {
    name: '',
    slug: '',
    status: 1,
    short_description: '',
    description: ' ',
    main_category_id: null,
    subcategory_ids: [],
    tags: [],
    brand_id: '',
    unit_id: '',
    price: '',
    stock: '',
    sku: '',
    code: '',
    discount_value: 0,
    discount_type: 'percent',
    date_range: null,
    variations: [],
    combinations: [],
    has_variation: 0,
    feature_image: null,
    is_featured: 0
  }
}

//  Reset Form
const setFormData = (data) => {
  ImageViewer.value = data.feature_image
  resetForm({
    values: {
      name: data.name,
      slug: data.slug,
      short_description: data.short_description || '',
      description: data.description || '',
      main_category_id: Array.isArray(data.category_ids) && data.category_ids.length ? data.category_ids[0] : null,
      subcategory_ids: Array.isArray(data.category_ids) && data.category_ids.length > 1 ? data.category_ids.slice(1) : [],
      tags: data.tags || [],
      brand_id: data.brand_id || null,
      unit_id: data.unit_id || null,
      price: data.price || null,
      stock: data.stock || null,
      sku: data.sku || null,
      code: data.code || null,
      discount_value: data.discount_value || 0,
      discount_type: data.discount_type || 'percent',
      date_range: data.date_range || null,
      variations: data.variations || [],
      combinations: data.combinations || [],
      has_variation: data.has_variation || 0,
      feature_image: data.feature_image,
      status: data.status || 1,
      is_featured: data.is_featured || 0,
    }
  })

  // Ensure subcategory options are loaded on edit when main category exists
  const mainId = Array.isArray(data.category_ids) && data.category_ids.length ? data.category_ids[0] : null
  if (mainId) {
    getSubCategory(mainId, data.brand_id || null)
  }
}

// Reload Datatable, SnackBar Message, Alert, Offcanvas Close
const reset_datatable_close_offcanvas = (res) => {
  IS_SUBMITED.value = false
  if (res.status) {
    window.successSnackbar(res.message)
    renderedDataTable.ajax.reload(null, false)
    bootstrap.Offcanvas.getInstance('#form-offcanvas').hide()
    setFormData(defaultData())
    removeImage({ imageViewerBS64: ImageViewer, changeFile: feature_image })
  } else {
    window.errorSnackbar(res.message)
    errorMessages.value = res.all_message
  }
}

const { t } = useI18n();
// Validations
const validationSchema = yup.object({
  name: yup.string().required(t('messages.product_name_required')).max(190, t('messages.name_max_length')),
  brand_id: yup.string().required(t('messages.brand_required')),
  main_category_id: yup.mixed().required(t('messages.category_required')),
  brand_id: yup.string()
    .required(t('messages.brand_required')),
  price: yup.number()
    .when('has_variation', {
      is: 0,
      then: (schema) => schema
        .required(t('messages.price_required'))
        .typeError(t('messages.price_valid_number'))
        .min(0.01, t('messages.price_min')),
      otherwise: (schema) => schema.nullable()
    }),
  stock: yup.number()
    .when('has_variation', {
      is: 0,
      then: (schema) => schema
        .required(t('messages.stock_required'))
        .typeError(t('messages.stock_valid_number'))
        .integer(t('messages.stock_integer'))
        .min(1, t('messages.stock_min')),
      otherwise: (schema) => schema.nullable()
    }),

  sku: yup.string().when('has_variation', {
    is: 0,
    then: (schema) => schema.required(t('messages.sku_required')),
    otherwise: (schema) => schema.nullable()
  }),

  code: yup.string().when('has_variation', {
    is: 0,
    then: (schema) => schema.required(t('messages.code_required')),
    otherwise: (schema) => schema.nullable()
  }),
  discount_value: yup.number()
    .typeError(t('messages.discount_value_valid_number'))
    .when('discount_type', {
      is: 'percent',
      then: (schema) => schema
        .min(0, t('messages.discount_value_min'))
        .max(100, t('messages.discount_value_max_percent')),
      otherwise: (schema) => schema.min(0, t('messages.discount_value_min'))
    }),
})

const { handleSubmit, errors, resetForm } = useForm({
  validationSchema
})

const { value: name } = useField('name')
const { value: status } = useField('status')
const { value: is_featured } = useField('is_featured')
const { value: short_description } = useField('short_description')
const { value: description } = useField('description')
const { value: main_category_id } = useField('main_category_id')
const { value: subcategory_ids } = useField('subcategory_ids')
const { value: brand_id } = useField('brand_id')
const { value: tags } = useField('tags')
const { value: unit_id } = useField('unit_id')
const { value: price } = useField('price')
const { value: stock } = useField('stock')
const { value: sku } = useField('sku')
const { value: code } = useField('code')
const { value: discount_value } = useField('discount_value')
const { value: discount_type } = useField('discount_type')
const { value: date_range } = useField('date_range')
const { value: has_variation } = useField('has_variation')
const { fields: variations, push, remove } = useFieldArray('variations')
const { fields: combinations, push: combinationsPush, remove: combinationsSplice, replace: combinationsReplace } = useFieldArray('combinations')
const { value: feature_image } = useField('feature_image')


const errorMessages = ref({})

onMounted(() => {
  setFormData(defaultData())
  getBrand()
  getCategory()
  getUnits()
  getTags()
  getVariations()
})


const getFilteredVariationOptions = (currentIndex) => {
  // Get all selected variation values except the current one
  const allSelectedValues = variations.value
    .map((v, i) => (i !== currentIndex && v.value.variation ? v.value.variation : null))
    .filter(v => v !== null && v !== '');

  // Map values to labels
  const allSelectedLabels = allSelectedValues
    .map(val => {
      const found = variationsData.value.options.find(opt => String(opt.value) === String(val));
      return found ? found.label : null;
    })
    .filter(Boolean);

  // Get the current selected variation's label
  const currentSelectedValue = variations.value[currentIndex]?.value.variation;
  const currentSelectedLabel = variationsData.value.options.find(opt => String(opt.value) === String(currentSelectedValue))?.label;

  // Filter options: exclude if label is in allSelectedLabels, unless it's the current selection
  return variationsData.value.options.filter(option => {
    return !allSelectedLabels.includes(option.label) || option.label === currentSelectedLabel;
  });
}


const brands = ref({ options: [], list: [] })

const getBrand = () => useSelect({ url: BRAND_LIST }, { value: 'id', label: 'name' }).then((data) => (brands.value = data))

const selectBrand = (value) => {
  getCategory(value)
}

const category = ref({ options: [], list: [] })
const subCategory = ref({ options: [], list: [] })

const getCategory = (value) => useSelect({ url: CATEGORY_LIST, data: { brand_id: value } }, { value: 'id', label: 'name' }).then((data) => (category.value = data))

const getSubCategory = (parentId, brandId) => useSelect({ url: SUBCATEGORY_LIST, data: { parent_id: parentId, brand_id: brandId } }, { value: 'id', label: 'name' }).then((data) => (subCategory.value = data))

const onSelectMainCategory = (value) => {
  subcategory_ids.value = []
  if (value) {
    getSubCategory(value, brand_id.value)
  } else {
    subCategory.value = { options: [], list: [] }
  }
}

const units = ref({ options: [], list: [] })

const getUnits = () => useSelect({ url: UNITS_LIST }, { value: 'id', label: 'name' }).then((data) => (units.value = data))

const tagsList = ref({ options: [], list: [] })

const getTags = () => useSelect({ url: TAGS_LIST }, { value: 'name', label: 'name' }).then((data) => (tagsList.value = data))

const variationsData = ref({ options: [], list: [] })

const getVariations = () => useSelect({ url: VARIATIONS_LIST }, { value: 'id', label: 'name' }).then((data) => (variationsData.value = data))

const variationValueCheck = (data) => {
  return buildMultiSelectObject(variationsData.value.list.find((item) => item.id == data)?.values || [], { value: 'id', label: 'name' })
}
const IS_SUBMITED = ref(false)
const formSubmit = handleSubmit((values) => {
  if (IS_SUBMITED.value) return false

  if (values.discount_type === 'fixed' && has_variation.value == 1) {

  const prices = values.combinations?.map(c => parseFloat(c.price)).filter(p => !isNaN(p)) || []
  const minPrice = Math.min(...prices)

  if (parseFloat(values.discount_value) > minPrice) {

      errorMessages.value['discount_value'] = [t('messages.discount_value_exceeds_min_price') || 'Discount must not exceed minimum variation price.']
      return
  }
}

if(values.discount_type === 'fixed' && has_variation.value == 0){

   if (parseFloat(values.discount_value) > values.price) {

      errorMessages.value['discount_value'] = [t('messages.discount_value_min_price') || 'Discount must not exceed price.']
      return
  }


}



  IS_SUBMITED.value = true
  values.combinations = JSON.stringify(values.combinations)
  values.tags = JSON.stringify(values.tags)
  const selectedCategories = []
  if (values.main_category_id) selectedCategories.push(values.main_category_id)
  if (Array.isArray(values.subcategory_ids) && values.subcategory_ids.length) {
    selectedCategories.push(...values.subcategory_ids)
  }
  values.category_ids = JSON.stringify(selectedCategories)

  if (currentId.value > 0) {
    updateRequest({ url: UPDATE_URL, id: currentId.value, body: values, type: 'file' }).then((res) => reset_datatable_close_offcanvas(res))
  } else {
    storeRequest({ url: STORE_URL, body: values, type: 'file' }).then((res) => reset_datatable_close_offcanvas(res))
  }
})

const generateCombinations = async () => {
  const varVal = variations.value
  const valuesArray = varVal.map((item) => (item.value.variation !== '' && item.value.variationValue.length > 0 ? item.value.variationValue : '')) || []
  const numVariations = varVal.filter((item) => item.value.variation !== '' && item.value.variationValue.length > 0).length

  const result = []
  const currentCombination = new Array(numVariations)
  const variationValuesArr = []
  const newVarval = variationsData.value.list
  newVarval.map((variation) => {
    const variationId = variation.id
    variation.values.forEach((value) => {
      value.variation_id = variationId
    })
    variationValuesArr.push(...variation.values)
  })

  // Store existing combinations data with both key and variation name
  const existingCombinations = combinations.value.reduce((acc, comb) => {
    // Store by both key and variation name for more robust matching
    acc[comb.value.variation_key] = comb.value
    acc[comb.value.variation] = comb.value
    return acc
  }, {})

  function backtrack(index) {
    if (index === numVariations) {
      const val_key = currentCombination
        .map((v) => {
          return variationValuesArr.find((x) => x.id == v).variation_id + ':' + v
        })
        .join('/')
      const val = currentCombination
        .map((v) => {
          return variationValuesArr.find((x) => x.id == v).name
        })
        .join('-')

      // Try to find existing values by key or variation name
      let existingValues = existingCombinations[val_key] || existingCombinations[val]

      // If no exact match found, try to find a partial match
      if (!existingValues) {
        const valParts = val.split('-')
        const existingKeys = Object.keys(existingCombinations)
        const matchingKey = existingKeys.find(key => {
          const keyParts = key.split('-')
          return valParts.some(part => keyParts.includes(part))
        })
        if (matchingKey) {
          existingValues = existingCombinations[matchingKey]
        }
      }

      // Use existing values or defaults
      const newCombination = {
        variation_key: val_key,
        variation: val,
        price: existingValues ? existingValues.price : '',
        stock: existingValues ? existingValues.stock : '',
        sku: existingValues ? existingValues.sku : val,
        code: existingValues ? existingValues.code : val.toLowerCase()
      }

      result.push(newCombination)
      return
    }
    for (const value of valuesArray[index]) {
      currentCombination[index] = value
      backtrack(index + 1)
    }
  }

  backtrack(0)

  // Only replace if we have valid combinations
  if (result.length > 0) {
    await combinationsReplace([])
    result.forEach((comb) => {
      combinationsPush(comb)
    })
  }

  return result
}

const typeOptions = [
  { label: 'Percent(%)', value: 'percent' },
  { label: 'Fixed', value: 'fixed' }
]
// Select Options
const singleSelectOption = ref({
  closeOnSelect: true,
  searchable: true
})

const multiselectOption = ref({
  mode: 'tags',
  searchable: true
})

const multiselectCreateOption = ref({
  mode: 'tags',
  createOption: true,
  searchable: true
})

// Add this computed property after the validation schema
const showPriceValidation = computed(() => {
  return has_variation.value === 0 && errors.value.price
})

const showStockValidation = computed(() => {
  return has_variation.value === 0 && errors.value.stock
})

// Add new computed property for variation validation
const isVariationValid = computed(() => {
  if (has_variation.value === 1) {
    // When variations are enabled, check if at least one variation is complete

    return variations.value.some(variation =>
      variation.value.variation &&
      variation.value.variationValue &&
      variation.value.variationValue.length > 0
    );
  } else {
    return Boolean(price.value) && Boolean(stock.value);
  }
})

const scrollToCalendar = () => {
  // Wait for the calendar to be rendered
  setTimeout(() => {
    const calendar = document.querySelector('.flatpickr-calendar');
    if (calendar) {
      calendar.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  }, 100);
}

// Helper to generate a unique key
function generateUniqueKey() {
  return Date.now() + Math.random();
}

// Updated push for variations to include a unique key
const variationsPush = (variation) => {
  const key = generateUniqueKey();
  push({ key, value: { ...variation } });
};

// Updated remove for variations to use key
const variationsSplice = (keyToRemove) => {
  const idx = variations.value.findIndex(v => v.key === keyToRemove);
  if (idx !== -1) {
    remove(idx);
  }
};
</script>
<style scoped>
.product-image-thumbnail {
  width: 100%;
  object-fit: cover;
  height: 200px;
  max-height: 200px;
  border-radius: 1rem;
  padding: 0.75rem;
  border: 1px solid var(--bs-border-color);
}

@media only screen and (min-width: 768px) {
  .offcanvas {
    width: 80%;
  }
}

@media only screen and (min-width: 1280px) {
  .offcanvas {
    width: 60%;
  }
}

.editor-container {
  height: 200px;
}
</style>