define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'Magento_Ui/js/model/messageList',
    'Magento_Customer/js/customer-data',
    'Magento_Customer/js/model/customer',
], function($, modal, messageList, customerData, customer) {
    var options = {
        type: 'popup',
        responsive: true,
        innerScroll: true,
        title: 'Notify me',
        buttons: [{
            text: $.mage.__('Submit'),
            class: '',
            click: function () {
                var name = $('#notify-me-name').val();
                var email = $('#notify-me-email').val();
                var productId = $('input[name=product_id]').val();
                var customerId = customerData.get('customer');
                // console.log(customerId);
                if (customer.isLoggedIn()) {
                    console.log('hihihi');
                    var customerModel = customerData.get('customerId');
                    if (customerModel().id) {
                        customerId = customerModel().id;
                        console.log(customerId);
                    }
                }

                if (!customerId && (!name || !email)) {
                    alert('Please enter your name and email address.');
                    return;
                }

                $.ajax({
                    url: 'notifystock/Notify/Save',
                    type: 'POST',
                    data: {
                        "customer_id": customerId,
                        "name": name,
                        "email": email,
                        "product_id": productId
                    },
                    success: function(data) {
                        console.log('Data passed successfully.');
                        alert('Your notification request has been submitted successfully!');
                        // You can perform additional actions here, such as showing a success message or updating the UI.
                    },
                    error: function(xhr, status, errorThrown) {
                        console.log('Error occurred while passing data via AJAX.');
                        alert('Error occurred while submitting the notification request.');
                        // You can handle the error scenario and display an error message if needed.
                    }
                });

                this.closeModal();
            }
        }, {
            text: $.mage.__('Close'),
            class: '',
            click: function () {
                this.closeModal();
            }
        }]
    };

    var popup = modal(options, $('#notification-popup'));
    $(document).on('click', '#notify-button', function() {
        if (customer.isLoggedIn()) {
            var productId = $('input[name=product_id]').val();
            var customerId = customerData.get('customer')().id;

            $.ajax({
                url: 'notifystock/Notify/Save',
                type: 'POST',
                data: {
                    "customer_id": customerId,
                    "product_id": productId
                },
                success: function(data) {
                    console.log('Data passed successfully.');
                    alert('Your notification request has been submitted successfully!');
                    // You can perform additional actions here, such as showing a success message or updating the UI.
                },
                error: function(xhr, status, errorThrown) {
                    console.log('Error occurred while passing data via AJAX.');
                    alert('Error occurred while submitting the notification request.');
                    // You can handle the error scenario and display an error message if needed.
                }
            });
        } else {
            popup.openModal();
        }
    });
});
