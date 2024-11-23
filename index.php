<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Physiowerkz</title>
    <link href="images/logo.png" rel="icon">
    <link rel="stylesheet" href="styles/styles1.css">
    <style>
        /* Our Services Section Styling */
    .services {
        text-align: center;
        margin: 40px 0;
    }

    .services h2 {
        font-size: 1.8em;
        margin-bottom: 20px;
        color: #2c3e50;
    }

    .service-list {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .service-item {
        border: 1px solid #bdc3c7;
        border-radius: 8px;
        overflow: hidden;
        transition: transform 0.3s;
        width: 150px;
        /* Adjust based on your layout preference */
    }

    .service-item img {
        width: 100%;
        height: auto;
    }

    .service-item p {
        margin: 10px 0;
        font-weight: bold;
        color: #34495e;
    }

    .service-item:hover {
        transform: scale(1.05);
        border-color: #3498db;
    }
    </style>
</head>

<body>
<?php include 'includes/header.php'; ?>
    <main>
        <div class="content">
            <h1>Welcome to Physiowerkz!</h1>
            <p>Check out our latest promotions:</p>
            <!-- Add your promotional content here -->
            <div class="promotion">
                <h2>Summer Special Discount</h2>
                <p>Get 20% off on all physiotherapy sessions booked this summer!</p>
                <p>Visit Us Now!</p>
            </div>
            <!-- Our Services Section -->
        <div class="services">
            <h2>Our Services</h2>
            <div class="service-list">
                <div class="service-item">
                    <a href="https://physiowerkz.com/index.php/body-mechanix-physiowerkz/">
                        <img src="images/body_mechanix.jpeg" alt="Body Mechanix">
                        <p>Body Mechanix</p>
                    </a>
                </div>
                <div class="service-item">
                    <a href="https://physiowerkz.com/index.php/scolio-x-physiowerkz/">
                        <img src="images/scolio_x.jpeg" alt="Scolio-X">
                        <p>Scolio-X</p>
                    </a>
                </div>
                <div class="service-item">
                    <a href="https://physiowerkz.com/index.php/sports-physiowerkz/">
                        <img src="images/sports.jpeg" alt="Sports">
                        <p>Sports</p>
                    </a>
                </div>
                <div class="service-item">
                    <a href="https://physiowerkz.com/index.php/mobifit/">
                        <img src="images/mobifit.jpeg" alt="MOBIFIT">
                        <p>MOBIFIT</p>
                    </a>
                </div>
                <div class="service-item">
                    <a href="https://physiowerkz.com/index.php/dra/">
                        <img src="images/dra.jpeg" alt="DRA">
                        <p>DRA</p>
                    </a>
                </div>
                <div class="service-item">
                    <a href="https://physiowerkz.com/index.php/infitnitum/">
                        <img src="images/infitnitum.jpeg" alt="inFITnitum">
                        <p>inFITnitum</p>
                    </a>
                </div>
            </div>
        </div>
        
    </main>
    <?php include 'includes/footer.php'; ?>
    <button class="contact-us-button" onclick="window.location.href='contact.php'">Contact Us</button>
</body>
</html>
