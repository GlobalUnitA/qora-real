$(document).ready(function() {

    $("input[name='income']").change(function () {

        $('#stock').html($(this).data('balance'));
        $('#stock-label').removeClass('d-none');
        $('#stock-label').addClass('d-block');
    });

    $('#depositForm').submit(function (event) {
        event.preventDefault();

        const income = $("input[name='income']:checked").val();
        const amount = $("input[name='amount']").val().trim();

        if (!income) {
            alertModal($('#msg_deposit_asset').data('label'));
            return;
        }

        if (!amount) {
            alertModal($('#msg_deposit_amount').data('label'));
            return;
        }

        const formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log(response);
                alertModal(response.message, response.url);
            },
            error: function(xhr, status, error) {
                console.log(error);
                if (xhr.status === 419) {
                    alertModal($('#msg_session_expried').data('label'), '/');
                    setTimeout(() => location.reload(), 2000);
                }
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    var errorMessage = '';

                    for (var field in errors) {
                        if (errors.hasOwnProperty(field)) {
                            errorMessage += errors[field].join('<br>');
                        }
                    }

                    alertModal(errorMessage.trim());
                } else {
                    alertModal(errorNotice);
                }
            }
        });
    });

});
