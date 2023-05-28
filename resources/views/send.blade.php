<!DOCTYPE html>
<html>

<head>
    <title>SMS Application</title>
    <script src="{{ asset('js/jquery-3.7.0.min.js') }}"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        $(document).ready(function() {
            // Update the character count and page number on message input change
            $('#message').on('input', function() {
                var messageLength = $(this).val().length;
                var charactersPerPage = 160;
                var firstPageCharacters = 160;
                var otherPagesCharacters = 154;

                var pages = Math.ceil(messageLength / charactersPerPage);
                var currentPage = Math.ceil(messageLength / charactersPerPage);
                var charactersLeft;

                if (currentPage === 1) {
                    charactersLeft = firstPageCharacters - (messageLength % charactersPerPage);
                } else {
                    charactersLeft = otherPagesCharacters - (messageLength % charactersPerPage);
                }

                $('#pages').text('Pages: ' + pages);
                $('#characters-left').text('You have ' + charactersLeft + ' characters left on this page');
            });

            // Send SMS form submission
            $('#send-sms-form').submit(function(event) {
                event.preventDefault();

                var senderId = $('#sender-id').val();
                var recipients = $('#recipients').val();
                var message = $('#message').val();

                $.ajax({
                    url: '/send',
                    type: 'POST',
                    data: {
                        sender_id: senderId,
                        recipients: recipients,
                        message: message,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        // Display the summary/breakdown in an alert
                        var alertMessage =
                            'Recipient(s): ' + response.processRecipients.join(', ') + '\n' +
                            'Pages: ' + response.pages + '\n' +
                            'Sending message to: ' + response.unitCount + ' numbers' + '\n' +
                            'Total charge for all Units: ' + response.addAllUnitCharges + '\n' +
                            'Total Charge (units*pages): ' + response.totalCharge;

                        alert(alertMessage);
                    },
                    error: function(xhr, status, error) {
                        // Handle the error response
                        alert(
                            'An error occurred while sending the SMS. Please try again later.');
                    }
                });
            });
        });
    </script>
</head>

<body>
    <h1>SMS Application</h1>

    <form id="send-sms-form" method="POST">
        @csrf

        <div>
            <label for="sender-id">Sender ID:</label>
            <input type="text" id="sender-id" name="sender_id" required minlength="3" maxlength="11">
        </div>

        <div>
            <label for="recipients">Recipients:</label>
            <textarea id="recipients" name="recipients" required></textarea>
        </div>

        <div>
            <label for="message">Message:</label>
            <textarea id="message" name="message" rows="8" cols="25" required></textarea>
        </div>

        <div>
            <p id="pages"></p>
            <p id="characters-left"></p>
        </div>

        <div>
            <button type="submit">Send SMS</button>
        </div>
    </form>
</body>

</html>
