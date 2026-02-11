<!DOCTYPE html>
<html>
<head>
    <title>Thank You</title>
</head>
<body>
    <h2>Hello {{ $contact->fname }},</h2>

    <p>Thank you for sharing your contact details with us.</p>

    <p>We have successfully saved your information in our system.</p>

    <p>
        <strong>Mobile:</strong> {{ $contact->mobile }} <br>
        <strong>Email:</strong> {{ $contact->email ?? 'N/A' }}
    </p>

    <p>Our team will contact you soon.</p>

    <br>

    <p>Regards,<br>
    <strong>CRM Team</strong></p>
</body>
</html>
