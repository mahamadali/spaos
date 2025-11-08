(function () {
  'use strict'
  $(document).on('change', '.datatable-filter [data-filter="select"]', function () {
    window.renderedDataTable.ajax.reload(null, false)
  })

  $(document).on('input', '.dt-search', function () {
    window.renderedDataTable.ajax.reload(null, false)
  })

  const confirmSwal = async (message) => {
    return await Swal.fire({
      title: message,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#858482',
      confirmButtonText: 'Yes, do it!',
      showClass: {
        popup: 'animate__animated animate__zoomIn'
      },
      hideClass: {
        popup: 'animate__animated animate__zoomOut'
      },
      reverseButtons: true
    }).then((result) => {
      return result
    })
  }

  window.confirmSwal = confirmSwal

  $('#quick-action-form').on('submit', function (e) {
    e.preventDefault()
    const form = $(this)
    const url = form.attr('action')
    const message = $('[name="message_' + $('[name="action_type"]').val() + '"]').val()
    const rowdIds = $('#datatable_wrapper .select-table-row:checked')
      .map(function () {
        return $(this).val()
      })
      .get()
    confirmSwal(message).then((result) => {
      if (!result.isConfirmed) return
      callActionAjax({ url: `${url}?rowIds=${rowdIds}`, body: form.serialize() })
      //
    })
  })


  $('#quick-action-type').on('change', function () {
    const selected = $(this).val()

    // Hide all fields first
    $('.quick-action-field').addClass('d-none')

    if (selected) {
      const field = $('#' + selected + '-action')
      field.removeClass('d-none')

      // Reset internal select to first option
      const select = field.find('select')
      if (select.length) {
        select.prop('selectedIndex', 0).trigger('change')
      }
    }
  })


  // Update status on switch
  $(document).on('change', '#datatable_wrapper .switch-status-change', function () {
    let url = $(this).attr('data-url')
    let body = {
      status: $(this).prop('checked') ? 1 : 0,
      _token: $(this).attr('data-token')
    }
    callActionAjax({ url: url, body: body })
  })

  // Update show_for_booking on switch
  $(document).on('change', '#datatable_wrapper .switch-show-for-booking', function () {
    let url = $(this).attr('data-url')
    let body = {
      show_for_booking: $(this).prop('checked') ? 1 : 0,
      _token: $(this).attr('data-token')
    }
    callActionAjax({ url: url, body: body })
  })

  $(document).on('change', '#datatable_wrapper .change-select', function () {
    let url = $(this).attr('data-url')
    let body = {
      value: $(this).val(),
      _token: $(this).attr('data-token')
    }
    callActionAjax({ url: url, body: body })
  })

  function callActionAjax({ url, body }) {
    $.ajax({
      type: 'POST',
      url: url,
      data: body,
      success: function (res) {
        if (res.status) {
          window.successSnackbar(res.message)
          window.renderedDataTable.ajax.reload(resetActionButtons, false)
          const event = new CustomEvent('update_quick_action', { detail: { value: true } })
          document.dispatchEvent(event)
        } else {
          Swal.fire({
            title: 'Error',
            text: res.message,
            icon: 'error',
            showClass: {
              popup: 'animate__animated animate__zoomIn'
            },
            hideClass: {
              popup: 'animate__animated animate__zoomOut'
            }
          })
          // window.errorSnackbar(res.message)
        }
      }
    })
  }

  // Update status on button click
  $(document).on('click', '#datatable_wrapper .button-status-change', function () {
    let url = $(this).attr('data-url')
    let body = {
      status: 1,
      _token: $(this).attr('data-token')
    }
    callActionAjax({ url: url, body: body })
  })

  function callActionAjax({ url, body }) {
    $.ajax({
      type: 'POST',
      url: url,
      data: body,
      success: function (res) {
        if (res.status) {
          window.successSnackbar(res.message)
          window.renderedDataTable.ajax.reload(resetActionButtons, false)
          const event = new CustomEvent('update_quick_action', { detail: { value: true } })
          document.dispatchEvent(event)
        } else {
          window.errorSnackbar(res.message)
        }
      }
    })
  }

  //select row in datatable
  const dataTableRowCheck = (id) => {
    checkRow()
    if ($('.select-table-row:checked').length > 0) {
      $('#quick-action-form').removeClass('form-disabled')
      //if at-least one row is selected
      document.getElementById('select-all-table').indeterminate = true
      $('#quick-actions').find('input, textarea, button, select').removeAttr('disabled')
    } else {
      //if no row is selected
      document.getElementById('select-all-table').indeterminate = false
      $('#select-all-table').attr('checked', false)
      resetActionButtons()
    }

    if ($('#datatable-row-' + id).is(':checked')) {
      $('#row-' + id).addClass('table-active')
    } else {
      $('#row-' + id).removeClass('table-active')
    }
  }
  window.dataTableRowCheck = dataTableRowCheck

  const selectAllTable = (source) => {
    const checkboxes = document.getElementsByName('datatable_ids[]')
    for (var i = 0, n = checkboxes.length; i < n; i++) {
      // if disabled property is given to checkbox, it won't select particular checkbox.
      if (!$('#' + checkboxes[i].id).prop('disabled')) {
        checkboxes[i].checked = source.checked
      }
      if ($('#' + checkboxes[i].id).is(':checked')) {
        $('#' + checkboxes[i].id)
          .closest('tr')
          .addClass('table-active')
        $('#quick-actions').find('input, textarea, button, select').removeAttr('disabled')
        if ($('#quick-action-type').val() == '') {
          $('#quick-action-apply').attr('disabled', true)
        }
      } else {
        $('#' + checkboxes[i].id)
          .closest('tr')
          .removeClass('table-active')
        resetActionButtons()
      }
    }

    checkRow()
  }

  window.selectAllTable = selectAllTable

  const checkRow = () => {
    if ($('.select-table-row:checked').length > 0) {
      $('#quick-action-form').removeClass('form-disabled')
      $('#quick-action-apply').removeClass('btn-gray').addClass('btn-primary')
    } else {
      $('#quick-action-form').addClass('form-disabled')
      $('#quick-action-apply').removeClass('btn-primary').addClass('btn-gray')
    }
  }

  window.checkRow = checkRow

  //reset table action form elements
  const resetActionButtons = () => {
    checkRow()
    if (document.getElementById('select-all-table') !== undefined && document.getElementById('select-all-table') !== null) {
      document.getElementById('select-all-table').checked = false
      document.getElementById('select-all-table').indeterminate = false
      $('#quick-action-form')[0].reset()
      $('#quick-actions').find('input, textarea, button, select').attr('disabled', 'disabled')
      $('#quick-action-form').find('select').select2('destroy').select2().val(null).trigger('change')
    }
  }

  window.resetActionButtons = resetActionButtons
  const initDatatable = ({ url, finalColumns, advanceFilter, drawCallback = undefined, orderColumn }) => {
    const data_table_limit = $('meta[name="data_table_limit"]').attr('content')

    window.renderedDataTable = $('#datatable').DataTable({
      processing: true,
      serverSide: true,
      autoWidth: false,
      responsive: true,
      fixedHeader: true,
      lengthMenu: [
        [5, 10, 15, 20, 25, 100, -1],
        [5, 10, 15, 20, 25, 100, 'All']
      ],
      order: orderColumn,
      pageLength: data_table_limit,
      language: {
        processing: window.translations.processing,
        search: window.translations.search,
        lengthMenu: window.translations.lengthMenu,
        info: window.translations.info,
        infoEmpty: window.translations.infoEmpty,
        infoFiltered: window.translations.infoFiltered,
        loadingRecords: window.translations.loadingRecords,
        zeroRecords: window.translations.zeroRecords,
        paginate: {
          first: window.translations.paginate.first,
          last: window.translations.paginate.last,
          next: window.translations.paginate.next,
          previous: window.translations.paginate.previous
        }
      },
      dom: '<"row align-items-center"><"table-responsive my-3 mt-3 mb-2 pb-1" rt><"row align-items-center data_table_widgets" <"col-md-6" <"d-flex align-items-center flex-wrap gap-3" l i>><"col-md-6" p>><"clear">',
      ajax: {
        type: 'GET',
        url: url,
        data: function (d) {
          d.search = {
            value: $('.dt-search').val()
          }
          d.filter = {
            column_status: $('#column_status').val()
          }
          if (typeof advanceFilter == 'function' && advanceFilter() !== undefined) {
            d.filter = { ...d.filter, ...advanceFilter() }
          }
        }
      },

      drawCallback: function () {
        if (laravel !== undefined) {
          window.laravel.initialize()
        }
        $('.select2').select2({
          width: '100%',
        })
        if (drawCallback !== undefined && typeof drawCallback == 'function') {
          drawCallback()
        }
      },
      columns: finalColumns
    })
  }
  window.initDatatable = initDatatable


  function formatCurrency(number, noOfDecimal, decimalSeparator, thousandSeparator, currencyPosition, currencySymbol) {
    // Convert the number to a string with the desired decimal places
    let formattedNumber = parseFloat(number).toFixed(noOfDecimal);


    // Split the number into integer and decimal parts
    let [integerPart, decimalPart] = formattedNumber.split('.')

    // Add thousand separators to the integer part
    integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, thousandSeparator)

    // Set decimalPart to an empty string if it is undefined
    decimalPart = decimalPart || ''

    // Construct the final formatted currency string
    let currencyString = ''

    if (currencyPosition === 'left' || currencyPosition === 'left_with_space') {
      currencyString += currencySymbol
      if (currencyPosition === 'left_with_space') {
        currencyString += ' '
      }
      currencyString += integerPart
      // Add decimal part and decimal separator if applicable
      if (noOfDecimal > 0) {
        currencyString += decimalSeparator + decimalPart
      }
    }

    if (currencyPosition === 'right' || currencyPosition === 'right_with_space') {
      // Add decimal part and decimal separator if applicable
      if (noOfDecimal > 0) {
        currencyString += integerPart + decimalSeparator + decimalPart
      }
      if (currencyPosition === 'right_with_space') {
        currencyString += ' '
      }
      currencyString += currencySymbol
    }

    return currencyString
  }

  // Enhanced currency formatting with word wrapping for long values
  function formatCurrencyWithWrapping(number, noOfDecimal, decimalSeparator, thousandSeparator, currencyPosition, currencySymbol) {
    const formatted = formatCurrency(number, noOfDecimal, decimalSeparator, thousandSeparator, currencyPosition, currencySymbol);

    // If the formatted string has more than 50 decimal places, break at 50 decimals
    const parts = formatted.split(decimalSeparator);
    if (parts.length === 2 && parts[1].length > 50) {
      const integerPart = parts[0];
      const decimalPart = parts[1];

      // Split decimal part into chunks of 50 characters
      let result = integerPart + decimalSeparator;
      for (let i = 0; i < decimalPart.length; i += 50) {
        if (i > 0) {
          result += '\n' + ' '.repeat(integerPart.length + decimalSeparator.length);
        }
        result += decimalPart.substring(i, i + 50);
      }

      return result;
    }

    return formatted;
  }

  window.formatCurrency = formatCurrency
  window.formatCurrencyWithWrapping = formatCurrencyWithWrapping

  window.confirmDelete = function (route, id, reloadPage = true) {
    Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert this!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: route,
          type: 'GET',
          data: {
            ids: id,
            _token: document.querySelector('meta[name="csrf-token"]').content
          },
          success: function (response) {
            Swal.fire(
              'Deleted!',
              'Record has been deleted.',
              'success'
            ).then((result) => {
              if (reloadPage) {
                location.reload();
              }
            });
          },
          error: function (xhr) {
            Swal.fire(
              'Error!',
              'An error occurred while deleting the record.',
              'error'
            );
            console.error(xhr.responseText);
          }
        });
      }
    });
  }
})()
