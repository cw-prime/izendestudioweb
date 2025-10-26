<?php
/**
 * 24/7 Chatbot Service Page
 */

$pageTitle = '24/7 Customer Service Chatbot | Izende Studio Web';
$metaDescription = 'Never miss a customer inquiry again. Our 24/7 chatbot answers questions instantly, captures leads, and works while you sleep. St. Louis chatbot installation services.';

require_once __DIR__ . '/../config/env-loader.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../config/cms-data.php';
require_once __DIR__ . '/../includes/SEOHelper.php';

// Get base path
$base_path = str_replace('/services', '', dirname($_SERVER['SCRIPT_NAME']));
if ($base_path === '/') $base_path = '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <?php
  SEOHelper::outputMetaTags('chatbot_service', [
      'page_title' => $pageTitle,
      'meta_description' => $metaDescription,
      'meta_keywords' => 'chatbot, customer service bot, lead capture, St. Louis chatbot, website chat, automated customer service',
      'og_title' => '24/7 Customer Service Chatbot - St. Louis',
      'og_description' => $metaDescription
  ]);
  ?>

  <?php include __DIR__ . '/../assets/includes/header-links.php'; ?>
</head>

<body>
  <?php include __DIR__ . '/../assets/includes/header.php'; ?>

  <main id="main">

    <!-- Page Title -->
    <section class="page-title" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 80px 0; color: white;">
      <div class="container text-center">
        <h1 style="font-size: 2.5rem; margin-bottom: 1rem;">24/7 Customer Service Chatbot</h1>
        <p style="font-size: 1.2rem; margin-bottom: 0;">Answer customer questions instantly. Capture leads while you sleep.</p>
      </div>
    </section>

    <!-- Main Content -->
    <section style="padding: 60px 0;">
      <div class="container">
        <div class="row">

          <!-- Main Content -->
          <div class="col-lg-8">

            <!-- Problem Statement -->
            <div style="background: #f8f9fa; padding: 30px; border-radius: 10px; margin-bottom: 40px;">
              <h2 style="color: #dc3545; margin-bottom: 20px;">Are You Losing Customers After Hours?</h2>
              <p style="font-size: 1.1rem; margin-bottom: 15px;">When someone visits your website at 9 PM, what happens?</p>
              <ul style="font-size: 1.05rem; line-height: 1.8;">
                <li>❌ They can't get answers to their questions</li>
                <li>❌ They fill out a contact form and wait... and wait...</li>
                <li>❌ They leave and go to your competitor</li>
                <li>❌ You lose the sale</li>
              </ul>
              <p style="font-size: 1.1rem; margin-top: 20px; margin-bottom: 0;"><strong>What if you could answer every question instantly, 24/7?</strong></p>
            </div>

            <!-- Solution -->
            <h2 style="margin-bottom: 20px;">That's What a Chatbot Does</h2>
            <p style="font-size: 1.05rem; line-height: 1.8;">A chatbot is like having an employee who never sleeps, never takes a break, and never gets tired. It sits on your website and instantly answers customer questions any time, day or night.</p>

            <div class="row" style="margin: 40px 0;">
              <div class="col-md-4 text-center mb-4">
                <i class="bi bi-clock" style="font-size: 3rem; color: #667eea;"></i>
                <h4 style="margin-top: 15px;">24/7 Availability</h4>
                <p>Never miss an inquiry, even at 2 AM</p>
              </div>
              <div class="col-md-4 text-center mb-4">
                <i class="bi bi-lightning" style="font-size: 3rem; color: #667eea;"></i>
                <h4 style="margin-top: 15px;">Instant Answers</h4>
                <p>Respond in seconds, not hours</p>
              </div>
              <div class="col-md-4 text-center mb-4">
                <i class="bi bi-person-check" style="font-size: 3rem; color: #667eea;"></i>
                <h4 style="margin-top: 15px;">Capture Leads</h4>
                <p>Get contact info automatically</p>
              </div>
            </div>

            <!-- How It Works -->
            <h2 style="margin: 40px 0 20px;">How It Works</h2>

            <div style="background: white; border-left: 4px solid #667eea; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
              <h4><span style="background: #667eea; color: white; padding: 5px 15px; border-radius: 50%; margin-right: 10px;">1</span> Customer Visits Your Site</h4>
              <p style="margin-bottom: 0;">A small chat bubble appears in the corner: "Hi! Have a question? I'm here to help."</p>
            </div>

            <div style="background: white; border-left: 4px solid #667eea; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
              <h4><span style="background: #667eea; color: white; padding: 5px 15px; border-radius: 50%; margin-right: 10px;">2</span> They Ask a Question</h4>
              <p style="margin-bottom: 0;">"What are your hours?" "How much does this cost?" "Do you offer X?"</p>
            </div>

            <div style="background: white; border-left: 4px solid #667eea; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
              <h4><span style="background: #667eea; color: white; padding: 5px 15px; border-radius: 50%; margin-right: 10px;">3</span> Instant Answer</h4>
              <p style="margin-bottom: 0;">The chatbot responds immediately with the right information.</p>
            </div>

            <div style="background: white; border-left: 4px solid #667eea; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
              <h4><span style="background: #667eea; color: white; padding: 5px 15px; border-radius: 50%; margin-right: 10px;">4</span> Capture Their Info</h4>
              <p style="margin-bottom: 0;">If they want to talk to you, the chatbot captures their name, email, and phone. You follow up when you're available.</p>
            </div>

            <!-- What You Get -->
            <h2 style="margin: 40px 0 20px;">What's Included</h2>
            <ul style="font-size: 1.05rem; line-height: 2;">
              <li>✅ <strong>Custom Setup</strong> - We install and configure the chatbot on your website</li>
              <li>✅ <strong>15-20 Q&A Pairs</strong> - Common questions your customers ask, with your answers</li>
              <li>✅ <strong>Lead Capture Form</strong> - Automatically collects visitor contact information</li>
              <li>✅ <strong>Mobile Optimized</strong> - Works perfectly on phones, tablets, and desktops</li>
              <li>✅ <strong>Conversation Dashboard</strong> - See all chats and leads in one place</li>
              <li>✅ <strong>Email Notifications</strong> - Get alerted when someone leaves their info</li>
              <li>✅ <strong>Monthly Updates</strong> - We update your Q&As whenever you need</li>
              <li>✅ <strong>Training & Support</strong> - We show you how to use it and answer your questions</li>
            </ul>

            <!-- Who It's For -->
            <h2 style="margin: 40px 0 20px;">Who This Is Perfect For</h2>
            <div class="row">
              <div class="col-md-6 mb-3">
                <div style="padding: 20px; background: #f8f9fa; border-radius: 8px; height: 100%;">
                  <h5>Service Businesses</h5>
                  <p style="margin-bottom: 0;">HVAC, plumbing, electrical, auto repair - answer "Do you service my area?" and "What are your rates?"</p>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <div style="padding: 20px; background: #f8f9fa; border-radius: 8px; height: 100%;">
                  <h5>Professional Services</h5>
                  <p style="margin-bottom: 0;">Lawyers, dentists, accountants - answer "Do you offer free consultations?" and "What do I need to bring?"</p>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <div style="padding: 20px; background: #f8f9fa; border-radius: 8px; height: 100%;">
                  <h5>Restaurants & Retail</h5>
                  <p style="margin-bottom: 0;">Answer "What are your hours?" "Do you deliver?" "Is X in stock?"</p>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <div style="padding: 20px; background: #f8f9fa; border-radius: 8px; height: 100%;">
                  <h5>E-Commerce</h5>
                  <p style="margin-bottom: 0;">Answer product questions, explain return policies, track orders</p>
                </div>
              </div>
            </div>

            <!-- Real Example -->
            <div style="background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%); padding: 30px; border-radius: 10px; margin: 40px 0;">
              <h3 style="margin-bottom: 20px;">Real Example</h3>
              <p style="font-style: italic; margin-bottom: 15px;">"We installed a chatbot on a local HVAC company's website. In the first month:</p>
              <ul style="font-size: 1.05rem;">
                <li>🎯 <strong>86 conversations</strong> (many after business hours)</li>
                <li>🎯 <strong>23 leads captured</strong> with contact info</li>
                <li>🎯 <strong>5 new customers</strong> booked (worth $4,200 in revenue)</li>
                <li>🎯 Paid for itself in the first week</li>
              </ul>
              <p style="margin-bottom: 0;"><strong>The owner's response:</strong> "I can't believe we waited this long. This thing works while I'm sleeping!"</p>
            </div>

            <!-- CTA -->
            <div class="text-center" style="margin: 50px 0;">
              <h3 style="margin-bottom: 20px;">Ready to Stop Missing Leads?</h3>
              <a href="<?php echo $base_path; ?>/book-consultation.php" class="btn btn-primary btn-lg" style="padding: 15px 40px; font-size: 1.2rem;">
                Schedule Free Demo
              </a>
              <p style="margin-top: 15px; color: #6c757d;">15-minute call · No obligation · See how it works</p>
            </div>

          </div>

          <!-- Sidebar -->
          <div class="col-lg-4">

            <!-- Pricing Box -->
            <div style="background: white; border: 2px solid #667eea; border-radius: 10px; padding: 30px; margin-bottom: 30px; position: sticky; top: 20px;">
              <h3 style="text-align: center; color: #667eea; margin-bottom: 20px;">Simple Pricing</h3>

              <div style="text-align: center; margin-bottom: 30px;">
                <div style="font-size: 3rem; font-weight: bold; color: #667eea;">$797</div>
                <div style="color: #6c757d;">One-time setup</div>
              </div>

              <div style="text-align: center; padding: 20px 0; border-top: 1px solid #eee; border-bottom: 1px solid #eee; margin-bottom: 30px;">
                <div style="font-size: 2rem; font-weight: bold; color: #667eea;">$147<span style="font-size: 1rem; color: #6c757d;">/month</span></div>
                <div style="color: #6c757d;">Hosting & updates</div>
              </div>

              <h5 style="margin-bottom: 15px;">What's Included:</h5>
              <ul style="list-style: none; padding-left: 0;">
                <li style="margin-bottom: 10px;">✅ Full setup & installation</li>
                <li style="margin-bottom: 10px;">✅ 15-20 Q&A pairs</li>
                <li style="margin-bottom: 10px;">✅ Lead capture forms</li>
                <li style="margin-bottom: 10px;">✅ Unlimited conversations</li>
                <li style="margin-bottom: 10px;">✅ Monthly updates</li>
                <li style="margin-bottom: 10px;">✅ Training & support</li>
              </ul>

              <a href="<?php echo $base_path; ?>/book-consultation.php" class="btn btn-primary btn-lg w-100" style="margin-top: 20px;">
                Get Started
              </a>

              <p style="text-align: center; margin-top: 15px; margin-bottom: 0; font-size: 0.9rem; color: #6c757d;">
                No long-term contract required
              </p>
            </div>

            <!-- FAQ -->
            <div style="background: #f8f9fa; padding: 25px; border-radius: 10px;">
              <h4 style="margin-bottom: 20px;">Common Questions</h4>

              <h6>How long does setup take?</h6>
              <p style="font-size: 0.95rem;">About 1 week from start to launch. Most of that time is us creating the Q&As for your business.</p>

              <h6>Can I update it myself?</h6>
              <p style="font-size: 0.95rem;">Yes! We show you how. Or we'll do it for you - updates included in monthly fee.</p>

              <h6>What if I need help?</h6>
              <p style="font-size: 0.95rem;">Just call or email us. We respond within 24 hours (usually same day).</p>

              <h6>Can it book appointments?</h6>
              <p style="font-size: 0.95rem; margin-bottom: 0;">It can capture appointment requests. Full calendar integration available as an add-on.</p>
            </div>

          </div>

        </div>
      </div>
    </section>

  </main>

  <?php include __DIR__ . '/../assets/includes/footer.php'; ?>

  <?php include __DIR__ . '/../assets/includes/scripts.php'; ?>

</body>
</html>
