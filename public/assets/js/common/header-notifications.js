
$('.notification_list').on('click', function () {
    notificationList();
});

$(document).on('click', '.notification_data', function (event) {
    event.stopPropagation();
})

function notificationList(type = '') {
    var url = NOTIFICATION_LIST_ENDPOINT;
    $.ajax({
        type: 'get',
        url: url,
        data: {
            'type': type
        },
        success: function (res) {
            $('.notification_data').html(res.data);
            getNotificationCounts();
            if (res.type == "markas_read") {
                notificationList();
            }
            $('.notify_count').removeClass('notification_tag').text('');
        }
    });
}

function setNotification(count) {
    if (Number(count) >= 100) {
        $('.notify_count').text('99+');
    }
}

function getNotificationCounts() {
    var url = NOTIFICATION_COUNT_ENDPOINT;

    $.ajax({
        type: 'get',
        url: url,
        success: function (res) {
            if (res.counts > 0) {
                $('.notify_count').addClass('notification_tag').text(res.counts);
                setNotification(res.counts);
                $('.notification_list span.dots').addClass('d-none')
                $('.notify_count').removeClass('d-none')
            } else {
                $('.notify_count').addClass('d-none')
                $('.notification_list span.dots').removeClass('d-none')
            }

            if (res.counts <= 0 && res.unread_total_count > 0) {
                $('.notification_list span.dots').removeClass('d-none')
            } else {
                $('.notification_list span.dots').addClass('d-none')
            }
        }
    });
}

getNotificationCounts();

$('.change-mode').on('click', function() {
    const element = $('body.theme-cyan');
    
    let newTheme = $(this).data('new_theme_mode');
    
    // Save to both sessionStorage and localStorage for consistency
    sessionStorage.setItem('theme_mode', newTheme);
    localStorage.setItem('data-bs-theme', newTheme);

    if (newTheme !== 'dark') {
        $(element).removeClass('menu_dark');
    } else {
        $(element).addClass('menu_dark');
    }
});

document.addEventListener('DOMContentLoaded', function () {
    let savedTheme = sessionStorage.getItem('theme_mode') || localStorage.getItem('data-bs-theme');
    
    if(savedTheme){
        const body = document.querySelector('body.theme-cyan');

        if(savedTheme === 'dark'){
            body.classList.add('menu_dark');
        } else {
            body.classList.remove('menu_dark');
        }

        document.querySelectorAll('.change-mode').forEach(link => {
            const newTheme = link.getAttribute('data-new_theme_mode');
            const iconCircle = link.querySelector('.icon-circle');
            const label = link.querySelector('.menu-info h4');

            if (newTheme === savedTheme) {
                iconCircle.classList.remove('bg-grey');
                iconCircle.classList.add('bg-green');
                if (!label.querySelector('span')) {
                    const activeSpan = document.createElement('span');
                    activeSpan.textContent = ' (Active)';
                    label.appendChild(activeSpan);
                }
            } else {
                iconCircle.classList.remove('bg-green');
                iconCircle.classList.add('bg-grey');
                const activeSpan = label.querySelector('span');
                if (activeSpan) activeSpan.remove();
            }
        });
    }
});