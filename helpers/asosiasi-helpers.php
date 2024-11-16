<?php
function getMemberId() {
    // Try from hidden input first
    var memberId = $('#member_id').val();
    
    if (!memberId) {
        // Try from URL params
        var urlParams = new URLSearchParams(window.location.search);
        memberId = urlParams.get('id');
    }
    
    if (!memberId) {
        // Try from form data attribute
        memberId = $('#member-form').data('member-id');
    }

    console.log('Getting member ID:', memberId);
    return memberId;
}

// Lalu gunakan dalam form submit handler:
setTimeout(function() {
    var memberId = getMemberId();
    if (typeof AsosiasiSKP !== 'undefined' && typeof AsosiasiSKP.reloadTable === 'function' && memberId) {
        try {
            console.log('Reloading SKP table for member:', memberId);
            AsosiasiSKP.reloadTable(memberId);
        } catch (error) {
            console.error('Error reloading SKP table:', error);
        }
    }
    $submitButton.prop('disabled', false);
}, 500);