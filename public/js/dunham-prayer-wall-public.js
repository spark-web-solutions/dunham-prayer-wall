(function() {
    // Load Masonry grid
    var grid = jQuery('.prayer-grid').masonry({
        itemSelector: '.prayer-grid-item'
    });
    // Re-layout Masonry on image load
    grid.imagesLoaded().progress(function() {
        grid.masonry('layout');
    });
    
    // Monitor for modal show
    jQuery('a.dunham-submit-prayer-modal').on('click', function(e) { 
        jQuery('#dunham_submit_prayer_modal').addClass('active');
        e.preventDefault();
        return false;
    });
    
    // Modal close
    jQuery('.dunham_prayer_modal a.dunham_prayer_modal_close, .dunham_prayer_modal a.veil').on('click', function(e) {
        jQuery('#dunham_submit_prayer_modal').removeClass('active');
        jQuery('#dunham_prayer_wall_submit_request_messages').html('').removeClass('dunham-prayer-form-error');
        e.preventDefault();
        return false;
    });
    
    // AJAX form submission
    jQuery('#dunham_prayer_wall_submit_request_form').on('submit', function(e) {
        jQuery('#dunham_prayer_wall_submit_request_messages').html('').removeClass('dunham-prayer-form-error');
        let errors = 0;
        let form = jQuery(this);
        let form_data = {
            action: 'dunham_prayer_wall_submit_request'
        };
        jQuery(form).find('input[type!="submit"], select, textarea').each(function() {
            let is_error = false;
            if (jQuery(this).attr('type') == 'radio') {
                let input_name = jQuery(this).attr('name').replace(/\[|\]/g, function(m) {return '\\'+m;});
                is_error = 'undefined' !== typeof jQuery(this).attr('required');
                jQuery(form).find('input[name='+input_name+']').each(function() {
                    if (jQuery(this).prop('checked')) {
                        is_error = false;
                        form_data[input_name] = jQuery(this).val();
                    }
                });
            } else {
                form_data[jQuery(this).attr('name')] = jQuery(this).val();
                if ('undefined' !== typeof jQuery(this).attr('required') && jQuery(this).val() == '') {
                    is_error = true;
                }
            }
            if (is_error) {
                errors++;
                jQuery(this).parents('label').addClass('dunham-prayer-form-error');
            } else {
                jQuery(this).parents('label').removeClass('dunham-prayer-form-error');
            }
        });
        if (errors > 0) {
            jQuery('#dunham_prayer_wall_submit_request_messages').html('All fields are required unless otherwise indicated.').addClass('dunham-prayer-form-error');
        } else {
            // No errors - process submission
            jQuery.ajax({
                type: "POST",
                url: prayerRequests.ajaxurl,
                data: form_data,
                success: function (data) {
                    jQuery('#dunham_prayer_wall_submit_request_messages').html('Your request has been submitted successfully.');
                    window.setTimeout(function() {jQuery('.dunham_prayer_modal a.dunham_prayer_modal_close').click();}, 3000);
                },
                error: function (req, status, error) {
                    if ('' == error) {
                        error = 'An unknown error occurred. Please try again later.';
                    } else if ('undefined' !== typeof req.responseJSON.data) {
                        error += ': '+req.responseJSON.data;
                    }
                    jQuery('#dunham_prayer_wall_submit_request_messages').html(error).addClass('dunham-prayer-form-error');
                }
            });
        }
        
        e.preventDefault();
        return false;
    });
    
    // AJAX prayer tracking
    let praying = false;
    jQuery('a.prayer-counter').on('click', function(e) {
        let a = jQuery(this);
        let request = a.data('request-id');
        a.blur();
        if (praying) {
            return;
        }
        praying = true;
        var count = jQuery('#prayer_count_'+request).html();
        jQuery('#prayer_count_'+request).html('<svg class="prayer-spinner" viewBox="0 0 50 50"><circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle></svg>');
        jQuery.post(prayerRequests.ajaxurl, {
            action: 'dunham_prayer_wall_pray',
            id: request
        }, function(response) {
            if (response.success) {
                jQuery('#prayer_count_'+request).html(response.data.count);
            } else {
                jQuery('#prayer_count_'+request).html(count);
                alert(response.data);
            }
            praying = false;
        });
        
        e.preventDefault();
        return false;
    });

    // AJAX Pagination
    /*let page = 2;
    let more_requests = true;
    let requests_loading = false;
    jQuery(window).on('scroll', function () {
        if (more_requests && !requests_loading) {
            if (jQuery(document).height() - jQuery(this).height() - jQuery('#row-footer').height() - jQuery('#row-copyright').height() <= jQuery(this).scrollTop()+2000) {
                requests_loading = true;
                jQuery('#prayer_requests_loading').show();
                jQuery.ajax({
                    type: "POST",
                    url: prayerRequests.ajaxurl,
                    data: {
                        action: 'cw_pray_load_requests',
                        page: page
                    },
                    success: function (data) {
                        requests_loading = false;
                        jQuery('#prayer_requests_loading').hide();
                        if (data) {
                            page++;
                            var content = jQuery(data);
                            jQuery('#prayer_requests').append(content).masonry('appended', content);
                            jQuery('.prayer-grid').masonry();
                        } else {
                            more_requests = false;
                        }
                    },
                    error: function (req, status, error) {
                        alert(error)
                        requests_loading = false;
                        jQuery('#prayer_requests_loading').hide();
                    }
                });
            }
        }
    });*/
})();
