# Tawk.to Live Chat Setup Guide

Complete setup guide for implementing Tawk.to live chat on the Izende Studio Web website.

**Last Updated:** 2025-10-15
**Next Review:** 2025-11-15

---

## Table of Contents

1. [Account Creation](#account-creation)
2. [Widget Installation](#widget-installation)
3. [Appearance Customization](#appearance-customization)
4. [Behavior Settings](#behavior-settings)
5. [Canned Responses](#canned-responses)
6. [Pre-Chat Form](#pre-chat-form)
7. [Chatbot Flows](#chatbot-flows)
8. [Mobile Apps](#mobile-apps)
9. [Integrations](#integrations)
10. [Analytics & Monitoring](#analytics--monitoring)
11. [Best Practices](#best-practices)
12. [Completion Checklist](#completion-checklist)

---

## Account Creation

### Step 1: Sign Up

1. Visit [tawk.to](https://www.tawk.to)
2. Click "Sign Up Free"
3. Enter business email: `support@izendestudioweb.com`
4. Create secure password
5. Verify email address

### Step 2: Create Property

1. Property Name: **Izende Studio Web**
2. Website URL: `https://izendestudioweb.com`
3. Select Industry: **Web Design & Development**
4. Time Zone: **America/Chicago (Central Time)**

---

## Widget Installation

### Implementation Method

The Tawk.to widget is already embedded in `/index.php` at line 863-874.

```javascript
<!--Start of Tawk.to Script-->
<script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/YOUR_PROPERTY_ID/YOUR_WIDGET_ID';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>
<!--End of Tawk.to Script-->
```

### Configuration Steps

1. Log in to Tawk.to dashboard
2. Navigate to **Administration** → **Channels** → **Chat Widget**
3. Click **Direct Chat Link**
4. Copy your unique Widget ID
5. Replace `YOUR_PROPERTY_ID/YOUR_WIDGET_ID` in index.php with your actual IDs
6. Save the file
7. Clear cache and test

### Testing

- Open website in incognito window
- Widget should appear in bottom-right corner
- Click widget to open chat interface
- Send test message from visitor side
- Check message appears in Tawk.to dashboard

---

## Appearance Customization

### Brand Colors

1. Go to **Administration** → **Chat Widget** → **Widget Appearance**
2. Set brand color: `#5cb874` (Izende green)
3. Upload logo: Use `assets/img/izende-T.png`
4. Widget position: **Bottom Right**
5. Offset: **20px** from bottom, **20px** from right

### Custom Styling

```css
/* Additional CSS for widget (if needed) */
#tawk-to-container {
    z-index: 999 !important;
}
```

### Welcome Message

Set in **Administration** → **Chat Widget** → **Widget Appearance** → **Bubble**

**Text:** "Hi! Need help with your website? Chat with us!"

---

## Behavior Settings

### Online/Offline Hours

**Business Hours:**
- Monday - Friday: 9:00 AM - 5:00 PM (Central Time)
- Saturday - Sunday: Offline

**Settings Path:** Administration → Operating Hours

### Trigger Behavior

**When to Show Widget:**
- ✅ Show immediately on page load
- ✅ Show after 10 seconds of inactivity
- ✅ Show when user scrolls 50% down page
- ❌ Don't show on exit intent (conflicts with lead magnet modal)

**Auto-Open Settings:**
- First-time visitors: After 15 seconds
- Returning visitors: Don't auto-open

### Visibility Rules

**Hide on Pages:**
- `/adminIzende/*` (admin area)

**Show on Priority Pages:**
- Homepage (`/`)
- `/quote.php`
- `/hosting.php`
- `/contact.php`

---

## Canned Responses

Pre-configured responses for common questions. Configure at **Shortcuts** in dashboard.

### General Greetings

**Shortcut:** `#hello`
```
Hi there! Thanks for reaching out to Izende Studio Web. I'm [Your Name], and I'm here to help you with your website needs. What can I assist you with today?
```

### Pricing Inquiry

**Shortcut:** `#pricing`
```
Great question! Our website packages start at $499 for a professional WordPress site. Hosting starts at just $4.99/month. Every project is custom-quoted based on your specific needs. Would you like me to get you a personalized quote? Or feel free to call us at +1 314-312-6441.
```

### Website Timeline

**Shortcut:** `#timeline`
```
Most websites are completed within 2-4 weeks, depending on complexity and your content readiness. We'll provide a detailed timeline during the consultation. Want to schedule a free consultation to discuss your project?
```

### Hosting Support

**Shortcut:** `#hosting`
```
We offer fast, reliable hosting with 99.9% uptime guarantee, free SSL, daily backups, and 24/7 support. Plans start at $4.99/month. Already a hosting client experiencing issues? Let me connect you with our technical team.
```

### After Hours

**Shortcut:** `#offline`
```
Thanks for your message! We're currently offline (business hours: Mon-Fri 9AM-5PM Central). We'll respond to your message first thing when we're back. For urgent matters, please call 314-312-6441 or email support@izendestudioweb.com.
```

### Lead Capture

**Shortcut:** `#email`
```
I'd love to send you more information! What's the best email address to reach you? And if you'd like, feel free to call us directly at 314-312-6441 to discuss your project.
```

---

## Pre-Chat Form

Collect visitor information before starting chat.

### Configuration

**Path:** Administration → Chat Widget → Pre-Chat Survey

**Enabled:** Yes (for offline hours only)

### Fields

1. **Name** (Required)
   - Placeholder: "Your Name"

2. **Email** (Required)
   - Placeholder: "your@email.com"
   - Validation: Email format

3. **Phone** (Optional)
   - Placeholder: "+1 314-312-6441"
   - Validation: Phone format

4. **Message** (Required)
   - Placeholder: "How can we help you?"
   - Type: Textarea

### Form Message

```
We're currently offline, but we'd love to hear from you!
Fill out this quick form and we'll get back to you within 24 hours.
```

---

## Chatbot Flows

### Basic Qualification Bot

**Trigger:** When operator is offline

**Flow:**

1. **Welcome**
   ```
   👋 Hi! Thanks for visiting Izende Studio Web.
   I'm here to help while our team is away.
   ```

2. **Intent Selection**
   ```
   What brings you here today?

   [Button: New Website]
   [Button: Hosting Support]
   [Button: Get a Quote]
   [Button: General Question]
   ```

3. **New Website Path**
   ```
   Great! We specialize in professional WordPress websites for businesses.
   Do you currently have a website?

   [Button: Yes, need redesign]
   [Button: No, starting fresh]
   ```

4. **Capture Contact**
   ```
   Perfect! Let me connect you with our team.
   What's your email address?

   [Input: Email]
   ```

5. **Phone Number**
   ```
   And a phone number where we can reach you?

   [Input: Phone]
   ```

6. **Confirmation**
   ```
   Thanks! We'll reach out within 24 hours.
   Or call us now at 314-312-6441.

   Want to explore our services while you wait?
   Visit: https://izendestudioweb.com/quote.php
   ```

---

## Mobile Apps

### iOS App

1. Download **Tawk.to Business** from App Store
2. Log in with dashboard credentials
3. Enable push notifications
4. Test receiving messages

### Android App

1. Download **Tawk.to Business** from Google Play
2. Log in with dashboard credentials
3. Enable push notifications
4. Test receiving messages

### Best Practices

- ✅ Enable notifications for instant response
- ✅ Set status to "away" when unavailable
- ✅ Use quick replies from mobile app
- ✅ Add multiple team members for coverage

---

## Integrations

### Google Analytics

**Path:** Administration → Integrations → Google Analytics

1. Enter GA4 Measurement ID: `G-JJ5VJ6SS5X`
2. Track chat events as conversions
3. Monitor visitor behavior before chat

### Email Notifications

**Path:** Administration → Notifications → Email

**Notify on:**
- ✅ New chat started
- ✅ New offline message
- ✅ Missed chat (no response in 2 minutes)

**Recipients:**
- `support@izendestudioweb.com`

### Slack (Optional)

Connect Tawk.to to Slack for team notifications.

1. Create #customer-chats channel in Slack
2. In Tawk.to: Administration → Integrations → Slack
3. Authorize and select channel
4. Test integration

---

## Analytics & Monitoring

### Key Metrics to Track

1. **Response Time**
   - Target: < 2 minutes average
   - Monitor in Dashboard → Analytics → Response Time

2. **Chat Volume**
   - Peak hours identification
   - Path: Dashboard → Analytics → Chat Volume

3. **Visitor Satisfaction**
   - Enable post-chat ratings
   - Path: Administration → Chat Widget → Post-Chat Survey

4. **Conversion Rate**
   - Chats that result in quotes/sales
   - Track manually or via CRM integration

### Weekly Reports

Enable weekly email reports:
- Path: Administration → Reports
- Send to: `support@izendestudioweb.com`
- Includes: Chat volume, response time, satisfaction scores

---

## Best Practices

### Response Guidelines

1. **Greeting**
   - Always introduce yourself by name
   - Ask how you can help

2. **Active Listening**
   - Acknowledge their concern
   - Ask clarifying questions
   - Avoid assumptions

3. **Professional Tone**
   - Friendly but professional
   - Use proper grammar and spelling
   - Match visitor's communication style

4. **Call to Action**
   - Every chat should end with next steps
   - Offer phone number: 314-312-6441
   - Direct to relevant page or form

### Common Pitfalls to Avoid

- ❌ Leaving visitors waiting without acknowledgment
- ❌ Copy-pasting long responses (personalize!)
- ❌ Forgetting to collect contact information
- ❌ Not following up on offline messages

### Security Considerations

- ✅ Never ask for passwords or credit card numbers in chat
- ✅ Use secure forms for sensitive information
- ✅ Verify client identity before discussing account details
- ✅ Enable IP blocking for spam/abuse

---

## Completion Checklist

Use this checklist to ensure proper setup:

### Initial Setup
- [ ] Tawk.to account created
- [ ] Property configured for izendestudioweb.com
- [ ] Widget code installed in index.php
- [ ] Property ID and Widget ID replaced in code
- [ ] Widget appears on website
- [ ] Test message sent and received

### Customization
- [ ] Brand color set to #5cb874
- [ ] Logo uploaded
- [ ] Widget position set (bottom-right, 20px offset)
- [ ] Welcome message configured
- [ ] Operating hours set (Mon-Fri 9AM-5PM Central)

### Features
- [ ] Pre-chat form enabled for offline hours
- [ ] Canned responses created (#hello, #pricing, #timeline, #hosting, #offline, #email)
- [ ] Basic chatbot flow configured
- [ ] Auto-trigger settings configured
- [ ] Visibility rules set

### Integrations
- [ ] Google Analytics connected (G-JJ5VJ6SS5X)
- [ ] Email notifications enabled
- [ ] Mobile apps downloaded and configured
- [ ] Slack integration (optional)

### Team Setup
- [ ] All operators added to dashboard
- [ ] Roles and permissions assigned
- [ ] Mobile app notifications tested
- [ ] Response time targets set

### Monitoring
- [ ] Weekly reports enabled
- [ ] Post-chat survey enabled
- [ ] Analytics dashboard bookmarked
- [ ] Team training completed

---

## Support & Resources

**Tawk.to Resources:**
- Knowledge Base: https://help.tawk.to
- Developer API: https://developer.tawk.to
- Status Page: https://status.tawk.to

**Internal Contact:**
- Questions: support@izendestudioweb.com
- Phone: +1 314-312-6441

---

**Document Status:** ✅ Complete
**Maintained By:** Izende Studio Web Team
**Version:** 1.0
