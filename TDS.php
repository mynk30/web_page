<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./assests/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./assests/fontawesome-free-6.7.2-web/css/all.min.css">
    <link rel="stylesheet" href="./assests/css/style.css">
    <title>TDS</title>
</head>

<body>
    <!-- Navigation -->
    <div>
        <?php include './include/header.php'; ?>
    </div>
    <!-- Hero Section for TDS -->
    <section class="hero-section-tds">
        <div class="hero-overlay-tds">
            <div class="container h-100 d-flex flex-column justify-content-center align-items-center text-center">
                <h1>Tax Deducted at Source (TDS)</h1>
                <p>
                    Understand how TDS works, check rates, due dates, and learn how to claim TDS refunds with our expert guidance.
                </p>
                <!-- <a href="#tds-calculator" class="btn">Calculate Your TDS</a> -->
            </div>
        </div>
    </section>

    <!-- tds-info-section  -->
    <section class="tds-info-section">
        <div class="container">
            <h2 class="section-title custom-heading middle-line">What is TDS?</h2>
            <div class="tds-info">
                <div class="info-card">
                    <div class="icon"><i class="fas fa-briefcase"></i></div>
                    <h3>TDS Concept</h3>
                    <p>TDS is a means of collecting income tax in India, under the Indian Income Tax Act of 1961. Any payment covered under these provisions shall be paid after deducting a prescribed percentage of tax.</p>
                </div>

                <div class="info-card">
                    <div class="icon"><i class="fas fa-calendar-alt"></i></div>
                    <h3>Due Dates</h3>
                    <p>TDS must be deposited with the government by the 7th of the following month. For March, the due date is April 30th. TDS returns must be filed quarterly.</p>
                </div>

                <div class="info-card">
                    <div class="icon"><i class="fa-solid fa-sack-dollar"></i></div>
                    <h3>TDS Refund</h3>
                    <p>If the TDS deducted is more than your total tax liability, you can claim a refund by filing your income tax return (ITR). The refund will be credited to your bank account.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- tds-rates-section  -->
    <section class="tds-rates-section">
        <div class="container">
            <h2 class="section-title custom-heading middle-line">TDS Rates for FY 2023-24 (AY 2024-25)</h2>
            <table class="tds-table">
                <thead>
                    <tr>
                        <th>Section</th>
                        <th>Nature of Payment</th>
                        <th>TDS Rate (%)</th>
                        <th>Threshold Limit (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td data-label="Section">192</td>
                        <td data-label="Nature of Payment">Salary</td>
                        <td data-label="TDS Rate (%)">As per slab rates</td>
                        <td data-label="Threshold Limit (₹)">Basic exemption limit</td>
                    </tr>
                    <tr>
                        <td data-label="Section">194</td>
                        <td data-label="Nature of Payment">Dividend</td>
                        <td data-label="TDS Rate (%)">10</td>
                        <td data-label="Threshold Limit (₹)">5,000</td>
                    </tr>
                    <tr>
                        <td data-label="Section">194A</td>
                        <td data-label="Nature of Payment">Interest (other than securities)</td>
                        <td data-label="TDS Rate (%)">10</td>
                        <td data-label="Threshold Limit (₹)">40,000 (₹50,000 for seniors)</td>
                    </tr>
                    <tr>
                        <td data-label="Section">194C</td>
                        <td data-label="Nature of Payment">Contractor payments</td>
                        <td data-label="TDS Rate (%)">1 (individual/HUF) / 2 (others)</td>
                        <td data-label="Threshold Limit (₹)">30,000 (single) / 1,00,000 (aggregate)</td>
                    </tr>
                    <tr>
                        <td data-label="Section">194H</td>
                        <td data-label="Nature of Payment">Commission/Brokerage</td>
                        <td data-label="TDS Rate (%)">5</td>
                        <td data-label="Threshold Limit (₹)">15,000</td>
                    </tr>
                    <tr>
                        <td data-label="Section">194I</td>
                        <td data-label="Nature of Payment">Rent</td>
                        <td data-label="TDS Rate (%)">2 (land/building) / 10 (machinery/equipment)</td>
                        <td data-label="Threshold Limit (₹)">2,40,000</td>
                    </tr>
                    <tr>
                        <td data-label="Section">194J</td>
                        <td data-label="Nature of Payment">Professional/Technical services</td>
                        <td data-label="TDS Rate (%)">10</td>
                        <td data-label="Threshold Limit (₹)">30,000</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- TDS-Calculater  -->
    <section class="TDS-Calculater" id="tds-calculator">
        <div class="container">
            <h2 class="section-title custom-heading middle-line">TDS Calculator</h2>
            <div class="info-card" style="max-width: 600px; margin: 0 auto;">
                <h3>Calculate Your TDS Deduction</h3>
                <form id="tdsForm" style="margin-top: 1.5rem;">
                    <div style="margin-bottom: 1rem;">
                        <label for="paymentType" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Payment Type</label>
                        <select id="paymentType" style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="194">Dividend (Section 194)</option>
                            <option value="194A">Interest (Section 194A)</option>
                            <option value="194C">Contractor Payment (Section 194C)</option>
                            <option value="194H">Commission/Brokerage (Section 194H)</option>
                            <option value="194I">Rent (Section 194I)</option>
                            <option value="194J">Professional Fees (Section 194J)</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 1rem;">
                        <label for="amount" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Payment Amount (₹)</label>
                        <input type="number" id="amount" style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 4px;">
                    </div>

                    <button type="button" onclick="calculateTDS()" class="custom-btn calculater-btn " style="width: 100%;">Calculate TDS</button>

                    <div id="result" style="margin-top: 1.5rem; padding: 1rem; background-color: #f8f9fa; border-radius: 4px; display: none;">
                        <h4 style="margin-bottom: 0.5rem;">TDS Calculation Result</h4>
                        <p id="tdsAmount"></p>
                        <p id="netPayment"></p>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- FAQ-Section  -->
    <section class="FAQ-section">
        <div class="container">
            <h2 class="section-title custom-heading middle-line">Frequently Asked Questions</h2>
            <div class="faq-container">
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        What is the difference between TDS and TCS?
                        <span>+</span>
                    </div>
                    <div class="faq-answer">
                        <p>TDS (Tax Deducted at Source) is tax deducted by the payer when making specified payments like salary, interest, professional fees, etc. TCS (Tax Collected at Source) is tax collected by the seller at the time of sale of specified goods like timber, scrap, minerals, etc. The main difference is who collects the tax and at what stage.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        How can I check my TDS credit?
                        <span>+</span>
                    </div>
                    <div class="faq-answer">
                        <p>You can check your TDS credit through:</p>
                        <ol>
                            <li>Form 26AS available on the Income Tax e-filing portal</li>
                            <li>Annual TDS certificate (Form 16/16A) issued by the deductor</li>
                            <li>AIS (Annual Information Statement) on the e-filing portal</li>
                        </ol>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        What is the penalty for late TDS payment?
                        <span>+</span>
                    </div>
                    <div class="faq-answer">
                        <p>Late payment of TDS attracts interest under Section 201(1A):</p>
                        <ul>
                            <li>1% per month or part of the month from the due date to the date of deduction</li>
                            <li>1.5% per month or part of the month from the date of deduction to the date of payment</li>
                        </ul>
                        <p>There may also be penalties under Section 271H for late filing of TDS returns.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(this)">
                        Can I get a refund if excess TDS is deducted?
                        <span>+</span>
                    </div>
                    <div class="faq-answer">
                        <p>Yes, if the total TDS deducted is more than your total tax liability for the year, you can claim a refund by filing your income tax return (ITR). The excess amount will be refunded to your bank account after processing your return. The refund process typically takes 2-6 months from the date of filing.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>  

    <!-- Footer -->
    <div>
        <?php include './include/footer.php'; ?>
    </div>


    <script src="./assests/js/bootstrap.bundle.min.js"></script>
    <script src="./assests/js/script.js"></script>

</body>

</html>