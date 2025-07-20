
  <!-- Navigation -->
 
    <?php include './include/header.php' ?>
 

  <!-- Hero Section -->
  <section class="hero-about">
    <div class="hero-overlay">
      <div class="container h-100 d-flex flex-column justify-content-center align-items-center text-center">
        <h1>Expert Financial Solutions</h1>
        <p>
          With over 15 years of experience in financial management and
          accounting, we provide expert solutions tailored to your specific
          needs.
        </p>
      </div>
    </div>
  </section>


  <!-- About Section -->
  <section class="about-section">
    <div class="container">
      <div class="section-header custom-heading middle-line">
        <h2>About Us</h2>
      </div>
      <div class="about-content">
        <div class="about-image">
          <img
            src="./assests/img/about-us.jpg"
            alt="Chartered Accountants Team" />
        </div>
        <div class="about-text">
          <h3>Who We Are</h3>
          <p>
            Chartered Accountants is a professionally managed firm offering
            expert services in audit, taxation, accounting, and financial
            consultancy. Backed by a team of experienced Chartered Accountants
            and advisors, we combine technical expertise with strategic
            insight to deliver tailored solutions.
          </p>
          <p>
            Our focus is on accuracy, integrity, and building lasting client
            relationships through proactive and personalized service. With
            over 15 years of experience, we've helped countless businesses
            optimize their financial operations and achieve their goals.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- Features Section -->
  <section class="features-section">
    <div class="container">
      <div class="section-header custom-heading middle-line">
        <h2>Why Choose Us</h2>
      </div>
      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon">
            <i class="fa-solid fa-check"></i>
          </div>
          <h3>Certified Professionals</h3>
          <p>
            Our team consists of highly qualified Chartered Accountants with
            extensive experience across various industries, ensuring expert
            guidance for all your financial needs.
          </p>
        </div>
        <div class="feature-card">
          <div class="feature-icon">
            <i class="fa-solid fa-check"></i>
          </div>
          <h3>Personalized Service</h3>
          <p>
            We believe in building lasting relationships through understanding
            each client's unique needs and providing customized financial
            solutions that align with their goals.
          </p>
        </div>
        <div class="feature-card">
          <div class="feature-icon">
            <i class="fa-solid fa-check"></i>
          </div>
          <h3>Expert Guidance</h3>
          <p>
            Our advisors provide strategic financial insights and
            recommendations that help businesses navigate complex financial
            landscapes and make informed decisions.
          </p>
        </div>
        <div class="feature-card">
          <div class="feature-icon">
            <i class="fa-solid fa-check"></i>
          </div>
          <h3>Client-Centered Approach</h3>
          <p>
            We prioritize your needs and objectives, maintaining clear
            communication and transparency throughout our engagement to ensure
            your complete satisfaction.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- Team Section -->
  <section class="team-section">
    <div class="container">
      <div class="section-header custom-heading middle-line">
        <h2>Our Expert Team</h2>
      </div>
      <div class="team-grid">
        <div class="team-member">
          <div class="team-photo">
            <img src="./assests/img/blank.jpg" alt="Team Member" />
          </div>
          <div class="team-info">
            <h3>Rajesh Kumar</h3>
            <p>Managing Partner</p>
            <div class="social-links">
              <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
              <a href="#"><i class="fa-brands fa-twitter"></i></a>
              <a href="#"><i class="fa-solid fa-envelope"></i></a>
            </div>
          </div>
        </div>
        <div class="team-member">
          <div class="team-photo">
            <img src="./assests/img/blank.jpg" alt="Team Member" />
          </div>
          <div class="team-info">
            <h3>Priya Sharma</h3>
            <p>Tax Specialist</p>
            <div class="social-links">
              <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
              <a href="#"><i class="fa-brands fa-twitter"></i></a>
              <a href="#"><i class="fa-solid fa-envelope"></i></a>
            </div>
          </div>
        </div>
        <div class="team-member">
          <div class="team-photo">
            <img src="./assests/img/blank.jpg" alt="Team Member" />
          </div>
          <div class="team-info">
            <h3>Anil Verma</h3>
            <p>Audit Director</p>
            <div class="social-links">
              <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
              <a href="#"><i class="fa-brands fa-twitter"></i></a>
              <a href="#"><i class="fa-solid fa-envelope"></i></a>
            </div>
          </div>
        </div>
        <div class="team-member">
          <div class="team-photo">
            <img src="./assests/img/blank.jpg" alt="Team Member" />
          </div>
          <div class="team-info">
            <h3>Sanjana Patel</h3>
            <p>Financial Advisor</p>
            <div class="social-links">
              <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
              <a href="#"><i class="fa-brands fa-twitter"></i></a>
              <a href="#"><i class="fa-solid fa-envelope"></i></a>
            </div>
          </div>
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
  
    <?php include './include/footer.php'; ?>
