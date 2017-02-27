//Author: Sam
$(document).ready(function() {
    var parkIds = [];
    $('.select').on('click', function(e) {
        e.preventDefault();
        var parkId = $(this).attr('data-id');
        if ($(this).hasClass('btn-success')) {
            $(this).removeClass('btn-success');
            $(this).text('Compare');
            parkIds.splice(parkIds.indexOf(parkId), 1);
            
        } else {
            if (parkIds.length != 2) {
                $(this).addClass('btn-success');
                $(this).text('Selected');

                parkIds.push(parkId);
            }
        }
        
        if (parkIds.length == 2) {

            $('#compare').attr('disabled', false);
            var url = '/compare?park1=' + parkIds[0] + '&park2=' + parkIds[1];
            $('#compare').attr('href', url);
        } else {
            $('#compare').attr('disabled', true);
        }
    });
});