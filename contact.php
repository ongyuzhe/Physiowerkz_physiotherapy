<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Physiowerkz</title>
    <link href="images/logo.png" rel="icon">
    <link rel="stylesheet" href="styles/styles1.css">
    <style>
        /* Additional styles specific to Contact Us page */
        .contact-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
        }

        .contact-info {
            margin-bottom: 20px;
            text-align: left;
        }

        .contact-info h3 {
            margin-bottom: 10px;
            color: #333;
        }

        .contact-info p {
            margin: 0;
            line-height: 1.6;
        }

        .contact-info strong {
            font-weight: bold;
        }

        .contact-info .branch {
            margin-top: 20px;
        }

        .branch h4 {
            margin-bottom: 10px;
            color: #333;
        }

        .branch p {
            margin: 0;
            line-height: 1.6;
        }

    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>
    <main>
        <div class="contact-container">
            <h2>Our Contact</h2>

            <div class="contact-info">
                <h3>Our Clinic Information</h3>
                <p>Our clinic has 5 private treatment rooms with ample waiting space, a modern gym equipped with full mirrors and facilities for rehabilitation treatment and classes. We also have individual sections/stations for various tests and measurement work for our PMAT.</p>
                <p>Visit to our clinic is <strong>by appointment basis only</strong>. Kindly call or WhatsApp us for an appointment, be it Assessment or Physiotherapy session.</p>
                <a href="login.php" class="book-now-button">Book Now</a>
            </div>
            <hr>
            <div class="branch">
                <h4>Branches:</h4>

                <div>
                    <h5>Physiowerkz - HQ</h5>
                    <p><strong>Address:</strong><br>
                        A2-02-06 Publika Solaris Dutamas<br>
                        1 Jalan Dutamas 1<br>
                        50480 Kuala Lumpur, MALAYSIA</p>
                    <p><strong>WhatsApp:</strong> 012-2039390<br>
                        <strong>Tel:</strong> 03-6411 4611</p>
                    <p><strong>Operating Hours:</strong><br>
                        Monday, Wednesday, Friday: 10:30am – 7:30pm<br>
                        Tuesday: 7.00am – 7.30pm<br>
                        Saturday: 8:00am – 5:00pm<br>
                        Sunday & Thursday: Closed</p>
                    <p><strong>Email:</strong> admin@physiowerkz.com</p>
                </div>
                <hr>
                <div>
                    <h5>Physiowerkz - Soho Suites KLCC</h5>
                    <p><strong>Address:</strong><br>
                        B1-10-8 SOHO Suites KLCC,<br>
                        Jalan Perak, 50450 Kuala Lumpur</p>
                    <p><strong>WhatsApp:</strong> 010-2109390<br>
                        <strong>Tel:</strong> 03-4821 1554</p>
                    <p><strong>Operating Hours:</strong><br>
                        Monday – Friday: 9.45am – 6.45pm<br>
                        Wednesday & Weekends: Closed</p>
                    <p><strong>Email:</strong> admin@physiowerkz.com</p>
                </div>
                <hr>
                <div>
                    <h5>Physiowerkz - Decathlon KL City Centre</h5>
                    <p><strong>Address:</strong><br>
                        145, Level 1, Shoppes At Four Seasons Place,<br>
                        Jalan Ampang, Kuala Lumpur City Centre,<br>
                        50450 Kuala Lumpur</p>
                    <p><strong>Operating Hours:</strong><br>
                        Wednesday: 10:00am – 7:00pm<br>
                        Saturday: 8:30am – 5:30pm<br>
                        Other day(s): Closed</p>
                    <p><strong>Email:</strong> admin@physiowerkz.com</p>
                </div>
            </div>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>

    <button class="contact-us-button" onclick="window.location.href='contact.php'">Contact Us</button>

</body>

</html>

