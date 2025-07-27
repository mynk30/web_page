
        <?php include './include/header.php'; ?>
   

    <!-- Hero Section -->
    <section class="contact-form-info">
      <div class="contact-overlay">
        <div class="container h-100">
          <div
            class="d-flex flex-column justify-content-center align-items-center text-center h-100"
          >
            <h1 class="display-4 fw-bold mb-4">CONTACT</h1>
            <p class="lead mb-4">
              We're here to help you with your tax and business needs. Whether
              you have a question, need advice, or want to book a consultation,
              feel free to reach out.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- contact-section   -->
    <div class="section get-contact-section">
      <div class="container">
        <div class="main-content">
          <div class="contact-wrapper">
            <section class="contact-info-section">
              <!-- <span class="contact-badge">CONTACT US</span> -->
              <h1 class="contact-heading">Get in Touch</h1>
              <p class="contact-intro">
                Have questions about our services? Need expert financial advice?
                Our team is ready to help you with any inquiries you might have.
              </p>

              <div class="contact-details">
                <div class="contact-detail-item">
                  <div class="contact-detail-icon">
                    <i class="fas fa-phone-alt"></i>
                  </div>
                  <div class="contact-detail-content">
                    <h3>Call Us</h3>
                    <p>+91-9530300195</p>
                  </div>
                </div>

                <div class="contact-detail-item">
                  <div class="contact-detail-icon">
                    <i class="fas fa-envelope"></i>
                  </div>
                  <div class="contact-detail-content">
                    <h3>Email Us</h3>
                    <p>prakash.jangidassociates@gmail.com</p>
                  </div>
                </div>

                <div class="contact-detail-item">
                  <div class="contact-detail-icon">
                    <i class="fas fa-map-marker-alt"></i>
                  </div>
                  <div class="contact-detail-content">
                    <h3>Visit Us</h3>
                    <p>B-1,Second floor utkarsh plaza near shanechar ji ka than,Jodhpur, Rajasthan</p>
                  </div>
                </div>

                <div class="contact-detail-item">
                  <div class="contact-detail-icon">
                    <i class="fas fa-clock"></i>
                  </div>
                  <div class="contact-detail-content">
                    <h3>Opening Hours</h3>
                    <p>Monday - Saturday: 11:00 AM - 7:00 PM</p>
                  </div>
                </div>
              </div>

              <div class="social-icon-link">
                <h3>Follow Us</h3>
                <div class="social-icons">
                  <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                  <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                  <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                  <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                </div>
              </div>
            </section>

            <section class="contact-form-section">
              <h2 class="form-title custom-heading custom-line">
                Send us a message
              </h2>

              <div class="form-message success" id="success-message">
                Thank you for contacting us! We have received your message and
                will get back to you shortly.
              </div>

              <div class="form-message error" id="error-message">
                Oops! There was a problem sending your message. Please try again
                later.
              </div>

              <form class="contact-form" id="contactForm" method="POST">
                <input type="hidden" name="forms" value="contact">
                <input type="hidden" name="ajax_submit" value="1">
                <div class="contact-form__row">
                  <div class="contact-form__group">
                    <label class="contact-form__label" for="firstName"> Full Name *</label>
                    <input class="contact-form__input" type="text" id="firstName" name="name" placeholder="Your first name" required/>
                    <span class="error-message" id="firstName-error">Please enter your first name</span>
                  </div>
                </div>

                <div class="contact-form__row">
                  <div class="contact-form__group">
                    <label class="contact-form__label" for="email">Email Address *</label>
                    <input class="contact-form__input" type="email" id="email" name="email" placeholder="Your email address" required/>
                    <span class="error-message" id="email-error">Please enter a valid email address</span>
                  </div>
                  <div class="contact-form__group">
                    <label class="contact-form__label" for="phone">Phone Number *</label>
                    <input class="contact-form__input" type="tel" id="phone" name="phone" placeholder="Your phone number"  required/>
                    <span class="error-message" id="phone-error">Please enter a valid phone number</span>
                  </div>
                </div>

                <div class="contact-form__group">
                  <label class="contact-form__label" for="subject">Subject *</label>
                  <select class="contact-form__select" id="subject" name="subject" required>
                    <option value="" disabled selected>Select a subject</option>
                    <option value="general">General Inquiry</option>
                    <option value="support">Customer Support</option>
                    <option value="billing">Billing Question</option>
                    <option value="partnership">Partnership Opportunity</option>
                    <option value="other">Other</option>
                  </select>
                  <span class="error-message" id="subject-error">Please select a subject</span>
                </div>

                <div class="contact-form__group">
                  <label class="contact-form__label" for="message">Your Message </label>
                  <textarea class="contact-form__textarea" id="message" name="message" placeholder="How can we help you?" ></textarea>
                  <span class="error-message" id="message-error" >Please enter your message</span>
                </div>
                <div class="contact-detail">
                <input type="hidden" name="forms" value="contact">
                  <button  class="contact-form__submit custom-btn"  type="submit"  id="contactSubmit"> SEND MESSAGE
                  <div class="spinner" id="submitSpinner"></div>
                  <i class="fas fa-paper-plane"></i>
                </button>
                </div>
                <p class="form-footer">
                  * Required fields. We'll get back to you as soon as possible.
                </p>
              </form>
            </section>
          </div>
        </div>
      </div>
    </div>

    <!-- google-map -->
     <section class="map">
     <div class="container">
    <div class="jodhpur-location-map-b1">
        <div class="map-container">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3578.0927246319354!2d73.02004531503315!3d26.278752183410273!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39418cf4aa9f0067%3A0x874119c2fe4f5676!2sShanichar%20Ji%20Ka%20Than!5e0!3m2!1sen!2sin!4v1651234567890!5m2!1sen!2sin" 
                width="100%" 
                height="300" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy" 
                
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </div>
</div>
</section>

    <!-- Footer -->
   
        <?php include './include/footer.php'; ?>
