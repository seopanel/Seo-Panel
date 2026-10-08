<ul class="nav navbar-nav" id="alert_noti_sec">
    <li class="dropdown">
    	<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-label="Notifications" aria-haspopup="true">
    		<i class="fas fa-bell" style="font-size: 16px;" aria-hidden="true"></i>
    		<span class="count" style="display: none;"></span>
    	</a>
    	<ul class="dropdown-menu dropdown-menu-right"></ul>
    </li>
</ul>

<script>
$(document).ready(function(){

    // keeps the bell's accessible name in sync with the live unread
    // count (e.g. "Notifications (3 unread)") for a screen-reader user,
    // since the count itself is only ever conveyed visually otherwise
    function update_notification_aria_label(count) {
    	var label = count > 0 ? 'Notifications (' + count + ' unread)' : 'Notifications';
    	$('#alert_noti_sec .dropdown-toggle').attr('aria-label', label);
    }

    // mirrors the same unread count onto the installed PWA's home
    // screen / taskbar icon via the Badging API, where supported -
    // feature-detected and purely additive, so it's a silent no-op on
    // browsers/platforms that don't implement it (support is currently
    // desktop Chrome/Edge only; not Android Chrome, and iOS Safari
    // support is inconsistent enough not to rely on)
    function update_app_badge(count) {
    	if (!('setAppBadge' in navigator)) return;
    	try {
    		if (count > 0) {
    			navigator.setAppBadge(count);
    		} else {
    			navigator.clearAppBadge();
    		}
    	} catch (e) {}
    }

    // updating the view with notifications using ajax
    function load_unseen_notification(view = '') {
    	$.ajax({
            url:"alerts.php",
            method:"POST",
            data:{view: view, 'sec': 'fetch_alerts'},
            dataType:"json",
            success:function(data) {
                if (view != 'yes') {
    				$('.dropdown-menu').html(data.notification);
                }

       			if(data.unseen_notification > 0) {
       				$('.count').show();
    				$('.count').html(data.unseen_notification);
       			}
       			update_notification_aria_label(data.unseen_notification);
       			update_app_badge(data.unseen_notification);
      		}
    	});
    }

    load_unseen_notification();

    $('.dropdown').on('shown.bs.dropdown', function () {
    	$('.count').html('');
    	$('.count').hide();
    	update_notification_aria_label(0);
    	update_app_badge(0);
    	load_unseen_notification('yes');
    })
    
    setInterval(function() {
       load_unseen_notification();;
    }, 100000);

});
</script>