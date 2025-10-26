# Tidio Chatbot Setup Guide for Izende Studio Web

## Step 1: Create Your Tidio Account

1. Go to **https://www.tidio.com**
2. Click "Sign Up Free"
3. Use your business email: **support@izendestudioweb.com**
4. Complete the signup process

## Step 2: Get Your Installation Code

1. After signing up, you'll be in the Tidio Dashboard
2. Go to **Settings** → **Channels** → **Live Chat**
3. Look for the "Installation" section
4. You'll see a code snippet that looks like:
   ```html
   <script src="//code.tidio.co/abcdefghijklmnopqrstuvwxyz123456.js" async></script>
   ```
5. Copy the code between `//code.tidio.co/` and `.js`
   - Example: If the script is `//code.tidio.co/abc123xyz.js`, copy `abc123xyz`

## Step 3: Add Your Code to Your Site

1. Open the file: `/var/www/html/izendestudioweb/assets/includes/tidio-widget.php`
2. Replace `YOUR_TIDIO_CODE` with the code you copied
3. Save the file

**Example:**
```php
<!-- Before -->
<script src="//code.tidio.co/YOUR_TIDIO_CODE.js" async></script>

<!-- After -->
<script src="//code.tidio.co/abc123xyz.js" async></script>
```

## Step 4: Configure Your Chatbot

Back in your Tidio dashboard:

### Basic Settings
1. **Chatbot Name**: "Izende Assistant"
2. **Welcome Message**: "Hi! 👋 Looking for web design, hosting, or chatbot services? I'm here to help!"
3. **Color Theme**: Match your brand (suggest: #5cb874 green)

### Create Common Responses

Set up these automated responses:

**Q: "What services do you offer?"**
A: "We offer:
- 24/7 Chatbot Services ($797 setup + $147/month)
- Web Design & Development
- WordPress Design
- SEO Services
- Web Hosting
- Video Editing
- E-Commerce Solutions

Which interests you?"

**Q: "How much does a website cost?"**
A: "Our websites start at $1,500 for a professional 5-page business site. We also offer custom quotes based on your needs. Want a free quote?"

**Q: "Tell me about your chatbot service"**
A: "Our 24/7 Chatbot Service includes:
- Setup on your website ($797 one-time)
- Custom responses for your business
- Lead capture while you sleep
- Monthly updates & monitoring ($147/month)

Perfect for businesses that get questions after hours!"

**Q: "How do I get started?"**
A: "Easy! You can:
1. Book a free consultation: [link to book-consultation.php]
2. Get a free quote: [link to quote.php]
3. Call us: 314-312-6441
4. Email: support@izendestudioweb.com

What works best for you?"

### Set Business Hours

- **Online Hours**: Your actual availability (or set to "Always Available")
- **Offline Message**: "We're currently offline, but leave your email and we'll get back to you within 24 hours!"

### Enable Lead Capture

1. Turn on "Collect visitor emails"
2. Set trigger: "After 30 seconds of inactivity"
3. Message: "Want to get web tips and special offers? Drop your email!"

## Step 5: Test Your Chatbot

1. Visit your website: **https://izendestudioweb.com**
2. You should see the chat widget in the bottom right corner
3. Click it and test the responses
4. Make sure it looks good on mobile too

## Step 6: Monitor Performance

Check your Tidio Dashboard daily for:
- New conversations
- Leads captured
- Common questions (add these to your automated responses)
- Response times

## Pro Tips for Your Own Chatbot

1. **Add a CTA on your chatbot service page**: "See it in action? Click the chat widget!"
2. **Screenshot it for client demos**: Show real examples from your site
3. **Track ROI**: Count how many leads you capture vs. before
4. **Update responses weekly**: Add new FAQs as you get them

## Pricing Reminder

- **Your Cost**: Free plan supports 50 conversations/month (perfect to start)
- **Paid Plan**: $29/month for unlimited (only needed if you get lots of traffic)
- **Your Client Pricing**: $797 setup + $147/month (you make $118/month profit on paid plan, or $147/month profit on free plan)

## Next Steps

Once your chatbot is live:
1. ✅ Test it thoroughly
2. ✅ Take screenshots for your portfolio
3. ✅ Add a "See it in action" note on your chatbot service page
4. ✅ Start reaching out to clients!

---

**Need Help?**
- Tidio Support: https://www.tidio.com/support/
- Your Setup: Check `/var/www/html/izendestudioweb/assets/includes/tidio-widget.php`
