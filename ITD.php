
<?php include './include/header.php' ?>
    <!-- hero-income-tax  -->
    <section class="hero-income-tax">
        <div class="hero-income-tax-overlay">
            <div class="container h-100">
                <div class="d-flex flex-column justify-content-center align-items-center text-center h-100">
                    <h1 class="display-4 fw-bold mb-4">Income Tax Department Services</h1>
                    <p class="lead mb-4">We offer complete income tax services to make sure everything is accurate, legal, and stress-free. With years of experience and a deep understanding of tax laws, we make the process simple and easy for you.</p>
                    <div class="d-flex gap-3 flex-wrap justify-content-center">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- main-section  -->
    <main class="py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-md-8">
                    <h2 class="custom-heading custom-line mb-4">Taxpayer Services</h2>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="service-card p-4">
                                <div class="service-icon">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </div>
                                <h4>File Your ITR</h4>
                                <p>File your Income Tax Return online quickly and securely through our e-Filing portal.</p>
                                <a href="https://eportal.incometax.gov.in/iec/foservices/#/login" class=" itd-btn custom-btn">File Now</a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="service-card p-4">
                                <div class="service-icon">
                                    <i class="fas fa-calculator"></i>
                                </div>
                                <h4>Tax Calculator</h4>
                                <p>Estimate your tax liability for the current financial year with our calculator.</p>
                                <a href="https://eportal.incometax.gov.in/iec/foservices/#/TaxCalc/calculator" class=" itd-btn custom-btn">Calculate Tax</a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="service-card p-4">
                                <div class="service-icon">
                                    <i class="fas fa-search-dollar"></i>
                                </div>
                                <h4>Track Refund</h4>
                                <p>Check the status of your income tax refund using your PAN and assessment year.</p>
                                <a href="https://eportal.incometax.gov.in/iec/foservices/#/login" class="itd-btn custom-btn">Track Now</a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="service-card p-4">
                                <div class="service-icon">
                                    <i class="fas fa-question-circle"></i>
                                </div>
                                <h4>Help & FAQs</h4>
                                <p>Find answers to common questions and get help with tax-related issues.</p>
                                <a href="#" class="itd-btn custom-btn">Get Help</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="quick-links mb-4">
                        <div class="quick-link-item">
                            <a href="https://eportal.incometax.gov.in/iec/foservices/#/login"><i class="fas fa-external-link-alt"></i> e-Filing Portal</a>
                        </div>
                        <div class="quick-link-item">
                            <a href="https://eportal.incometax.gov.in/iec/foservices/#/TaxCalc/calculator"><i class="fas fa-calendar-alt"></i> Tax Calendar</a>
                        </div>
                        <div class="quick-link-item">
                            <a href="https://www.incometax.gov.in/iec/foportal/sites/default/files/2024-04/taxpayer-charter-english.pdf"><i class="fas fa-book"></i> Taxpayer's Charter</a>
                        </div>
                        <div class="quick-link-item">
                            <a href="https://incometaxindia.gov.in/pages/acts/income-tax-act.aspx"><i class="fas fa-gavel"></i> Acts & Rules</a>
                        </div>
                        <div class="quick-link-item">
                            <a href="https://incometaxindia.gov.in/Documents/Direct%20Tax%20Data/Approved-version-Income-Tax-Return-Statistics-for-the-AY-2023-24.pdf"><i class="fas fa-chart-line"></i> Tax Statistics</a>
                        </div>
                        <div class="quick-link-item">
                            <a href="https://incometaxindia.gov.in/pages/press-releases.aspx"><i class="fas fa-newspaper"></i> Press Releases</a>
                        </div>
                        <div class="quick-link-item">
                            <a href="https://www.incometax.gov.in/iec/foportal/downloads"><i class="fas fa-download"></i> Forms & Downloads</a>
                        </div>
                    </div>

                    <div class="tax-calculator">
                        <h4 class="fw-bold custom-heading  mb-4">Tax Calculator</h4>
                        <form id="taxCalculatorForm">
                            <div class="mb-3">
                                <label for="income" class="form-label">Annual Income (₹)</label>
                                <input type="number" class="form-control" id="income" required>
                            </div>
                            <div class="mb-3">
                                <label for="ageGroup" class="form-label">Age Group</label>
                                <select class="form-select" id="ageGroup" required>
                                    <option value="below60">Below 60 years</option>
                                    <option value="60to80">60 to 80 years</option>
                                    <option value="above80">Above 80 years</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="deductions" class="form-label">Deductions (₹)</label>
                                <input type="number" class="form-control" id="deductions" value="0">
                            </div>
                            <button type="submit" class="tax-btn  custom-btn w-100">Calculate Tax</button>
                        </form>
                        <div id="taxResult" class="mt-3 p-3 bg-light rounded d-none">
                            <h5>Tax Liability</h5>
                            <p id="taxAmount" class="fw-bold fs-4">₹0</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-5">
                <div class="col-12">
                    <h2 class="fw-bold custom-heading custom-line mb-4">Latest Updates</h2>
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="card h-100">
                                <img src="./assests/img/about.jpg" class="card-img-top" alt="ITR Extension">
                                <div class="card-body">
                                    <h5 class="card-title">ITR Filing Deadline Extended</h5>
                                    <p class="card-text">The due date for filing Income Tax Returns for FY 2022-23 has been extended to December 31, 2023.</p>
                                    <a href="#" class=" btn-sm custom-btn itd-btn">Read More</a>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <small class="text-muted">Posted on November 15, 2023</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <img src="./assests/img/about.jpg" class="card-img-top" alt="Tax Simplification">
                                <div class="card-body">
                                    <h5 class="card-title">New Tax Regime Simplified</h5>
                                    <p class="card-text">Government announces simplified tax structure under new regime with reduced rates and no deductions.</p>
                                    <a href="#" class="btn-sm custom-btn itd-btn">Read More</a>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <small class="text-muted">Posted on October 28, 2023</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <img src="./assests/img/about.jpg" .news-tickerclass="card-img-top" alt="Cyber Awareness">
                                <div class="card-body">
                                    <h5 class="card-title">Cyber Security Awareness</h5>
                                    <p class="card-text">Income Tax Department launches campaign to educate taxpayers about online fraud prevention.</p>
                                    <a href="#" class="btn-sm custom-btn itd-btn">Read More</a>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <small class="text-muted">Posted on October 15, 2023</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 24-25 -->
              <div class="row">
                <div class="col-12" >
                    <h2 class="fw-bold custom-heading custom-line mb-4">Tax Slabs for FY 2024-25</h2>
                    <div class="table-responsive">
                        <table class="table table-bordered tax-table">
                            <thead>
                                <tr>
                                    <th>Income Range (₹)</th>
                                    <th>Tax Rate (Old Regime)</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Up to Rs. 2.5 lakh</td>
                                    <td>Nil</td>
                                    
                                </tr>
                                <tr>
                                    <td>Rs. 2.5 lakh - Rs. 5 lakh</td>
                                    <td>5%</td>
                                </tr>
                                <tr>
                                    <td>Rs. 5 lakh - Rs. 10 lakh</td>
                                    <td>20%</td>
                                </tr>
                                <tr>
                                    <td>Rs. 10 lakh - Rs. 12 lakh</td>
                                    <td>30%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
                <!-- 24-25 new -->

                 <div class="row">
                <div class="col-12" >
                    <h2 class="fw-bold custom-heading custom-line mb-4">Tax Slabs for FY 2024-25</h2>
                    <div class="table-responsive">
                        <table class="table table-bordered tax-table">
                            <thead>
                                <tr>
                                    <th>Income Range (₹)</th>
                                    <th>Tax Rate (New Regime)</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Up to Rs. 3 lakh</td>
                                    <td>Nil</td>
                                    
                                </tr>
                                <tr>
                                    <td>Rs. 3 lakh - Rs. 7 lakh</td>
                                    <td>5%</td>
                                </tr>
                                <tr>
                                    <td>Rs. 7 lakh - Rs. 10 lakh</td>
                                    <td>10%</td>
                                </tr>
                                <tr>
                                    <td>Rs. 10 lakh - Rs. 12 lakh</td>
                                    <td>15%</td>
                                </tr>
                                <tr>
                                    <td>Rs. 12 lakh - Rs. 15 lakh</td>
                                    <td>20%</td>
                                </tr>
                                <tr>
                                    <td>Above Rs. 15 lakh</td>
                                    <td>30%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- 25-26 -->
            <!-- <div class="row">
                <div class="col-12" >
                    <h2 class="fw-bold custom-heading custom-line mb-4">Tax Slabs for FY 2025-26</h2>
                    <div class="table-responsive">
                        <table class="table table-bordered tax-table">
                            <thead>
                                <tr>
                                    <th>Income Range (₹)</th>
                                    <th>Tax Rate (New Regime)</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Up to Rs. 4 lakh</td>
                                    <td>Nil</td>
                                    
                                </tr>
                                <tr>
                                    <td>Rs. 4 lakh - Rs. 8 lakh</td>
                                    <td>5%</td>
                                </tr>
                                <tr>
                                    <td>Rs. 8 lakh - Rs. 12 lakh</td>
                                    <td>10%</td>
                                </tr>
                                <tr>
                                    <td>Rs. 12 lakh - Rs. 16 lakh</td>
                                    <td>15%</td>
                                </tr>
                                <tr>
                                    <td>Rs. 16 lakh - Rs. 20 lakh</td>
                                    <td>20%</td>
                                </tr>
                                <tr>
                                    <td>Rs. 20 lakh - Rs. 24 lakh</td>
                                    <td>25%</td>
                                </tr>
                                <tr>
                                    <td>Above Rs. 24 lakh</td>
                                    <td>30%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div> -->
        </div>
    </main>



    <!-- Footer -->

        <?php include './include/footer.php'; ?>
   