<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PRAKASH JANGID & ASSOCIATES
    </title>
    <link href="./assests/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./assests/fontawesome-free-6.7.2-web/css/all.min.css">
    <link rel="stylesheet" href="./assests/css/style.css">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="./assests/img/logo.png" alt="">
                <h1 class="brand-title mb-0">PRAKASH JANGID & ASSOCIATES</h1>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="./index.php">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#services" id="servicesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Services
                        </a>
                        <ul class="dropdown-menu no-radius" aria-labelledby="servicesDropdown">
                            <li><a class="dropdown-item" href="./TDS.php">Tax Deducted Source</a></li>
                            <li><a class="dropdown-item" href="./ITD.php">Income Tax Department</a></li>
                            <li><a class="dropdown-item" href="./GST.php">Goods and Services Tax</a></li>
                            <li><a class="dropdown-item" href="./MCA.php">Ministry of Corporate Affairs</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./Contact.php">Contact</a>
                    </li>
                </ul>
                <a href="#contact" class="btn btn-outline-light ms-lg-3">Login</a>
            </div>
        </div>
    </nav>

    <!-- hero-audit -->
    <section class="hero-audit">
        <div class="hero-audit-overlay">
            <div class="container h-100">
                <div class="d-flex flex-column justify-content-center align-items-center text-center h-100">
                    <h1 class="display-4 fw-bold mb-4">Trusted Audit Services for Smarter Business Decisions</h1>
                    <p class="lead mb-4">We provide reliable and transparent audit services that help you strengthen your business, ensure compliance, and build trust with your stakeholders.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- audit-benefits -->
    <section class="audit-benefits py-4 py-lg-5">
        <div class="container">
            <div class="row g-4 g-lg-5 align-items-center">

                <div class="col-12 col-lg-6 order-2 order-lg-1">
                    <div class="pe-lg-4 pe-xl-5">
                        <h2 class="display-6 display-lg-5 fw-bold custom-heading mb-3 mb-lg-4">
                            <span class="border-bottom border-3 border-secondary pb-1 pb-lg-2 d-inline-block">Why Choose</span>
                            <span class="d-block mt-2 ">Our Audit Services?</span>
                        </h2>

                        <p class="lead text-muted mb-4 mb-lg-5">
                            In today's complex business environment, audits are crucial for maintaining financial integrity
                            and regulatory compliance. Our approach goes beyond basic compliance to deliver real value.
                        </p>
                    </div>
                </div>

                <div class="col-12 col-lg-6 order-1 order-lg-2">
                    <div class="position-relative mb-4 mb-lg-0">
                        <img src="./assests/img/about.jpg" alt="Audit Process"
                            class="img-fluid rounded-3 shadow-lg w-100 h-auto">

                    </div>
                </div>
            </div>
        </div>
    </section>




    <!-- Services Offered -->

    <section class="bg-light py-5">
        <div class="container">
            <h2 class=" custom-heading middle-line text-center mb-5">Our Audit Services Include</h2>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="audit-card p-4 bg-white rounded shadow-sm h-100">
                        <h5><i class="fas fa-file-invoice-dollar fa-2x me-2 text-primary"></i> Financial Statement Audit</h5>
                        <p>Detailed review of your financial records to ensure accuracy and compliance.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="audit-card p-4 bg-white rounded shadow-sm h-100">
                        <h5><i class="fas fa-shield-alt fa-2x me-2 text-success"></i> Internal Audit</h5>
                        <p>Check your internal processes and systems to improve efficiency and reduce risks.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="audit-card p-4 bg-white rounded shadow-sm h-100">
                        <h5><i class="fas fa-balance-scale fa-2x me-2 text-warning"></i> Compliance Audit</h5>
                        <p>Ensure your business meets all regulatory and legal requirements.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="audit-card p-4 bg-white rounded shadow-sm h-100">
                        <h5><i class="fas fa-receipt fa-2x me-2 text-danger"></i> Tax Audit</h5>
                        <p>Prepare and review your tax documents to avoid penalties and ensure correct filing.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="audit-card p-4 bg-white rounded shadow-sm h-100">
                        <h5><i class="fas fa-tasks fa-2x me-2 text-info"></i> Special Purpose Audit</h5>
                        <p>Custom audits based on your business needs or investor requirements.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="audit-card p-4 bg-white rounded shadow-sm h-100">
                        <h5><i class="fas fa-cogs fa-2x me-2 text-secondary"></i> Operational Audit</h5>
                        <p>Analyze your day-to-day operations to improve productivity, cost control, and overall performance.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="custom-heading middle-line text-center mb-5">How Our Audits Help Your Business</h2>
            <div class="row g-4">
                <div class="col-md-6">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><i class="fa-solid fa-circle-check me-2"></i>Build trust with investors, banks, and partners.</li>
                        <li class="list-group-item"><i class="fa-solid fa-circle-check me-2"></i>Find hidden risks early and fix them.</li>
                        <li class="list-group-item"><i class="fa-solid fa-circle-check me-2"></i>Improve your financial management and decision-making.</li>
                        <li class="list-group-item"><i class="fa-solid fa-circle-check me-2"></i>Ensure you are following all necessary laws and standards.</li>
                        <li class="list-group-item"><i class="fa-solid fa-circle-check me-2"></i>Make your business stronger and more profitable.</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <img src="./assests/img/audit-1.jpg" class="img-fluid rounded" alt="Audit Services">
                </div>
            </div>
        </div>
    </section>

    <!------ form ------>

    <section id="contact" class="contact-section py-5">

        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center mb-5">
                    <h2 class="home-form custom-heading middle-line">Get In Touch</h2>
                    <p class="lead">Ready to take your financial management to the next level? Contact us today for a free consultation.</p>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-lg-6 mb-4 mb-lg-0">
                    <form class="common-contact-form" id="common-form1" action="php/insert.php" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="name" placeholder="Your Name" required>
                            </div>
                            <div class="col-md-6">
                                <input type="email" class="form-control" name="email" placeholder="Your Email" required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="subject" placeholder="Subject">
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="phone" placeholder="Moblie Number" required>
                            </div>
                            <div class="col-12">
                                <textarea class="form-control" rows="5" name="message" placeholder="Your Message"></textarea>
                            </div>
                            <div class="col-12">
                                <input type="hidden" name="forms" value="contact">
                                <button type="submit" class="custom-btn form-btn">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <h4 class="mb-4">Contact Information</h4>
                            <div class="d-flex mb-4">
                                <div class="me-3">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Address</h6>
                                    <p class="mb-0">B-1,Second floor utkarsh plaza near shanechar ji ka than,Jodhpur, Rajasthan</p>
                                </div>
                            </div>

                            <div class="d-flex mb-4">
                                <div class="me-3 ">
                                    <i class="fas fa-phone-alt"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Phone</h6>
                                    <p class="mb-0">+91-9530300195</p>
                                </div>
                            </div>

                            <div class="d-flex mb-4">
                                <div class="me-3 ">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Email</h6>
                                    <p class="mb-0">prakash.jangidassociates@gmail.com</p>
                                </div>
                            </div>

                            <div class="d-flex">
                                <div class="me-3 ">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Working Hours</h6>
                                    <p class="mb-0">Monday - Saturday: 11:00 AM - 7:00 PM</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>




    <!-- Footer -->
    <div>
        <?php include './include/footer.php'; ?>
    </div>

    <script src="./assests/js/script.js"></script>
    <script src="./assests/js/bootstrap.bundle.min.js"></script>
</body>

</html>